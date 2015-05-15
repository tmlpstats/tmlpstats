<?php
namespace TmlpStats\Import\Xlsx\ImportDocument;

use TmlpStats\Import\ImportDocument;
use TmlpStats\Validate;
use PHPExcel_IOFactory;
use Carbon\Carbon;

abstract class ImportDocumentAbstract extends \TmlpStats\Import\ImportDocument
{
    protected $xlsType = 'Excel2007'; // Same format for Excel 2010
    protected $sheetNames = array('Current Weekly Stats', 'Class List', 'CAP & CPC Course Info.', 'Local Team Contact Info.');
    protected $sheets = array(null, null, null, null);

    protected $version = null;
    protected $expectedDate = null;
    protected $enforceVersion = true;

    protected $messages = array(
        'errors' => array(),
        'warnings' => array(),
    );

    abstract protected function loadVersion();

    public function __construct($file, $expectedDate = null, $enforceVersion = true)
    {
        $this->loadWorkbook($file);

        $this->loadVersion();

        $this->expectedDate = $expectedDate;
        $this->enforceVersion = $enforceVersion;
    }

    public function import($validateReport = true)
    {
        $isValid = false;
        $this->process();


        if ($this->statsReport) {
            if (!$validateReport || $this->validateReport()) {
                $this->postProcess();
                $this->statsReport->validated = true;
                $isValid =  true;
            } else {
                $this->statsReport->validated = false;
            }
            $this->statsReport->save();

            if (defined('VALIDATE_ONLY') || !$isValid || !$this->isValid()) {
                // Flush all data imported from this report. Keeps db clean from
                // data left over from invalid sheets
                $this->statsReport->clear();
            }
        }

        // Check if there were any errors during import
        if (!$this->isValid()) {
            $isValid = false;
        }

        return $isValid;
    }

    public function __get($name)
    {
        switch($name) {

            case 'version': return $this->version;
            case 'messages': return $this->messages;
            default: return parent::__get($name);
        }
    }

    public function isValid()
    {
        return count($this->messages['errors']) == 0;
    }

    protected function validateReport()
    {
        $validator = $this->getValidator();
        return $validator->run($this->statsReport);
    }

    protected function getValidator($type = null)
    {
        return Validate\ValidatorFactory::build($this->version, $type);
    }

    protected function loadWorkbook($file)
    {
        $reader = PHPExcel_IOFactory::createReader($this->xlsType);

        // Verify document has only the expected worksheets
        $expectedSheets = $this->sheetNames;
        array_push($expectedSheets, 'Instructions - Revision History');

        $inputWorksheetNames = $reader->listWorksheetNames($file);
        $diff = array_diff($inputWorksheetNames, $expectedSheets);
        if (count($diff) > 0) {
            $ignoreExtra = true;;
            foreach ($diff as $sheet) {

                // Ignore default sheets excel may add
                if (!preg_match('/^Sheet\d+$/', $sheet)) {
                    $ignoreExtra = false;
                }
            }

            if (!$ignoreExtra) {
                throw new \Exception("Excel document doesn't appear to be a center stats report.");
            }
        }

        // Make sure we can load the document
        $reader->setLoadSheetsOnly($this->sheetNames);
        $doc = $reader->load($file);
        if (!$doc) {
            throw new \Exception("Unable to load excel file.");
        }

        $this->loadAllSheets($doc);

        // Disconnect the worksheets to save memory (added for importing large numbers of sheets)
        $doc->disconnectWorksheets();
    }

    protected function loadAllSheets($doc)
    {
        $loadedCount = 0;
        for($i = 0; $i < count($this->sheetNames); $i++) {

            $sheet = $this->loadSheet($i, $doc);
            if (!is_array($sheet) || count($sheet) == 0) {
                throw new \Exception("Workbook is missing sheet '{$this->sheetNames[$i]}'");
            }
        }
    }

    protected function loadSheet($index, $doc=null)
    {
        if ($this->sheets[$index] === null && $doc) {
            $sheet = $doc->getSheetByName($this->sheetNames[$index]);
            if (!$sheet) {
                throw new \Exception("Could not find sheet {$this->sheetNames[$index]}");
            } else {
                $this->sheets[$index] = $sheet->toArray(null,true,false,true);
            }
        }
        return $this->sheets[$index];
    }

    protected function postProcess() { }
}
