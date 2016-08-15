<?php
namespace TmlpStats\Api\Parsers;

class IntParser extends ParserBase
{
    protected $type = 'number';

    public function validate($value)
    {
        return is_numeric($value);
    }

    public function parse($value)
    {
        return intval($value);
    }
}
