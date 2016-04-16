<?php
namespace TmlpStats\Api\Exceptions;

class NotAuthorizedException extends Exception
{
    protected $statusCode = 403;
    protected $statusMessage = 'unauthorized';
}
