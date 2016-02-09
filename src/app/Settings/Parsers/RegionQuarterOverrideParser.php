<?php
namespace TmlpStats\Settings\Parsers;

use Carbon\Carbon;

class RegionQuarterOverrideParser extends AbstractParser
{
    protected $format = ReportDeadlinesParser::FORMAT_JSON;

    /**
     * Parse the setting value and return an array of overriden dates
     *
     * @return array
     * @throws \Exception
     */
    protected function parse()
    {
        $validFields = [
            'startWeekendDate',
            'endWeekendDate',
            'classroom1Date',
            'classroom2Date',
            'classroom3Date',
        ];

        $response = [];

        $settings = $this->decode();
        if ($settings) {
            foreach ($settings as $field => $value) {
                if (in_array($field, $validFields)) {
                    $response[$field] = Carbon::parse($value);
                }
            }
        }

        return $response;
    }
}
