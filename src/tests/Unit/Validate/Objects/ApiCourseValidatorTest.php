<?php
namespace TmlpStats\Tests\Unit\Validate\Objects;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Domain;
use TmlpStats\Tests\Unit\Traits;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Objects\ApiCourseValidator;

class ApiCourseValidatorTest extends ApiValidatorTestAbstract
{
    use Traits\MocksSettings, Traits\MocksQuarters, Traits\MocksModel;

    protected $testClass = ApiCourseValidator::class;

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'error',
        'reference' => [
            'id' => null,
            'type' => 'course',
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
     * @dataProvider providerRun
     */
    public function testRun($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getCourse($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerRun()
    {
        return [
            // Test Required
            [
                [
                    'startDate' => null,
                    'location' => null,
                    'type' => 'CAP',
                    'quarterStartTer' => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer' => null,
                    'currentTer' => null,
                    'currentStandardStarts' => null,
                    'currentXfer' => null,
                    'completedStandardStarts' => null,
                    'potentials' => null,
                    'registrations' => null,
                    'guestsPromised' => null,
                    'guestsInvited' => null,
                    'guestsConfirmed' => null,
                    'guestsAttended' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'startDate',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'quarterStartTer',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'quarterStartStandardStarts',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'quarterStartXfer',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'currentTer',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'currentStandardStarts',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'currentXfer',
                    ]),
                ],
                false,
            ],
            // Test Default CAP
            [
                [],
                [],
                true,
            ],
            // Test Default CPC
            [
                [
                    'type' => 'CPC',
                ],
                [],
                true,
            ],
            // Test Default w/ location
            [
                [
                    'location' => 'Germany',
                ],
                [],
                true,
            ],
            // Test Guests, before course completes
            [
                [
                    'startDate' => '2016-09-03',
                    'completedStandardStarts' => null,
                    'potentials' => null,
                    'registrations' => null,
                    'guestsPromised' => 0,
                    'guestsInvited' => 0,
                    'guestsConfirmed' => 0,
                    'guestsAttended' => null,
                ],
                [],
                true,
            ],
            // Test Guests, after course completes
            [
                [
                    'startDate' => '2016-08-27',
                    'guestsPromised' => 30,
                    'guestsInvited' => 50,
                    'guestsConfirmed' => 20,
                    'guestsAttended' => 10,
                ],
                [],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateCourseCompletionStats
     */
    public function testValidateCourseCompletionStats($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getCourse($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateCourseCompletionStats()
    {
        return [
            // Course in past with completion stats
            [
                [],
                [],
                true,
            ],
            // Course in future
            [
                [
                    'startDate' => '2016-09-03',
                    'completedStandardStarts' => null,
                    'potentials' => null,
                    'registrations' => null,
                ],

                [],
                true,
            ],

            // Course in past missing completedStandardStarts
            [
                [
                    'completedStandardStarts' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETED_SS_MISSING',
                        'reference.field' => 'completedStandardStarts',
                    ]),
                ],
                false,
            ],
            // Course in past missing potentials
            [
                [
                    'potentials' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_POTENTIALS_MISSING',
                        'reference.field' => 'potentials',
                    ]),
                ],
                false,
            ],
            // Course in past missing registrations
            [
                [
                    'registrations' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_REGISTRATIONS_MISSING',
                        'reference.field' => 'registrations',
                    ]),
                ],
                false,
            ],
            // Course in past missing all completion stats
            [
                [
                    'completedStandardStarts' => null,
                    'potentials' => null,
                    'registrations' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETED_SS_MISSING',
                        'reference.field' => 'completedStandardStarts',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_POTENTIALS_MISSING',
                        'reference.field' => 'potentials',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_REGISTRATIONS_MISSING',
                        'reference.field' => 'registrations',
                    ]),
                ],
                false,
            ],

