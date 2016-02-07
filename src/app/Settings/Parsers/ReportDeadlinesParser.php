<?php
namespace TmlpStats\Settings\Parsers;

use Carbon\Carbon;
use TmlpStats\Traits\ParsesQuarterDates;

class ReportDeadlinesParser extends AbstractParser
{
    use ParsesQuarterDates;

    protected $format = ReportDeadlinesParser::FORMAT_JSON;

    protected $reportingDate = null;

    /**
     * Parse the setting object and merge with defaults
     *
     * @return array
     * @throws \Exception
     */
    protected function parse()
    {
        $this->reportingDate = $this->arguments['reportingDate'];

        $deadlines = [
            'report'   => [
                'dueDate'  => '+0days',
                'time'     => '19:00:59',
                'timezone' => $this->center->timezone,
            ],
            'response' => [
                'dueDate'  => '+1days',
                'time'     => '10:00:00',
                'timezone' => $this->center->timezone,
            ],
        ];

        $settings = $this->decode();
        if ($settings) {
            foreach ($settings as $dateInfo) {

                if (!isset($dateInfo['reportingDate'])) {
                    throw new \Exception("Missing reportingDate in setting {$this->setting->id}");
                }

                $reportingDate = $this->parseQuarterDate($dateInfo['reportingDate']);

                // If we're not looking at the setting for this week, skip-it
                if (!$reportingDate->eq($this->reportingDate)) {
                    continue;
                }

                $deadlines = $this->mergeSettings($deadlines, $dateInfo);

                break;
            }
        }

        return $this->prepareResults($deadlines);
    }

    /**
     * Merge the updated settings with the default values
     *
     * The settings override the defaults
     *
     * @param $defaults
     * @param $settings
     *
     * @return mixed
     */
    protected function mergeSettings($defaults, $settings)
    {
        foreach (['report', 'response'] as $type) {
            if (!isset($settings[$type])) {
                continue;
            }

            $setting = $settings[$type];

            if (isset($setting['dueDate'])) {
                $defaults[$type]['dueDate'] = $setting['dueDate'];
            }
            if (isset($setting['time'])) {
                $defaults[$type]['time'] = $setting['time'];
            }
            if (isset($setting['timezone'])) {
                $defaults[$type]['timezone'] = $setting['timezone'];
            }
        }

        return $defaults;
    }

    /**
     * Parse the dueDate field.
     *
     * Values can be any of the following:
     *     +0days, +1day, +2days, etc
     *     An actual date in string format. e.g. 2015-12-31
     *
     * @param $settingValue
     *
     * @return null|static
     * @throws \Exception
     */
    protected function parseDueDate($settingValue)
    {
        if (preg_match('/^\+(\d+)days?$/', $settingValue, $matches)) {
            $offsetDays = $matches[1];
            $dueDate    = $this->reportingDate->copy();

            return $dueDate->addDays($offsetDays);
        }

        if (preg_match('/^\d\d\d\d-\d\d-\d\d$/', $settingValue)) {
            return Carbon::parse($settingValue);
        }

        $blame = $this->setting ? "setting {$this->setting->id}" : "default";
        throw new \Exception("Invalid report dueDate format in {$blame}: {$settingValue}");
    }

    /**
     * Parse the time field.
     *
     * Values must be in the following 24 hour format
     *     01:01:01, 12:00:00, 23:59:59, etc
     *
     * @param $settingValue
     *
     * @return null
     * @throws \Exception
     */
    protected function parseTime($settingValue)
    {
        if (!preg_match('/^[0-2][0-9]:[0-5][0-9]:[0-5][0-9]$/', $settingValue)) {
            $blame = $this->setting ? "setting {$this->setting->id}" : "default";
            throw new \Exception("Invalid report time format in {$blame}: {$settingValue}");
        }

        return $settingValue;
    }

    /**
     * Convert prepared settings arrays into date objects
     *
     * @param $results
     *
     * @return array
     * @throws \Exception
     */
    protected function prepareResults($results)
    {
        $response = [
            'report'   => null,
            'response' => null,
        ];

        foreach (['report', 'response'] as $type) {
            $deadline = $results[$type];

            $dueDate  = $this->parseDueDate($deadline['dueDate']);
            $time     = $this->parseTime($deadline['time']);
            $timezone = $deadline['timezone'];

            $dateString      = $dueDate->toDateString();
            $response[$type] = Carbon::parse(
                "{$dateString} {$time}",
                $timezone
            );
        }

        return $response;
    }
}
