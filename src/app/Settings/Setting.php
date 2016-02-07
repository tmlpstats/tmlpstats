<?php
namespace TmlpStats\Settings;

use TmlpStats\Center;
use TmlpStats\Quarter;

class Setting
{
    /**
     * Setting Name
     *
     * @var string
     */
    protected static $settingName = null;

    /**
     * Parser Class Name
     *
     * @var string
     */
    protected static $parserClass = null;

    /**
     * Static Name setter
     *
     * Starts Builder chain
     *
     * @param $name
     *
     * @return Builder
     */
    public static function name($name)
    {
        return Builder::create()->name($name);
    }

    /**
     * Static Format setter
     *
     * Starts Builder chain
     *
     * @param $format
     *
     * @return Builder
     * @throws \Exception
     */
    public static function format($format)
    {
        return Builder::create()->format($format);
    }

    /**
     * Static Parser Class setter
     *
     * Starts Builder chain
     *
     * @param $class
     *
     * @return Builder
     */
    public static function parserClass($class)
    {
        return Builder::create()->parserClass($class);
    }

    /**
     * Static Argument setter
     *
     * Starts Builder chain
     *
     * @param Center|null  $center
     * @param Quarter|null $quarter
     * @param null         $arguments
     *
     * @return Builder
     */
    public static function with(Center $center = null, Quarter $quarter = null, $arguments = null)
    {
        return Builder::create()->with($center, $quarter, $arguments);
    }

    /**
     * Static Getter
     *
     * Handles all of the Builder calls and returns the setting value
     *
     * @param Center|null  $center
     * @param Quarter|null $quarter
     * @param null         $arguments
     *
     * @return mixed
     */
    public static function get(Center $center = null, Quarter $quarter = null, $arguments = null)
    {
        $builder = Builder::create();

        if ($center || $quarter || $arguments) {
            $builder->with($center, $quarter, $arguments);
        }

        if (static::$settingName) {
            $builder->name(static::$settingName);
        }

        if (static::$parserClass) {
            $builder->parserClass(static::$parserClass);
        }

        return $builder->get();
    }
}