            // Course in past with more completed SS that current SS
            [
                [
                    'completedStandardStarts' => 40,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS',
                        'reference.field' => 'completedStandardStarts',
                    ]),
                ],
                false,
            ],

            // Course in past with more completed SS that current SS, at London center for London
            [
                [
                    'completedStandardStarts' => 40,
                    'location' => 'London',
                    '__centerName' => 'London',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS',
                        'reference.field' => 'completedStandardStarts',
                    ]),
                ],
                false,
            ],
            // Course in past with more completed SS that current SS, at London center for Germany
            [
                [
                    'completedStandardStarts' => 40,
                    'location' => 'Germany',
                    '__centerName' => 'London',
                ],
                [],
                true,
            ],
            // Course in past with more completed SS that current SS, at London center for Into
            [
                [
                    'completedStandardStarts' => 40,
                    'location' => 'INTL',
                    '__centerName' => 'London',
                ],
                [],
                true,
            ],

            // Course in past with more than 3 withdraw during course
            [
                [
                    'completedStandardStarts' => 35,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETED_SS_LESS_THAN_CURRENT_SS',
                        'reference.field' => 'completedStandardStarts',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // Course in past with more than 3 withdraw during course, but checked over a week after the course
            [
                [
                    'startDate' => '2016-08-20',
                    'completedStandardStarts' => 35,
                ],
                [],
                true,
            ],
            // Course in past with <= 3 withdraw during course
            [
                [
                    'completedStandardStarts' => 36,
                ],
                [],
                true,
            ],
            // Course in past with more registrations than potentials
            [
                [
                    'potentials' => 25,
                    'registrations' => 30,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETED_REGISTRATIONS_GREATER_THAN_POTENTIALS',
                        'reference.field' => 'registrations',
                    ]),
                ],
                false,
            ],

            // Course in future with completedStandardStarts
            [
                [
                    'startDate' => '2016-09-03',
                    'registrations' => null,
                    'potentials' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE',
                        'reference.field' => 'completedStandardStarts',
                    ]),
                ],
                false,
            ],
            // Course in future with registrations
            [
                [
                    'startDate' => '2016-09-03',
                    'completedStandardStarts' => null,
                    'potentials' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE',
                        'reference.field' => 'registrations',
                    ]),
                ],
                false,
            ],
            // Course in future with potentials
            [
                [
                    'startDate' => '2016-09-03',
                    'completedStandardStarts' => null,
                    'registrations' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE',
                        'reference.field' => 'potentials',
                    ]),
                ],
                false,
            ],
            // Course in future with completion stats
            [
                [
                    'startDate' => '2016-09-03',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE',
                        'reference.field' => 'registrations',
                    ]),
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateCourseStartDate
     */
    public function testValidateCourseStartDate($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getCourse($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateCourseStartDate()
    {
        return [
            // Start Date during quarter
            [
                [
                ],
                [],
                true,
            ],
            // Start Date before quarter
            [
                [
                    'startDate' => '2016-08-13',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_COURSE_DATE_BEFORE_QUARTER',
                        'reference.field' => 'startDate',
                    ]),
                ],
                false,
            ],
            // Start Date not Saturday
            [
                [
                    'startDate' => '2016-08-28',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_START_DATE_NOT_SATURDAY',
                        'reference.field' => 'startDate',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateCourseBalance
     */
    public function testValidateCourseBalance($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getCourse($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateCourseBalance()
    {
        return [
            // Quarter Start valid 1
            [
                [
                    'quarterStartTer' => 20,
                    'quarterStartStandardStarts' => 20,
                    'quarterStartXfer' => 0,
                ],
                [],
                true,
            ],
            // Quarter Start valid 2
            [
                [
                    'quarterStartTer' => 20,
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer' => 0,
                ],
                [],
                true,
            ],
            // Quarter Start valid 3
            [
                [
                    'quarterStartTer' => 20,
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer' => 10,
                    'currentXfer' => 10,
                ],
                [],
                true,
            ],

            // Quarter Start invalid QStart TER less than QStart SS
            [
                [
                    'quarterStartTer' => 20,
                    'quarterStartStandardStarts' => 25,
                    'quarterStartXfer' => 0,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_SS_GREATER_THAN_QSTART_TER',
                        'reference.field' => 'quarterStartStandardStarts',
                    ]),
                ],
                false,
            ],
            // Quarter Start invalid QStart TER less than QStart xfer
            [
                [
                    'quarterStartTer' => 15,
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer' => 18,
                    'currentXfer' => 18,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_QSTART_XFER_GREATER_THAN_QSTART_TER',
                        'reference.field' => 'quarterStartXfer',
                    ]),
                ],
                false,
            ],

            // Current valid 1
            [
                [
                    'currentTer' => 40,
                    'currentStandardStarts' => 40,
                    'currentXfer' => 0,
                ],
                [],
                true,
            ],
            // Current valid 2
            [
                [
                    'currentTer' => 45,
                    'currentStandardStarts' => 40,
                    'currentXfer' => 0,
                ],
                [],
                true,
            ],
            // Current valid 3
            [
                [
                    'currentTer' => 45,
                    'currentStandardStarts' => 40,
                    'currentXfer' => 10,
                ],
                [],
                true,
            ],

            // Current invalid Current TER less than Current SS
            [
                [
                    'currentTer' => 30,
                    'currentStandardStarts' => 39,
                    'currentXfer' => 0,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER',
                        'reference.field' => 'currentStandardStarts',
                    ]),
                ],
                false,
            ],
            // Current invalid Current TER less than Current xfer
            [
                [
                    'currentTer' => 40,
                    'currentStandardStarts' => 40,
                    'currentXfer' => 45,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_CURRENT_XFER_GREATER_THAN_CURRENT_TER',
                        'reference.field' => 'currentXfer',
                    ]),
                ],
                false,
            ],

            // Current TER less than QStart TER
            [
                [
                    'quarterStartTer' => 40,
                    'currentTer' => 39,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_CURRENT_TER_LESS_THAN_QSTART_TER',
                        'reference.field' => 'currentTer',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // Current Xfer less than QStart Xfer
            [
                [
                    'quarterStartXfer' => 2,
                    'currentXfer' => 0,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_CURRENT_XFER_LESS_THAN_QSTART_XFER',
                        'reference.field' => 'currentXfer',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateGuestGame
     */
    public function testValidateGuestGame($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getCourse($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateGuestGame()
    {
        return [
            // Guests stats provided
            [
                [
                    'guestsPromised' => 1,
                    'guestsInvited' => 1,
                    'guestsConfirmed' => 1,
                    'guestsAttended' => 1,
                ],
                [],
                true,
            ],
            // Guests promised provided, but guestsInvited missing
            [
                [
                    'guestsPromised' => 1,
                    'guestsInvited' => null,
                    'guestsConfirmed' => 1,
                    'guestsAttended' => 1,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_GUESTS_INVITED_MISSING',
                        'reference.field' => 'guestsInvited',
                    ]),
                ],
                false,
            ],
            // Guests promised provided, but guestsConfirmed missing
            [
                [
                    'guestsPromised' => 1,
                    'guestsInvited' => 1,
                    'guestsConfirmed' => null,
                    'guestsAttended' => 1,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_GUESTS_CONFIRMED_MISSING',
                        'reference.field' => 'guestsConfirmed',
                    ]),
                ],
                false,
            ],
            // Guests promised provided, but guestsAttended missing
            [
                [
                    'guestsPromised' => 1,
                    'guestsInvited' => 1,
                    'guestsConfirmed' => 1,
                    'guestsAttended' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_GUESTS_ATTENDED_MISSING',
                        'reference.field' => 'guestsAttended',
                    ]),
                ],
                false,
            ],
            // Guests promised provided, but other stats missing
            [
                [
                    'guestsPromised' => 1,
                    'guestsInvited' => null,
                    'guestsConfirmed' => null,
                    'guestsAttended' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_GUESTS_INVITED_MISSING',
                        'reference.field' => 'guestsInvited',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_GUESTS_CONFIRMED_MISSING',
                        'reference.field' => 'guestsConfirmed',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_GUESTS_ATTENDED_MISSING',
                        'reference.field' => 'guestsAttended',
                    ]),
                ],
                false,
            ],

            // Guests attended provided before course completed
            [
                [
                    'startDate' => '2016-09-03',
                    'completedStandardStarts' => null,
                    'potentials' => null,
                    'registrations' => null,
                    'guestsPromised' => 1,
                    'guestsInvited' => 1,
                    'guestsConfirmed' => 1,
                    'guestsAttended' => 1,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_GUESTS_ATTENDED_PROVIDED_BEFORE_COURSE',
                        'reference.field' => 'guestsAttended',
                    ]),
                ],
                false,
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

        return Domain\Course::fromArray($data);
    }
}
