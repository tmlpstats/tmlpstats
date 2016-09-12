<?php
namespace TmlpStats\Http\Controllers;

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

class ApiController extends ApiControllerBase
{
    protected $methods = [
        "Admin.Region.getRegion" => "Admin__Region__getRegion",
        "Application.create" => "Application__create",
        "Application.update" => "Application__update",
        "Application.allForCenter" => "Application__allForCenter",
        "Application.getWeekData" => "Application__getWeekData",
        "Application.stash" => "Application__stash",
        "Context.getCenter" => "Context__getCenter",
        "Context.setCenter" => "Context__setCenter",
        "Context.getSetting" => "Context__getSetting",
        "Course.create" => "Course__create",
        "Course.update" => "Course__update",
        "Course.allForCenter" => "Course__allForCenter",
        "Course.getWeekData" => "Course__getWeekData",
        "Course.stash" => "Course__stash",
        "GlobalReport.getRating" => "GlobalReport__getRating",
        "GlobalReport.getQuarterScoreboard" => "GlobalReport__getQuarterScoreboard",
        "GlobalReport.getWeekScoreboard" => "GlobalReport__getWeekScoreboard",
        "GlobalReport.getWeekScoreboardByCenter" => "GlobalReport__getWeekScoreboardByCenter",
        "GlobalReport.getApplicationsListByCenter" => "GlobalReport__getApplicationsListByCenter",
        "GlobalReport.getClassListByCenter" => "GlobalReport__getClassListByCenter",
        "GlobalReport.getCourseList" => "GlobalReport__getCourseList",
        "GlobalReport.getReportPages" => "GlobalReport__getReportPages",
        "LiveScoreboard.getCurrentScores" => "LiveScoreboard__getCurrentScores",
        "LiveScoreboard.setScore" => "LiveScoreboard__setScore",
        "LocalReport.getQuarterScoreboard" => "LocalReport__getQuarterScoreboard",
        "LocalReport.getWeekScoreboard" => "LocalReport__getWeekScoreboard",
        "LocalReport.getApplicationsList" => "LocalReport__getApplicationsList",
        "LocalReport.getClassList" => "LocalReport__getClassList",
        "LocalReport.getClassListByQuarter" => "LocalReport__getClassListByQuarter",
        "LocalReport.getCourseList" => "LocalReport__getCourseList",
        "LocalReport.getCenterQuarter" => "LocalReport__getCenterQuarter",
        "Scoreboard.allForCenter" => "Scoreboard__allForCenter",
        "Scoreboard.stash" => "Scoreboard__stash",
        "Scoreboard.getScoreboardLockQuarter" => "Scoreboard__getScoreboardLockQuarter",
        "Scoreboard.setScoreboardLockQuarter" => "Scoreboard__setScoreboardLockQuarter",
        "SubmissionCore.initSubmission" => "SubmissionCore__initSubmission",
        "SubmissionData.ignoreMe" => "SubmissionData__ignoreMe",
        "TeamMember.create" => "TeamMember__create",
        "TeamMember.update" => "TeamMember__update",
        "TeamMember.setWeekData" => "TeamMember__setWeekData",
        "TeamMember.allForCenter" => "TeamMember__allForCenter",
        "TeamMember.stash" => "TeamMember__stash",
        "TeamMember.bulkStashWeeklyReporting" => "TeamMember__bulkStashWeeklyReporting",
        "UserProfile.setLocale" => "UserProfile__setLocale",
        "ValidationData.validate" => "ValidationData__validate",
    ];

    protected $unauthenticatedMethods = [
        "LiveScoreboard__getCurrentScores",
    ];

