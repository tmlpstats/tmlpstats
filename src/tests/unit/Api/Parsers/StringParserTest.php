<?php
namespace TmlpStats\Tests\Api\Parsers;

use TmlpStats\Api\Parsers;
use TmlpStats\Tests\TestAbstract;

class StringParserTest extends TestAbstract
{
    protected $testClass = Parsers\StringParser::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($value, $expectedResult)
    {
        $result = Parsers\StringParser::create()->validate($value);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        return [
            ['1', true],
            [1, false],
            ['0', true],
            [0, false],
            [true, false],
            [false, false],
            [null, false],
            ['a', true],
            ['asdf', true],
            [[], false],
            [['asdf'], false],
            ['[]', true],
            ['["asdf"]', true],
        ];
    }

    public function testParse()
    {
        $result = Parsers\StringParser::create()->parse('asdf');
        $this->assertSame('asdf', $result);

        $result = Parsers\StringParser::create()->parse('1');
        $this->assertSame('1', $result);
    }
}
