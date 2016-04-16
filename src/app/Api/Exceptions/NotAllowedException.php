<?php
namespace TmlpStats\Api\Exceptions;

class NotAllowedException extends Exception
{
    protected $statusCode = 405;
    protected $statusMessage = 'method_not_allowed';
}
