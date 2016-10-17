<?php
namespace TmlpStats\Tests\Unit;

use TmlpStats\Tests\TestAbstract;
use TmlpStats\Traits\SanitizesLastNames;

class TestSanitizesLastNamesClass
{
    use SanitizesLastNames;

    public static function run($people)
    {
        $me = new static();

        return $me->sanitizeNames($people);
    }
}

class SanitizesLastNamesTest extends TestAbstract
{
    protected $testClass = SanitizesLastNames::class;

    public function testSanitizeNames()
    {
        $data = [
            [
                'firstName' => 'Anne',
                'lastName' => 'Aabcd',
                'expectedLastName' => 'Aab',
            ],
            [
                'firstName' => 'Anne',
                'lastName' => 'Aaaef',
                'expectedLastName' => 'Aaa',
            ],
            [
                'firstName' => 'Anne',
                'lastName' => 'Aghi',
                'expectedLastName' => 'Ag',
            ],
            [
                'firstName' => 'Bob',
                'lastName' => 'Bcde',
                'expectedLastName' => 'Bc',
            ],
            [
                'firstName' => 'Bob',
                'lastName' => 'Bghi',
                'expectedLastName' => 'Bg',
            ],
            [
                'firstName' => 'Cathy',
                'lastName' => 'K',
                'expectedLastName' => 'K',
            ],
            [
                'firstName' => 'Cathy',
                'lastName' => 'KL',
                'expectedLastName' => 'KL',
            ],
        ];

        $people = [];
        $expected = [];

        $count = 1;
        for ($i = 0; $i < count($data); $i++) {
            $person = new \stdClass();
            $person->id = $count++;
            $person->firstName = $data[$i]['firstName'];
            $person->lastName = $data[$i]['lastName'];

            $people[$person->id] = $person;

            $expectedPerson = clone $person;
            $expectedPerson->lastName = $data[$i]['expectedLastName'];
            $expected[$person->id] = $expectedPerson;
        }

        $result = TestSanitizesLastNamesClass::run($people);

        $this->assertEquals($expected, $result);
    }
}
