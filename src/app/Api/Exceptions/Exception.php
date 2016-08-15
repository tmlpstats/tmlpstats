<?php
namespace TmlpStats\Api\Exceptions;

use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Exception extends HttpException implements Arrayable, \JsonSerializable
{
    protected $message = ''; // override to default empty message

    protected $statusCode = 500;
    protected $statusMessage = 'unknown_error';

    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct($this->statusCode, $message ?: $this->statusMessage, $previous, array(), $code);
    }

    public function __toString()
    {
        json_encode($this->toArray());
    }

    /**
     * Implementation for Arrayable
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'status_code' => $this->statusCode,
            'status_message' => $this->statusMessage,
            'message' => $this->getMessage() ?: $this->statusMessage,
        ];
    }

    /**
     * Implementation for JsonSerializable interface
     *
     * @return array  Array that is serializable with json_encode()
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
