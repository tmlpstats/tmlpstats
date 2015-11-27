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

    protected $workspace = [];

    public function __construct(&$statsReport)
    {
        $this->statsReport = $statsReport;
    }

    public function run(&$data)
    {
        $isValid = true;

        foreach (array_keys($data) as $type) {

            if ($type == 'expectedVersion' || $type == 'expectedDate') {
                continue;
            }

            if (!$this->processDataList($type, $data[$type])) {
                $isValid = false;
            }
        }

        $duplicateChecks = [
            'duplicateTeamMember'       => 'classList',
            'duplicateTmlpRegistration' => 'tmlpRegistration',
        ];
        foreach ($duplicateChecks as $type => $key) {
            if (!$this->processDataList($type, $data[$key])) {
                $isValid = false;
            }
        }

        // These need needs the class list to
        $teamMemberExistsChecks = [
            'contactInfoTeamMember' => 'contactInfo',
            'committedTeamMember'   => 'tmlpRegistration',
        ];
        foreach ($teamMemberExistsChecks as $type => $key) {
            if (!$this->processDataList($type, $data[$key], $this->workspace['duplicateTeamMember'])) {
                $isValid = false;
            }
        }

        foreach (['teamExpansion', 'centerGames', 'statsReport'] as $type) {
            $validator = ValidatorFactory::build($this->statsReport, $type);
            if (!$validator->run($data)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages());
        }

        $this->resetValidators();

        return $isValid;
    }

    public function processDataList($type, &$list, $supplementalData = null)
    {
        if (!isset($this->workspace[$type])) {
            $this->workspace[$type] = [];
        }

        $isValid = true;
        foreach ($list as &$dataArray) {
            $dataObj = Util::arrayToObject($dataArray);
            $validator = ValidatorFactory::build($this->statsReport, $type);
            if (!$validator->run($dataObj, $supplementalData)) {
                $isValid = false;
                foreach ($validator->getMessages() as $message) {
                    if (isset($message['offset'])) {
                        $dataArray['errors'][] = $message;
                    }
                }
            }
            $this->updateWorkspace($type, $validator->getWorkingData());
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

    protected function updateWorkspace($type, $workspace)
    {
        $this->workspace[$type] = array_merge($this->workspace[$type], $workspace);
    }

    /**
     * Reset the working data for all validators that reported it.
     *
     * @throws \Exception
     */
    protected function resetValidators()
    {
        foreach ($this->workspace as $type => $data) {
            if ($data) {
                $validator = ValidatorFactory::build($this->statsReport, $type);
                $validator->resetWorkingData();
            }
        }
    }
}




