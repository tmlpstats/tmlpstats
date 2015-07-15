<?php
namespace TmlpStats\Import\Xlsx;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;

// This handles taking an uploaded file, validating that the data
class XlsxImporter
{
    protected $allowedFileTypes = array('xlsx', 'xlsm');
    protected $importDocument = null;

    protected $file = '';
    protected $version = '';
    protected $expectedDate = null;
    protected $enforceVersion = true;

    public function __construct($filePath, $fileName, $expectedDate = null, $enforceVersion = true)
    {
        if (!is_file($filePath)) {
            // TODO: log this condition
            throw new \Exception("There was a problem processing '$fileName'. Please try again.");
        }
        if (!$this->isCorrectFileType($fileName)) {

            throw new \Exception("Uploaded file '$fileName' is not in the correct file format. The following formats are supported: "
                                 . implode(', ', $this->allowedFileTypes));
        }

        $this->file = $filePath;
        $this->expectedDate = $expectedDate;
        $this->enforceVersion = $enforceVersion;

        $this->importDocument = $this->getNewImportDocument();
    }

    public function import($saveReport = true)
    {
        if ($this->importDocument) {

            return $this->importDocument->import($saveReport);
        }
    }
    public function getImportDocument()
    {
        return $this->importDocument;
    }

    protected function getNewImportDocument()
    {
        return new ImportDocument($this->file, $this->expectedDate, $this->enforceVersion);
    }

    protected function isCorrectFileType($fileName)
    {
        $temp = explode(".", $fileName);
        $extension = end($temp);

        return (in_array($extension, $this->allowedFileTypes));
    }
}
