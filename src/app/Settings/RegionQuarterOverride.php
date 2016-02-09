<?php
namespace TmlpStats\Settings;

use TmlpStats\Settings\Parsers\RegionQuarterOverrideParser;

class RegionQuarterOverride extends Setting
{
    protected static $settingName = 'regionQuarterOverride';
    protected static $parserClass = RegionQuarterOverrideParser::class;
}
