<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Util;

class ValidationManager
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected $data = null;
    protected $statsReport = null;
    protected $expectedVersion = null;
    protected $expectedDate = null;

    protected $messages = [];

    public function __construct(&$statsReport, &$data)
    {
        $this->statsReport = $statsReport;
        $this->data = $data;
    }

    public function run()
    {
        $isValid = true;

        foreach ($this->data as $type => $list) {

            if ($type == 'expectedVersion' || $type == 'expectedDate') {
                continue;
            }

            foreach ($list as $dataArray) {
                $data = Util::arrayToObject($dataArray);
                $validator = ValidatorFactory::build($this->statsReport, $type);
                if (!$validator->run($data)) {
                    $isValid = false;
                }
                $this->mergeMessages($validator->getMessages());
            }
        }

        foreach (['teamExpansion', 'centerGames', 'statsReport'] as $type) {

            $validator = ValidatorFactory::build($this->statsReport, $type);
            if (!$validator->run($this->data)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages());
        }

        return $isValid;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    protected function mergeMessages($messages)
    {
        $this->messages = array_merge($this->messages, $messages);
    }
}




