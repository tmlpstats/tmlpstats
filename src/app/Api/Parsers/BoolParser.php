<?php
namespace TmlpStats\Api\Parsers;

class BoolParser extends ParserBase
{
    protected $type = 'bool';

    public function validate($value)
    {
        return ($value === false
            || $value === true
            || $value === 'false'
            || $value === 'true'
            || $value === 'no'
            || $value === 'yes');
    }

    public function parse($value)
    {
        if ($value === false || $value === 'false' || $value === 'no') {
            return false;
        }
        return true;
    }
}
