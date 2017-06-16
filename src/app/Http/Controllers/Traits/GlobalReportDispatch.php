<?php
namespace TmlpStats\Http\Controllers\Traits;

///////////////////////////////
// THIS CODE IS AUTO-GENERATED
// do not edit this code by hand!
//
// To edit the resulting API code, instead edit config/reports.yml
// and then run the command:
//   php artisan reports:codegen
//
///////////////////////////////

trait GlobalReportDispatch
{
    // NOTE these are lowercased for now to allow case insensitivity, may change soon.
    protected $dispatchMap = [
        'ratingsummary' => [
            'id' => 'RatingSummary',
            'method' => 'getRatingSummary',
        ],
        'regionsummary' => [
            'id' => 'RegionSummary',
            'method' => 'getRegionSummary',
        ],
        'centerstatsreports' => [
            'id' => 'CenterStatsReports',
            'method' => 'getCenterStatsReports',
        ],
        'regionalstats' => [
            'id' => 'RegionalStats',
            'method' => 'getRegionalStats',
        ],
        'gamesbycenter' => [
            'id' => 'GamesByCenter',
            'method' => 'getGamesByCenter',
        ],
        'repromisesbycenter' => [
            'id' => 'RepromisesByCenter',
            'method' => 'getRepromisesByCenter',
        ],
        'regperparticipantweekly' => [
            'id' => 'RegPerParticipantWeekly',
            'method' => 'getRegPerParticipantWeekly',
        ],
        'gaps' => [
            'id' => 'Gaps',
            'method' => 'getGaps',
        ],
        'accesstopowereffectiveness' => [
            'id' => 'AccessToPowerEffectiveness',
            'method' => 'getAccessToPowerEffectiveness',
        ],
        'powertocreateeffectiveness' => [
            'id' => 'PowerToCreateEffectiveness',
            'method' => 'getPowerToCreateEffectiveness',
        ],
        'team1expansioneffectiveness' => [
            'id' => 'Team1ExpansionEffectiveness',
            'method' => 'getTeam1ExpansionEffectiveness',
        ],
        'team2expansioneffectiveness' => [
            'id' => 'Team2ExpansionEffectiveness',
            'method' => 'getTeam2ExpansionEffectiveness',
        ],
        'gameintheworldeffectiveness' => [
            'id' => 'GameInTheWorldEffectiveness',
            'method' => 'getGameInTheWorldEffectiveness',
        ],
        'landmarkforumeffectiveness' => [
            'id' => 'LandmarkForumEffectiveness',
            'method' => 'getLandmarkForumEffectiveness',
        ],
        'tmlpregistrationsoverview' => [
            'id' => 'TmlpRegistrationsOverview',
            'method' => 'getTmlpRegistrationsOverview',
        ],
        'tmlpregistrationsbystatus' => [
            'id' => 'TmlpRegistrationsByStatus',
            'method' => 'getTmlpRegistrationsByStatus',
        ],
        'tmlpregistrationsbycenter' => [
            'id' => 'TmlpRegistrationsByCenter',
            'method' => 'getTmlpRegistrationsByCenter',
        ],
        'team2registeredatweekend' => [
            'id' => 'Team2RegisteredAtWeekend',
            'method' => 'getTeam2RegisteredAtWeekend',
        ],
        'tmlpregistrationsoverdue' => [
            'id' => 'TmlpRegistrationsOverdue',
            'method' => 'getTmlpRegistrationsOverdue',
        ],
        'coursesthisweek' => [
            'id' => 'CoursesThisWeek',
            'method' => 'getCoursesThisWeek',
        ],
        'coursesnextmonth' => [
            'id' => 'CoursesNextMonth',
            'method' => 'getCoursesNextMonth',
        ],
        'coursesupcoming' => [
            'id' => 'CoursesUpcoming',
            'method' => 'getCoursesUpcoming',
        ],
        'coursescompleted' => [
            'id' => 'CoursesCompleted',
            'method' => 'getCoursesCompleted',
        ],
        'coursesguestgames' => [
            'id' => 'CoursesGuestGames',
            'method' => 'getCoursesGuestGames',
        ],
        'coursessummary' => [
            'id' => 'CoursesSummary',
            'method' => 'getCoursesSummary',
        ],
        'tdosummary' => [
            'id' => 'TdoSummary',
            'method' => 'getTdoSummary',
        ],
        'gitwsummary' => [
            'id' => 'GitwSummary',
            'method' => 'getGitwSummary',
        ],
        'teammemberstatusctw' => [
            'id' => 'TeamMemberStatusCtw',
            'method' => 'getTeamMemberStatusCtw',
        ],
        'teammemberstatuswbo' => [
            'id' => 'TeamMemberStatusWbo',
            'method' => 'getTeamMemberStatusWbo',
        ],
        'teammemberstatustransfer' => [
            'id' => 'TeamMemberStatusTransfer',
            'method' => 'getTeamMemberStatusTransfer',
        ],
        'teammemberstatuswithdrawn' => [
            'id' => 'TeamMemberStatusWithdrawn',
            'method' => 'getTeamMemberStatusWithdrawn',
        ],
        'withdrawreport' => [
            'id' => 'WithdrawReport',
            'method' => 'getWithdrawReport',
        ],
        'team1summarygrid' => [
            'id' => 'Team1SummaryGrid',
            'method' => 'getTeam1SummaryGrid',
        ],
        'team2summarygrid' => [
            'id' => 'Team2SummaryGrid',
            'method' => 'getTeam2SummaryGrid',
        ],
        'travelreport' => [
            'id' => 'TravelReport',
            'method' => 'getTravelReport',
        ],
        'teammemberstatuspotentialsoverview' => [
            'id' => 'TeamMemberStatusPotentialsOverview',
            'method' => 'getTeamMemberStatusPotentialsOverview',
        ],
        'teammemberstatuspotentials' => [
            'id' => 'TeamMemberStatusPotentials',
            'method' => 'getTeamMemberStatusPotentials',
        ],
        'acknowledgementreport' => [
            'id' => 'AcknowledgementReport',
            'method' => 'getAcknowledgementReport',
        ],
        'programsupervisor' => [
            'id' => 'ProgramSupervisor',
            'method' => 'getProgramSupervisor',
        ],
    ];

