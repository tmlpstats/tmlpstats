<?php
namespace TmlpStats\Http\Controllers\Encapsulate;

use App;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Encapsulations;

class GlobalReportRegPerParticipantData
{
    const GAMES = ['cap', 'cpc', 'lf'];

    private $globalReport;
    private $region;
    private $regionQuarter;

    protected $scoreboardData = [];
    protected $globalReports = [];

    public function __construct(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $this->globalReport = $globalReport;
        $this->region = $region;

        $this->regionQuarter = App::make(Api\Context::class)->getEncapsulation(Encapsulations\RegionQuarter::class, [
            'quarter' => Models\Quarter::getQuarterByDate($globalReport->reportingDate, $region),
            'region' => $region,
        ]);
    }

    /**
     * Get the scoreboard data by globalReport
     *
     * Cached to reduce queries
     *
     * @param  Models\GlobalReport $globalReport
     * @param  Models\Region       $region
     * @return [type]
     */
    protected function getScoreboardData(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $dateStr = $globalReport->reportingDate->toDateString();
        if (isset($this->scoreboardData[$dateStr])) {
            return $this->scoreboardData[$dateStr];
        }

        return $this->scoreboardData[$dateStr] = App::make(Api\GlobalReport::class)->getWeekScoreboardByCenter($globalReport, $region);
    }

    /**
     * Get global report by date
     *
     * Cached to reduce queries
     *
     * @param  Carbon $reportingDate
     * @return [type]
     */
    protected function getGlobalReport(Carbon $reportingDate)
    {
        $dateStr = $reportingDate->toDateString();
        if (isset($this->globalReports[$dateStr])) {
            return $this->globalReports[$dateStr];
        }

        return $this->globalReports[$dateStr] = Models\GlobalReport::reportingDate($reportingDate)->first();
    }

    /**
     * Get RPP for a single week
     *
     * @param  Models\GlobalReport $globalReport
     * @param  Models\Region       $region
     * @param  boolean             $returnRawData
     * @return array
     */
    protected function getRegPerParticipant(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $defaultGames = array_fill_keys(static::GAMES + ['total'], 0);
        $template = [
            'rpp' => [
                'net' => [
                    'week' => $defaultGames,
                    'quarter' => $defaultGames,
                ],
                'gross' => [
                    'week' => $defaultGames,
                    'quarter' => $defaultGames,
                ],
                'participantCount' => 0,
            ],
            'change' => $defaultGames,
        ];

        $data = $this->getRegPerParticipantData($globalReport, $region) ?? [];

        $reportData = [];
        foreach ($data as $centerName => $centerData) {

            $centerRpp = $template;

            $participantCount = $centerData['participantCount'] ?? 0;
            if (!$participantCount) {
                continue;
            }

            $totalWeeklyNetReg = $totalQuarterlyNetReg = $totalWeeklyGrossReg = $totalQuarterlyGrossReg = 0;
            foreach (static::GAMES as $game) {

                // Calculate Net Reg Per Participant
                $netReg = $centerData[$game]['netReg'];
                $lastWeekNetReg = $centerData[$game]['lastWeekNetReg'];

                $change = $netReg - $lastWeekNetReg;
                $totalWeeklyNetReg += $change;
                $totalQuarterlyNetReg += $netReg;

                $centerRpp['change'][$game] = $change;
                $centerRpp['net']['week'][$game] = round($change / $participantCount, 2);
                $centerRpp['net']['quarter'][$game] = round($netReg / $participantCount, 2);

                // Calculate Gross Reg Per Participant
                $grossReg = $centerData[$game]['grossReg'];
                $lastWeekGrossReg = $centerData[$game]['lastWeekGrossReg'];

                $totalWeeklyGrossReg += ($grossReg - $lastWeekGrossReg);
                $totalQuarterlyGrossReg += $grossReg;

                $centerRpp['gross']['week'][$game] = $grossReg;
                $centerRpp['gross']['quarter'][$game] = round($grossReg / $participantCount, 2);
            }
            $centerRpp['net']['week']['total'] = round($totalWeeklyNetReg / $participantCount, 2);
            $centerRpp['net']['quarter']['total'] = round($totalQuarterlyNetReg / $participantCount, 2);
            $centerRpp['gross']['week']['total'] = round($totalWeeklyGrossReg / $participantCount, 2);
            $centerRpp['gross']['quarter']['total'] = round($totalQuarterlyGrossReg / $participantCount, 2);

            $reportData[$centerName]['rpp'] = $centerRpp;
            $reportData[$centerName]['scoreboard'] = $centerData['scoreboard'] ?? [];
            $reportData[$centerName]['participantCount'] = $participantCount;
        }

        $games = static::GAMES;
        return compact('reportData', 'games');
    }

