<?php
namespace TmlpStats\Validate;

use TmlpStats\Message;
use TmlpStats\StatsReport;

abstract class ValidatorAbstract
{
    protected $sheetId = null;

    protected $isValid = true;
    protected $data = null;
    protected $statsReport = null;

    protected $messages = array();

    public function __construct(&$statsReport)
    {
        $this->statsReport = $statsReport;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'center':
            case 'quarter':
            case 'reportingDate':
                return $this->statsReport->$name;
            default:
                return null;
        }
    }

    public function run($data)
    {
        $this->data = $data;

        $this->validate($data);

        return $this->isValid;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    abstract protected function validate($data);

    protected function getOffset($data)
    {
        return isset($data->offset) ? $data->offset : null;
    }

    protected function addMessage($messageId)
    {
        $message = Message::create($this->sheetId);

        $arguments = array_slice(func_get_args(), 1);
        array_unshift($arguments, $messageId, $this->getOffset($this->data));

        $this->messages[] = $this->callMessageAdd($message, $arguments);
    }

    // @codeCoverageIgnoreStart
    protected function callMessageAdd($message, $arguments)
    {
        return call_user_func_array(array($message, 'addMessage'), $arguments);
    }
    // @codeCoverageIgnoreEnd
}
