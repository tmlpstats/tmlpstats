<?php
namespace TmlpStats\Import\Xlsx;

use Carbon\Carbon;

use Exception;
use Log;

class XlsxArchiver
{
    protected $archiveBasePath = 'app/archive/xlsx';

    public static function getInstance()
    {
        return new static();
    }

    public function archive($file, $statsReport)
    {
        $fileName = $this->getFileName($file, $statsReport);

        $destination = $this->getArchivedFilePath($statsReport->reportingDate->toDateString(), $fileName);

        return $this->saveFile($destination, $file);
    }

    public function saveWorkingSheet($file, $statsReport = null)
    {
        $fileName = $this->getFileName($file, $statsReport);
        $reportingDate = $statsReport
            ? $statsReport->reportingDate->toDateString()
            : Carbon::now()->toDateString();

        $destination = $this->getWorkingSheetFilePath($reportingDate,$fileName);

        return $this->saveFile($destination, $file);
    }

    public function getSheetPath($statsReport)
    {
        $fileName = $this->getFileName(null, $statsReport);

        if ($statsReport->submittedAt) {
            // New loction with date in file name
            $archivedFile = $this->getArchivedFilePath($statsReport->reportingDate->toDateString(), $fileName);

            if (file_exists($archivedFile)) {
                return $archivedFile;
            }

            // Old loction with no dates in file name
            $archivedFile = $this->getArchivedFilePath($statsReport->reportingDate->toDateString(), $statsReport->center->sheetFilename);

            if (file_exists($archivedFile)) {
                return $archivedFile;
            }
        } else {
            // New loction with date in file name
            $workingFile = $this->getWorkingSheetFilePath($statsReport->reportingDate->toDateString(), $fileName);

            if (file_exists($workingFile)) {
                return $workingFile;
            }

            // Old loction with no dates in file name
            $workingFile = $this->getWorkingSheetFilePath($statsReport->reportingDate->toDateString(), $statsReport->center->sheetFilename);

            if (file_exists($workingFile)) {
                return $workingFile;
            }
        }

        return null;
    }

    protected function getArchivedFilePath($reportingDate, $fileName)
    {
        $baseDir = $this->getArchiveDirectory();

        $name = "{$baseDir}/{$reportingDate}/{$fileName}";
        if (strpos($name, '.xlsx') === false) {
            $name .= '.xlsx';
        }
        return $name;
    }

    protected function getWorkingSheetFilePath($reportingDate, $fileName)
    {
        $baseDir = $this->getArchiveDirectory();

        $name = "{$baseDir}/working_files/{$reportingDate}/{$fileName}";
        if (strpos($name, '.xlsx') === false) {
            $name .= '.xlsx';
        }
        return $name;
    }

    protected function getFileName($file, $statsReport = null)
    {
        $fileName = null;
        if ($statsReport) {
            $sheetName = $statsReport->center
                ? $statsReport->center->sheetFilename
                : null;

            $date = $statsReport->submittedAt
                ? clone $statsReport->submittedAt
                : Carbon::now();

            $date->setTimezone($statsReport->center->timeZone);
            $dateString = $date->format('Y-m-d_H-i-s');

            if ($sheetName) {
                $fileName = "{$sheetName}_{$dateString}.xlsx";
            }
        }
        if (!$fileName) {
            if ($file) {
                $fileName = $file->getClientOriginalName();
            } else {
                $dateString = Carbon::now()->format('Y-m-d_H-i-s');
                $fileName = "Unknown_{$dateString}.xlsx";
            }
        }
        return $fileName;
    }

    protected function getArchiveDirectory()
    {
        return storage_path() . '/' . $this->archiveBasePath;
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
