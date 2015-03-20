<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\Reader\ReaderFactory;
use TmlpStats\Message;

abstract class DataImporterAbstract
{
    protected $classDisplayName = "";
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
            throw new \Exception('An error occurred while processing request. StatsReoirt not provided to data importer.');
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
            $this->addMessage('Unable to import tab.', 'error');
        }
    }
    abstract protected function load();

    // Some data isn't all in one large block, but broken into segments.
    // This lets us process each block separately
    abstract protected function populateSheetRanges();

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
            $this->loadEntry($offset, $arg);
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

    protected function addMessage($message, $severity = 'error', $offset = false)
    {
        $this->messages[] = Message::create($this->classDisplayName)->addMessage($message, $severity, $offset);
    }

    public function postProcess() { }
}
