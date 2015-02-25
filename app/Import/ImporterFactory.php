<?php
namespace TmlpStats\Import;

class ImporterFactory
{
    public static function build($type, $filePath, $fileName, $expectedDate = null, $enforceVersion = true, $validateReport = true)
    {
        switch ($type)
        {
            case 'xlsx':
                return new Xlsx\XlsxImporter($filePath, $fileName, $expectedDate, $enforceVersion, $validateReport);

            default:
                return null;
        }
    }
}
