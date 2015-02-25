<?php
namespace TmlpStats\Validate;

use TmlpStats\Message;
use TmlpStats\StatsReport;
use TmlpStats\Util;

use Carbon\Carbon;
use Respect\Validation\Validator as v;

abstract class ValidatorAbstract
{
    protected $classDisplayName = '';
    protected $dataValidators = array();

    protected $isValid = true;
    protected $data = NULL;
    protected $reader = NULL;

    protected $messages = array();

    public function __construct() { }

    public function run($data)
    {
        $this->data = $data;
        $this->populateValidators();

        foreach ($this->dataValidators as $field => $validator)
        {
            $value = $this->data->$field;
            if (!$validator->validate($value))
            {
                $displayName = $this->getValueDisplayName($field);
                if ($value === null || $value === '')
                {
                    $value = '[empty]';
                }

                $this->addMessage("Value provided for $displayName '$value' is incorrect.", 'error');
                $this->isValid = false;
            }
        }
        $this->validate();
        return $this->isValid;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    abstract protected function populateValidators();
    abstract protected function validate();

    protected function getValueDisplayName($value)
    {
        return ucwords(Util::toWords($value));
    }

    protected function getOffset($data)
    {
        return $data->offset;
    }

    protected function getStatsReport()
    {
        return StatsReport::findOrFail($this->data->statsReportId);
    }

    protected function getDateObject($date)
    {
        return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
    }

    protected function addMessage($message, $severity = 'error')
    {
        $this->messages[] = Message::create($this->classDisplayName)->addMessage($message, $severity, $this->getOffset($this->data));
    }
}
