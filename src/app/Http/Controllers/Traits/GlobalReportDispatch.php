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

use App;
use TmlpStats\Api;
use TmlpStats\Http\Controllers\ApiControllerBase;
trait GlobalReportDispatch {
    public function newDispatch($action, $globalReport, $region) {
        $funcName = $this->dispatchFuncName($action);
        if (!$funcName) {
            // TODO FAIL
        }
        return $this->$funcName($globalReport, $region);
    }

    public function dispatchFuncName($action) {
        switch ($action) {
            case 'RatingSummary':
            case 'ratingsummary':
                return 'getRatingSummary';
                break;
            case 'RegionSummary':
            case 'regionsummary':
                return 'getRegionSummary';
                break;
            case 'RegionalStats':
            case 'regionalstats':
                return 'getRegionalStats';
                break;
            case 'GamesByCenter':
            case 'gamesbycenter':
                return 'getGamesByCenter';
                break;
            case 'RepromisesByCenter':
            case 'repromisesbycenter':
                return 'getRepromisesByCenter';
                break;
            case 'RegPerParticipant':
            case 'regperparticipant':
                return 'getRegPerParticipant';
                break;
            case 'Gaps':
            case 'gaps':
                return 'getGaps';
                break;
            case 'CenterStatsReports':
            case 'centerstatsreports':
                return 'getCenterStatsReports';
                break;
            case 'TmlpRegistrationsOverview':
            case 'tmlpregistrationsoverview':
                return 'getTmlpRegistrationsOverview';
                break;
            case 'TmlpRegistrationsByStatus':
            case 'tmlpregistrationsbystatus':
                return 'getTmlpRegistrationsByStatus';
                break;
            case 'TmlpRegistrationsByCenter':
            case 'tmlpregistrationsbycenter':
                return 'getTmlpRegistrationsByCenter';
                break;
            case 'Team2RegisteredAtWeekend':
            case 'team2registeredatweekend':
                return 'getTeam2RegisteredAtWeekend';
                break;
            case 'TmlpRegistrationsOverdue':
            case 'tmlpregistrationsoverdue':
                return 'getTmlpRegistrationsOverdue';
                break;
            case 'TravelReport':
            case 'travelreport':
                return 'getTravelReport';
                break;
            case 'CoursesThisWeek':
            case 'coursesthisweek':
                return 'getCoursesThisWeek';
                break;
            case 'CoursesNextMonth':
            case 'coursesnextmonth':
                return 'getCoursesNextMonth';
                break;
            case 'CoursesUpcoming':
            case 'coursesupcoming':
                return 'getCoursesUpcoming';
                break;
            case 'CoursesCompleted':
            case 'coursescompleted':
                return 'getCoursesCompleted';
                break;
            case 'CoursesGuestGames':
            case 'coursesguestgames':
                return 'getCoursesGuestGames';
                break;
            case 'TeamMemberStatusCtw':
            case 'teammemberstatusctw':
                return 'getTeamMemberStatusCtw';
                break;
            case 'TeamMemberStatusTransfer':
            case 'teammemberstatustransfer':
                return 'getTeamMemberStatusTransfer';
                break;
            case 'TeamMemberStatusWithdrawn':
            case 'teammemberstatuswithdrawn':
                return 'getTeamMemberStatusWithdrawn';
                break;
            case 'TdoSummary':
            case 'tdosummary':
                return 'getTdoSummary';
                break;
            case 'TeamMemberStatusPotentialsOverview':
            case 'teammemberstatuspotentialsoverview':
                return 'getTeamMemberStatusPotentialsOverview';
                break;
            case 'TeamMemberStatusPotentials':
            case 'teammemberstatuspotentials':
                return 'getTeamMemberStatusPotentials';
                break;
            case 'WithdrawReport':
            case 'withdrawreport':
                return 'getWithdrawReport';
                break;
        }
    }
    // Get report Ratings
    protected abstract function getRatingSummary();

    // Get report At A Glance
    protected abstract function getRegionSummary();

    // Get report Scoreboard
    protected abstract function getRegionalStats();

    // Get report By Center
    protected abstract function getGamesByCenter();

    // Get report Repromises
    protected abstract function getRepromisesByCenter();

    // Get report Reg. Per Participant
    protected abstract function getRegPerParticipant();

    // Get report Gaps
    protected abstract function getGaps();

    // Get report Center Reports
    protected abstract function getCenterStatsReports();

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

    // Get report Travel Summary
    protected abstract function getTravelReport();

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

    // Get report CTW
    protected abstract function getTeamMemberStatusCtw();

    // Get report Transfers
    protected abstract function getTeamMemberStatusTransfer();

    // Get report Withdrawn
    protected abstract function getTeamMemberStatusWithdrawn();

    // Get report Training & Development
    protected abstract function getTdoSummary();

    // Get report Overview
    protected abstract function getTeamMemberStatusPotentialsOverview();

    // Get report Details
    protected abstract function getTeamMemberStatusPotentials();

    // Get report Withdraws
    protected abstract function getWithdrawReport();

}
