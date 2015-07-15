<?php
namespace TmlpStats\Import;

use Carbon\Carbon;


use Auth;
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
    protected $saveReport = false;

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

    public function import($saveReport = false)
    {
        $this->saveReport = $saveReport;
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

            $fileSaved = false;

            try {
                // If someone refreshes the page after submitting file, and the browser doesn't send the file contents,
                // we end up with empty files.
                if (!($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)) {
                    $file = null;
                    throw new Exception("There was a problem uploading one of the files. Please try again.");
                }

                $fileName = $file->getClientOriginalName();
                $doc = null;
                if (!$file->isValid()) {
                    $file = null;
                    Log::error("Error uploading '$fileName': {$file->getError()}");
                    throw new Exception("There was a problem uploading '$fileName'. Please try again.");
                } else {
                    try {
                        $importer = ImporterFactory::build($this->dataType, $file->getRealPath(), $fileName, $this->expectedDate, $this->enforceVersion);
                        $importer->import($this->saveReport);
                        $doc = $importer->getImportDocument();
                    } catch(Exception $e) {
                        Log::error("Error processing '$fileName': " . $e->getMessage() . "\n" . $e->getTraceAsString());
                        throw new Exception("There was an error processing '$fileName': ".$e->getMessage());
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

                $user = Auth::user()->email;
                $errorCount = count($sheet['errors']);
                $warningCount = count($sheet['warnings']);
                Log::info("{$user} submitted sheet for {$sheet['center']} with {$errorCount} errors and {$warningCount} warnings.");

                if ($doc->statsReport) {

                    if ($doc->saved()) {
                        $this->archiveSheet($file,
                                            $doc->statsReport->reportingDate->toDateString(),
                                            $doc->statsReport->center->sheetFilename);
                    } else {
                        $this->saveWorkingSheet($file,
                                                $doc->statsReport->reportingDate->toDateString(),
                                                $doc->statsReport->center->sheetFilename);
                    }
                    $fileSaved = true;
                }

                // We're done with doc now, so hand it over to the garbage collector. (Added to help with importing large numbers of files)
                $doc = null;

                if (count($sheet['errors']) > 0) {
                    $sheet['result'] = 'error';
                    $errorSheets[]   = $sheet;
                } else if (count($sheet['warnings']) > 0) {
                    $sheet['result'] = 'warning';
                    $warnSheets[]    = $sheet;
                } else {
                    $sheet['result'] = 'ok';
                    $successSheets[] = $sheet;
                }
            } catch(Exception $e) {

                Log::error("Error processing file: " . $e->getMessage());
                $unknownFiles[] = $e->getMessage();
            }

            if (!$fileSaved && $file) {

                $this->saveWorkingSheet($file,
                                        Carbon::now()->toDateString(),
                                        $file->getClientOriginalName());
            }
        }

        $this->results['sheets'] = array_merge($successSheets, $warnSheets, $errorSheets);
        $this->results['unknownFiles'] = $unknownFiles;
    }

    public static function getExpectedReportDate()
    {
        $expectedDate = null;
        if (Carbon::now()->dayOfWeek == Carbon::FRIDAY) {
            $expectedDate = Carbon::now();
        } else if (Carbon::now()->isWeekend()) {
            $expectedDate = new Carbon('last friday');
        } else {
            $expectedDate = new Carbon('next friday');
        }
        return $expectedDate->startOfDay();
    }

    public static function getArchiveDirectory()
    {
        return storage_path() . "/app/archive/xlsx";
    }

    public static function getArchivedFilePath($reportingDate, $fileName)
    {
        $baseDir = static::getArchiveDirectory();

        $name = "{$baseDir}/{$reportingDate}/{$fileName}";
        if (strpos($name, '.xlsx') === false) {
            $name .= '.xlsx';
        }
        return $name;
    }

    public static function getWorkingSheetFilePath($reportingDate, $fileName)
    {
        $baseDir = static::getArchiveDirectory();

        $name = "{$baseDir}/working_files/{$reportingDate}/{$fileName}";
        if (strpos($name, '.xlsx') === false) {
            $name .= '.xlsx';
        }
        return $name;
    }

    public static function getSheetPath($reportingDate, $name)
    {
        $archivedFile = static::getArchivedFilePath($reportingDate, $name);

        if (file_exists($archivedFile)) {
            return $archivedFile;
        }

        $workingFile = static::getWorkingSheetFilePath($reportingDate, $name);

        if (file_exists($workingFile)) {
            return $workingFile;
        }

        return '';
    }

    public function archiveSheet($file, $reportingDate, $fileName)
    {
        $destination = static::getArchivedFilePath($reportingDate, $fileName);

        return $this->saveFile($destination, $file);
    }

    protected function saveWorkingSheet($file, $reportingDate, $fileName)
    {
        $destination = static::getWorkingSheetFilePath($reportingDate, $fileName);

        return $this->saveFile($destination, $file);
    }

    protected function saveFile($destinationPath, $sourceFile)
    {
        $savedFile = '';

        if (!($sourceFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)) {
            Log::error('Unable to save file. Invalid file.');
            return $savedFile;
        }

        $pathInfo = pathinfo($destinationPath);
        $dir = $pathInfo['dirname'];
        $fileName = $pathInfo['basename'];

        if (is_dir($dir) || mkdir($dir, 0777, true)) {
            try {
                $sourceFile->move($dir, $fileName);
                $savedFile = $destinationPath;
            } catch (Exception $e) {
                Log::error("Unable to save file '$destinationPath'. Caught exception moving file: {$e->getMessage()}.");
            }
        } else {
            Log::error("Unable to save file '$destinationPath'. Failed to setup directory.");
        }

        return $savedFile;
    }
}
