<?php
namespace TmlpStats\Validate;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Util;
use TmlpStats\Validate\Sequences\ApplicationSequenceValidator;

class ValidationManager
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected $statsReport = null;
    protected $expectedVersion = null;
    protected $expectedDate = null;

    protected $messages = [];

    public function __construct(&$statsReport)
    {
        $this->statsReport = $statsReport;
    }

    public function run(&$data)
    {
        $isValid = true;

        foreach ($data as $type => &$list) {

            if ($type == 'expectedVersion' || $type == 'expectedDate') {
                continue;
            }

            foreach ($list as &$dataArray) {
                $dataObj = Util::arrayToObject($dataArray);
                $validator = ValidatorFactory::build($this->statsReport, $type);
                if (!$validator->run($dataObj)) {
                    $isValid = false;
                    foreach ($validator->getMessages() as $message) {
                        if (isset($message['offset'])) {
                            $dataArray['errors'][] = $message;
                        }
                    }
                }
                $this->mergeMessages($validator->getMessages());
            }
        }

        foreach (['teamExpansion', 'centerGames', 'statsReport'] as $type) {

            $validator = ValidatorFactory::build($this->statsReport, $type);
            if (!$validator->run($data)) {
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




