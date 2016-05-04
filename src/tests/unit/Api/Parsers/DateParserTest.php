<?php
namespace TmlpStats\Tests\Unit\Api\Parsers;

use Carbon\Carbon;
use TmlpStats\Api\Parsers;
use TmlpStats\Tests\TestAbstract;

class DateParserTest extends TestAbstract
{
    protected $testClass = Parsers\DateParser::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($value, $expectedResult)
    {
        $result = Parsers\DateParser::create()->validate($value);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        return [
            ['1', false],
            [1, false],
            ['0', false],
            [0, false],
            [true, false],
            [false, false],
            [null, false],
            ['a', false],
            ['asdf', false],
            [[], false],
            [['asdf'], false],
            ['[]', false],
            ['["asdf"]', false],
            ['2016-05-01', true],
            ['2016-05-01 01:23:45', true],
            [Carbon::create(2016, 5, 1, 0, 0, 0), true],
        ];
    }

    public function testParse()
    {
        $result = Parsers\DateParser::create()->parse('2016-05-01');
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertTrue($result->eq(Carbon::create(2016, 5, 1, 0, 0, 0)));

        $result = Parsers\DateParser::create()->parse('2016-05-01 01:23:45');
        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertTrue($result->eq(Carbon::create(2016, 5, 1, 0, 0, 0)));
    }
}
