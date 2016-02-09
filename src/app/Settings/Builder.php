<?php
namespace TmlpStats\Settings;

use TmlpStats\Center;
use TmlpStats\Quarter;
use TmlpStats\Setting as SettingModel;
use TmlpStats\Settings\Parsers\DefaultParser;
use TmlpStats\Settings\Parsers\QuarterDateParser;

class Builder
{
    /**
     * Parser class to instantiate
     *
     * @var string
     */
    protected $parserClass = null;

    /**
     * Setting name
     *
     * @var string
     */
    protected $settingName = null;

    /**
     * Setting value format. Used when parsing value.
     *
     * @var string
     */
    protected $format = null;

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
     * Get a new instance of Builder
     *
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Set Arguments
     *
     * @param Center|null  $center
     * @param Quarter|null $quarter
     * @param null         $arguments
     *
     * @return $this
     */
    public function with(Center $center = null, Quarter $quarter = null, $arguments = null)
    {
        $this->center    = $center;
        $this->quarter   = $quarter;
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * Set setting name
     *
     * @param $name
     *
     * @return $this
     */
    public function name($name)
    {
        $this->settingName = $name;

        return $this;
    }

    /**
     * Set setting format
     *
     * @param $format
     *
     * @return $this
     * @throws \Exception
     */
    public function format($format)
    {
        $parserClass = $this->getParserClass();

        switch ($format) {
            case $parserClass::FORMAT_JSON:
            case $parserClass::FORMAT_BINARY:
            case $parserClass::FORMAT_DATE:
                $this->format = $format;

                return $this;
        }

        throw new \Exception("Invalid format {$format}");
    }

    /**
     * Set parser class
     *
     * @param $class
     *
     * @return $this
     */
    public function parserClass($class)
    {
        $this->parserClass = $class;

        return $this;
    }

    /**
     * Get the setting value
     *
     * @return null|mixed
     * @throws \Exception
     */
    public function get()
    {
        $setting = $this->getSetting();
        if ($setting) {
            $parser = $this->getParser($setting);

            return $parser->run();
        }

        return null;
    }

    /**
     * Get the Parser class name
     *
     * @return string
     */
    protected function getParserClass()
    {
        switch ($this->settingName) {
            case 'repromiseDate':
            case 'travelDueByDate':
                return QuarterDateParser::class;
                break;
            default:
                return DefaultParser::class;
        }
    }

    /**
     * Get the parser object
     *
     * @param $setting
     *
     * @return mixed
     */
    protected function getParser($setting)
    {
        $class = $this->parserClass ?: $this->getParserClass();

        $parser = new $class($setting, $this->center, $this->quarter, $this->arguments);

        if ($this->format) {
            $parser->setFormat($this->format);
        }

        return $parser;
    }

    /**
     * Get the actual setting
     *
     * @return null|Setting
     * @throws \Exception
     */
    protected function getSetting()
    {
        if (!$this->settingName) {
            throw new \Exception('No setting name provided');
        }

        return SettingModel::get($this->settingName, $this->center, $this->quarter);
    }
}
