<?php
namespace TmlpStats\Tests\Validate\Objects;

use TmlpStats\Util;
use TmlpStats\Validate\objects\CommCourseInfoValidator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use stdClass;

class CommCourseInfoValidatorTest extends ObjectsValidatorTestAbstract
{
    protected $testClass = CommCourseInfoValidator::class;

    protected $dataFields = [
        'startDate',
        'location',
        'type',
        'quarterStartTer',
        'quarterStartStandardStarts',
        'quarterStartXfer',
        'currentTer',
        'currentStandardStarts',
        'currentXfer',
        'completedStandardStarts',
        'potentials',
        'registrations',
        'guestsPromised',
        'guestsInvited',
        'guestsConfirmed',
        'guestsAttended',
    ];

    protected $validateMethods = [
        'validateCourseBalance',
        'validateCourseCompletionStats',
        'validateCourseStartDate',
        'validateGuestGame',
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

        Log::shouldReceive('error');

        $result = $validator->run($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerRun()
    {
        return [
            // Test Required
            [
                Util::arrayToObject([
                    'startDate'                  => null,
                    'location'                   => null,
                    'type'                       => null,
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                    'completedStandardStarts'    => null,
                    'potentials'                 => null,
                    'registrations'              => null,
                    'guestsPromised'             => null,
                    'guestsInvited'              => null,
                    'guestsConfirmed'            => null,
                    'guestsAttended'             => null,
                ]),
                [
                    ['INVALID_VALUE', 'Start Date', '[empty]'],
                    ['INVALID_VALUE', 'Type', '[empty]'],
                    ['INVALID_VALUE', 'Quarter Start Ter', '[empty]'],
                    ['INVALID_VALUE', 'Quarter Start Standard Starts', '[empty]'],
                    ['INVALID_VALUE', 'Quarter Start Xfer', '[empty]'],
                    ['INVALID_VALUE', 'Current Ter', '[empty]'],
                    ['INVALID_VALUE', 'Current Standard Starts', '[empty]'],
                    ['INVALID_VALUE', 'Current Xfer', '[empty]'],
                ],
                false,
            ],
            // Test Valid 1
            [
                Util::arrayToObject([
                    'startDate'                  => '2015-05-16',
                    'location'                   => '',
                    'type'                       => 'CAP',
                    'quarterStartTer'            => 15,
                    'quarterStartStandardStarts' => 14,
                    'quarterStartXfer'           => 0,
                    'currentTer'                 => 45,
                    'currentStandardStarts'      => 39,
                    'currentXfer'                => 1,
                    'completedStandardStarts'    => 38,
                    'potentials'                 => 35,
                    'registrations'              => 25,
                    'guestsPromised'             => null,
                    'guestsInvited'              => null,
                    'guestsConfirmed'            => null,
                    'guestsAttended'             => null,
                ]),
                [],
                true,
            ],
            // Test Valid 2
            [
                Util::arrayToObject([
                    'startDate'                  => '2016-05-16',
                    'location'                   => 'Germany',
                    'type'                       => 'CPC',
                    'quarterStartTer'            => 0,
                    'quarterStartStandardStarts' => 0,
                    'quarterStartXfer'           => 0,
                    'currentTer'                 => 0,
                    'currentStandardStarts'      => 0,
                    'currentXfer'                => 0,
                    'completedStandardStarts'    => 0,
                    'potentials'                 => 0,
                    'registrations'              => 0,
                    'guestsPromised'             => 0,
                    'guestsInvited'              => 0,
                    'guestsConfirmed'            => 0,
                    'guestsAttended'             => 0,
                ]),
                [],
                true,
            ],

            // Test Invalid StartDate non-date
            [
                Util::arrayToObject([
                    'startDate'                  => 'asdf',
                    'location'                   => null,
                    'type'                       => 'CPC',
                    'quarterStartTer'            => 0,
                    'quarterStartStandardStarts' => 0,
                    'quarterStartXfer'           => 0,
                    'currentTer'                 => 0,
                    'currentStandardStarts'      => 0,
                    'currentXfer'                => 0,
                    'completedStandardStarts'    => 0,
                    'potentials'                 => 0,
                    'registrations'              => 0,
                    'guestsPromised'             => 0,
                    'guestsInvited'              => 0,
                    'guestsConfirmed'            => 0,
                    'guestsAttended'             => 0,
                ]),
                [
                    ['INVALID_VALUE', 'Start Date', 'asdf'],
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateCourseCompletionStats
     */
    public function testValidateCourseCompletionStats($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);

        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                if (count($messages[$i]) > 1) {
                    $validator->expects($this->at($i))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1]);
                } else {
                    $validator->expects($this->at($i))
                              ->method('addMessage')
                              ->with($messages[$i][0]);
                }
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $result = $validator->validateCourseCompletionStats($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateCourseCompletionStats()
    {
        $london       = new stdClass();
        $london->name = 'London';

        $atlanta       = new stdClass();
        $atlanta->name = 'Atlanta';

        $statsReport                = new stdClass;
        $statsReport->reportingDate = Carbon::createFromDate(2015, 5, 8);
        $statsReport->center        = $atlanta;

        $statsReportLondon         = clone $statsReport;
        $statsReportLondon->center = $london;

        $statsReportTwoWeeksAfterCourse                = clone $statsReport;
        $statsReportTwoWeeksAfterCourse->reportingDate = Carbon::createFromDate(2015, 5, 15);

        return [
            // Gracefully handle null start date (don't blow up - required validator will catch this error)
            [
                Util::arrayToObject([
                    'startDate'               => null,
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => null,
                    'potentials'              => null,
                    'registrations'           => null,
                    'currentStandardStarts'   => null,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Course in future
            [
                Util::arrayToObject([
                    'startDate'               => '2015-06-06',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => null,
                    'potentials'              => null,
                    'registrations'           => null,
                    'currentStandardStarts'   => null,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Course in past with completion stats
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReport,
                [],
                true,
            ],

            // Course in past missing completedStandardStarts
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => null,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReport,
                [
                    ['COMMCOURSE_COMPLETED_SS_MISSING'],
                ],
                false,
            ],
            // Course in past missing potentials
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => null,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReport,
                [
                    ['COMMCOURSE_POTENTIALS_MISSING'],
                ],
                false,
            ],
            // Course in past missing registrations
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => 30,
                    'registrations'           => null,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReport,
                [
                    ['COMMCOURSE_REGISTRATIONS_MISSING'],
                ],
                false,
            ],

            // Course in past with more completed SS that current SS
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 40,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReport,
                [
                    ['COMMCOURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS'],
                ],
                false,
            ],
            // Course in past with more completed SS that current SS, at London center for London
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'London',
                    'completedStandardStarts' => 40,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReportLondon,
                [
                    ['COMMCOURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS'],
                ],
                false,
            ],
            // Course in past with more completed SS that current SS, at London center for Germany
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'Germany',
                    'completedStandardStarts' => 40,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReportLondon,
                [],
                true,
            ],
            // Course in past with more completed SS that current SS, at London center for Into
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'INTL',
                    'completedStandardStarts' => 40,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReportLondon,
                [],
                true,
            ],

            // Course in past with more than 3 withdraw during course
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 31,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReport,
                [
                    ['COMMCOURSE_COMPLETED_SS_LESS_THAN_CURRENT_SS', 4],
                ],
                true,
            ],
            // Course in past with more than 3 withdraw during course, but checked over a week after the course
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 31,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReportTwoWeeksAfterCourse,
                [],
                true,
            ],
            // Course in past with <= 3 withdraw during course
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 32,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                ]),
                $statsReport,
                [],
                true,
            ],

