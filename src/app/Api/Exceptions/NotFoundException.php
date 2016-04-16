<?php
namespace TmlpStats\Api\Exceptions;

class NotFoundException extends Exception
{
    protected $statusCode = 404;
    protected $statusMessage = 'not_found';
}
