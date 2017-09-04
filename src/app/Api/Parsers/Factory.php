<?php
namespace TmlpStats\Api\Parsers;

use App;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Api\Parsers;

class Factory
{
    public static function build($type, $options = [])
    {
        $input = ['options' => $options];
        switch ($type) {
            case 'array':
                return App::make(Parsers\ArrayParser::class);
            case 'bool':
                return App::make(Parsers\BoolParser::class);
            case 'date':
                return App::make(Parsers\DateParser::class);
            case 'int':
                return App::make(Parsers\IntParser::class);
            case 'string':
                return App::make(Parsers\StringParser::class, $input);
            case 'Application':
                return App::make(Parsers\ApplicationParser::class);
            case 'Center':
                return App::make(Parsers\CenterParser::class);
            case 'Course':
                return App::make(Parsers\CourseParser::class);
            case 'GlobalReport':
                return App::make(Parsers\GlobalReportParser::class);
            case 'LocalReport':
                return App::make(Parsers\LocalReportParser::class);
            case 'Quarter':
                return App::make(Parsers\QuarterParser::class);
            case 'Region':
                return App::make(Parsers\RegionParser::class);
            case 'TeamMember':
                return App::make(Parsers\TeamMemberParser::class);
            case 'WithdrawCode':
                return App::make(Parsers\WithdrawCodeParser::class);
            default:
                throw new ApiExceptions\ServerErrorException("Unknown parameter type {$type}");
        }
    }
}
