<?php
namespace TmlpStats\Import\Xlsx;

use Exception;

// This handles taking an uploaded file, validating that the data
class XlsxImporter
{
    protected $allowedFileTypes = array('xlsx', 'xlsm');
    protected $results = array();

    protected $file = '';
    protected $version = '';
    protected $expectedDate = null;
    protected $enforceVersion = true;

    protected $importDocument = null;

    public function __construct($filePath, $fileName, $expectedDate = null, $enforceVersion = true)
    {
        if (!is_file($filePath)) {
            throw new Exception("There was a problem processing '{$fileName}'. Please try again.");
        }
        if (!$this->isCorrectFileType($fileName)) {

            throw new Exception("Uploaded file '{$fileName}' is not in the correct file format. The following formats are supported: "
                                 . implode(', ', $this->allowedFileTypes));
        }

        $this->file = $filePath;
        $this->expectedDate = $expectedDate;
        $this->enforceVersion = $enforceVersion;
    }

    public function import()
    {
        $this->importDocument = new ImportDocument\ImportDocument($this->file, $this->expectedDate, $this->enforceVersion);
        $this->importDocument->import();
    }

    public function saveReport()
    {
        return $this->importDocument
            ? $this->importDocument->saveReport()
            : false;
    }

    public function getResults()
    {
        $submittedAt = ($this->importDocument->saved() && $this->importDocument->statsReport)
            ? $this->importDocument->statsReport->submittedAt
            : null;

        $this->results = array(
            'statsReportId' => ($this->importDocument->statsReport) ? $this->importDocument->statsReport->id : null,
            'statsReport'   => ($this->importDocument->statsReport) ? $this->importDocument->statsReport : null,
            'centerId'      => ($this->importDocument->center) ? $this->importDocument->center->id : null,
            'center'        => ($this->importDocument->center) ? $this->importDocument->center->name : null,
            'reportingDate' => ($this->importDocument->reportingDate) ? $this->importDocument->reportingDate : null,
            'sheetVersion'  => ($this->importDocument->version) ? $this->importDocument->version : null,
            'sheetFilename' => ($this->importDocument->statsReport) ? $this->importDocument->statsReport->center->sheetFilename : null,
            'submittedAt'   => $submittedAt,
            'errors'        => $this->importDocument->messages['errors'],
            'warnings'      => $this->importDocument->messages['warnings'],
        );

        if (count($this->results['errors']) > 0) {
            $this->results['result'] = 'error';
        } else if (count($this->results['warnings']) > 0) {
            $this->results['result'] = 'warning';
        } else {
            $this->results['result'] = 'ok';
        }

        return $this->results;
    }

    protected function isCorrectFileType($fileName)
    {
        $temp = explode(".", $fileName);
        $extension = end($temp);

        return (in_array($extension, $this->allowedFileTypes));
    }
}
