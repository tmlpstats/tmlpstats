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
    protected $files = array();
    protected $expectedDate = null;
    protected $enforceVersion = false;

    protected $results = array();

    public function __construct($files, $expectedDate = null, $enforceVersion = true)
    {
        $this->files = $files;
        if ($expectedDate) {
            $this->expectedDate = Carbon::createFromFormat('Y-m-d', $expectedDate)->startOfDay();
        }
        $this->enforceVersion = $enforceVersion;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function import($saveReport = false)
    {
        $successSheets = array();
        $warnSheets = array();
        $errorSheets = array();
        $unknownFiles = array();

        foreach ($this->files as $file) {

            $fileSaved = false;

            try {
                $fileName = $file->getClientOriginalName();
                if (!($file instanceof \Symfony\Component\HttpFoundation\File\UploadedFile)) {
                    // If someone refreshes the page after submitting file, and the browser doesn't send the file contents,
                    // we end up with empty files.
                    $file = null;
                    throw new Exception("There was a problem uploading one of the files. Please try again.");
                } else if (!$file->isValid()) {
                    Log::error("Error uploading '$fileName': {$file->getError()}");
                    $file = null;
                    throw new Exception("There was a problem uploading '$fileName'. Please try again.");
                }

                $sheet = array();
                try {
                    $importer = new Xlsx\XlsxImporter($file->getRealPath(), $fileName, $this->expectedDate, $this->enforceVersion);
                    $importer->import($saveReport);
                    $sheet = $importer->getResults();
                } catch(Exception $e) {
                    Log::error("Error processing '$fileName': " . $e->getMessage() . "\n" . $e->getTraceAsString());
                    throw new Exception("There was an error processing '$fileName': ".$e->getMessage());
                }

                $user = Auth::user()->email;
                $errorCount = count($sheet['errors']);
                $warningCount = count($sheet['warnings']);
                Log::info("{$user} submitted sheet for {$sheet['center']} with {$errorCount} errors and {$warningCount} warnings.");

                if (isset($sheet['statsReportId'])) {

                    if ($sheet['saved']) {
                        $this->archiveSheet($file,
                                            $sheet['reportingDate']->toDateString(),
                                            $sheet['sheetFilename']);
                    } else {
                        $this->saveWorkingSheet($file,
                                                $sheet['reportingDate']->toDateString(),
                                                $sheet['sheetFilename']);
                    }
                    $fileSaved = true;
                }

                if ($sheet['result'] == 'error') {
                    $errorSheets[] = $sheet;
                } else if ($sheet['result'] == 'warning') {
                    $warnSheets[] = $sheet;
                } else {
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
