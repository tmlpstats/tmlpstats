<?php
namespace TmlpStats\Tests\Api\Parsers;

use TmlpStats\Api\Parsers;
use TmlpStats\Tests\TestAbstract;

class CenterMock
{
    public $id;
    public $abbreviation;

    public static function find($id)
    {
        $me = new static();
        $me->id = $id;

        return $me;
    }

    public static function abbreviation($abbreviation)
    {
        $me = new static();
        $me->abbreviation = $abbreviation;

        return $me;
    }

    public function first()
    {
        return $this;
    }
}

class CenterParserTest extends TestAbstract
{
    protected $testClass = Parsers\CenterParser::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($value, $expectedResult)
    {
        $parser = new Parsers\CenterParser();
        $this->setProperty($parser, 'class', CenterMock::class);

        $result = $parser->validate($value);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        return [
            ['1', true],
            [1, true],
            ['9999', true],
            [9999, true],
            ['0', false],
            [0, false],
            [true, false],
            [false, false],
            [null, false],
            ['a', false],
            ['bos', true],
            [[], false],
            [['asdf'], false],
            ['[]', false],
            ['["asdf"]', false],
        ];
    }

    /**
     * @dataProvider providerParse
     */
    public function testParse($id, $obj)
    {
        $parser = new Parsers\CenterParser();
        $this->setProperty($parser, 'class', CenterMock::class);

        $result = $parser->parse($id);

        $this->assertEquals($obj, $result);
    }

    public function providerParse()
    {
        return [
            [1, CenterMock::find(1)],
            ['9999', CenterMock::find('9999')],
            ['bos', CenterMock::abbreviation('bos')],
        ];
    }
}
