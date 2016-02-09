<?php
namespace TmlpStats\Tests\Settings;

use TmlpStats\Center;
use TmlpStats\Quarter;
use TmlpStats\Settings\Builder;
use TmlpStats\Settings\Parsers\AbstractParser;
use TmlpStats\Settings\Parsers\DefaultParser;
use TmlpStats\Settings\Setting;
use TmlpStats\Tests\TestAbstract;
use TmlpStats\Tests\Traits\MocksSettings;

class SettingImplementation extends Setting
{
    protected static $settingName = 'SettingImplementation_setting';
    protected static $parserClass = DefaultParser::class;
}

class SettingTest extends TestAbstract
{
    use MocksSettings;

    protected $testClass = Setting::class;

    public function testName()
    {
        $name = 'mySetting';
        $builder = Setting::name($name);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertAttributeEquals($name, 'settingName', $builder);
    }

    public function testFormat()
    {
        $format = AbstractParser::FORMAT_DATE;
        $builder = Setting::format($format);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertAttributeEquals($format, 'format', $builder);
    }

    public function testParserClass()
    {
        $parserClass = DefaultParser::class;
        $builder = Setting::parserClass($parserClass);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertAttributeEquals($parserClass, 'parserClass', $builder);
    }

    public function testWith()
    {
        $center = new Center(['name' => 'Boston']);
        $quarter = new Quarter(['year' => 2016]);
        $arguments = ['my' => 'argument'];

        $builder = Setting::with($center, $quarter, $arguments);

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertAttributeEquals($center, 'center', $builder);
        $this->assertAttributeEquals($quarter, 'quarter', $builder);
        $this->assertAttributeEquals($arguments, 'arguments', $builder);
    }

    public function testGet()
    {
        $settingName = 'SettingImplementation_setting';

        $center = new Center(['name' => 'Boston']);
        $center->id = 0;

        $quarter = new Quarter(['year' => 2016]);
        $arguments = ['my' => 'argument'];

        $this->setSetting($settingName, 'myValue');

        $result = SettingImplementation::get($center, $quarter, $arguments);

        $this->assertEquals('myValue', $result);

        $this->clearSettings();
    }

    public function testGetThrowsExceptionWhenNoSettingNameSet()
    {
        $this->setExpectedException('\Exception');

        $settingName = 'SettingImplementation_setting';

        $center = new Center(['name' => 'Boston']);
        $center->id = 0;

        $quarter = new Quarter(['year' => 2016]);
        $arguments = ['my' => 'argument'];

        $this->setSetting($settingName, 'myValue');

        Setting::get($center, $quarter, $arguments);

        $this->clearSettings();
    }
}
