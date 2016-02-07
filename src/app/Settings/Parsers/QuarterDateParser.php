<?php
namespace TmlpStats\Settings\Parsers;

use TmlpStats\Traits\ParsesQuarterDates;

class QuarterDateParser extends DefaultParser
{
    use ParsesQuarterDates;

    /**
     * Although the result is a date, the field data is binary
     *
     * @var string
     */
    protected $format = QuarterDateParser::FORMAT_BINARY;

    /**
     * Parse the setting object and merge with defaults
     *
     * @return array
     * @throws \Exception
     */
    protected function parse()
    {
        $setting = $this->decode();
        if ($setting) {
            return $this->parseQuarterDate($setting);
        }

        return $this->quarter
            ? $this->quarter->getClassroom2Date($this->center)
            : null;
    }
}
