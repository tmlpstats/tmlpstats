<?php
namespace TmlpStats\Tests\Unit\Validate\Differences;

use TmlpStats\Domain\Course;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Differences\ApiCourseChangeValidator;

class ApiCourseChangeValidatorTest extends ApiValidatorTestAbstract
{
    protected $testClass = ApiCourseChangeValidator::class;

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'warning',
        'reference' => [
            'id' => null,
            'type' => 'Course',
        ],
    ];

    public function setUp()
    {
        parent::setUp();

        $this->statsReport->center->name = 'Atlanta';

        $this->dataTemplate = [
            'startDate' => '2016-08-27',
            'location' => '',
            'type' => 'CAP',
            'quarterStartTer' => 15,
            'quarterStartStandardStarts' => 14,
            'quarterStartXfer' => 0,
            'currentTer' => 45,
            'currentStandardStarts' => 39,
            'currentXfer' => 1,
            'completedStandardStarts' => 39,
            'potentials' => 30,
            'registrations' => 25,
            'guestsPromised' => null,
            'guestsInvited' => null,
            'guestsConfirmed' => null,
            'guestsAttended' => null,
        ];
    }

    /**
     * @dataProvider providerValidateCourseStartDate
     */
    public function testValidateCourseStartDate($data, $expectedMessages, $pastWeeks = [])
    {
        $data = $this->getCourse($data);

        if ($pastWeeks) {
            $pastWeeks = [ $this->getCourse($pastWeeks) ];
        }

        $validator = $this->getObjectMock(['isFirstWeek']);
        $validator->expects($this->any())
                  ->method('isFirstWeek')
                  ->willReturn(false);

        $result = $validator->run($data, $pastWeeks);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertTrue($result);
    }

    public function providerValidateCourseStartDate()
    {
        return [
            // Start Date with no past weeks
            [
                [],
                [],
            ],
            // Start Date did not change
            [
                [],
                [],
                [
                    'startDate' => '2016-08-27',
                ],
            ],
            // Start Date changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_START_DATE_CHANGED',
                        'reference.field' => 'startDate',
                    ]),
                ],
                [
                    'startDate' => '2016-09-06',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerValidateCourseQuarterStart
     */
    public function testValidateCourseQuarterStart($data, $expectedMessages, $pastWeeks = [])
    {
        $isFirstWeek = false;
        if (isset($data['_isFirstWeek'])) {
            $isFirstWeek = $data['_isFirstWeek'];
            unset($data['_isFirstWeek']);
        }

        $data = $this->getCourse($data);

        if ($pastWeeks) {
            $pastWeeks = [ $this->getCourse($pastWeeks) ];
        }

        $validator = $this->getObjectMock(['isFirstWeek']);
        $validator->expects($pastWeeks ? $this->once() : $this->never())
                  ->method('isFirstWeek')
                  ->willReturn($isFirstWeek);

        $result = $validator->run($data, $pastWeeks);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertTrue($result);
    }

    public function providerValidateCourseQuarterStart()
    {
        return [
            // No past weeks
            [
                [],
                [],
            ],

            // First Week, QStart values did not change
            [
                [
                    '_isFirstWeek' => true,
                ],
                [],
                [
                    'currentTer' => '15',
                    'currentStandardStarts' => 14,
                    'currentXfer' => 0,
                ],
            ],
            // First Week, quarterStartTer doesn't match
            [
                [
                    '_isFirstWeek' => true,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_TER_DOES_NOT_MATCH_QEND',
                        'reference.field' => 'quarterStartTer',
                    ]),
                ],
                [
                    'currentTer' => '20',
                    'currentStandardStarts' => 14,
                    'currentXfer' => 0,
                ],
            ],
            // First Week, quarterStartStandardStarts doesn't match
            [
                [
                    '_isFirstWeek' => true,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_SS_DOES_NOT_MATCH_QEND',
                        'reference.field' => 'quarterStartStandardStarts',
                    ]),
                ],
                [
                    'currentTer' => '15',
                    'currentStandardStarts' => 15,
                    'currentXfer' => 0,
                ],
            ],
            // First Week, quarterStartXfer doesn't match
            [
                [
                    '_isFirstWeek' => true,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_XFER_DOES_NOT_MATCH_QEND',
                        'reference.field' => 'quarterStartXfer',
                    ]),
                ],
                [
                    'currentTer' => '15',
                    'currentStandardStarts' => 14,
                    'currentXfer' => 2,
                ],
            ],
            // First Week, QStart values doesn't match
            [
                [
                    '_isFirstWeek' => true,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_TER_DOES_NOT_MATCH_QEND',
                        'reference.field' => 'quarterStartTer',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_SS_DOES_NOT_MATCH_QEND',
                        'reference.field' => 'quarterStartStandardStarts',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_XFER_DOES_NOT_MATCH_QEND',
                        'reference.field' => 'quarterStartXfer',
                    ]),
                ],
                [
                    'currentTer' => '20',
                    'currentStandardStarts' => 19,
                    'currentXfer' => 2,
                ],
            ],






            // Later Week, QStart values did not change
            [
                [],
                [],
                [
                    'quarterStartTer' => '15',
                    'quarterStartStandardStarts' => 14,
                    'quarterStartXfer' => 0,
                ],
            ],
            // Later Week, quarterStartTer changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_TER_CHANGED',
                        'reference.field' => 'quarterStartTer',
                    ]),
                ],
                [
                    'quarterStartTer' => '20',
                    'quarterStartStandardStarts' => 14,
                    'quarterStartXfer' => 0,
                ],
            ],
            // Later Week, quarterStartStandardStarts changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_SS_CHANGED',
                        'reference.field' => 'quarterStartStandardStarts',
                    ]),
                ],
                [
                    'quarterStartTer' => '15',
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer' => 0,
                ],
            ],
            // Later Week, quarterStartXfer changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_XFER_CHANGED',
                        'reference.field' => 'quarterStartXfer',
                    ]),
                ],
                [
                    'quarterStartTer' => '15',
                    'quarterStartStandardStarts' => 14,
                    'quarterStartXfer' => 2,
                ],
            ],
            // Later Week, QStart values changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_TER_CHANGED',
                        'reference.field' => 'quarterStartTer',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_SS_CHANGED',
                        'reference.field' => 'quarterStartStandardStarts',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_XFER_CHANGED',
                        'reference.field' => 'quarterStartXfer',
                    ]),
                ],
                [
                    'quarterStartTer' => '20',
                    'quarterStartStandardStarts' => 19,
                    'quarterStartXfer' => 2,
                ],
            ],
        ];
    }

    public function getCourse($data)
    {
        if (isset($data['__centerName'])) {
            $this->statsReport->center->name = $data['__centerName'];
            unset($data['__centerName']);
        }

        if (isset($data['__reportingDate'])) {
            $this->statsReport->reportingDate = $data['__reportingDate'];
            unset($data['__reportingDate']);
        }

        $data = array_merge($this->dataTemplate, $data);

        return Course::fromArray($data);
    }
}
