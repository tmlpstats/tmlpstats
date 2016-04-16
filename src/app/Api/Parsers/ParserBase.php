<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\Api\Exceptions as ApiExceptions;

abstract class ParserBase
{
    protected $type = '';

    public static function create()
    {
        return new static();
    }

    public function run($input, $key, $required = true)
    {
        if ($required && !$input->has($key)) {
            throw new ApiExceptions\MissingParameterException("{$key} is a required parameter and is missing.");
        }

        $value = $input->get($key);

        if (!$this->validate($value)) {
            throw new ApiExceptions\BadRequestException("{$key} is not a valid {$this->type}.");
        }

        return $this->parse($value);
    }

    abstract public function validate($value);

    abstract public function parse($value);
}
