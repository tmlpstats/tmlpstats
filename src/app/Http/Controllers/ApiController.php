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
        "Context.getCenter" => "Context__getCenter",
        "Context.setCenter" => "Context__setCenter",
        "Context.getSetting" => "Context__getSetting",
        "GlobalReport.getRating" => "GlobalReport__getRating",
        "GlobalReport.getQuarterScoreboard" => "GlobalReport__getQuarterScoreboard",
        "GlobalReport.getWeekScoreboard" => "GlobalReport__getWeekScoreboard",
        "GlobalReport.getWeekScoreboardByCenter" => "GlobalReport__getWeekScoreboardByCenter",
        "GlobalReport.getIncomingTeamMembersListByCenter" => "GlobalReport__getIncomingTeamMembersListByCenter",
        "LiveScoreboard.getCurrentScores" => "LiveScoreboard__getCurrentScores",
        "LiveScoreboard.setScore" => "LiveScoreboard__setScore",
        "LocalReport.getQuarterScoreboard" => "LocalReport__getQuarterScoreboard",
        "LocalReport.getWeekScoreboard" => "LocalReport__getWeekScoreboard",
        "LocalReport.getIncomingTeamMembersList" => "LocalReport__getIncomingTeamMembersList",
        "LocalReport.getClassList" => "LocalReport__getClassList",
        "LocalReport.getClassListByQuarter" => "LocalReport__getClassListByQuarter",
        "UserProfile.setLocale" => "UserProfile__setLocale",
    ];

    protected $unauthenticatedMethods = [
        "LiveScoreboard__getCurrentScores",
    ];

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
            $this->parse($input, 'region', 'Region')
        );
    }
    protected function GlobalReport__getWeekScoreboardByCenter($input)
    {
        return App::make(Api\GlobalReport::class)->getWeekScoreboardByCenter(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region'),
            $this->parse($input, 'options', 'array')
        );
    }
    protected function GlobalReport__getIncomingTeamMembersListByCenter($input)
    {
        return App::make(Api\GlobalReport::class)->getIncomingTeamMembersListByCenter(
            $this->parse($input, 'globalReport', 'GlobalReport'),
            $this->parse($input, 'region', 'Region'),
            $this->parse($input, 'options', 'array')
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
            $this->parse($input, 'options', 'array')
        );
    }
    protected function LocalReport__getWeekScoreboard($input)
    {
        return App::make(Api\LocalReport::class)->getWeekScoreboard(
            $this->parse($input, 'localReport', 'LocalReport')
        );
    }
    protected function LocalReport__getIncomingTeamMembersList($input)
    {
        return App::make(Api\LocalReport::class)->getIncomingTeamMembersList(
            $this->parse($input, 'localReport', 'LocalReport'),
            $this->parse($input, 'options', 'array')
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
    protected function UserProfile__setLocale($input)
    {
        return App::make(Api\UserProfile::class)->setLocale(
            $this->parse($input, 'locale', 'string'),
            $this->parse($input, 'timezone', 'string')
        );
    }
}
