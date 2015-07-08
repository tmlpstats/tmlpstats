<?php
namespace TmlpStats\Import\Xlsx\Reader;

class ReaderFactory
{
    public static function build($type, $data)
    {
        $namespace = 'TmlpStats\\Import\\Xlsx\\Reader\\';

        switch ($type) {

            case 'CenterStats':
            case 'ClassList':
            case 'CommCourseInfo':
            case 'ContactInfo':
            case 'TmlpGameInfo':
            case 'TmlpRegistration':
                $class = $namespace . $type . 'Reader';
                return new $class($data);

            default:
                return NULL;
        }
    }
}
