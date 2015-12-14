<?php
namespace TmlpStats\Tests\Validate\Objects;

use TmlpStats\Util;
use TmlpStats\Validate\Objects\TmlpCourseInfoValidator;

class TmlpCourseInfoValidatorTest extends ObjectsValidatorTestAbstract
{
    protected $testClass = TmlpCourseInfoValidator::class;

    protected $dataFields = [
        'type',
        'quarterStartRegistered',
        'quarterStartApproved',
    ];

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
                    'type'                   => null,
                    'quarterStartRegistered' => null,
                    'quarterStartApproved'   => null,
                ]),
                [
                    ['INVALID_VALUE', 'Type', '[empty]'],
                    ['INVALID_VALUE', 'Quarter Start Registered', '[empty]'],
                    ['INVALID_VALUE', 'Quarter Start Approved', '[empty]'],
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
                    'validateQuarterStartValues' => true,
                ],
                true,
            ],
            // validateQuarterStartValues fails
            [
                [
                    'validateQuarterStartValues' => false,
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateQuarterStartValues
     */
    public function testValidateQuarterStartValues($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i))
                          ->method('addMessage')
                          ->with($messages[$i]);
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $result = $validator->validateQuarterStartValues($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateQuarterStartValues()
    {
        return [
            // validateQuarterStartValues passes
            [
                Util::arrayToObject([
                    'quarterStartApproved'   => 2,
                    'quarterStartRegistered' => 2,
                ]),
                [],
                true,
            ],
            // validateQuarterStartValues passes
            [
                Util::arrayToObject([
                    'quarterStartApproved'   => 1,
                    'quarterStartRegistered' => 2,
                ]),
                [],
                true,
            ],
            // validateQuarterStartValues Fails When quarterStartApproved greater than quarterStartRegistered
            [
                Util::arrayToObject([
                    'quarterStartApproved'   => 3,
                    'quarterStartRegistered' => 2,
                ]),
                ['TMLPCOURSE_QSTART_TER_LESS_THAN_QSTART_APPROVED'],
                false,
            ],
        ];
    }
}
