<?php
namespace TmlpStats\Api\Parsers;

abstract class IdOrAbbrParserBase extends ParserBase
{
    protected $type = 'id';
    protected $class = '';
    protected $allowAbbr = true;
    protected $allowObj = false;
    protected $keyAttr = 'id';
    protected $parsed;

    public function validate($value)
    {
        if (is_numeric($value)) {
            if ($value <= 0) {
                return false;
            }
        } else if (is_array($value)) {
            if (!$this->allowObj) {
                return false;
            }
        } else {
            // not numeric and obj not allowed, so check abbr string
            if (!$this->allowAbbr || !is_string($value) || !preg_match('/^[a-zA-Z]{2,5}$/', $value)) {
                return false;
            }
        }

        return $this->exists($value);
    }

    public function parse($id)
    {
        if ($this->parsed) {
            return $this->parsed;
        }

        if (is_array($id)) {
            if (!$this->allowObj) {
                throw new \Exception('Array input not allowed');
            }
            if (!array_key_exists($this->keyAttr, $id)) {
                return null;
            }
            $id = $id[$this->keyAttr];
        }

        $class = $this->class;

        if (is_numeric($id)) {
            return $class::find(intval($id));
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
