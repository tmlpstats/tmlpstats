<?php
namespace TmlpStats\Api\Parsers;

abstract class IdOrAbbrParserBase extends ParserBase
{
    protected $type = 'id';
    protected $class = '';
    protected $parsed;

    public function validate($value)
    {
        if (is_numeric($value) && $value <= 0) {
            return false;
        }

        if (!is_numeric($value) && (!is_string($value) || !preg_match('/^[a-zA-Z]{2,5}$/', $value))) {
            return false;
        }
        return $this->exists($value);
    }

    public function parse($id)
    {
        $class = $this->class;

        if ($this->parsed) {
            return $this->parsed;
        }

        if (is_numeric($id)) {
            return $class::find($id);
        } else {
            return $class::abbreviation($id)->first();
        }
    }

    public function exists($id)
    {
        $this->parsed = $this->parse($id);

        return ($this->parsed !== null);
    }
}
