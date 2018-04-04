<?php
namespace TmlpStats;

use App;
use Cache;
use Carbon\Carbon;
use Exception;
use Log;
use Session;
use TmlpStats\Api;
use stdClass;

class Util
{
    /**
     * Convert camelCase string to lowercase words
     *
     * @param $str
     *
     * @return string
     */
    public static function toWords($str)
    {
        $output = preg_replace("/([A-Z])/", " $1", $str);
        return strtolower($output);
    }

    public static function formatPhone($phone)
    {
        // TODO: This handles the standard 10 digit North American phone number. Update to handle international formats
        if ($phone && preg_match('/^(\d\d\d)[\s\.\-]?(\d\d\d)[\s\.\-]?(\d\d\d\d)$/', $phone, $matches)) {
            return "({$matches[1]}) {$matches[2]}-{$matches[3]}";
        }

        return $phone;
    }

    /**
     * Convert a string with an unknown date format into a Carbon datetime object
     *
     * @param $dateStr
     *
     * @return null|static
     */
    public static function parseUnknownDateFormat($dateStr)
    {
        $dateObj = null;
        try {
            if (preg_match("/^\d{5,}$/", $dateStr)) { // Unix timestamp
                $dateObj = Carbon::createFromFormat('U', $dateStr);
            } else if (preg_match("/^\d\d-\d\d-\d\d\d\d$/", $dateStr)) { // 01-01-2015
                $dateObj = Carbon::createFromFormat('m-d-Y', $dateStr);
            } else if (preg_match("/^\d\d\/\d\d\/\d\d\d\d$/", $dateStr)) { // 01/01/2015
                $dateObj = Carbon::createFromFormat('m/d/Y', $dateStr);
            } else if (preg_match("/^\d\d\d\d\/\d\d\/\d\d$/", $dateStr)) { // 2015/01/01
                $dateObj = Carbon::createFromFormat('Y/m/d', $dateStr);
            } else if (preg_match("/^\d\d\d\d-\d\d-\d\d$/", $dateStr)) { // 2015-01-01
                $dateObj = Carbon::createFromFormat('Y-m-d', $dateStr);
            } else if (preg_match("/^\d\d?-[a-zA-Z]{3}$/", $dateStr)) { // 1-Jan
                $dateObj = Carbon::createFromFormat('j-M', $dateStr);
            } else if (preg_match("/^\d\d?\/\d\d?$/", $dateStr)) { // 1/1
                $dateObj = Carbon::createFromFormat('n-j', $dateStr);
            } else if (preg_match("/^\d\d?-[a-zA-Z]{3}-\d\d$/", $dateStr)) { // 1-Jan-15
                $dateObj = Carbon::createFromFormat('j-M-y', $dateStr);
            } else {
                $dateObj = Carbon::createFromFormat('U', strtotime($dateStr));
            }
        } catch (Exception $e) {
            Log::error("Unable to parse date '$dateStr'.");
        }
        return $dateObj ? $dateObj->startOfDay() : null;
    }

    /**
     * Get the previously set report date, or today
     *
     * @return Carbon
     */
    public static function getReportDate()
    {
        return App::make(Api\Context::class)->getReportingDate();
    }

    /**
     * Get a random string (sha512 that's been base64 encoded to shorten it)
     *
     * @return string
     */
    public static function getRandomString()
    {
        return str_random(64);
    }
}
