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

trait LocalReportDispatch
{
    // NOTE these are lowercased for now to allow case insensitivity, may change soon.
    protected $dispatchMap = [
        'summary' => [
            'id' => 'Summary',
            'method' => 'getSummary',
        ],
        'overview' => [
            'id' => 'Overview',
            'method' => 'getOverview',
            'cacheTime' => 0,
        ],
        'centerstats' => [
            'id' => 'CenterStats',
            'method' => 'getCenterStats',
        ],
        'classlist' => [
            'id' => 'ClassList',
            'method' => 'getClassList',
        ],
        'gitwsummary' => [
            'id' => 'GitwSummary',
            'method' => 'getGitwSummary',
        ],
        'tdosummary' => [
            'id' => 'TdoSummary',
            'method' => 'getTdoSummary',
        ],
        'tmlpregistrations' => [
            'id' => 'TmlpRegistrations',
            'method' => 'getTmlpRegistrations',
        ],
        'tmlpregistrationsbystatus' => [
            'id' => 'TmlpRegistrationsByStatus',
            'method' => 'getTmlpRegistrationsByStatus',
        ],
        'courses' => [
            'id' => 'Courses',
            'method' => 'getCourses',
        ],
        'contactinfo' => [
            'id' => 'ContactInfo',
            'method' => 'getContactInfo',
        ],
        'peopletransfersummary' => [
            'id' => 'PeopleTransferSummary',
            'method' => 'getPeopleTransferSummary',
        ],
        'coursestransfersummary' => [
            'id' => 'CoursesTransferSummary',
            'method' => 'getCoursesTransferSummary',
        ],
        'teamweekendsummary' => [
            'id' => 'TeamWeekendSummary',
            'method' => 'getTeamWeekendSummary',
        ],
        'teamtravelsummary' => [
            'id' => 'TeamTravelSummary',
            'method' => 'getTeamTravelSummary',
        ],
        'nextqtraccountabilities' => [
            'id' => 'NextQtrAccountabilities',
            'method' => 'getNextQtrAccountabilities',
            'cacheTime' => 2,
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

    public function newDispatch($report, $statsReport)
    {
        $config = array_get($this->dispatchMap, strtolower($report), null);
        if (!$config) {
            throw new \Exception("Could not find report $report");
        }
        $funcName = $config['method'];

        return $this->$funcName($statsReport);
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
