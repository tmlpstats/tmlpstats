<?php
namespace TmlpStats\Tests\Unit\Api\Parsers;

use TmlpStats\Api\Parsers;
use TmlpStats\Tests\TestAbstract;

class ArrayParserTest extends TestAbstract
{
    protected $testClass = Parsers\ArrayParser::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($value, $expectedResult)
    {
        $result = Parsers\ArrayParser::create()->validate($value);

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
            [[], true],
            [['asdf'], true],
            ['[]', true],
            ['["asdf"]', true],
        ];
    }

    public function testParse()
    {
        $result = Parsers\ArrayParser::create()->parse([]);
        $this->assertSame([], $result);

        $result = Parsers\ArrayParser::create()->parse(['asdf']);
        $this->assertSame(['asdf'], $result);

        $result = Parsers\ArrayParser::create()->parse('[]');
        $this->assertSame([], $result);

        $result = Parsers\ArrayParser::create()->parse('["asdf"]');
        $this->assertSame(['asdf'], $result);
    }
}
