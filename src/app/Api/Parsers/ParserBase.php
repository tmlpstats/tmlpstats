<?php
namespace TmlpStats\Api\Parsers;

use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Util;

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
                $display = ucwords(Util::toWords($key));
                $e = new ApiExceptions\MissingParameterException("{$display} is a required parameter and is missing.");
                $e->setField($key);
                // TODO: don't assume the index key is id
                $e->setRefernce($input->get('id'));
                throw $e;
            } else {
                return null;
            }
        }

        $value = $input->get($key);

        if ($value === null && !$required) {
            return null;
        }

        if (!$this->validate($value)) {
            $display = ucwords(Util::toWords($key));
            $e = new ApiExceptions\BadRequestException("{$display} is not a valid {$this->type}.");
            $e->setField($key);
            // TODO: don't assume the index key is id
            $e->setRefernce($input->get('id'));
            throw $e;
        }

        return $this->parse($value);
    }

    abstract public function validate($value);

    abstract public function parse($value);
}
