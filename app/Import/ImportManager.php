<?php
namespace TmlpStats\Import;

use Carbon\Carbon;

use Log;
use Exception;

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
        switch($this->dataType) {

            case 'xlsx':
                $this->importXlsx();
                break;
            default:
                // This is a developer error. The caller must provide a valid type
                throw new Exception("Unable to import: Unknown input type");
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

        foreach($this->files as $file) {

            try {
                // If someone refreshes the page after submitting file, and the browser doesn't send the file contents,
                // we end up with empty files.
                if (!($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)) {
                    throw new Exception("There was a problem uploading one of the files. Please try again.");
                }

                $fileName = $file->getClientOriginalName();
                $doc = NULL;
                if (!$file->isValid()) {
                    Log::error("Error uploading '$fileName': {$file->getError()}");
                    throw new Exception("There was a problem uploading '$fileName'. Please try again.");
                } else {
                    try {
                        $importer = ImporterFactory::build($this->dataType, $file->getRealPath(), $fileName, $this->expectedDate, $this->enforceVersion);
                        $importer->import($this->validateReport);
                        $doc = $importer->getImportDocument();
                    } catch(Exception $e) {
                        Log::error("Error processing '$fileName': " . $e->getMessage() . "\n" . $e->getTraceAsString());
                        throw new Exception("Error processing '$fileName': ".$e->getMessage());
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

                if ($doc->statsReport && $doc->statsReport->isValidated()) {
                    $this->archiveSheet($file, $doc->statsReport);
                }

                // We're done with doc now, so hand it over to the garbage collector. (Added to help with importing large numbers of files)
                $doc = NULL;

                if (count($sheet['errors']) > 0) {
                    $sheet['result'] = 'error';
                    $errorSheets[]   = $sheet;
                } else if (count($sheet['warnings']) > 0) {
                    $sheet['result'] = 'warn';
                    $warnSheets[]    = $sheet;
                } else {
                    $sheet['result'] = 'ok';
                    $successSheets[] = $sheet;
                }
            } catch(Exception $e) {
                $unknownFiles[] = $e->getMessage();
            }
        }

        $this->results['sheets'] = array_merge($successSheets, $warnSheets, $errorSheets);
        $this->results['unknownFiles'] = $unknownFiles;
    }

    protected function archiveSheet($file, $statsReport)
    {
        if (!$statsReport || !($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)) {
            Log::error('Unable to archive file. Invalid file or stats report.');
            return;
        }

        $baseDir = storage_path() . "/app/archive/xlsx";
        $reportingDate = $statsReport->reportingDate->toDateString();
        $centerName = $statsReport->center->sheetFilename;

        $dir = "{$baseDir}/{$reportingDate}";
        $name = "{$centerName}.xlsx";

        if (is_dir($dir) || mkdir($dir, 0777, true)) {
            try {
                $file->move($dir, $name);
            } catch (Exception $e) {
                Log::error("Unable to archive file '$reportingDate/$newFile'. Caught exception moving file: {$e->getMessage()}.");
            }
        } else {
            Log::error("Unable to archive file '$reportingDate/$newFile'. Failed to setup directory.");
        }
    }
}
