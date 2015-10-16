<?php
namespace TmlpStats\Validate;

class ValidatorFactory
{
    public static function build($statsReport, $type = null)
    {
        if ($type === null)
        {
            $type = 'null';
        }

        switch ($type)
        {
            case 'centerStats':
            case 'tmlpRegistration':
            case 'classList':
            case 'contactInfo':
            case 'commCourseInfo':
            case 'tmlpCourseInfo':
            case 'null':
                $class = '\\TmlpStats\\Validate\\' . ucfirst($type) . 'Validator';
                break;
            default:
                throw new \Exception("Invalid type passed to ValidatorFactory");
        }

       return new $class($statsReport);
    }
}
