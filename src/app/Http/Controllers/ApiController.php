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
        "LiveScoreboard.getCurrentScores" => "LiveScoreboard__getCurrentScores",
        "LiveScoreboard.setScore" => "LiveScoreboard__setScore",
        "LocalReport.getQuarterScoreboard" => "LocalReport__getQuarterScoreboard",
        "LocalReport.getWeekScoreboard" => "LocalReport__getWeekScoreboard",
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
            $this->parse_center($input, 'center'),
            $this->parse_bool($input, 'permanent')
        );
    }
    protected function Context__getSetting($input)
    {
        return App::make(Api\Context::class)->getSetting(
            $this->parse_string($input, 'name'),
            $this->parse_Center($input, 'center')
        );
    }
    protected function GlobalReport__getRating($input)
    {
        return App::make(Api\GlobalReport::class)->getRating(
            $this->parse_GlobalReport($input, 'globalReport'),
            $this->parse_Region($input, 'region')
        );
    }
    protected function GlobalReport__getQuarterScoreboard($input)
    {
        return App::make(Api\GlobalReport::class)->getQuarterScoreboard(
            $this->parse_GlobalReport($input, 'globalReport'),
            $this->parse_Region($input, 'region')
        );
    }
    protected function GlobalReport__getWeekScoreboard($input)
    {
        return App::make(Api\GlobalReport::class)->getWeekScoreboard(
            $this->parse_GlobalReport($input, 'globalReport'),
            $this->parse_Region($input, 'region')
        );
    }
    protected function GlobalReport__getWeekScoreboardByCenter($input)
    {
        return App::make(Api\GlobalReport::class)->getWeekScoreboardByCenter(
            $this->parse_GlobalReport($input, 'globalReport'),
            $this->parse_Region($input, 'region'),
            $this->parse_array($input, 'options')
        );
    }
    protected function LiveScoreboard__getCurrentScores($input)
    {
        return App::make(Api\LiveScoreboard::class)->getCurrentScores(
            $this->parse_Center($input, 'center')
        );
    }
    protected function LiveScoreboard__setScore($input)
    {
        return App::make(Api\LiveScoreboard::class)->setScore(
            $this->parse_Center($input, 'center'),
            $this->parse_string($input, 'game'),
            $this->parse_string($input, 'type'),
            $this->parse_int($input, 'value')
        );
    }
    protected function LocalReport__getQuarterScoreboard($input)
    {
        return App::make(Api\LocalReport::class)->getQuarterScoreboard(
            $this->parse_LocalReport($input, 'localReport'),
            $this->parse_array($input, 'options')
        );
    }
    protected function LocalReport__getWeekScoreboard($input)
    {
        return App::make(Api\LocalReport::class)->getWeekScoreboard(
            $this->parse_LocalReport($input, 'localReport')
        );
    }
    protected function LocalReport__getClassList($input)
    {
        return App::make(Api\LocalReport::class)->getClassList(
            $this->parse_LocalReport($input, 'localReport')
        );
    }
    protected function LocalReport__getClassListByQuarter($input)
    {
        return App::make(Api\LocalReport::class)->getClassListByQuarter(
            $this->parse_LocalReport($input, 'localReport')
        );
    }
    protected function UserProfile__setLocale($input)
    {
        return App::make(Api\UserProfile::class)->setLocale(
            $this->parse_string($input, 'locale'),
            $this->parse_string($input, 'timezone')
        );
    }
}
