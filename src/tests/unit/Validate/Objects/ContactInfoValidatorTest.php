<?php
namespace TmlpStats\Tests\Validate\Objects;

use TmlpStats\Util;
use TmlpStats\Validate\Objects\ContactInfoValidator;
use stdClass;

class ContactInfoValidatorTest extends ObjectsValidatorTestAbstract
{
    protected $testClass = ContactInfoValidator::class;

    protected $dataFields = [
        'name',
        'accountability',
        'phone',
        'email',
    ];


    public function testPopulateValidatorsSetsValidatorsForEachInput($data = null)
    {
        $data                 = new stdClass;
        $data->accountability = 'Program Manager';
        $data->name           = 'Jeff Bridges';

        parent::testPopulateValidatorsSetsValidatorsForEachInput($data);
    }

    public function testPopulateValidatorsSkipsNameWhenNotApplicable($data = null)
    {
        $data                 = new stdClass;
        $data->accountability = 'Program Manager';
        $data->name           = 'N/A';

        // When name is N/A, we skip all validation
        $tmpDataFields    = $this->dataFields;
        $this->dataFields = [];

        parent::testPopulateValidatorsSetsValidatorsForEachInput($data);

        $this->dataFields = $tmpDataFields;
    }

    public function testPopulateValidatorsSetsValidatorsForEachInputReportingStatistician($data = null)
    {
        $data                 = new stdClass;
        $data->accountability = 'Reporting Statistician';

        parent::testPopulateValidatorsSetsValidatorsForEachInput($data);
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(['addMessage', 'validate']);

        $i = 0;
        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i))
                          ->method('addMessage')
                          ->with($messages[$i][0], $messages[$i][1], $messages[$i][2]);
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $validator->expects($this->at($i))
                  ->method('validate')
                  ->with($data);

        $result = $validator->run($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerRun()
    {
        return [
            // Test Required
            [
                Util::arrayToObject([
                    'name'           => null,
                    'accountability' => null,
                    'phone'          => null,
                    'email'          => null,
                ]),
                [
                    ['INVALID_VALUE', 'Name', '[empty]'],
                    ['INVALID_VALUE', 'Accountability', '[empty]'],
                    ['INVALID_VALUE', 'Phone', '[empty]'],
                    ['INVALID_VALUE', 'Email', '[empty]'],
                ],
                false,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [],
                true,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Classroom Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [],
                true,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'T-1 Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [],
                true,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'T-2 Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [],
                true,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Team 2 Team Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [],
                true,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Team 1 Team Leader',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [],
                true,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Statistician',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [],
                true,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Statistician Apprentice',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [],
                true,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Reporting Statistician',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [],
                true,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Reporting Statistician',
                    'phone'          => '555-555-5555',
                    'email'          => '',
                ]),
                [],
                true,
            ],


            // Test Invalid First Name
            [
                Util::arrayToObject([
                    'name'           => ' Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [
                    ['INVALID_VALUE', 'Name', ' Stone'],
                ],
                false,
            ],
            // Test Invalid Last Name
            [
                Util::arrayToObject([
                    'name'           => 'Keith ',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [
                    ['INVALID_VALUE', 'Name', 'Keith '],
                ],
                false,
            ],
            // Test Invalid accountability
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'asdf',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone@example.com',
                ]),
                [
                    ['INVALID_VALUE', 'Accountability', 'asdf'],
                ],
                false,
            ],
            // Test Invalid phone
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => 'asdf',
                    'email'          => 'keith.stone@example.com',
                ]),
                [
                    ['INVALID_VALUE', 'Phone', 'asdf'],
                ],
                false,
            ],
            // Test Invalid email
            [
                Util::arrayToObject([
                    'name'           => 'Keith Stone',
                    'accountability' => 'Program Manager',
                    'phone'          => '555-555-5555',
                    'email'          => 'keith.stone',
                ]),
                [
                    ['INVALID_VALUE', 'Email', 'keith.stone'],
                ],
                false,
            ],
        ];
    }

    public function providerValidate()
    {
        return [
            // Validate Succeeds
            [
                [
                    'validateName'  => true,
                    'validateEmail' => true,
                ],
                true,
            ],
            // validateName fails
            [
                [
                    'validateName'  => false,
                    'validateEmail' => true,
                ],
                false,
            ],
            // validateEmail fails
            [
                [
                    'validateName'  => true,
                    'validateEmail' => false,
                ],
                false,
            ],
        ];
    }
}
