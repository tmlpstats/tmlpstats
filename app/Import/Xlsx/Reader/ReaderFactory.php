<?php
namespace TmlpStats\Import\Xlsx\Reader;

class ReaderFactory
{
    public static function build($type, $version, $data)
    {
        $namespace = 'TmlpStats\\Import\\Xlsx\\Reader\\';

        if (preg_match('/^(10|11|15)\.\d+$/', $version)) {

            $namespace .= 'V11\\';

        } else if (preg_match('/^15\.\d+\.\d+$/', $version)) {

            // use the current version which doesn't use a namespaces

        } else {
            throw new \Exception("Unsupported spreadsheet version '$version'");
        }

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
