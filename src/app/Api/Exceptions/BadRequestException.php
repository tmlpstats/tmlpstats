<?php
namespace TmlpStats\Api\Exceptions;

class BadRequestException extends Exception
{
    protected $statusCode = 400;
    protected $statusMessage = 'bad_request';
    protected $field = null;
    protected $reference = null;

    public function setField($field)
    {
        $this->field = $field;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * Adds a few additional fields to exception.
     *
     * This brings the output closer to Messages output for easier message reuse
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'reference' => $this->reference,
            'field' => $this->field,
            'level' => 'error',
            'id' => class_basename($this) . ':' . $this->field,
        ]);
    }
}