    public function getPageCacheTime($report)
    {
        $globalUseCache = env('REPORTS_USE_CACHE', true);
        if (!$globalUseCache) {
            return 0;
        }
        $config = array_get($this->dispatchMap, strtolower($report), []);
        $cacheTime = array_get($config, 'cacheTime', 60*24*7);

        return $cacheTime;
    }

    public function newDispatch($report, $globalReport, $region)
    {
        $config = array_get($this->dispatchMap, strtolower($report), null);
        if (!$config) {
            throw new \Exception("Could not find report $report");
        }
        $funcName = $config['method'];

        return $this->$funcName($globalReport, $region);
    }

    // Get report Ratings
    protected abstract function getRatingSummary();

    // Get report At A Glance
    protected abstract function getRegionSummary();

    // Get report Center Reports
    protected abstract function getCenterStatsReports();

    // Get report Scoreboard
    protected abstract function getRegionalStats();

    // Get report By Center
    protected abstract function getGamesByCenter();

    // Get report New Promises
    protected abstract function getRepromisesByCenter();

    // Get report Reg. Per Participant
    protected abstract function getRegPerParticipantWeekly();

    // Get report Gaps
    protected abstract function getGaps();

    // Get report CAP
    protected abstract function getAccessToPowerEffectiveness();

    // Get report CPC
    protected abstract function getPowerToCreateEffectiveness();

    // Get report T1X
    protected abstract function getTeam1ExpansionEffectiveness();

    // Get report T2X
    protected abstract function getTeam2ExpansionEffectiveness();

    // Get report GITW
    protected abstract function getGameInTheWorldEffectiveness();

    // Get report LF
    protected abstract function getLandmarkForumEffectiveness();

    // Get report Overview
    protected abstract function getTmlpRegistrationsOverview();

    // Get report By Status
    protected abstract function getTmlpRegistrationsByStatus();

    // Get report By Center
    protected abstract function getTmlpRegistrationsByCenter();

    // Get report T2 Reg. At Weekend
    protected abstract function getTeam2RegisteredAtWeekend();

    // Get report Overdue
    protected abstract function getTmlpRegistrationsOverdue();

    // Get report Completed This Week
    protected abstract function getCoursesThisWeek();

    // Get report Next 5 Weeks
    protected abstract function getCoursesNextMonth();

    // Get report Upcoming
    protected abstract function getCoursesUpcoming();

    // Get report Completed
    protected abstract function getCoursesCompleted();

    // Get report Guest Games
    protected abstract function getCoursesGuestGames();

    // Get report Summary
    protected abstract function getCoursesSummary();

    // Get report Training & Development
    protected abstract function getTdoSummary();

    // Get report GITW
    protected abstract function getGitwSummary();

    // Get report CTW
    protected abstract function getTeamMemberStatusCtw();

    // Get report WBI
    protected abstract function getTeamMemberStatusWbo();

    // Get report Transfers
    protected abstract function getTeamMemberStatusTransfer();

    // Get report Withdrawn
    protected abstract function getTeamMemberStatusWithdrawn();

    // Get report Withdraw Compliance
    protected abstract function getWithdrawReport();

    // Get report Team 1 Summary Grid
    protected abstract function getTeam1SummaryGrid();

    // Get report Team 2 Summary Grid
    protected abstract function getTeam2SummaryGrid();

    // Get report Travel Summary
    protected abstract function getTravelReport();

    // Get report Potentials Overview
    protected abstract function getTeamMemberStatusPotentialsOverview();

    // Get report Potentials Details
    protected abstract function getTeamMemberStatusPotentials();

    // Get report Acknowledgement Report
    protected abstract function getAcknowledgementReport();

    // Get report Program Supervisor
    protected abstract function getProgramSupervisor();

}