    protected function Admin__Region__getRegion($input)
    {
        return App::make(Api\Admin\Region::class)->getRegion(
            $this->parse($input, 'region', 'Region')
        );
    }
    protected function Application__create($input)
    {
        return App::make(Api\Application::class)->create(
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Application__update($input)
    {
        return App::make(Api\Application::class)->update(
            $this->parse($input, 'application', 'Application'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Application__allForCenter($input)
    {
        return App::make(Api\Application::class)->allForCenter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date', false),
            $this->parse($input, 'includeInProgress', 'bool', false)
        );
    }
    protected function Application__getWeekData($input)
    {
        return App::make(Api\Application::class)->getWeekData(
            $this->parse($input, 'application', 'Application'),
            $this->parse($input, 'reportingDate', 'date', false)
        );
    }
    protected function Application__stash($input)
    {
        return App::make(Api\Application::class)->stash(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Context__getCenter($input)
    {
        return App::make(Api\Context::class)->getCenter(
        );
    }
    protected function Context__setCenter($input)
    {
        return App::make(Api\Context::class)->setCenter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'permanent', 'bool')
        );
    }
    protected function Context__getSetting($input)
    {
        return App::make(Api\Context::class)->getSetting(
            $this->parse($input, 'name', 'string'),
            $this->parse($input, 'center', 'Center')
        );
    }
    protected function Course__create($input)
    {
        return App::make(Api\Course::class)->create(
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Course__update($input)
    {
        return App::make(Api\Course::class)->update(
            $this->parse($input, 'course', 'Course'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Course__allForCenter($input)
    {
        return App::make(Api\Course::class)->allForCenter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date', false),
            $this->parse($input, 'includeInProgress', 'bool', false)
        );
    }
    protected function Course__getWeekData($input)
    {
        return App::make(Api\Course::class)->getWeekData(
            $this->parse($input, 'course', 'Course'),
            $this->parse($input, 'reportingDate', 'date', false)
        );
    }
    protected function Course__stash($input)
    {
        return App::make(Api\Course::class)->stash(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function GlobalReport__getRating($input)
    {
        return App::make(Api\GlobalReport::class)->getRating(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region')
        );
    }
    protected function GlobalReport__getQuarterScoreboard($input)
    {
        return App::make(Api\GlobalReport::class)->getQuarterScoreboard(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region')
        );
    }
    protected function GlobalReport__getWeekScoreboard($input)
    {
        return App::make(Api\GlobalReport::class)->getWeekScoreboard(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region'),
            $this->parse($input, 'futureDate', 'date', false)
        );
    }
    protected function GlobalReport__getWeekScoreboardByCenter($input)
    {
        return App::make(Api\GlobalReport::class)->getWeekScoreboardByCenter(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region'),
            $this->parse($input, 'options', 'array', false)
        );
    }
    protected function GlobalReport__getApplicationsListByCenter($input)
    {
        return App::make(Api\GlobalReport::class)->getApplicationsListByCenter(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region'),
            $this->parse($input, 'options', 'array', false)
        );
    }
    protected function GlobalReport__getClassListByCenter($input)
    {
        return App::make(Api\GlobalReport::class)->getClassListByCenter(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region'),
            $this->parse($input, 'options', 'array', false)
        );
    }
    protected function GlobalReport__getCourseList($input)
    {
        return App::make(Api\GlobalReport::class)->getCourseList(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region')
        );
    }
    protected function GlobalReport__getReportPages($input)
    {
        return App::make(Api\GlobalReport::class)->getReportPages(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region'),
            $this->parse($input, 'pages', 'array')
        );
    }
    protected function LiveScoreboard__getCurrentScores($input)
    {
        return App::make(Api\LiveScoreboard::class)->getCurrentScores(
            $this->parse($input, 'center', 'Center')
        );
    }
    protected function LiveScoreboard__setScore($input)
    {
        return App::make(Api\LiveScoreboard::class)->setScore(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'game', 'string'),
            $this->parse($input, 'type', 'string'),
            $this->parse($input, 'value', 'int')
        );
    }
    protected function LocalReport__getQuarterScoreboard($input)
    {
        return App::make(Api\LocalReport::class)->getQuarterScoreboard(
            $this->parse($input, 'localReport', 'LocalReport'),
            $this->parse($input, 'options', 'array', false)
        );
    }
    protected function LocalReport__getWeekScoreboard($input)
    {
        return App::make(Api\LocalReport::class)->getWeekScoreboard(
            $this->parse($input, 'localReport', 'LocalReport')
        );
    }
    protected function LocalReport__getApplicationsList($input)
    {
        return App::make(Api\LocalReport::class)->getApplicationsList(
            $this->parse($input, 'localReport', 'LocalReport'),
            $this->parse($input, 'options', 'array', false)
        );
    }
    protected function LocalReport__getClassList($input)
    {
        return App::make(Api\LocalReport::class)->getClassList(
            $this->parse($input, 'localReport', 'LocalReport')
        );
    }
    protected function LocalReport__getClassListByQuarter($input)
    {
        return App::make(Api\LocalReport::class)->getClassListByQuarter(
            $this->parse($input, 'localReport', 'LocalReport')
        );
    }
    protected function LocalReport__getCourseList($input)
    {
        return App::make(Api\LocalReport::class)->getCourseList(
            $this->parse($input, 'localReport', 'LocalReport')
        );
    }
    protected function LocalReport__getCenterQuarter($input)
    {
        return App::make(Api\LocalReport::class)->getCenterQuarter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'quarter', 'Quarter')
        );
    }
    protected function Scoreboard__allForCenter($input)
    {
        return App::make(Api\Scoreboard::class)->allForCenter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'includeInProgress', 'bool', false)
        );
    }
    protected function Scoreboard__stash($input)
    {
        return App::make(Api\Scoreboard::class)->stash(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Scoreboard__getScoreboardLockQuarter($input)
    {
        return App::make(Api\Scoreboard::class)->getScoreboardLockQuarter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'quarter', 'Quarter')
        );
    }
    protected function Scoreboard__setScoreboardLockQuarter($input)
    {
        return App::make(Api\Scoreboard::class)->setScoreboardLockQuarter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'quarter', 'Quarter'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function SubmissionCore__initSubmission($input)
    {
        return App::make(Api\SubmissionCore::class)->initSubmission(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date')
        );
    }
    protected function SubmissionData__ignoreMe($input)
    {
        return App::make(Api\SubmissionData::class)->ignoreMe(
            $this->parse($input, 'center', 'string'),
            $this->parse($input, 'timezone', 'string')
        );
    }
    protected function TeamMember__create($input)
    {
        return App::make(Api\TeamMember::class)->create(
            $this->parse($input, 'data', 'array')
        );
    }
    protected function TeamMember__update($input)
    {
        return App::make(Api\TeamMember::class)->update(
            $this->parse($input, 'teamMember', 'TeamMember'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function TeamMember__setWeekData($input)
    {
        return App::make(Api\TeamMember::class)->setWeekData(
            $this->parse($input, 'teamMember', 'TeamMember'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function TeamMember__allForCenter($input)
    {
        return App::make(Api\TeamMember::class)->allForCenter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'includeInProgress', 'bool', false)
        );
    }
    protected function TeamMember__stash($input)
    {
        return App::make(Api\TeamMember::class)->stash(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function TeamMember__bulkStashWeeklyReporting($input)
    {
        return App::make(Api\TeamMember::class)->bulkStashWeeklyReporting(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'updates', 'array')
        );
    }
    protected function UserProfile__setLocale($input)
    {
        return App::make(Api\UserProfile::class)->setLocale(
            $this->parse($input, 'locale', 'string'),
            $this->parse($input, 'timezone', 'string')
        );
    }
    protected function ValidationData__validate($input)
    {
        return App::make(Api\ValidationData::class)->validate(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date')
        );
    }
}
