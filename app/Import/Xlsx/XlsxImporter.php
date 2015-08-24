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

    public function import($saveReport = true)
    {
        $doc = new ImportDocument\ImportDocument($this->file, $this->expectedDate, $this->enforceVersion);
        $doc->import($saveReport);

        $submittedAt = null;
        if ($doc->saved() && $doc->statsReport) {
            // convert timestamp to use center's local time
            $submittedAt = clone $doc->statsReport->submittedAt;
            $submittedAt->setTimezone($doc->statsReport->center->timeZone);
        }

        $this->results = array(
            'statsReportId' => ($doc->statsReport) ? $doc->statsReport->id : null,
            'statsReport'   => ($doc->statsReport) ? $doc->statsReport : null,
            'centerId'      => ($doc->center) ? $doc->center->id : null,
            'center'        => ($doc->center) ? $doc->center->name : null,
            'reportingDate' => ($doc->reportingDate) ? $doc->reportingDate : null,
            'sheetVersion'  => ($doc->version) ? $doc->version : null,
            'sheetFilename' => ($doc->statsReport) ? $doc->statsReport->center->sheetFilename : null,
            'submittedAt'   => $submittedAt,
            'errors'        => $doc->messages['errors'],
            'warnings'      => $doc->messages['warnings'],
        );

        if (count($this->results['errors']) > 0) {
            $this->results['result'] = 'error';
        } else if (count($this->results['warnings']) > 0) {
            $this->results['result'] = 'warning';
        } else {
            $this->results['result'] = 'ok';
        }
    }

    public function getResults()
    {
        return $this->results;
    }

    protected function isCorrectFileType($fileName)
    {
        $temp = explode(".", $fileName);
        $extension = end($temp);

        return (in_array($extension, $this->allowedFileTypes));
    }
}
