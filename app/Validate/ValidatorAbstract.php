<?php
namespace TmlpStats\Validate;

use TmlpStats\Message;
use TmlpStats\StatsReport;
use TmlpStats\Util;

use Carbon\Carbon;
use Respect\Validation\Validator as v;

abstract class ValidatorAbstract
{
    protected $sheetId = NULL;
    protected $dataValidators = array();

    protected $isValid = true;
    protected $data = null;
    protected $reader = null;

    protected $messages = array();

    public function __construct() { }

    public function run($data)
    {
        $this->data = $data;
        $this->populateValidators($data);

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

                $this->addMessage('INVALID_VALUE', $displayName, $value);
                $this->isValid = false;
            }
        }

        $this->validate($data);
        return $this->isValid;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    abstract protected function populateValidators($data);
    abstract protected function validate($data);

    protected function getValueDisplayName($value)
    {
        return ucwords(Util::toWords($value));
    }

    protected function getOffset($data)
    {
        return $data->offset;
    }

    protected function getDateObject($date)
    {
        return $date ? Carbon::createFromFormat('Y-m-d', $date)->startOfDay() : null;
    }

    protected function addMessage($messageId)
    {
        $message = Message::create($this->sheetId);

        $arguments = array_slice(func_get_args(), 1);
        array_unshift($arguments, $messageId, $this->getOffset($this->data));

        $this->messages[] = $this->getMessage($message, $arguments);
    }

    // @codeCoverageIgnoreStart
    // PHPUnit dropped support for mocking static methods, and I don't want testcases
    // that call into the database.
    protected function getStatsReport()
    {
        return StatsReport::findOrFail($this->data->statsReportId);
    }

    protected function getMessage($message, $arguments)
    {
        return call_user_func_array(array($message, 'addMessage'), $arguments);
    }
    // @codeCoverageIgnoreEnd
}
