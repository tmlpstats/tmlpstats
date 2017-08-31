<?php
namespace TmlpStats\Api\Parsers;

class StringParser extends ParserBase
{
    protected $type = 'string';

    public function validate($value)
    {
        return is_string($value);
    }

    public function parse($value)
    {
        $value = (string) $value;
        if (array_get($this->parserOptions, 'trim', false)) {
            $value = trim($value);
        }

        return $value;
    }
}