            // Course in future with completion stats
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-16',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => 30,
                    'registrations'           => null,
                    'currentStandardStarts'   => null,
                ]),
                $statsReport,
                [
                    ['COMMCOURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE'],
                ],
                false,
            ],
            // Course in future with completion stats
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-16',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => null,
                    'registrations'           => 30,
                    'currentStandardStarts'   => null,
                ]),
                $statsReport,
                [
                    ['COMMCOURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE'],
                ],
                false,
            ],
            // Course in future with completion stats
            [
                Util::arrayToObject([
                    'startDate'               => '2015-05-16',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => null,
                    'registrations'           => null,
                    'currentStandardStarts'   => 30,
                ]),
                $statsReport,
                [
                    ['COMMCOURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE'],
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateCourseStartDate
     */
    public function testValidateCourseStartDate($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);

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

        $result = $validator->validateCourseStartDate($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateCourseStartDate()
    {
        $statsReport                            = new stdClass;
        $statsReport->quarter                   = new stdClass;
        $statsReport->quarter->startWeekendDate = Carbon::createFromDate(2015, 2, 20);

        return [
            // Gracefully handle null start date (don't blow up - required validator will catch this error)
            [
                Util::arrayToObject([
                    'startDate' => null,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Start Date during quarter
            [
                Util::arrayToObject([
                    'startDate' => '2015-02-27',
                ]),
                $statsReport,
                [],
                true,
            ],
            // Start Date before quarter
            [
                Util::arrayToObject([
                    'startDate' => '2015-02-13',
                ]),
                $statsReport,
                ['COMMCOURSE_COURSE_DATE_BEFORE_QUARTER'],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateCourseBalance
     */
    public function testValidateCourseBalance($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

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

        $result = $validator->validateCourseBalance($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateCourseBalance()
    {
        return [
            // All null
            [
                Util::arrayToObject([
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                ]),
                [],
                true,
            ],

            // Quarter Start valid 1
            [
                Util::arrayToObject([
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 20,
                    'quarterStartXfer'           => 0,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                ]),
                [],
                true,
            ],
            // Quarter Start valid 2
            [
                Util::arrayToObject([
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer'           => 0,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                ]),
                [],
                true,
            ],
            // Quarter Start valid 3
            [
                Util::arrayToObject([
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer'           => 10,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                ]),
                [],
                true,
            ],

            // Quarter Start invalid QStart TER less than QStart SS
            [
                Util::arrayToObject([
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 25,
                    'quarterStartXfer'           => 0,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                ]),
                [
                    ['COMMCOURSE_QSTART_SS_GREATER_THAN_QSTART_TER', 25, 20],
                ],
                false,
            ],
            // Quarter Start invalid QStart TER less than QStart xfer
            [
                Util::arrayToObject([
                    'quarterStartTer'            => 15,
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer'           => 18,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                ]),
                [
                    ['COMMCOURSE_QSTART_XFER_GREATER_THAN_QSTART_TER', 18, 15],
                ],
                false,
            ],

            // Current valid 1
            [
                Util::arrayToObject([
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 20,
                    'currentStandardStarts'      => 20,
                    'currentXfer'                => 0,
                ]),
                [],
                true,
            ],
            // Current valid 2
            [
                Util::arrayToObject([
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 20,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 0,
                ]),
                [],
                true,
            ],
            // Current valid 3
            [
                Util::arrayToObject([
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 20,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 10,
                ]),
                [],
                true,
            ],

            // Current invalid Current TER less than Current SS
            [
                Util::arrayToObject([
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 20,
                    'currentStandardStarts'      => 25,
                    'currentXfer'                => 0,
                ]),
                [
                    ['COMMCOURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER', 25, 20],
                ],
                false,
            ],
            // Current invalid Current TER less than Current xfer
            [
                Util::arrayToObject([
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 15,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 18,
                ]),
                [
                    ['COMMCOURSE_CURRENT_XFER_GREATER_THAN_CURRENT_TER', 18, 15],
                ],
                false,
            ],

            // Current and QStart values align
            [
                Util::arrayToObject([
                    'quarterStartTer'            => 12,
                    'quarterStartStandardStarts' => 12,
                    'quarterStartXfer'           => 2,
                    'currentTer'                 => 18,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 5,
                ]),
                [],
                true,
            ],
            // Current and QStart values align with less standard stats
            [
                Util::arrayToObject([
                    'quarterStartTer'            => 12,
                    'quarterStartStandardStarts' => 12,
                    'quarterStartXfer'           => 2,
                    'currentTer'                 => 18,
                    'currentStandardStarts'      => 10,
                    'currentXfer'                => 5,
                ]),
                [],
                true,
            ],
            // Current TER less than QStart TER
            [
                Util::arrayToObject([
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 12,
                    'quarterStartXfer'           => 2,
                    'currentTer'                 => 18,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 5,
                ]),
                [
                    ['COMMCOURSE_CURRENT_TER_LESS_THAN_QSTART_TER', 18, 20],
                ],
                true,
            ],
            // Current Xfer less than QStart Xfer
            [
                Util::arrayToObject([
                    'quarterStartTer'            => 12,
                    'quarterStartStandardStarts' => 12,
                    'quarterStartXfer'           => 2,
                    'currentTer'                 => 18,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 0,
                ]),
                [
                    ['COMMCOURSE_CURRENT_XFER_LESS_THAN_QSTART_XFER', 0, 2],
                ],
                true,
            ],
        ];
    }
}
