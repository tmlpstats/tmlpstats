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
        if (!$input->has($key)) {
            if ($required) {
                throw new ApiExceptions\MissingParameterException("{$key} is a required parameter and is missing.");
            } else {
                return null;
            }
        }

        $value = $input->get($key);

        if ($value === null && !$required) {
            return null;
        }

        if (!$this->validate($value)) {
            throw new ApiExceptions\BadRequestException("{$key} is not a valid {$this->type}.");
        }

        return $this->parse($value);
    }

    abstract public function validate($value);

    abstract public function parse($value);
}
