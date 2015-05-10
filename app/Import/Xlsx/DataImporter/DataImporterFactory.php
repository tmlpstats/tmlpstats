<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

class DataImporterFactory
{
    public static function build($type, $version, $data, $statsReport)
    {
        $namespace = 'TmlpStats\\Import\\Xlsx\\DataImporter\\';

        switch ($type) {

            case 'CenterStats':
            case 'ClassList':
            case 'CommCourseInfo':
            case 'ContactInfo':
            case 'TmlpGameInfo':
            case 'TmlpRegistration':
                $class = $namespace . $type . 'Importer';
                return new $class($data, $statsReport);

            default:
                return NULL;
        }
    }
}
