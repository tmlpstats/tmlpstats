<?php
namespace TmlpStats\Traits;

use TmlpStats\Contracts\Referenceable;
use TmlpStats\Domain;
use TmlpStats\Message;
use TmlpStats\Settings\Setting;

trait GeneratesApiMessages
{
    /**
     * Override message class for specific message set
     * @var string
     */
    protected $messageClass = Message::class;

    /**
     * Get messages
     *
     * This is required because we can't know if the has already included a $messages member.
     *
     * @return array Array of messages
     */
    public function getMessages()
    {
        if (!isset($this->messages)) {
            $this->messages = [];
        }

        return $this->messages;
    }

    /**
     * Get sheet ID
     *
     * This is required because we can't know if the has already included a $sheetId member.
     *
     * @return string Sheet identifier
     */
    public function getSheetId()
    {
        if (!isset($this->sheetId)) {
            $this->sheetId = 'unknown';
        }

        return $this->sheetId;
    }

    /**
     * Create and add message to local message store
     *
     * @param string $messageId Message identifier
     * @param mixed  Arbitrary number of additional arguments
     */
    protected function addMessage($messageId)
    {
        $class = $this->messageClass;
        $message = $class::create($this->getSheetId());
        $offset = null;

        $arguments = func_get_args();
        if (isset($this->data) && $this->data instanceof Referenceable) {
            $offset = $this->data->getKey();
        }
        array_splice($arguments, 1, 0, [$offset]);

        return $this->messages[] = $this->callMessageAdd($message, $arguments);
    }

    /**
     * Calls addMessage on message class
     *
     * @codeCoverageIgnore
     */
    protected function callMessageAdd($message, $arguments)
    {
        return call_user_func_array([$message, 'addMessage'], $arguments);
    }


    /**
     * New Interface
     */

    public function createMessage($messageId, $parameters = [], $reference = null, $level = 'error')
    {
        if (!is_array($reference) && $reference instanceof Referenceable) {
            $reference = $reference->getReference();
        }

        return Domain\ValidationMessage::create($messageId, $parameters)
            ->level($level)
            ->ref($reference);
    }

    public function createError($messageId, $parameters = [], $reference = null)
    {
        return $this->createMessage($messageId, $parameters, $reference, 'error');
    }

    public function createWarning($messageId, $parameters = [], $reference = null)
    {
        return $this->createMessage($messageId, $parameters, $reference, 'warning');
    }

    public function createInfo($messageId, $parameters = [], $reference = null)
    {
        return $this->createMessage($messageId, $parameters, $reference, 'info');
    }
}
