<?php
namespace TmlpStats\Tests\Settings\Parsers;

use Carbon\Carbon;
use TmlpStats\Center;
use TmlpStats\Quarter;
use TmlpStats\Setting;
use TmlpStats\Settings\Parsers\AbstractParser;
use TmlpStats\Tests\TestAbstract;
use TmlpStats\Tests\Traits\MocksSettings;

class AbstractSettingsImplementation extends AbstractParser
{
    protected function parse()
    {
        return $this->arguments;
    }
}

class AbstractSettingsTest extends TestAbstract
{
    use MocksSettings;

    protected $testClass = AbstractSettingsImplementation::class;

    protected $setting = null;
    protected $center = null;
    protected $quarter = null;
    protected $arguments = null;
    protected $settingName = 'superSpecialSetting';
    protected $settingValue = '"myValue"';

    public function setUp()
    {
        $this->setting = $this->setSetting($this->settingName, $this->settingValue);

        $this->quarter = new Quarter();

        $this->center     = new Center();
        $this->center->id = 0;

        $this->arguments = ['myArgument'];
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->setting   = null;
        $this->center    = null;
        $this->quarter   = null;
        $this->arguments = null;

        $this->clearSettings();
    }

    public function testConstructorSetupsProperties()
    {
        $object = new AbstractSettingsImplementation($this->setting, $this->center, $this->quarter, $this->arguments);

        $this->assertEquals($this->setting, $this->getProperty($object, 'setting'));
        $this->assertEquals($this->center, $this->getProperty($object, 'center'));
        $this->assertEquals($this->quarter, $this->getProperty($object, 'quarter'));
        $this->assertEquals($this->arguments, $this->getProperty($object, 'arguments'));
    }

    public function testRunCallsParse()
    {
        $object = new AbstractSettingsImplementation($this->setting, $this->center, $this->quarter, $this->arguments);

        $result = $object->run();

        $this->assertEquals($this->arguments, $result);
    }

    /**
     * @dataProvider providerDecode
     */
    public function testDecode($format, $data, $expectedResult)
    {
        $setting        = new Setting();
        $setting->name  = $this->settingName;
        $setting->value = $data;

        $object = new AbstractSettingsImplementation($setting, $this->center, $this->quarter, $this->arguments);

        $this->setProperty($object, 'format', $format);

        $result = $this->runMethod($object, 'decode');

        $this->assertEquals($expectedResult, $result);
    }

    public function providerDecode()
    {
        return [
            [
                'json',
                null,
                null,
            ],
            [
                'json',
                '"myStringValue"',
                'myStringValue',
            ],
            [
                'json',
                '{"myKey":"myValue"}',
                [
                    'myKey' => 'myValue',
                ],
            ],
            [
                'binary',
                '"myStringValue"',
                '"myStringValue"',
            ],
            [
                'binary',
                'UTF-8: ✓',
                'UTF-8: ✓',
            ],
            [
                'date',
                '2016-02-15',
                Carbon::create(2016, 2, 15)->startOfDay(),
            ],
            [
                'date',
                '2016-02-15 12:15:25',
                Carbon::create(2016, 2, 15, 12, 15, 25),
            ],
        ];
    }

    public function testDecodeThrowsExceptionForInvalidFormat()
    {
        $this->setExpectedException('\Exception');

        $object = new AbstractSettingsImplementation($this->setting, $this->center, $this->quarter, $this->arguments);

        $this->setProperty($object, 'format', 'invalid');

        $this->runMethod($object, 'decode');
    }

    public function testDecodeThrowsExceptionForInvalidDate()
    {
        $this->setExpectedException('\Exception');

        $object = new AbstractSettingsImplementation($this->setting, $this->center, $this->quarter, $this->arguments);

        $this->setProperty($object, 'format', 'date');

        $this->runMethod($object, 'decode');
    }
}
