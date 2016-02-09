<?php
namespace TmlpStats\Tests\Settings;

use TmlpStats\Center;
use TmlpStats\Quarter;
use TmlpStats\Settings\Builder;
use TmlpStats\Settings\Parsers\AbstractParser;
use TmlpStats\Settings\Parsers\DefaultParser;
use TmlpStats\Settings\Parsers\QuarterDateParser;
use TmlpStats\Tests\TestAbstract;
use TmlpStats\Tests\Traits\MocksSettings;


class BuilderTest extends TestAbstract
{
    use MocksSettings;

    protected $testClass = Builder::class;

    public function testCreateReturnsInstance()
    {
        $builder = Builder::create();

        $this->assertInstanceOf(Builder::class, $builder);
    }

    /**
     * @dataProvider providerWithSetsProperties
     */
    public function testWithSetsProperties($center, $quarter, $arguments)
    {
        $builder = null;

        if ($arguments) {
            $builder = Builder::create()->with($center, $quarter, $arguments);
        } else if ($quarter) {
            $builder = Builder::create()->with($center, $quarter);
        } else if ($center) {
            $builder = Builder::create()->with($center);
        } else {
            $builder = Builder::create()->with();
        }

        $this->assertAttributeSame($center, 'center', $builder);
        $this->assertAttributeSame($quarter, 'quarter', $builder);
        $this->assertAttributeSame($arguments, 'arguments', $builder);
    }

    public function providerWithSetsProperties()
    {
        return [
            [
                null,
                null,
                null,
            ],
            [
                new Center(['name' => 'Boston']),
                null,
                null,
            ],
            [
                new Center(['name' => 'Boston']),
                new Quarter(['year' => 2016]),
                null,
            ],
            [
                new Center(['name' => 'Boston']),
                new Quarter(['year' => 2016]),
                ['my' => 'argument'],
            ],
        ];
    }

    public function testNameSetsProperty()
    {
        $name    = 'mySettingName';
        $builder = Builder::create()->name($name);

        $this->assertAttributeSame($name, 'settingName', $builder);
    }

    /**
     * @dataProvider providerFormatSetsProperty
     */
    public function testFormatSetsProperty($format)
    {
        $builder = Builder::create()->format($format);

        $this->assertAttributeSame($format, 'format', $builder);
    }

    public function providerFormatSetsProperty()
    {
        return [
            [AbstractParser::FORMAT_BINARY],
            [AbstractParser::FORMAT_JSON],
            [AbstractParser::FORMAT_DATE],
        ];
    }

    public function testFormatThrowsExceptionWhenInputIsInvalid()
    {
        $this->setExpectedException('\Exception');

        Builder::create()->format('invalid');
    }

    public function testParserClassSetsProperty()
    {
        $class   = 'MyParserClass';
        $builder = Builder::create()->parserClass($class);

        $this->assertAttributeSame($class, 'parserClass', $builder);
    }

    public function testGetRunsParserWhenSettingFound()
    {
        $expectedResult = 'mySettingValue';

        $setting = $this->getSettingMock();

        $parser = $this->getParserMock(DefaultParser::class, [$setting]);
        $parser->expects($this->once())
               ->method('run')
               ->willReturn($expectedResult);

        $builder = $this->getObjectMock([
            'getSetting',
            'getParser',
        ]);
        $builder->expects($this->once())
                ->method('getSetting')
                ->willReturn($setting);
        $builder->expects($this->once())
                ->method('getParser')
                ->with($this->equalTo($setting))
                ->willReturn($parser);

        $result = $builder->get();

        $this->assertEquals($expectedResult, $result);

        $this->clearSettings();
    }

    public function testGetReturnsNullWhenNoSettingFound()
    {
        $builder = $this->getObjectMock([
            'getSetting',
            'getParser',
        ]);
        $builder->expects($this->once())
                ->method('getSetting')
                ->willReturn(null);
        $builder->expects($this->never())
                ->method('getParser');

        $result = $builder->get();

        $this->assertNull($result);
    }

    /**
     * @dataProvider providerGetParser
     */
    public function testGetParser($settingName, $format, $expectedClass, $expectedFormat)
    {
        $setting   = $this->getSettingMock([
            'name' => $settingName,
        ]);
        $center    = new Center();
        $quarter   = new Quarter();
        $arguments = ['my' => 'argument'];

        $builder = Builder::create()
                          ->name($settingName)
                          ->with($center, $quarter, $arguments);

        if ($settingName == 'setParserClass') {
            $builder->parserClass($expectedClass);
        }

        if ($format) {
            $builder->format($format);
        }

        $result = $this->runMethod($builder, 'getParser', $setting);

        $this->assertInstanceOf($expectedClass, $result);
        $this->assertAttributeEquals($setting, 'setting', $result);
        $this->assertAttributeEquals($center, 'center', $result);
        $this->assertAttributeEquals($quarter, 'quarter', $result);
        $this->assertAttributeEquals($arguments, 'arguments', $result);
        $this->assertAttributeEquals($expectedFormat, 'format', $result);
    }

    public function providerGetParser()
    {
        return [
            [
                'repromiseDate',
                null,
                QuarterDateParser::class,
                AbstractParser::FORMAT_BINARY,
            ],
            [
                'travelDueByDate',
                null,
                QuarterDateParser::class,
                AbstractParser::FORMAT_BINARY,
            ],
            [
                'setParserClass',
                AbstractParser::FORMAT_JSON,
                QuarterDateParser::class,
                AbstractParser::FORMAT_JSON,
            ],
            [
                'somethingElse',
                AbstractParser::FORMAT_DATE,
                DefaultParser::class,
                AbstractParser::FORMAT_DATE,
            ],
        ];
    }

    public function testGetSetting()
    {
        $settingName = 'mySetting';
        $setting = $this->setSetting($settingName, 'mySettingValue');

        $builder = Builder::create()->name($settingName);

        $result = $this->runMethod($builder, 'getSetting');

        $this->assertSame($setting, $result);

        $this->clearSettings();
    }

    public function testGetSettingThrowsExceptionWhenSettingNameNotSet()
    {
        $this->setExpectedException('\Exception');

        $builder = Builder::create();

        $this->runMethod($builder, 'getSetting');
    }

    protected function getParserMock($class, $constructorArgs = [])
    {
        $methods = [
            'run',
        ];

        if (!$constructorArgs) {
            $setting         = $this->getSettingMock();
            $constructorArgs = [$setting];
        }

        return $this->getMockBuilder($class)
                    ->setMethods($methods)
                    ->setConstructorArgs($constructorArgs)
                    ->getMock();
    }
}
