<?php
namespace TmlpStats\Import;

use Carbon\Carbon;

// Required for importing multiple sheets
ini_set('max_execution_time', 240);
ini_set('memory_limit', '512M');
ini_set('max_file_uploads', '30');

// ImportManager takes the list of uploaded files, has them all processed, and returns an array of results
class ImportManager
{
    protected $dataType = '';
    protected $files = array();
    protected $expectedDate = null;
    protected $enforceVersion = false;
    protected $validateReport = true;

    protected $results = array();

    public function __construct($files, $expectedDate = null, $enforceVersion = true, $type='xlsx')
    {
        $this->dataType = $type;
        $this->files = $files;
        if ($expectedDate) {
            $this->expectedDate = Carbon::createFromFormat('Y-m-d', $expectedDate)->startOfDay();
        }
        $this->enforceVersion = $enforceVersion;
    }

    public function import($validateReport = true)
    {
        $this->validateReport = $validateReport;
        switch($this->dataType)
        {
            case 'xlsx':
                $this->importXlsx();
                break;
            default:
                // This is a developer error. The caller must provide a valid type
                throw new \Exception("Unable to import: Unknown input type");
        }
    }

    public function getResults()
    {
        return $this->results;
    }

    protected function importXlsx()
    {
        $successSheets = array();
        $warnSheets = array();
        $errorSheets = array();
        $unknownFiles = array();

        foreach($this->files as $file)
        {
            try
            {
                $fileName = $file->getClientOriginalName();
                $doc = NULL;
                if (!$file->isValid())
                {
                    // TODO: Log a message with the error rather than provide it to user.
                    //       They can't do anything with it anyway
                    throw new \Exception("Error uploading '$fileName': {$file->getError()}");
                }
                else
                {
                    try
                    {
                        $importer = ImporterFactory::build($this->dataType, $file->getRealPath(), $fileName, $this->expectedDate, $this->enforceVersion);
                        $importer->import($this->validateReport);
                        $doc = $importer->getImportDocument();
                    }
                    catch(\Exception $e)
                    {
                        throw new \Exception("Error processing '$fileName': ".$e->getMessage()."\n\n".$e->getTraceAsString());
                    }
                }

                $sheet = array(
                    'statsReportId' => ($doc->statsReport) ? $doc->statsReport->id : '',
                    'centerId'      => ($doc->center) ? $doc->center->id : '',
                    'center'        => ($doc->center) ? $doc->center->name : 'Unknown',
                    'reportingDate' => ($doc->reportingDate) ? $doc->reportingDate->format('M j, Y') : 'Unknown',
                    'sheetVersion'  => ($doc->version) ? $doc->version : 'Unknown',
                    'errors'        => $doc->messages['errors'],
                    'warnings'      => $doc->messages['warnings'],
                );

                // We're done with doc now, so hand it over to the garbage collector. (Added to help with importing large numbers of files)
                $doc = NULL;

                if (count($sheet['errors']) > 0)
                {
                    $sheet['result'] = 'error';
                    $errorSheets[]   = $sheet;
                }
                else if (count($sheet['warnings']) > 0)
                {
                    $sheet['result'] = 'warn';
                    $warnSheets[]    = $sheet;
                }
                else
                {
                    $sheet['result'] = 'ok';
                    $successSheets[] = $sheet;
                }
            }
            catch(\Exception $e)
            {
                $unknownFiles[] = $e->getMessage();
            }
            // TODO: delete/archive files after they have been processes
        }

        $this->results['sheets'] = array_merge($successSheets, $warnSheets, $errorSheets);
        $this->results['unknownFiles'] = $unknownFiles;
    }

    protected function archiveSheet($file,$statsReport)
    {
        $baseDir = "xlsx";
        $reportingDate = $statsReport->reportingDate;
        $centerName = $statsReport->center->sheetName;

        $dir = "$baseDir/$reportingDate";
        $newFile = "$centerName.xlsx";

        $path = "$dir/$newFile";

        if (is_dir($dir) || mkdir($dir,0777,true))
        {
            move_uploaded_file($file, $path);
            return $path;
        }
        else
        {
            throw new \Exception("Unable to archive file '$reportingDate/$newFile'.");
        }
    }
}
