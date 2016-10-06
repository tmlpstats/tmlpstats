<?php
namespace TmlpStats\Api;

// This is an API servicer. All API methods are simple static methods
// that take typed input and return array responses.
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Domain;
use TmlpStats\Reports\Arrangements;

class LocalReport extends ApiBase
{
    public function getQuarterScoreboard(Models\StatsReport $statsReport, $options = [])
    {
        $cached = $this->checkCache($this->merge(compact('statsReport'), $options));
        if ($cached) {
            return $cached;
        }

        $returnUnprocessed = isset($options['returnUnprocessed']) ? (bool) $options['returnUnprocessed'] : false;
        $returnUnflattened = isset($options['returnUnflattened']) ? (bool) $options['returnUnflattened'] : false;
        $returnObject = array_get($options, 'returnObject', false);
        $includeOriginalPromise = isset($options['includeOriginalPromise']) ? (bool) $options['includeOriginalPromise'] : false;

        $scoreboardData = [];

        $originalPromise = null;

        $quarterStartDate = $statsReport->quarter->getQuarterStartDate($statsReport->center);

        // We are going to mutate this week value, so copy it.
        $week = $statsReport->quarter->getQuarterEndDate($statsReport->center)->copy();
        while ($week->gt($quarterStartDate)) {

            // Walk backwards through the quarter week by week and collect the promise/actual objects
            // from each official report as we find them
            // Take the newest version of each
            $report = Models\StatsReport::byCenter($statsReport->center)
                ->reportingDate($week)
                ->official()
                ->first();
            if ($report) {
                $weekData = $report->centerStatsData()->get();
                foreach ($weekData as $data) {

                    $dateStr = $data->reportingDate->toDateString();
                    if (!isset($scoreboardData[$dateStr][$data->type])) {
                        if ($data->type == 'promise' || $data->reportingDate->lte($statsReport->reportingDate)) {
                            $scoreboardData[$dateStr][$data->type] = $data;
                        }
                    }

                    // Keep searching until we find the very first promise
                    if ($includeOriginalPromise && $data->type == 'promise') {
                        $originalData = clone $data;
                        $originalData->type = 'original';
                        $scoreboardData[$dateStr]['original'] = $originalData;
                    }
                }
            }

            $week = $week->copy()->subWeek(); // Wasn't strictly necessary, but let's be safe with mutable dates
        }

        if ($returnUnprocessed) {
            if ($returnUnflattened) {
                $response = $scoreboardData;
            } else {
                $response = array_flatten($scoreboardData);
            }

            $this->putCache($response);

            return $response;
        }

        $a = new Arrangements\GamesByWeek();
        $weeks = $a->buildObject(array_flatten($scoreboardData));
        if ($returnObject) {
            return $weeks;
        } else {
            $weeksArray = $weeks->toArray();
            $this->putCache($weeksArray);

            return $weeksArray;
        }
    }

    public function getWeekScoreboard(Models\StatsReport $statsReport)
    {
        $cached = $this->checkCache(compact('statsReport'));
        if ($cached) {
            return $cached;
        }

        $scoreboardData = $this->getQuarterScoreboard($statsReport);

        $dateStr = $statsReport->reportingDate->toDateString();

        $response = [];
        if (isset($scoreboardData[$dateStr])) {
            $response = $scoreboardData[$dateStr];
        }

        $this->putCache($response);

        return $response;
    }

    public function getApplicationsList(Models\StatsReport $statsReport, $options = [])
    {
        $cached = $this->checkCache($this->merge(compact('statsReport'), $options));
        if ($cached) {
            return $cached;
        }

        $returnUnprocessed = isset($options['returnUnprocessed']) ? (bool) $options['returnUnprocessed'] : false;

        $registrations = Models\TmlpRegistrationData::byStatsReport($statsReport)
            ->with('registration.person', 'committedTeamMember.person')
            ->get();
        if (!$registrations) {
            return [];
        } else if ($returnUnprocessed) {
            $this->putCache($registrations);

            return $registrations;
        }

        $a = new Arrangements\TmlpRegistrationsByIncomingQuarter([
            'registrationsData' => $registrations,
            'quarter' => $statsReport->quarter,
        ]);
        $data = $a->compose();

        $this->putCache($data['reportData']);

        return $data['reportData'];
    }

    // TODO decide if we want this to be a public API function. Right now it's not exactly API-valid
    public function getClassList(Models\StatsReport $statsReport)
    {
        $teamMembers = Models\TeamMemberData::byStatsReport($statsReport)->with('teamMember.person')->get();
        if (!$teamMembers) {
            return [];
        }

        return $teamMembers;
    }

    public function getClassListByQuarter(Models\StatsReport $statsReport)
    {
        $teamMembers = self::getClassList($statsReport);

        $a = new Arrangements\TeamMembersByQuarter(['teamMembersData' => $teamMembers]);
        $data = $a->compose();

        return $data['reportData'];
    }

    public function getCourseList(Models\StatsReport $statsReport)
    {
        $courses = [];
        $courseData = Models\CourseData::byStatsReport($statsReport)->with('course')->get();
        foreach ($courseData as $data) {
            $courses[] = $data;
        }

        return $courses;
    }

    public function getLastStatsReportSince(Models\Center $center, Carbon $reportingDate, $flags=[])
    {
        $quarter = static::quarterForCenterDate($center, $reportingDate);

        $query = Models\StatsReport::byCenter($center)
            ->byQuarter($quarter)
            ->where('reporting_date', '<', $reportingDate)
            ->orderBy('reporting_date', 'desc');

        if (in_array('official', $flags)) {
            $query = $query->official();
        }

        return $query->first();
    }

    public static function getStatsReport(Models\Center $center, Carbon $reportingDate, $requireNew = true)
    {
        $quarter = static::quarterForCenterDate($center, $reportingDate);

        $query = Models\StatsReport::byCenter($center)
            ->byQuarter($quarter)
            ->reportingDate($reportingDate)
            ->orderBy('id', 'desc');
        if ($requireNew) {
            $query->submitted(false);
        }
        $report = $query->first();

        if (!$report) {
            $report = Models\StatsReport::create([
                'center_id' => $center->id,
                'quarter_id' => $quarter->id,
                'reporting_date' => $reportingDate->toDateTimeString(),
                'version' => 'api',
            ]);
        }

        return $report;
    }

    public function getCenterQuarter(Models\Center $center, Models\Quarter $quarter)
    {
        $quarter->setRegion($center->region);

        return Domain\CenterQuarter::fromModel($center, $quarter);
    }

    public static function quarterForCenterDate(Models\Center $center, Carbon $reportingDate)
    {
        $quarter = Models\Quarter::getQuarterByDate($reportingDate, $center->region);
        if (!$quarter) {
            throw new ApiExceptions\BadRequestException('Unable to find quarter which is required for fetching this data');
        }

        return $quarter;
    }

    public static function getReportingDate(Models\Center $center)
    {
        return $reportingDate = Carbon::parse('this friday', $center->timezone)->startOfDay();
    }
}
