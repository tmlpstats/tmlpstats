<?php
namespace TmlpStats\Tests\Unit\Api\Parsers;

use TmlpStats\Api\Parsers;
use TmlpStats\Tests\TestAbstract;

class IntParserTest extends TestAbstract
{
    protected $testClass = Parsers\IntParser::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($value, $expectedResult)
    {
        $result = Parsers\IntParser::create()->validate($value);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        return [
            ['1', true],
            [1, true],
            ['0', true],
            [0, true],
            [true, false],
            [false, false],
            [null, false],
            ['a', false],
            ['asdf', false],
            [[], false],
            [['asdf'], false],
            ['[]', false],
            ['["asdf"]', false],
        ];
    }

    public function testParse()
    {
        $result = Parsers\IntParser::create()->parse(0);
        $this->assertSame(0, $result);

        $result = Parsers\IntParser::create()->parse('1');
        $this->assertSame(1, $result);
    }
}
