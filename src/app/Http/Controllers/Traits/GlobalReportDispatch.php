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
trait GlobalReportDispatch
{
    public function newDispatch($action, $globalReport, $region)
    {
        $funcName = $this->dispatchFuncName($action);
        if (!$funcName) {
            // TODO FAIL
        }
        return $this->$funcName($globalReport, $region);
    }

    public function dispatchFuncName($action)
    {
        switch (strtolower($action)) {
            case 'ratingsummary':
                return 'getRatingSummary';
                break;
            case 'regionsummary':
                return 'getRegionSummary';
                break;
            case 'centerstatsreports':
                return 'getCenterStatsReports';
                break;
            case 'regionalstats':
                return 'getRegionalStats';
                break;
            case 'gamesbycenter':
                return 'getGamesByCenter';
                break;
            case 'repromisesbycenter':
                return 'getRepromisesByCenter';
                break;
            case 'regperparticipant':
                return 'getRegPerParticipant';
                break;
            case 'gaps':
                return 'getGaps';
                break;
            case 'accesstopowereffectiveness':
                return 'getAccessToPowerEffectiveness';
                break;
            case 'powertocreateeffectiveness':
                return 'getPowerToCreateEffectiveness';
                break;
            case 'team1expansioneffectiveness':
                return 'getTeam1ExpansionEffectiveness';
                break;
            case 'team2expansioneffectiveness':
                return 'getTeam2ExpansionEffectiveness';
                break;
            case 'gameintheworldeffectiveness':
                return 'getGameInTheWorldEffectiveness';
                break;
            case 'landmarkforumeffectiveness':
                return 'getLandmarkForumEffectiveness';
                break;
            case 'tmlpregistrationsoverview':
                return 'getTmlpRegistrationsOverview';
                break;
            case 'tmlpregistrationsbystatus':
                return 'getTmlpRegistrationsByStatus';
                break;
            case 'tmlpregistrationsbycenter':
                return 'getTmlpRegistrationsByCenter';
                break;
            case 'team2registeredatweekend':
                return 'getTeam2RegisteredAtWeekend';
                break;
            case 'tmlpregistrationsoverdue':
                return 'getTmlpRegistrationsOverdue';
                break;
            case 'coursesthisweek':
                return 'getCoursesThisWeek';
                break;
            case 'coursesnextmonth':
                return 'getCoursesNextMonth';
                break;
            case 'coursesupcoming':
                return 'getCoursesUpcoming';
                break;
            case 'coursescompleted':
                return 'getCoursesCompleted';
                break;
            case 'coursesguestgames':
                return 'getCoursesGuestGames';
                break;
            case 'coursessummary':
                return 'getCoursesSummary';
                break;
            case 'tdosummary':
                return 'getTdoSummary';
                break;
            case 'gitwsummary':
                return 'getGitwSummary';
                break;
            case 'teammemberstatusctw':
                return 'getTeamMemberStatusCtw';
                break;
            case 'teammemberstatustransfer':
                return 'getTeamMemberStatusTransfer';
                break;
            case 'teammemberstatuswithdrawn':
                return 'getTeamMemberStatusWithdrawn';
                break;
            case 'withdrawreport':
                return 'getWithdrawReport';
                break;
            case 'travelreport':
                return 'getTravelReport';
                break;
            case 'teammemberstatuspotentialsoverview':
                return 'getTeamMemberStatusPotentialsOverview';
                break;
            case 'teammemberstatuspotentials':
                return 'getTeamMemberStatusPotentials';
                break;
        }
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

    // Get report Repromises
    protected abstract function getRepromisesByCenter();

    // Get report Reg. Per Participant
    protected abstract function getRegPerParticipant();

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

    // Get report Transfers
    protected abstract function getTeamMemberStatusTransfer();

    // Get report Withdrawn
    protected abstract function getTeamMemberStatusWithdrawn();

    // Get report Withdraw Compliance
    protected abstract function getWithdrawReport();

    // Get report Travel Summary
    protected abstract function getTravelReport();

    // Get report Potentials Overview
    protected abstract function getTeamMemberStatusPotentialsOverview();

    // Get report Potentials Details
    protected abstract function getTeamMemberStatusPotentials();

}
