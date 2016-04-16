<?php
namespace TmlpStats\Api\Exceptions;

class BadRequestException extends Exception
{
    protected $statusCode = 400;
    protected $statusMessage = 'bad_request';
}
