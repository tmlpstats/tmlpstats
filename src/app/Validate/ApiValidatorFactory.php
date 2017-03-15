<?php
namespace TmlpStats\Validate;

class ApiValidatorFactory
{
    public static function build($statsReport, $type)
    {
        switch ($type) {
            case 'Course':
            case 'Scoreboard':
            case 'TeamApplication':
            case 'TeamMember':
                $class = '\\TmlpStats\\Validate\\Objects\\Api' . ucfirst($type) . 'Validator';
                break;
            case 'Accountability':
            case 'CenterGames':
                $class = '\\TmlpStats\\Validate\\Relationships\\Api' . ucfirst($type) . 'Validator';
                break;
            case 'CourseChange':
            case 'TeamApplicationChange':
                $class = '\\TmlpStats\\Validate\\Differences\\Api' . ucfirst($type) . 'Validator';
                break;
            case 'null':
                $class = '\\TmlpStats\\Validate\\' . ucfirst($type) . 'Validator';
                break;
            default:
                throw new \Exception("Invalid type passed to ValidatorFactory");
        }

        return new $class($statsReport);
    }
}
