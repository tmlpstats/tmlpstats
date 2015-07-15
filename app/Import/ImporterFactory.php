<?php
namespace TmlpStats\Import;

class ImporterFactory
{
    public static function build($type, $filePath, $fileName, $expectedDate = null, $enforceVersion = true)
    {
        switch ($type)
        {
            case 'xlsx':
                return new Xlsx\XlsxImporter($filePath, $fileName, $expectedDate, $enforceVersion);

            default:
                return null;
        }
    }
}
