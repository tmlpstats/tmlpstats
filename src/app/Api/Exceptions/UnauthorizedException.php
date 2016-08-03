<?php
namespace TmlpStats\Api\Exceptions;

class UnauthorizedException extends Exception
{
    protected $statusCode = 403;
    protected $statusMessage = 'unauthorized';
}
