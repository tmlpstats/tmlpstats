<?php
namespace TmlpStats\Api\Exceptions;

class NotAuthenticatedException extends Exception
{
    protected $statusCode = 401;
    protected $statusMessage = 'unauthenticated';
}
