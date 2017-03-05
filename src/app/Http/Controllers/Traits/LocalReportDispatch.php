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
trait LocalReportDispatch {
    public function newDispatch($action, $statsReport) {
        $funcName = $this->dispatchFuncName($action);
        if (!$funcName) {
            // TODO FAIL
        }
        return $this->$funcName($statsReport);
    }

    public function dispatchFuncName($action) {
        switch ($action) {
            case 'Summary':
            case 'summary':
                return 'getSummary';
                break;
            case 'Overview':
            case 'overview':
                return 'getOverview';
                break;
            case 'CenterStats':
            case 'centerstats':
                return 'getCenterStats';
                break;
            case 'ClassList':
            case 'classlist':
                return 'getClassList';
                break;
            case 'GitwSummary':
            case 'gitwsummary':
                return 'getGitwSummary';
                break;
            case 'TdoSummary':
            case 'tdosummary':
                return 'getTdoSummary';
                break;
            case 'TmlpRegistrations':
            case 'tmlpregistrations':
                return 'getTmlpRegistrations';
                break;
            case 'TmlpRegistrationsByStatus':
            case 'tmlpregistrationsbystatus':
                return 'getTmlpRegistrationsByStatus';
                break;
            case 'Courses':
            case 'courses':
                return 'getCourses';
                break;
            case 'ContactInfo':
            case 'contactinfo':
                return 'getContactInfo';
                break;
            case 'PeopleTransferSummary':
            case 'peopletransfersummary':
                return 'getPeopleTransferSummary';
                break;
            case 'CoursesTransferSummary':
            case 'coursestransfersummary':
                return 'getCoursesTransferSummary';
                break;
            case 'TeamWeekendSummary':
            case 'teamweekendsummary':
                return 'getTeamWeekendSummary';
                break;
            case 'TeamTravelSummary':
            case 'teamtravelsummary':
                return 'getTeamTravelSummary';
                break;
            case 'NextQtrAccountabilities':
            case 'nextqtraccountabilities':
                return 'getNextQtrAccountabilities';
                break;
        }
    }
    // Get report Weekly Summary
    protected abstract function getSummary();

    // Get report Report Details
    protected abstract function getOverview();

    // Get report Center Games
    protected abstract function getCenterStats();

    // Get report Summary
    protected abstract function getClassList();

    // Get report GITW
    protected abstract function getGitwSummary();

    // Get report TDO
    protected abstract function getTdoSummary();

    // Get report By Team Year
    protected abstract function getTmlpRegistrations();

    // Get report By Status
    protected abstract function getTmlpRegistrationsByStatus();

    // Get report Courses
    protected abstract function getCourses();

    // Get report Contact Info
    protected abstract function getContactInfo();

    // Get report People
    protected abstract function getPeopleTransferSummary();

    // Get report Courses
    protected abstract function getCoursesTransferSummary();

    // Get report Team Summary
    protected abstract function getTeamWeekendSummary();

    // Get report Travel / Room
    protected abstract function getTeamTravelSummary();

    // Get report Next Quarter Accountabilities
    protected abstract function getNextQtrAccountabilities();

}
