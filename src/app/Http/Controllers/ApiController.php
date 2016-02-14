<?php
namespace TmlpStats\Http\Controllers;

///////////////////////////////
// THIS CODE IS AUTO-GENERATED
// do not edit this code by hand!
//
// To edit the resulting API code, instead edit config/reports.yml
// and then run the command:
//   php artisan reports:codegen

use App;
use TmlpStats\Api;
use TmlpStats\Http\Controllers\ApiControllerBase;

class ApiController extends ApiControllerBase
{
    protected $methods = [
        "GlobalReport.getRating" => "GlobalReport__getRating",
        "LocalReport.getWeeklyPromises" => "LocalReport__getWeeklyPromises",
        "LocalReport.getClassList" => "LocalReport__getClassList",
        "LocalReport.getClassListByQuarter" => "LocalReport__getClassListByQuarter",
    ];

    protected function GlobalReport__getRating($input)
    {
        return App::make(Api\GlobalReport::class)->getRating(
            $this->parse_GlobalReport($input, 'globalReport')
        );
    }
    protected function LocalReport__getWeeklyPromises($input)
    {
        return App::make(Api\LocalReport::class)->getWeeklyPromises(
            $this->parse_LocalReport($input, 'localReport'),
            $this->parse_bool($input, 'enable_something'),
            $this->parse_string($input, 'first_name')
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
}