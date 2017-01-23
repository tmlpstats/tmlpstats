<?php
namespace TmlpStats\Http\Controllers\Encapsulate;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Encapsulations;

class GlobalReportRegPerParticipantData
{
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
     * @return [type]
     */
    protected function getRegPerParticipant(Models\GlobalReport $globalReport, Models\Region $region, $returnRawData = false)
    {
        $reportData = $this->getScoreboardData($globalReport, $region);
        if (!$reportData) {
            return null;
        }

        $lastWeekDate = $globalReport->reportingDate->copy()->subWeek();
        $lastGlobalReport = $this->getGlobalReport($lastWeekDate);
        if (!$lastGlobalReport) {
            return null;
        }

        $lastWeekReportData = $this->getScoreboardData($lastGlobalReport, $region);

        $statsReportsAll = $globalReport->statsReports()
            ->validated()
            ->byRegion($region)
            ->get();

        $statsReports = [];
        foreach ($statsReportsAll as $report) {
            $centerName = $report->center->name;
            $statsReports[$centerName] = $report;
        }

        $games = ['cap', 'cpc', 'lf'];
        foreach ($reportData as $centerName => $centerData) {
            $reportData[$centerName]['statsReport'] = $statsReports[$centerName];
        }
        ksort($reportData);

        foreach ($reportData as $centerName => $centerData) {
            $participantCount = Models\TeamMemberData::byStatsReport($statsReports[$centerName])
                ->active()
                ->count();
            $totalWeekly = 0;
            $totalQuarterly = 0;
            foreach ($games as $game) {
                $change = 0;
                $rppWeekly = 0;
                $rppQuarterly = 0;
                if (isset($centerData['actual'])) {
                    $actual = $centerData['actual'][$game];
                    $totalQuarterly += $actual;
                    $rppQuarterly = $actual / $participantCount;

                    if ($lastWeekDate->eq($this->regionQuarter->startWeekendDate)) {
                        $lastWeekReportData[$centerName]['actual'][$game] = 0;
                    }

                    if (isset($lastWeekReportData[$centerName]['actual'])) {
                        $change = $actual - $lastWeekReportData[$centerName]['actual'][$game];
                        $totalWeekly += $change;
                        $rppWeekly = $change / $participantCount;
                    }
                }
                $reportData[$centerName]['change'][$game] = $change;
                $reportData[$centerName]['rpp']['week'][$game] = round($rppWeekly, 1);
                $reportData[$centerName]['rpp']['quarter'][$game] = round($rppQuarterly, 1);
            }
            $reportData[$centerName]['rpp']['week']['total'] = round($totalWeekly / $participantCount, 1);
            $reportData[$centerName]['rpp']['quarter']['total'] = round($totalQuarterly / $participantCount, 1);
        }

        if ($returnRawData) {
            return $reportData;
        }

        return view('globalreports.details.regperparticipant', compact('reportData', 'games'));
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
            $this->regionQuarter->startWeekendDate->copy()->addWeek(),
            $this->regionQuarter->endWeekendDate
        )->get();

        $initialData = [];
        foreach ($reports as $weekReport) {
            $dateStr = $weekReport->reportingDate->toDateString();
            $initialData[$dateStr] = $this->getRegPerParticipant($weekReport, $region, true);
        }

        $reportData = [];
        $dates = [];
        foreach ($initialData as $dateStr => $weekData) {
            if (!is_array($weekData)) {
                continue;
            }
            $dates[] = Carbon::parse($dateStr);
            foreach ($weekData as $centerName => $centerWeekData) {
                $reportData[$centerName][$dateStr] = $centerWeekData;
            }
        }

        return view('globalreports.details.rppweekly', [
            'reportData' => $reportData,
            'games' => ['cap', 'cpc', 'lf'],
            'dates' => $dates,
            'milestones' => $this->regionQuarter->datesAsArray(),
        ]);
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
