<?php
namespace TmlpStats\Tests\Api\Parsers;

use TmlpStats\Api\Parsers;
use TmlpStats\Tests\TestAbstract;

class TeamMemberMock
{
    public static function find($id)
    {
        $obj = new \stdClass();
        $obj->id = $id;

        return $obj;
    }
}

class TeamMemberParserTest extends TestAbstract
{
    protected $testClass = Parsers\TeamMemberParser::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($value, $expectedResult)
    {
        $parser = new Parsers\TeamMemberParser();
        $this->setProperty($parser, 'class', TeamMemberMock::class);

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
            ['asdf', false],
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
        $parser = new Parsers\TeamMemberParser();
        $this->setProperty($parser, 'class', TeamMemberMock::class);

        $result = $parser->parse($id);

        $this->assertEquals($obj, $result);
    }

    public function providerParse()
    {
        return [
            [1, (object) ['id' => 1]],
            ['9999', (object) ['id' => '9999']],
        ];
    }
}
