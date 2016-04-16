<?php
namespace TmlpStats\Api\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class Exception extends HttpException
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
        json_encode([
            'status_code' => $this->statusCode,
            'status_message' => $this->statusMessage,
            'message' => $this->message ?: $this->statusMessage,
        ]);
    }
}
