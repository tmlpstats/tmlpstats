<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Api\Parsers;

class Factory
{
    public static function build($type)
    {
        switch ($type) {
            case 'int':
                return Parsers\IntParser::create();
            case 'string':
                return Parsers\StringParser::create();
            case 'bool':
                return Parsers\BoolParser::create();
            case 'array':
                return Parsers\ArrayParser::create();
            case 'date':
                return Parsers\DateParser::create();
            case 'Center':
                return Parsers\CenterParser::create();
            case 'Region':
                return Parsers\RegionParser::create();
            case 'LocalReport':
                return Parsers\LocalReportParser::create();
            case 'GlobalReport':
                return Parsers\GlobalReportParser::create();
            case 'Quarter':
                return Parsers\QuarterParser::create();
            case 'Application':
                return Parsers\ApplicationParser::create();
            case 'TeamMember':
                return Parsers\TeamMemberParser::create();
            default:
                throw new ApiExceptions\ServerErrorException("Unknown parameter type {$type}");
        }
    }
}
