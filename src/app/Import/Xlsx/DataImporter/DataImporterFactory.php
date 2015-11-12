<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

class DataImporterFactory
{
    public static function build($type, &$data, &$statsReport)
    {
        switch ($type) {
            case 'CenterStats':
            case 'ClassList':
            case 'CommCourseInfo':
            case 'ContactInfo':
            case 'TmlpGameInfo':
            case 'TmlpRegistration':
                $class = 'TmlpStats\\Import\\Xlsx\\DataImporter\\' . $type . 'Importer';
                break;
            default:
                throw new \Exception("Invalid type passed to DataImporterFactory");
        }

        return new $class($data, $statsReport);
    }
}
