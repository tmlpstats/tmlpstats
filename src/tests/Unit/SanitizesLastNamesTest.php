<?php
namespace TmlpStats\Tests\Unit;

use TmlpStats\Tests\TestAbstract;
use TmlpStats\Traits\SanitizesLastNames;

class TestSanitizesLastNamesClass
{
    // use SanitizesLastNames;

    public function sanitizeNames($people)
    {
        $nameHash = [];
        foreach ($people as $id => $person) {
            $nameHash[$person->firstName][] = $person;
        }

        sort($nameHash);

        $nameUpdates = [];

        $results = [];
        foreach ($nameHash as $firstName => $nameGroup) {
            if (count($nameGroup) == 1) {
                $results[$nameGroup[0]->id] = $nameGroup[0];
                continue;
            }

            usort($nameGroup, function ($a, $b) {
                return strcmp($a->lastName, $b->lastName);
            });

            foreach ($nameGroup as $idx => $person) {
                if ($idx === 0) {
                    continue;
                }

                $them = $nameGroup[$idx - 1];

                $myName = $person->lastName;
                $theirName = $them->lastName;
                $theirUpdatedName = isset($nameUpdates[$them->id]) ? $nameUpdates[$them->id] : $theirName;

                if ($myName === $theirName) {
                    if ($myName === $theirUpdatedName) {
                        $nameUpdates[$person->id] = $myName[0];
                        $nameUpdates[$them->id] = $theirName[0];
                    } else {
                        $nameUpdates[$person->id] = $theirUpdatedName;
                    }

                    continue;
                }

                $uniquePos = strspn($myName ^ $theirName, "\0");

                if (strlen($myName) >= ($uniquePos + 1)) {
                    $nameUpdates[$person->id] = substr($myName, 0, $uniquePos + 1);
                } else {
                    $nameUpdates[$person->id] = $myName;
                }

                if ($theirUpdatedName !== substr($theirName, 0, $uniquePos + 1)) {
                    if (strlen($theirName) >= ($uniquePos + 1)) {
                        $nameUpdates[$them->id] = substr($theirName, 0, $uniquePos + 1);
                    }
                }
            }

            foreach ($nameUpdates as $id => $lastName) {
                $person = $people[$id];
                $person->lastName = $lastName;
                $results[$id] = $person;
            }

            // Values are the same
            // One is a subset of the other
            // they differ at some point
        }
        dd($results);
    }

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
            // Test multiple level of samness
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
            // Test a single level of sameness
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
            // Test case when one person's name is a subset of anothers
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
            // Test multiple people with the exact same last initial
            [
                'firstName' => 'Dan',
                'lastName' => 'M',
                'expectedLastName' => 'M',
            ],
            [
                'firstName' => 'Dan',
                'lastName' => 'M',
                'expectedLastName' => 'M',
            ],
            [
                'firstName' => 'Dan',
                'lastName' => 'M',
                'expectedLastName' => 'M',
            ],
            // Test multiple people with the exact same last name
            [
                'firstName' => 'Emma',
                'lastName' => 'Nelson',
                'expectedLastName' => 'Nels',
            ],
            [
                'firstName' => 'Emma',
                'lastName' => 'Nelson',
                'expectedLastName' => 'Nels',
            ],
            [
                'firstName' => 'Emma',
                'lastName' => 'Nelson',
                'expectedLastName' => 'Nels',
            ],
            [
                'firstName' => 'Emma',
                'lastName' => 'Nel',
                'expectedLastName' => 'Nel',
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

        foreach ($expected as $id => $expectedPerson) {
            // Make it easier to figure out which one if someone is missing
            $this->assertArrayHasKey("$id", $result, "Result is missing {$expectedPerson->firstName} {$expectedPerson->lastName} (ID: {$id})");

            // Make sure the names match
            $this->assertEquals($expectedPerson->firstName, $result[$id]->firstName);
            $this->assertEquals($expectedPerson->lastName, $result[$id]->lastName);
        }

        // Make sure we got all of the people back we expected
        $this->assertEquals(count($expected), count($result));
    }
}
