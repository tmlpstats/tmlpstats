<?php
namespace TmlpStats;

use Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use Session;
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

    /**
     * Convert snake_case string to camelCase
     *
     * @param $str
     *
     * @return mixed
     */
    public static function camelCase($str)
    {
        $parts = explode('_', $str);
        $output = '';
        if (count($parts) > 0) {
            $output = array_shift($parts);
        }
        foreach ($parts as $part) {

            $output .= ucfirst($part);
        }
        return $output ?: $str;
    }

    /**
     * Convert an associative array to an object using the camelCase version of the keys as properties
     *
     * @param $array
     *
     * @return stdClass
     */
    public static function arrayToObject($array)
    {
        $object = new stdClass;

        foreach ($array as $key => $value) {

            $objectKey = static::camelCase($key);
            $object->$objectKey = $value;
        }

        return $object;
    }

    /**
     * Convert an object with snake_case properties to an object with camelCase properties
     *
     * @param $object
     *
     * @return stdClass
     */
    public static function objectToCamelCase($object)
    {
        $new = new stdClass;
        $properties = get_object_vars($object);

        foreach ($properties as $key => $value) {

            $newKey = static::camelCase($key);
            $new->$newKey = $value;
        }
        return $new;
    }

    /**
     * Convert an Excel date integer into a Carbon datetime object
     *
     * @param $excelDate
     *
     * @return bool|Carbon
     */
    public static function getExcelDate($excelDate)
    {
        $dateObj = null;
        // Excel dates are numeric. If it's not, then it's probably some kind of date string.
        if (is_numeric($excelDate)) {
            $formattedDate = \PHPExcel_Style_NumberFormat::toFormattedString($excelDate, "YYYY-MM-DD");
            $dateObj = Carbon::createFromFormat('Y-m-d', $formattedDate);
        }

        return $dateObj ? $dateObj->startOfDay() : false;
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
     * Assumed report date
     *
     * @var null|Carbon
     */
    protected static $reportDate = null;

    /**
     * Set the report date
     *
     * @param Carbon $date
     */
    public static function setReportDate(Carbon $date)
    {
        static::$reportDate = $date;
    }

    /**
     * Get the previously set report date, or today
     *
     * @return Carbon
     */
    public static function getReportDate()
    {
        return static::$reportDate ?: Carbon::now()->startOfDay();
    }

    /**
     * Alias for getReportDate() except you can optionally include the current time
     *
     * @param bool|false $includeTime
     *
     * @return Carbon|static
     */
    public static function now($includeTime = false)
    {
        $date = static::getReportDate();

        if ($includeTime && $date->eq(Carbon::now()->startOfDay())) {
            $date = Carbon::now();
        }

        return $date;
    }

    /**
     * Split a name string into firstName and lastName parts
     *
     * @param $name
     *
     * @return array
     */
    public static function getNameParts($name)
    {
        $parts = array(
            'firstName' => '',
            'lastName'  => '',
        );

        if (strpos($name, '/') !== false) {
            $name = str_replace('/', ' ', $name);
        }

        $names = explode(' ', trim($name));

        $partsCount = count($names);
        if ($names && $partsCount > 0) {
            // For names Like 'Mary Louise C'
            if ($partsCount > 2) {
                $parts['firstName'] = implode(' ', array_slice($names, 0, -1));
                $parts['firstName'] = trim($parts['firstName']);
                $parts['lastName'] = trim(str_replace('.', '', $names[$partsCount - 1]));
            } else {
                $parts['firstName'] = trim($names[0]);
                if ($partsCount > 1) {
                    $parts['lastName'] = trim(str_replace('.', '', $names[1]));
                }
            }
        }
        return $parts;
    }

    /**
     * Get a random string (sha512 that's been base64 encoded to shorten it)
     *
     * @return string
     */
    public static function getRandomString()
    {
        $b64Hash = base64_encode(hash('sha512', openssl_random_pseudo_bytes(32), true));

        return str_replace(['+','/','='], ['-','_',''], $b64Hash);
    }

    /**
     * Get the current active sessions from memcache. Careful using this in production.
     *
     * @return array
     */
    public static function getMemcacheSessions()
    {
        $sessions = [];

        try {
            $memcache = new \Memcache;
            $memcache->connect('127.0.0.1', 11211) or die ("Could not connect to memcache server");
            $allSlabs = $memcache->getExtendedStats('slabs');
            foreach ($allSlabs as $server => $slabs) {
                foreach ($slabs AS $slabId => $slabMeta) {
                    $cdump = $memcache->getExtendedStats('cachedump', (int)$slabId);
                    foreach ($cdump AS $keys => $arrVal) {
                        if (!is_array($arrVal)) continue;
                        foreach ($arrVal AS $k => $v) {
                            if (preg_match('/^laravel:([^:]+)$/', $k, $matches)) {
                                $sessions[$matches[1]] = unserialize(Cache::get($matches[1]));
                            }
                        }
                    }
                    usleep(20000);// 20ms between lookups
                }
            }
        } catch (\Exception $e) { }

        return $sessions;
    }

    /**
     * Get the base classname of an object.
     *   e.g.
     *      \TmlpStats\StatsReport => StatsReport
     *
     * @param $object
     * @return string
     */
    public static function getClassBasename($object)
    {
        return substr(strrchr(get_class($object), '\\'), 1);
    }

    /**
     * Get the date formatted using the locale
     * e.g.
     *      en-US => 12/25/2015
     *      en-UK => 25/12/2015
     *
     * @return string
     */
    public static function getLocaleDateFormat()
    {
        $format = 'M j, Y';
        if (Session::has('locale')) {
            $format = Session::get('locale') == 'en-US'
                ? 'n/j/y'
                : 'j/n/y';
        }

        return $format;
    }
}
