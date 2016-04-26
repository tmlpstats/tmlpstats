<?php
namespace TmlpStats\Tests\Unit\Settings;

use Carbon\Carbon;
use TmlpStats\Settings\Parsers\DefaultParser;
use TmlpStats\Settings\Setting;
use TmlpStats\Tests\TestAbstract;
use TmlpStats\Tests\Unit\Traits\MocksSettings;

class DefaultParserSettingTest extends TestAbstract
{
    use MocksSettings;

    protected $testClass = Setting::class;

    public function tearDown()
    {
        parent::tearDown();

        $this->clearSettings();
    }

    public function testGetUsesBinaryFormatterByDefault()
    {
        $settingName  = 'mySetting';
        $settingValue = '2016-02-06';

        $this->setSetting($settingName, $settingValue);

        $result = Setting::name($settingName)->get();

        $this->assertEquals($settingValue, $result);
    }

    public function testGetWithDateFormatter()
    {
        $settingName  = 'mySetting';
        $settingValue = '2016-02-06';

        $this->setSetting($settingName, $settingValue);

        $result = Setting::name($settingName)
                         ->format(DefaultParser::FORMAT_DATE)
                         ->get();

        $this->assertEquals(Carbon::parse($settingValue), $result);
    }
}
