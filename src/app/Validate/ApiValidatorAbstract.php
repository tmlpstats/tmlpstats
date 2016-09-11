<?php
namespace TmlpStats\Validate;

use TmlpStats\Domain\ValidationMessage;

abstract class ApiValidatorAbstract
{
    protected $statsReport = null;
    protected $isValid = true;
    protected $data = null;

    protected $messages = [];

    public function __construct($statsReport)
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

    abstract protected function validate($data);

    public function run($data)
    {
        $this->data = $data;

        if (!$this->validate($data)) {
            $this->isValid = false;
        }

        return $this->isValid;
    }

    /**
     * Get messages
     *
     * @return array Array of messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Create and add message to local message store
     *
     * @param strng $level  Log level (error|warning|info)
     * @param array $data   Message data
     * @param mixed         Arbitrary number of additional arguments
     */
    protected function addMessage($level, $data)
    {
        $this->messages[] = ValidationMessage::create($level, $data);
    }
}
