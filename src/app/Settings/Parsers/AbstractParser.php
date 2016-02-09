<?php
namespace TmlpStats\Settings\Parsers;

use Carbon\Carbon;
use TmlpStats\Center;
use TmlpStats\Quarter;
use TmlpStats\Setting;

abstract class AbstractParser
{
    /**
     * Valid setting value formats
     */
    const FORMAT_BINARY = 'binary';
    const FORMAT_JSON   = 'json';
    const FORMAT_DATE   = 'date';

    /**
     * Setting value format. Used when parsing value.
     *
     * @var string
     */
    protected $format = AbstractParser::FORMAT_BINARY;

    /**
     * The target setting
     *
     * @var null|Setting
     */
    protected $setting = null;

    /**
     * Center to use when searching for Setting
     *
     * @var null|Center
     */
    protected $center = null;

    /**
     * Quarter to use when searching for Setting
     *
     * @var null|Quarter
     */
    protected $quarter = null;

    /**
     * Additional arguments
     *
     * @var null|mixed
     */
    protected $arguments = null;

    /**
     * AbstractParser constructor.
     *
     * @param Setting      $setting
     * @param Center|null  $center
     * @param Quarter|null $quarter
     * @param null         $arguments
     */
    public function __construct(Setting $setting, Center $center = null, Quarter $quarter = null, $arguments = null)
    {
        $this->setting   = $setting;
        $this->center    = $center;
        $this->quarter   = $quarter;
        $this->arguments = $arguments;
    }

    /**
     * Set value format
     *
     * @param $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Run parser
     *
     * @return array
     * @throws \Exception
     */
    public function run()
    {
        return $this->parse();
    }

    /**
     * Parse the setting object and merge with defaults
     *
     * @return array
     * @throws \Exception
     */
    abstract protected function parse();

    /**
     * Decode setting value based on $this->format
     *
     * @return mixed|null
     * @throws \Exception
     */
    protected function decode()
    {
        if (!$this->setting) {
            return null;
        }

        $value = $this->setting->value;

        if ($this->format == static::FORMAT_BINARY) {
            return $value;
        }

        if ($this->format == static::FORMAT_JSON) {
            return json_decode($value, true);
        }

        if ($this->format == static::FORMAT_DATE) {
            if (!preg_match('/^\d\d\d\d-\d\d-\d\d( \d\d:\d\d:\d\d)?$/', $value)) {
                throw new \Exception("Invalid date format '{$value}'");
            }

            return Carbon::parse($value);
        }

        throw new \Exception("Invalid format type '{$this->format}'");
    }
}
