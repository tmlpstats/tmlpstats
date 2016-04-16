<?php
namespace TmlpStats\Tests\Api\Parsers;

use TmlpStats\Api\Parsers;
use TmlpStats\Tests\TestAbstract;

class RegionMock
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

class RegionParserTest extends TestAbstract
{
    protected $testClass = Parsers\RegionParser::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($value, $expectedResult)
    {
        $parser = new Parsers\RegionParser();
        $this->setProperty($parser, 'class', RegionMock::class);

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
            ['eme', true],
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
        $parser = new Parsers\RegionParser();
        $this->setProperty($parser, 'class', RegionMock::class);

        $result = $parser->parse($id);

        $this->assertEquals($obj, $result);
    }

    public function providerParse()
    {
        return [
            [1, RegionMock::find(1)],
            ['9999', RegionMock::find('9999')],
            ['eme', RegionMock::abbreviation('eme')],
        ];
    }
}
