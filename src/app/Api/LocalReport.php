<?php
namespace TmlpStats\Api;

// This is an API servicer. All API methods are simple static methods
// that take typed input and return array responses.
use App;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Http\Controllers;
use TmlpStats\Reports\Arrangements;

class LocalReport extends ApiBase
{
    public function getQuarterScoreboard(Models\StatsReport $statsReport, $options = [])
    {
        $cached = $this->checkCache($this->merge(compact('statsReport'), $options));
        if (false && $cached) {
            return $cached;
        }

        $returnUnprocessed = isset($options['returnUnprocessed']) ? (bool) $options['returnUnprocessed'] : false;
        $returnUnflattened = isset($options['returnUnflattened']) ? (bool) $options['returnUnflattened'] : false;
        $includeOriginalPromise = isset($options['includeOriginalPromise']) ? (bool) $options['includeOriginalPromise'] : false;

        $scoreboardData = [];

        $originalPromise = null;

        $week = $statsReport->quarter->getQuarterEndDate($statsReport->center);
        while ($week->gt($statsReport->quarter->getQuarterStartDate($statsReport->center))) {

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

            $week->subWeek();
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

        $a = new Arrangements\GamesByWeek(array_flatten($scoreboardData));
        $centerStatsData = $a->compose();

        $this->putCache($centerStatsData['reportData']);

        return $centerStatsData['reportData'];
    }

    public function getWeekScoreboard(Models\StatsReport $statsReport)
    {
        $cached = $this->checkCache(compact('statsReport'));
        if (false && $cached) {
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
        if (false && $cached) {
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
        $teamMembers = App::make(Controllers\TeamMembersController::class)->getByStatsReport($statsReport);
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
}
