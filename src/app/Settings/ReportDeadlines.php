<?php
namespace TmlpStats\Settings;

use TmlpStats\Center;
use TmlpStats\Quarter;
use TmlpStats\Settings\Parsers\ReportDeadlinesParser;

class ReportDeadlines extends Setting
{
    protected static $settingName = 'reportDeadlines';
    protected static $parserClass = ReportDeadlinesParser::class;

    /**
     * Override getter to make passing reportingDate more convenient
     *
     * @param Center|null  $center
     * @param Quarter|null $quarter
     * @param Carbon|null  $reportingDate
     *
     * @return mixed
     */
    public static function get(Center $center = null, Quarter $quarter = null, $reportingDate = null)
    {
        $arguments = [
            'reportingDate' => $reportingDate,
        ];

        return parent::get($center, $quarter, $arguments);
    }
}
