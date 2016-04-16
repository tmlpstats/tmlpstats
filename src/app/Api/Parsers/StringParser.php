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
        return (string) $value;
    }
}
