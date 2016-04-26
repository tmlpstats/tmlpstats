<?php
namespace TmlpStats\Tests\Unit\Validate\Objects;

use TmlpStats\Tests\Unit\Traits\MocksMessages;
use TmlpStats\Util;
use TmlpStats\Validate\Objects\CenterStatsValidator;

class CenterStatsValidatorTest extends ObjectsValidatorTestAbstract
{
    use MocksMessages;

    protected $testClass = CenterStatsValidator::class;

    protected $dataFields = [
        'reportingDate',
        'type',
        'tdo',
        'cap',
        'cpc',
        't1x',
        't2x',
        'gitw',
        'lf',
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
                    'reportingDate' => null,
                    'type'          => null,
                    'tdo'           => null,
                    'cap'           => null,
                    'cpc'           => null,
                    't1x'           => null,
                    't2x'           => null,
                    'gitw'          => null,
                    'lf'            => null,
                ]),
                [
                    ['INVALID_VALUE', 'Reporting Date', '[empty]'],
                    ['INVALID_VALUE', 'Type', '[empty]'],
                    ['INVALID_VALUE', 'Cap', '[empty]'],
                    ['INVALID_VALUE', 'Cpc', '[empty]'],
                    ['INVALID_VALUE', 'T1x', '[empty]'],
                    ['INVALID_VALUE', 'T2x', '[empty]'],
                    ['INVALID_VALUE', 'Gitw', '[empty]'],
                    ['INVALID_VALUE', 'Lf', '[empty]'],
                ],
                false,
            ],
            // Test Valid
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'promise',
                    'tdo'           => 0,
                    'cap'           => 0,
                    'cpc'           => 0,
                    't1x'           => 0,
                    't2x'           => 0,
                    'gitw'          => 0,
                    'lf'            => 0,
                ]),
                [],
                true,
            ],
            // Test Valid (Version 2)
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'actual',
                    'tdo'           => 1,
                    'cap'           => 55,
                    'cpc'           => 66,
                    't1x'           => 77,
                    't2x'           => 88,
                    'gitw'          => 99,
                    'lf'            => 100,
                ]),
                [],
                true,
            ],

            // Test Invalid reportingDate
            [
                Util::arrayToObject([
                    'reportingDate' => 'asdf',
                    'type'          => 'actual',
                    'tdo'           => 1,
                    'cap'           => 55,
                    'cpc'           => 66,
                    't1x'           => 77,
                    't2x'           => 88,
                    'gitw'          => 99,
                    'lf'            => 100,
                ]),
                [
                    ['INVALID_VALUE', 'Reporting Date', 'asdf'],
                ],
                false,
            ],
            // Test Invalid type
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'asdf',
                    'tdo'           => 1,
                    'cap'           => 55,
                    'cpc'           => 66,
                    't1x'           => 77,
                    't2x'           => 88,
                    'gitw'          => 99,
                    'lf'            => 100,
                ]),
                [
                    ['INVALID_VALUE', 'Type', 'asdf'],
                ],
                false,
            ],
            // Test Invalid tdo
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'actual',
                    'tdo'           => 'asdf',
                    'cap'           => 55,
                    'cpc'           => 66,
                    't1x'           => 77,
                    't2x'           => 88,
                    'gitw'          => 99,
                    'lf'            => 100,
                ]),
                [
                    ['INVALID_VALUE', 'Tdo', 'asdf'],
                ],
                false,
            ],
            // Test Invalid cap
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'actual',
                    'tdo'           => 1,
                    'cap'           => 'asdf',
                    'cpc'           => 66,
                    't1x'           => 77,
                    't2x'           => 88,
                    'gitw'          => 99,
                    'lf'            => 100,
                ]),
                [
                    ['INVALID_VALUE', 'Cap', 'asdf'],
                ],
                false,
            ],
            // Test Invalid cpc
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'actual',
                    'tdo'           => 1,
                    'cap'           => 55,
                    'cpc'           => 'asdf',
                    't1x'           => 77,
                    't2x'           => 88,
                    'gitw'          => 99,
                    'lf'            => 100,
                ]),
                [
                    ['INVALID_VALUE', 'Cpc', 'asdf'],
                ],
                false,
            ],
            // Test Invalid t1x
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'actual',
                    'tdo'           => 1,
                    'cap'           => 55,
                    'cpc'           => 66,
                    't1x'           => 'asdf',
                    't2x'           => 88,
                    'gitw'          => 99,
                    'lf'            => 100,
                ]),
                [
                    ['INVALID_VALUE', 'T1x', 'asdf'],
                ],
                false,
            ],
            // Test Invalid t2x
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'actual',
                    'tdo'           => 1,
                    'cap'           => 55,
                    'cpc'           => 66,
                    't1x'           => 77,
                    't2x'           => 'asdf',
                    'gitw'          => 99,
                    'lf'            => 100,
                ]),
                [
                    ['INVALID_VALUE', 'T2x', 'asdf'],
                ],
                false,
            ],
            // Test Invalid gitw
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'actual',
                    'tdo'           => 1,
                    'cap'           => 55,
                    'cpc'           => 66,
                    't1x'           => 77,
                    't2x'           => 88,
                    'gitw'          => 101,
                    'lf'            => 100,
                ]),
                [
                    ['INVALID_VALUE', 'Gitw', '101'],
                ],
                false,
            ],
            // Test Invalid gitw 1
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'actual',
                    'tdo'           => 1,
                    'cap'           => 55,
                    'cpc'           => 66,
                    't1x'           => 77,
                    't2x'           => 88,
                    'gitw'          => -101,
                    'lf'            => 100,
                ]),
                [
                    ['INVALID_VALUE', 'Gitw', '-101'],
                ],
                false,
            ],
            // Test Invalid lf
            [
                Util::arrayToObject([
                    'reportingDate' => '2015-01-01',
                    'type'          => 'actual',
                    'tdo'           => 1,
                    'cap'           => 55,
                    'cpc'           => 66,
                    't1x'           => 77,
                    't2x'           => 88,
                    'gitw'          => 99,
                    'lf'            => 'asdf',
                ]),
                [
                    ['INVALID_VALUE', 'Lf', 'asdf'],
                ],
                false,
            ],
        ];
    }
}
