<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\Reader\ReaderFactory;
use TmlpStats\Message;

abstract class DataImporterAbstract
{
    protected $sheetId = "";
    protected $sheet = null;
    protected $reader = null;
    protected $statsReport = null;
    protected $blocks = [];

    protected $data = array();

    protected $messages = array();

    public function __construct(&$sheet, &$statsReport)
    {
        if ($sheet == null)
        {
            throw new \Exception('An error occurred while processing request. Stats sheet not provided to data importer.');
        }
        if ($statsReport == null)
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

            // Cleanup to save on memory since this object lives for the whole request
            $this->sheet = null;
            $this->reader = null;
            $this->blocks = null;
        }
        else
        {
            $this->addMessage('IMPORT_TAB_FAILED', null);
        }
    }
    abstract protected function load();

    // Some data isn't all in one large block, but broken into segments.
    // This lets us process each block separately
    abstract protected function populateSheetRanges();

    /**
     * @param $startRow                 Index of the row to start searching in
     * @param $targetStartText          Cell text that indicates the start of the range
     * @param $targetEndText            Cell text that indicates the end of the range
     * @param string $targetStartColumn Column to look in for the targetStartText
     * @param null $targetEndColumn     Column to look in for the targetEndText
     * @return array[start, end]        Array with the start and end of the range, inclusive
     *                                  start is the index immediately after the row with targetStartText
     *                                  end is the index immediately before the row with targetEndText
     */
    protected function findRange($startRow, $targetStartText, $targetEndText, $targetStartColumn = 'A', $targetEndColumn = null)
    {
        $maxSearchRows = 500;
        $maxConsecutiveBlankRows = 100;

        $rangeStart = null;
        $rangeEnd = null;

        if ($targetEndColumn === null) {
            $targetEndColumn = $targetStartColumn;
        }

        $row = $startRow;
        $maxRows = count($this->sheet);
        $targetColumn = $targetStartColumn;

        $searching = true;
        $blankCount = 0;
        while ($searching && $row < $maxSearchRows) {
            $value = $this->sheet[$row][$targetColumn];

            if ($rangeStart === null && $value == $targetStartText) {
                $rangeStart = $row + 1; // Range starts the row after start text
                $targetColumn = $targetEndColumn;
            }

            if ($rangeStart !== null) {
                $searchValue = $this->sheet[$row][$targetStartColumn];
                if ($searchValue === '' || $searchValue === null) {
                    $blankCount++;
                } else {
                    $blankCount = 0;
                }

                if ((is_array($targetEndText) && in_array($value, $targetEndText))
                    || $value == $targetEndText
                    || $blankCount >= $maxConsecutiveBlankRows
                    || $row >= $maxRows
                ) {
                    $rangeEnd = $row - 1; // Range ends the row before end text
                    $searching = false;
                }
            }

            $row++;
        }
        if ($rangeEnd === null) {
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
        return ReaderFactory::build($readerType, $data);
    }

    protected function loadBlock($blockParams, $arg=null)
    {
        foreach ($blockParams['rows'] as $offset)
        {
            try {
                $this->loadEntry($offset, $arg);
            } catch (\Exception $e) {
                $this->addMessage('EXCEPTION_LOADING_ENTRY', $offset, $e->getMessage());
            }
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setValues($object, $data)
    {
        foreach ($data as $key => $value) {
            $object->$key = $value;
        }
        return $object;
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
