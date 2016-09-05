<?php
namespace TmlpStats\Validate;

class ValidatorFactory
{
    public static function build($statsReport, $type = null)
    {
        if ($type === null) {
            $type = 'null';
        }

        switch ($type) {
            case 'centerStats':
            case 'tmlpRegistration':
            case 'classList':
            case 'contactInfo':
            case 'commCourseInfo':
            case 'tmlpCourseInfo':
            case 'statsReport':
            case 'apiCourse':
            case 'apiScoreboard':
            case 'apiTeamApplication':
            case 'apiTeamMember':
                $class = '\\TmlpStats\\Validate\\Objects\\' . ucfirst($type) . 'Validator';
                break;
            case 'committedTeamMember':
            case 'contactInfoTeamMember':
            case 'duplicateTeamMember':
            case 'duplicateTmlpRegistration':
            case 'teamExpansion':
            case 'centerGames':
            case 'courseCompletion':
            case 'apiCenterGames':
                $class = '\\TmlpStats\\Validate\\Relationships\\' . ucfirst($type) . 'Validator';
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
