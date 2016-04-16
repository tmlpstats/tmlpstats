<?php
namespace TmlpStats\Tests\Api\Parsers;

use TmlpStats\Api\Parsers;
use TmlpStats\Tests\TestAbstract;

class BoolParserTest extends TestAbstract
{
    protected $testClass = Parsers\BoolParser::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($value, $expectedResult)
    {
        $result = Parsers\BoolParser::create()->validate($value);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        return [
            ['1', false],
            [1, false],
            ['0', false],
            [0, false],
            [true, true],
            [false, true],
            [null, false],
            ['a', false],
            ['asdf', false],
            [[], false],
            [['asdf'], false],
            ['[]', false],
            ['["asdf"]', false],
            ['false', true],
            ['true', true],
            ['yes', true],
            ['no', true],
        ];
    }

    /**
     * @dataProvider providerParse
     */
    public function testParse($value, $expectedResult)
    {
        $result = Parsers\BoolParser::create()->parse($value);
        $this->assertSame($expectedResult, $result);
    }

    public function providerParse()
    {
        return [
            [true, true],
            [false, false],
            ['true', true],
            ['false', false],
            ['yes', true],
            ['no', false],
        ];
    }
}