    /**
     * Get the reg-per-participant raw data
     *
     * @param  Models\GlobalReport $globalReport
     * @param  Models\Region       $region
     * @return array
     */
    protected function getRegPerParticipantData(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $scoreboards = $this->getScoreboardData($globalReport, $region);
        if (!$scoreboards) {
            return null;
        }

        $lastWeekDate = $globalReport->reportingDate->copy()->subWeek();
        $lastGlobalReport = $this->getGlobalReport($lastWeekDate);
        if (!$lastGlobalReport) {
            return null;
        }

        $lastWeekScoreboard = $this->getScoreboardData($lastGlobalReport, $region);

        $statsReports = $globalReport->statsReports()
            ->validated()
            ->byRegion($region)
            ->with('TeamMemberData', 'CourseData')
            ->get()
            ->keyBy(function ($report) {
                return $report->center->name;
            });

        $lastWeekStatsReports = $lastGlobalReport->statsReports()
            ->validated()
            ->byRegion($region)
            ->with('TeamMemberData', 'CourseData')
            ->get()
            ->keyBy(function ($report) {
                return $report->center->name;
            });

        $rawData = [];
        foreach ($scoreboards as $centerName => $centerData) {
            if (!isset($statsReports[$centerName])) {
                continue;
            }

            // Pre-fetch Gross Reg Data
            $grossReg = $this->calculateGrossReg($statsReports[$centerName]->courseData()->get());
            $lastWeekGrossReg = ['cap' => 0, 'cpc' => 0];
            if (isset($lastWeekStatsReports[$centerName])
                && $lastWeekDate->ne($this->regionQuarter->startWeekendDate)
            ) {
                // Only capture last week data if it's not the first week
                $lastWeekGrossReg = $this->calculateGrossReg($lastWeekStatsReports[$centerName]->courseData()->get());
            }

            $participantCount = $statsReports[$centerName]->teamMemberData()
                ->active()
                ->count();

            $rawData[$centerName]['participantCount'] = $participantCount;
            $rawData[$centerName]['scoreboard'] = $centerData->toArray();

            foreach (static::GAMES as $game) {

                $netReg = $centerData->game($game)->actual();
                if ($netReg === null) {
                    // No actual data for this week. skip it...
                    continue;
                }

                $lastWeekNetReg = 0;
                if (isset($lastWeekScoreboard[$centerName])
                    && $lastWeekDate->ne($this->regionQuarter->startWeekendDate)
                ) {
                    // Only capture last week data if it's not the first week
                    $lastWeekNetReg = $lastWeekScoreboard[$centerName]->game($game)->actual();
                }

                $gameData = [];

                $gameData['netReg'] = $netReg;
                $gameData['lastWeekNetReg'] = $lastWeekNetReg;

                // For LF, use the net as gross
                // LF withdraws don't count against the scoreboard, so gross and net are always the same
                $gameData['grossReg'] = $netReg;
                $gameData['lastWeekGrossReg'] = $lastWeekNetReg;

                if ($game === 'cap' || $game === 'cpc') {
                    $gameData['grossReg'] = $grossReg[$game];
                    $gameData['lastWeekGrossReg'] = $lastWeekGrossReg[$game];
                }

                $rawData[$centerName][$game] = $gameData;
            }
        }
        ksort($rawData);

        // Calculate totals
        $totals = [];
        foreach (static::GAMES as $game) {
            foreach (['netReg', 'lastWeekNetReg', 'grossReg', 'lastWeekGrossReg'] as $metric) {
                $totals[$game][$metric] = collect($rawData)->map(function ($item) use ($game, $metric) {
                    return $item[$game][$metric] ?? 0;
                })->sum();
            }

            foreach (['promise', 'actual'] as $type) {
                $totals['scoreboard']['games'][$game][$type] = collect($rawData)->map(function ($item) use ($game, $type) {
                    return $item['scoreboard']['games'][$game][$type] ?? 0;
                })->sum();
            }
        }
        $totals['participantCount'] = collect($rawData)->map(function ($item) {
            return $item['participantCount'] ?? 0;
        })->sum();

        $rawData['Total'] = $totals;

        return $rawData;
    }

    /**
     * Calculate Gross Registrations for CAP/CPC courses
     *
     * @param  Collection $coursesData Array of Models\CourseData object
     * @return array
     */
    protected function calculateGrossReg(Collection $coursesData)
    {
        $ter = $qTer = $cumXfer = $qCumXfer = ['cap' => 0, 'cpc' => 0];
        foreach ($coursesData as $course) {
            $game = strtolower($course->type);

            $ter[$game] += $course->currentTer;
            $qTer[$game] += $course->quarterStartTer;
            $cumXfer[$game] += $course->currentXfer;
            $qCumXfer[$game] += $course->quarterStartXfer;
        }

        $gross = ['cap' => 0, 'cpc' => 0];
        foreach ($gross as $game => $unused) {
            $gross[$game] = ($ter[$game] - $qTer[$game] - $cumXfer[$game] - $qCumXfer[$game]);
        }

        return $gross;
    }

    /**
     * Get RPP for all weeks so far in the quarter
     *
     * @param  Models\GlobalReport $globalReport
     * @param  Models\Region       $region
     * @return [type]
     */
    protected function getRegPerParticipantWeekly(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $reports = Models\GlobalReport::between(
            $this->regionQuarter->firstWeekDate,
            $this->regionQuarter->endWeekendDate
        )->get();

        $reportData = [];
        $dates = [];
        foreach ($reports as $weekReport) {
            $dateStr = $weekReport->reportingDate->toDateString();
            $dates[$dateStr] = $weekReport->reportingDate;

            $rpp = $this->getRegPerParticipant($weekReport, $region);
            foreach ($rpp['reportData'] as $centerName => $centerWeekData) {
                $reportData[$centerName][$dateStr] = $centerWeekData;
            }
        }

        return [
            'reportData' => $reportData,
            'games' => static::GAMES,
            'dates' => array_flatten($dates),
            'milestones' => $this->regionQuarter->datesAsArray(),
        ];
    }

    public function getOne($page)
    {
        $globalReport = $this->globalReport;
        $region = $this->region;

        switch (strtolower($page)) {
            case 'regperparticipantweekly':
                return $this->getRegPerParticipantWeekly($globalReport, $region);
            case 'regperparticipant':
                return $this->getRegPerParticipant($globalReport, $region);
            default:
                throw new \Exception("Unknown page {$page}");
        }
    }
}
