<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\Region;

class RegionParser extends IdOrAbbrParserBase
{
    protected $type = 'region';
    protected $class = Region::class;
}
