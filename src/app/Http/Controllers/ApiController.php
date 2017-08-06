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
        "Admin.Quarter.filter" => "Admin__Quarter__filter",
        "Admin.System.allSystemMessages" => "Admin__System__allSystemMessages",
        "Admin.System.writeSystemMessage" => "Admin__System__writeSystemMessage",
        "Application.create" => "Application__create",
        "Application.allForCenter" => "Application__allForCenter",
        "Application.stash" => "Application__stash",
        "Context.getCenter" => "Context__getCenter",
        "Context.setCenter" => "Context__setCenter",
        "Context.getSetting" => "Context__getSetting",
        "Course.create" => "Course__create",
        "Course.allForCenter" => "Course__allForCenter",
        "Course.stash" => "Course__stash",
        "GlobalReport.getRating" => "GlobalReport__getRating",
        "GlobalReport.getQuarterScoreboard" => "GlobalReport__getQuarterScoreboard",
        "GlobalReport.getWeekScoreboard" => "GlobalReport__getWeekScoreboard",
        "GlobalReport.getWeekScoreboardByCenter" => "GlobalReport__getWeekScoreboardByCenter",
        "GlobalReport.getApplicationsListByCenter" => "GlobalReport__getApplicationsListByCenter",
        "GlobalReport.getClassListByCenter" => "GlobalReport__getClassListByCenter",
        "GlobalReport.getCourseList" => "GlobalReport__getCourseList",
        "GlobalReport.getReportPages" => "GlobalReport__getReportPages",
        "GlobalReport.getReportPagesByDate" => "GlobalReport__getReportPagesByDate",
        "GlobalReport.reportViewOptions" => "GlobalReport__reportViewOptions",
        "LiveScoreboard.getCurrentScores" => "LiveScoreboard__getCurrentScores",
        "LiveScoreboard.setScore" => "LiveScoreboard__setScore",
        "LocalReport.getQuarterScoreboard" => "LocalReport__getQuarterScoreboard",
        "LocalReport.getWeekScoreboard" => "LocalReport__getWeekScoreboard",
        "LocalReport.getApplicationsList" => "LocalReport__getApplicationsList",
        "LocalReport.getClassList" => "LocalReport__getClassList",
        "LocalReport.getClassListByQuarter" => "LocalReport__getClassListByQuarter",
        "LocalReport.getCourseList" => "LocalReport__getCourseList",
        "LocalReport.getCenterQuarter" => "LocalReport__getCenterQuarter",
        "LocalReport.reportViewOptions" => "LocalReport__reportViewOptions",
        "LocalReport.getReportPages" => "LocalReport__getReportPages",
        "Lookups.getRegionCenters" => "Lookups__getRegionCenters",
        "Submission.ProgramLeader.allForCenter" => "Submission__ProgramLeader__allForCenter",
        "Submission.ProgramLeader.stash" => "Submission__ProgramLeader__stash",
        "Submission.Scoreboard.allForCenter" => "Submission__Scoreboard__allForCenter",
        "Submission.Scoreboard.stash" => "Submission__Scoreboard__stash",
        "Submission.Scoreboard.getScoreboardLockQuarter" => "Submission__Scoreboard__getScoreboardLockQuarter",
        "Submission.Scoreboard.setScoreboardLockQuarter" => "Submission__Scoreboard__setScoreboardLockQuarter",
        "Submission.NextQtrAccountability.allForCenter" => "Submission__NextQtrAccountability__allForCenter",
        "Submission.NextQtrAccountability.stash" => "Submission__NextQtrAccountability__stash",
        "SubmissionCore.initSubmission" => "SubmissionCore__initSubmission",
        "SubmissionCore.completeSubmission" => "SubmissionCore__completeSubmission",
        "SubmissionCore.initFirstWeekData" => "SubmissionCore__initFirstWeekData",
        "SubmissionData.ignoreMe" => "SubmissionData__ignoreMe",
        "TeamMember.setWeekData" => "TeamMember__setWeekData",
        "TeamMember.allForCenter" => "TeamMember__allForCenter",
        "TeamMember.stash" => "TeamMember__stash",
        "TeamMember.bulkStashWeeklyReporting" => "TeamMember__bulkStashWeeklyReporting",
        "UserProfile.setLocale" => "UserProfile__setLocale",
        "UserProfile.needsShim" => "UserProfile__needsShim",
        "ValidationData.validate" => "ValidationData__validate",
    ];

    protected $tokenAuthenticatedMethods = [
        "GlobalReport__getReportPages",
        "GlobalReport__getReportPagesByDate",
        "GlobalReport__reportViewOptions",
        "LocalReport__getQuarterScoreboard",
        "LocalReport__reportViewOptions",
        "LocalReport__getReportPages",
    ];

    protected $unauthenticatedMethods = [
        "LiveScoreboard__getCurrentScores",
        "UserProfile__needsShim",
    ];

    protected function Admin__Region__getRegion($input)
    {
        return App::make(Api\Admin\Region::class)->getRegion(
            $this->parse($input, 'region', 'Region')
        );
    }
    protected function Admin__Quarter__filter($input)
    {
        return App::make(Api\Admin\Quarter::class)->filter(
        );
    }
    protected function Admin__System__allSystemMessages($input)
    {
        return App::make(Api\Admin\System::class)->allSystemMessages(
        );
    }
    protected function Admin__System__writeSystemMessage($input)
    {
        return App::make(Api\Admin\System::class)->writeSystemMessage(
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Application__create($input)
    {
        return App::make(Api\Application::class)->create(
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
    protected function Course__allForCenter($input)
    {
        return App::make(Api\Course::class)->allForCenter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date', false),
            $this->parse($input, 'includeInProgress', 'bool', false)
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
    protected function GlobalReport__getReportPagesByDate($input)
    {
        return App::make(Api\GlobalReport::class)->getReportPagesByDate(
            $this->parse($input, 'region', 'Region'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'pages', 'array')
        );
    }
    protected function GlobalReport__reportViewOptions($input)
    {
        return App::make(Api\GlobalReport::class)->reportViewOptions(
            $this->parse($input, 'region', 'Region'),
            $this->parse($input, 'reportingDate', 'date')
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
    protected function LocalReport__reportViewOptions($input)
    {
        return App::make(Api\LocalReport::class)->reportViewOptions(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date')
        );
    }
    protected function LocalReport__getReportPages($input)
    {
        return App::make(Api\LocalReport::class)->getReportPages(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'pages', 'array')
        );
    }
    protected function Lookups__getRegionCenters($input)
    {
        return App::make(Api\Lookups::class)->getRegionCenters(
            $this->parse($input, 'region', 'Region')
        );
    }
    protected function Submission__ProgramLeader__allForCenter($input)
    {
        return App::make(Api\Submission\ProgramLeader::class)->allForCenter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'includeInProgress', 'bool', false)
        );
    }
    protected function Submission__ProgramLeader__stash($input)
    {
        return App::make(Api\Submission\ProgramLeader::class)->stash(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Submission__Scoreboard__allForCenter($input)
    {
        return App::make(Api\Submission\Scoreboard::class)->allForCenter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'includeInProgress', 'bool', false)
        );
    }
    protected function Submission__Scoreboard__stash($input)
    {
        return App::make(Api\Submission\Scoreboard::class)->stash(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Submission__Scoreboard__getScoreboardLockQuarter($input)
    {
        return App::make(Api\Submission\Scoreboard::class)->getScoreboardLockQuarter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'quarter', 'Quarter')
        );
    }
    protected function Submission__Scoreboard__setScoreboardLockQuarter($input)
    {
        return App::make(Api\Submission\Scoreboard::class)->setScoreboardLockQuarter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'quarter', 'Quarter'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function Submission__NextQtrAccountability__allForCenter($input)
    {
        return App::make(Api\Submission\NextQtrAccountability::class)->allForCenter(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'includeInProgress', 'bool', false)
        );
    }
    protected function Submission__NextQtrAccountability__stash($input)
    {
        return App::make(Api\Submission\NextQtrAccountability::class)->stash(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
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
    protected function SubmissionCore__completeSubmission($input)
    {
        return App::make(Api\SubmissionCore::class)->completeSubmission(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'reportingDate', 'date'),
            $this->parse($input, 'data', 'array')
        );
    }
    protected function SubmissionCore__initFirstWeekData($input)
    {
        return App::make(Api\SubmissionCore::class)->initFirstWeekData(
            $this->parse($input, 'center', 'Center'),
            $this->parse($input, 'quarter', 'Quarter')
        );
    }
    protected function SubmissionData__ignoreMe($input)
    {
        return App::make(Api\SubmissionData::class)->ignoreMe(
            $this->parse($input, 'center', 'string'),
            $this->parse($input, 'timezone', 'string')
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
    protected function UserProfile__needsShim($input)
    {
        return App::make(Api\UserProfile::class)->needsShim(
            $this->parse($input, 'v', 'string')
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
