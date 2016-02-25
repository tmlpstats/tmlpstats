<?php namespace TmlpStats\Api;

// This is an API servicer. All API methods are simple static methods
// that take typed input and return array responses.
use App;
use TmlpStats\Http\Controllers;
use TmlpStats\Reports\Arrangements;
use TmlpStats\StatsReport;

class LocalReport
{
    public function getWeeklyPromises(StatsReport $statsReport)
    {
        $centerStatsData = App::make(Controllers\CenterStatsController::class)->getByStatsReport($statsReport);
        if (!$centerStatsData) {
            return null;
        }
        $a = App::make(Arrangements\GamesByWeek::class);
        $weeklyData = $a->compose($centerStatsData);
        return $weeklyData['reportData'];
    }

    // TODO decide if we want this to be a public API function. Right now it's not exactly API-valid
    public function getClassList(StatsReport $statsReport)
    {
        $teamMembers = App::make(Controllers\TeamMembersController::class)->getByStatsReport($statsReport);
        if (!$teamMembers) {
            return [];
        }
        return $teamMembers;
    }

    public function getClassListByQuarter(StatsReport $statsReport)
    {
        $teamMembers = self::getClassList($statsReport);

        $a = new Arrangements\TeamMembersByQuarter(['teamMembersData' => $teamMembers]);
        $data = $a->compose();
        return $data['reportData'];
    }
}
