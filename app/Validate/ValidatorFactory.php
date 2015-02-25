<?php
namespace TmlpStats\Validate;

class ValidatorFactory
{
    public static function build($version, $type = NULL)
    {
        if ($type === NULL)
        {
            $type = 'null';
        }
        switch ($version)
        {
            case '10':
            default:
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
               return new $class();
        }
    }
}
