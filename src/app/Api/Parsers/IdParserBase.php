<?php
namespace TmlpStats\Api\Parsers;

abstract class IdParserBase extends ParserBase
{
    protected $type = 'id';
    protected $class = '';
    protected $parsed;

    public function validate($value)
    {
        if (!is_numeric($value) || $value <= 0) {
            return false;
        }

        return $this->exists($value);
    }

    public function parse($id)
    {
        $class = $this->class;

        return $this->parsed ?: $class::find($id);
    }

    public function exists($id)
    {
        $this->parsed = $this->parse($id);

        return ($this->parsed !== null);
    }
}
