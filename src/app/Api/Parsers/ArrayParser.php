<?php
namespace TmlpStats\Api\Parsers;

class ArrayParser extends ParserBase
{
    protected $type = 'array';
    protected $parsed;

    public function validate($value)
    {
        if (is_array($value)) {
            return true;
        } else if (is_string($value)) {
            $this->parsed = $this->parse($value);
            return is_array($this->parsed);
        }

        return false;
    }

    public function parse($value)
    {
        if ($this->parsed) {
            return $this->parsed;
        }

        return is_array($value) ? $value : json_decode($value, true);
    }
}
