<?php
namespace TmlpStats\Api\Parsers;

use Carbon\Carbon;

class DateParser extends ParserBase
{
    protected $type = 'date';
    protected $parsed;

    public function validate($value)
    {
        if ($value instanceof Carbon) {
            $this->parsed = $value;
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        try {
            $this->parsed = $this->parse($value);
            return ($this->parsed instanceof Carbon);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function parse($value)
    {
        return $this->parsed ?: Carbon::parse($value)->startOfDay();
    }
}
