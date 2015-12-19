<?php
namespace TmlpStats\Tests\Validate\Objects;

use TmlpStats\Tests\Traits\MocksMessages;
use TmlpStats\Util;
use TmlpStats\Validate\Objects\TmlpCourseInfoValidator;

class TmlpCourseInfoValidatorTest extends ObjectsValidatorTestAbstract
{
    use MocksMessages;

    protected $testClass = TmlpCourseInfoValidator::class;

    protected $dataFields = [
        'type',
        'quarterStartRegistered',
        'quarterStartApproved',
    ];

    protected $validateMethods = [
        'validateQuarterStartValues',
    ];

    /**
     * @dataProvider providerRun
     */
    public function testRun($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(['addMessage', 'validate']);

        $i = 0;
        $this->setupMessageMocks($validator, $messages, $i);

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

    /**
     * @dataProvider providerValidateQuarterStartValues
     */
    public function testValidateQuarterStartValues($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        $this->setupMessageMocks($validator, $messages);

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
                [
                    ['TMLPCOURSE_QSTART_TER_LESS_THAN_QSTART_APPROVED'],
                ],
                false,
            ],
        ];
    }
}
