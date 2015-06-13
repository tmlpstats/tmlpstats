<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\Reader\ReaderFactory;
use TmlpStats\Message;

abstract class DataImporterAbstract
{
    protected $sheetId = "";
    protected $sheet = NULL;
    protected $reader = NULL;
    protected $statsReport = NULL;

    protected $messages = array();

    public function __construct(&$sheet, &$statsReport)
    {
        if ($sheet == NULL)
        {
            throw new \Exception('An error occurred while processing request. Stats sheet not provided to data importer.');
        }
        if ($statsReport == NULL)
        {
            throw new \Exception('An error occurred while processing request. StatsReport not provided to data importer.');
        }
        $this->sheet = $sheet;
        $this->statsReport = $statsReport;
        $this->populateSheetRanges();

        if (empty($this->classDisplayName))
        {
            $this->classDisplayName = get_class($this);
        }
    }

    public function getMessages()
    {
        return $this->messages;
    }

    public function import()
    {
        if ($this->statsReport)
        {
            $this->load();
        }
        else
        {
            $this->addMessage('IMPORT_TAB_FAILED', false);
        }
    }
    abstract protected function load();

    // Some data isn't all in one large block, but broken into segments.
    // This lets us process each block separately
    abstract protected function populateSheetRanges();

    protected function findRange($startRow, $targetStartText, $targetEndText, $targetColumn = 'A')
    {
        $rangeStart = NULL;
        $rangeEnd = NULL;

        $row = $startRow;
        $searching = true;
        $maxRows = count($this->sheet);
        $maxSearchRows = 1000;
        while ($searching && $row < $maxSearchRows) {
            $value = $this->sheet[$row][$targetColumn];

            if ($rangeStart === NULL && $value == $targetStartText) {
                $rangeStart = $row + 1; // Range starts the row after start text
            }

            if ($rangeEnd === NULL && ($value == $targetEndText || $row >= $maxRows)) {
                $rangeEnd = $row - 1; // Range ends the row before end text
                $searching = false;
            }

            $row++;
        }
        if ($rangeEnd === NULL) {
            $rangeEnd = $row;
        }

        return array('start' => $rangeStart, 'end' => $rangeEnd);
    }

    protected function getReader($data)
    {
        // First, strip off the namespace if there is one
        $class = preg_replace("/^\\\\?(\w+\\\\)*/", '', get_class($this));

        // Now, grab the type
        $readerType = preg_replace("/Importer$/", '', $class);
        return ReaderFactory::build($readerType, $this->statsReport->spreadsheetVersion, $data);
    }

    protected function loadBlock($blockParams, $arg=NULL)
    {
        foreach($blockParams[1] as $offset)
        {
            try {
                $this->loadEntry($offset, $arg);
            } catch (\Exception $e) {
                $this->addMessage('EXCEPTION_LOADING_ENTRY', $offset, $e->getMessage());
            }
        }
    }

    protected function getOffset($data)
    {
        return $data->offset;
    }

    protected function excelRange($start, $end)
    {
        $MAX_RANGE = 1000;

        $values = array();
        if (is_numeric($start) && is_numeric($end))
        {
            $values = range($start, $end);
        }
        else
        {
            // This can handle ranges of letters. e.g. A to M, or C to AG
            for($i=0;$i<$MAX_RANGE;$i++)
            {
                $values[] = $start;
                if ($start == $end) break;
                $start++;
            }
        }
        return $values;
    }

    protected function addMessage($messageId, $offset)
    {
        $message = Message::create($this->sheetId);

        $arguments = func_get_args();

        $this->messages[] = call_user_func_array(array($message, 'addMessage'), $arguments);
    }

    public function postProcess() { }
}
