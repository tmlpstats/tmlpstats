<?php
namespace TmlpStats\Tests\Validate;

use TmlpStats\Validate\CommCourseInfoValidator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use stdClass;

class CommCourseInfoValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = 'TmlpStats\Validate\CommCourseInfoValidator';

    protected $dataFields = array(
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
    );

    /**
    * @dataProvider providerRun
    */
    public function testRun($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(array('addMessage', 'validate'));

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
        return array(
            // Test Required
            array(
                $this->arrayToObject(array(
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
                )),
                array(
                    array('INVALID_VALUE', 'Start Date', '[empty]'),
                    array('INVALID_VALUE', 'Type', '[empty]'),
                    array('INVALID_VALUE', 'Quarter Start Ter', '[empty]'),
                    array('INVALID_VALUE', 'Quarter Start Standard Starts', '[empty]'),
                    array('INVALID_VALUE', 'Quarter Start Xfer', '[empty]'),
                    array('INVALID_VALUE', 'Current Ter', '[empty]'),
                    array('INVALID_VALUE', 'Current Standard Starts', '[empty]'),
                    array('INVALID_VALUE', 'Current Xfer', '[empty]'),
                ),
                false,
            ),
            // Test Valid 1
            array(
                $this->arrayToObject(array(
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
                )),
                array(),
                true,
            ),
            // Test Valid 2
            array(
                $this->arrayToObject(array(
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
                )),
                array(),
                true,
            ),

            // Test Invalid StartDate non-date
            array(
                $this->arrayToObject(array(
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
                )),
                array(
                    array('INVALID_VALUE', 'Start Date', 'asdf'),
                ),
                false,
            ),
        );
    }

    /**
    * @dataProvider providerValidateExtended
    */
    public function testValidateExtended($returnValues, $expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'validateCourseBalance',
            'validateCourseCompletionStats',
            'validateCourseStartDate',
        ));
        $validator->expects($this->once())
                  ->method('validateCourseBalance')
                  ->will($this->returnValue($returnValues['validateCourseBalance']));
        $validator->expects($this->once())
                  ->method('validateCourseCompletionStats')
                  ->will($this->returnValue($returnValues['validateCourseCompletionStats']));
        $validator->expects($this->once())
                  ->method('validateCourseStartDate')
                  ->will($this->returnValue($returnValues['validateCourseStartDate']));

        $result = $this->runMethod($validator, 'validate', array());

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateExtended()
    {
        return array(
            // Validate Succeeds
            array(
                array(
                    'validateCourseBalance'         => true,
                    'validateCourseCompletionStats' => true,
                    'validateCourseStartDate'       => true,
                ),
                true,
            ),
            // validateCourseBalance fails
            array(
                array(
                    'validateCourseBalance'         => false,
                    'validateCourseCompletionStats' => true,
                    'validateCourseStartDate'       => true,
                ),
                false,
            ),
            // validateCourseCompletionStats fails
            array(
                array(
                    'validateCourseBalance'         => true,
                    'validateCourseCompletionStats' => false,
                    'validateCourseStartDate'       => true,
                ),
                false,
            ),
            // validateCourseStartDate fails
            array(
                array(
                    'validateCourseBalance'         => true,
                    'validateCourseCompletionStats' => true,
                    'validateCourseStartDate'       => false,
                ),
                false,
            ),
        );
    }

    /**
    * @dataProvider providerValidate
    */
    public function testValidate($expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'validateCourseBalance',
            'validateCourseCompletionStats',
            'validateCourseStartDate',
        ));
        $validator->expects($this->once())
                  ->method('validateCourseBalance')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateCourseCompletionStats')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateCourseStartDate')
                  ->will($this->returnValue(true));

        $this->setProperty($validator, 'isValid', $expectedResult);

        $result = $this->runMethod($validator, 'validate', array());

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        return array(
            array(true),
            array(false),
        );
    }

    /**
    * @dataProvider providerValidateCourseCompletionStats
    */
    public function testValidateCourseCompletionStats($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'getStatsReport',
            'addMessage',
        ));
        $validator->expects($this->once())
                  ->method('getStatsReport')
                  ->will($this->returnValue($statsReport));

        if ($messages) {
            $offset = 1;
            for ($i = 0; $i < count($messages); $i++) {
                if (count($messages[$i]) > 1) {
                    $validator->expects($this->at($i+$offset))
                              ->method('addMessage')
                              ->with($messages[$i][0],$messages[$i][1]);
                } else {
                    $validator->expects($this->at($i+$offset))
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
        $london = new stdClass();
        $london->name = 'London';

        $atlanta = new stdClass();
        $atlanta->name = 'Atlanta';

        $statsReport = new stdClass;
        $statsReport->reportingDate = Carbon::createFromDate(2015, 5, 8);
        $statsReport->center = $atlanta;

        $statsReportLondon = clone $statsReport;
        $statsReportLondon->center = $london;

        $statsReportTwoWeeksAfterCourse = clone $statsReport;
        $statsReportTwoWeeksAfterCourse->reportingDate = Carbon::createFromDate(2015, 5, 15);

        return array(
            // Gracefully handle null start date (don't blow up - required validator will catch this error)
            array(
                $this->arrayToObject(array(
                    'startDate'               => null,
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => null,
                    'potentials'              => null,
                    'registrations'           => null,
                    'currentStandardStarts'   => null,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Course in future
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-06-06',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => null,
                    'potentials'              => null,
                    'registrations'           => null,
                    'currentStandardStarts'   => null,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Course in past with completion stats
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReport,
                array(),
                true,
            ),

            // Course in past missing completedStandardStarts
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => null,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReport,
                array(
                    array('COMMCOURSE_COMPLETED_SS_MISSING'),
                ),
                false,
            ),
            // Course in past missing potentials
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => null,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReport,
                array(
                    array('COMMCOURSE_POTENTIALS_MISSING'),
                ),
                false,
            ),
            // Course in past missing registrations
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => 30,
                    'registrations'           => null,
                    'currentStandardStarts'   => 35,
                )),
                $statsReport,
                array(
                    array('COMMCOURSE_REGISTRATIONS_MISSING'),
                ),
                false,
            ),

            // Course in past with more completed SS that current SS
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 40,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReport,
                array(
                    array('COMMCOURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS'),
                ),
                false,
            ),
            // Course in past with more completed SS that current SS, at London center for London
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'London',
                    'completedStandardStarts' => 40,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReportLondon,
                array(
                    array('COMMCOURSE_COMPLETED_SS_GREATER_THAN_CURRENT_SS'),
                ),
                false,
            ),
            // Course in past with more completed SS that current SS, at London center for Germany
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'Germany',
                    'completedStandardStarts' => 40,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReportLondon,
                array(),
                true,
            ),
            // Course in past with more completed SS that current SS, at London center for Into
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'INTL',
                    'completedStandardStarts' => 40,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReportLondon,
                array(),
                true,
            ),

            // Course in past with more than 3 withdraw during course
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 31,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReport,
                array(
                    array('COMMCOURSE_COMPLETED_SS_LESS_THAN_CURRENT_SS', 4),
                ),
                true,
            ),
            // Course in past with more than 3 withdraw during course, but checked over a week after the course
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 31,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReportTwoWeeksAfterCourse,
                array(),
                true,
            ),
            // Course in past with <= 3 withdraw during course
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-02',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 32,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReport,
                array(),
                true,
            ),

            // Course in future with completion stats
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-16',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => 30,
                    'registrations'           => null,
                    'currentStandardStarts'   => null,
                )),
                $statsReport,
                array(
                    array('COMMCOURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE')
                ),
                false,
            ),
            // Course in future with completion stats
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-16',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => null,
                    'registrations'           => 30,
                    'currentStandardStarts'   => null,
                )),
                $statsReport,
                array(
                    array('COMMCOURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE')
                ),
                false,
            ),
            // Course in future with completion stats
            array(
                $this->arrayToObject(array(
                    'startDate'               => '2015-05-16',
                    'location'                => 'Atlanta',
                    'completedStandardStarts' => 35,
                    'potentials'              => null,
                    'registrations'           => null,
                    'currentStandardStarts'   => 30,
                )),
                $statsReport,
                array(
                    array('COMMCOURSE_COMPLETION_STATS_PROVIDED_BEFORE_COURSE')
                ),
                false,
            ),
        );
    }

    /**
    * @dataProvider providerValidateCourseStartDate
    */
    public function testValidateCourseStartDate($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'getStatsReport',
            'addMessage',
        ));
        $validator->expects($this->once())
                  ->method('getStatsReport')
                  ->will($this->returnValue($statsReport));

        if ($messages) {
            $offset = 1;
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i+$offset))
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
        $statsReport = new stdClass;
        $statsReport->quarter = new stdClass;
        $statsReport->quarter->startWeekendDate = Carbon::createFromDate(2015, 2, 20);

        return array(
            // Gracefully handle null start date (don't blow up - required validator will catch this error)
            array(
                $this->arrayToObject(array(
                    'startDate'     => null,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Start Date during quarter
            array(
                $this->arrayToObject(array(
                    'startDate'     => '2015-02-27',
                )),
                $statsReport,
                array(),
                true,
            ),
            // Start Date before quarter
            array(
                $this->arrayToObject(array(
                    'startDate'     => '2015-02-13',
                )),
                $statsReport,
                array('COMMCOURSE_COURSE_DATE_BEFORE_QUARTER'),
                false,
            ),
        );
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
        return array(
            // All null
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                )),
                array(),
                true,
            ),

            // Quarter Start valid 1
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 20,
                    'quarterStartXfer'           => 0,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                )),
                array(),
                true,
            ),
            // Quarter Start valid 2
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer'           => 0,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                )),
                array(),
                true,
            ),
            // Quarter Start valid 3
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer'           => 10,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                )),
                array(),
                true,
            ),

            // Quarter Start invalid QStart TER less than QStart SS
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 25,
                    'quarterStartXfer'           => 0,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                )),
                array(
                    array('COMMCOURSE_QSTART_SS_GREATER_THAN_QSTART_TER', 25, 20),
                ),
                false,
            ),
            // Quarter Start invalid QStart TER less than QStart xfer
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => 15,
                    'quarterStartStandardStarts' => 15,
                    'quarterStartXfer'           => 18,
                    'currentTer'                 => null,
                    'currentStandardStarts'      => null,
                    'currentXfer'                => null,
                )),
                array(
                    array('COMMCOURSE_QSTART_XFER_GREATER_THAN_QSTART_TER', 18, 15),
                ),
                false,
            ),

            // Current valid 1
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 20,
                    'currentStandardStarts'      => 20,
                    'currentXfer'                => 0,
                )),
                array(),
                true,
            ),
            // Current valid 2
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 20,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 0,
                )),
                array(),
                true,
            ),
            // Current valid 3
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 20,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 10,
                )),
                array(),
                true,
            ),

            // Current invalid Current TER less than Current SS
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 20,
                    'currentStandardStarts'      => 25,
                    'currentXfer'                => 0,
                )),
                array(
                    array('COMMCOURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER', 25, 20),
                ),
                false,
            ),
            // Current invalid Current TER less than Current xfer
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => null,
                    'quarterStartStandardStarts' => null,
                    'quarterStartXfer'           => null,
                    'currentTer'                 => 15,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 18,
                )),
                array(
                    array('COMMCOURSE_CURRENT_XFER_GREATER_THAN_CURRENT_TER', 18, 15),
                ),
                false,
            ),

            // Current and QStart values align
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => 12,
                    'quarterStartStandardStarts' => 12,
                    'quarterStartXfer'           => 2,
                    'currentTer'                 => 18,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 5,
                )),
                array(),
                true,
            ),
            // Current and QStart values align with less standard stats
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => 12,
                    'quarterStartStandardStarts' => 12,
                    'quarterStartXfer'           => 2,
                    'currentTer'                 => 18,
                    'currentStandardStarts'      => 10,
                    'currentXfer'                => 5,
                )),
                array(),
                true,
            ),
            // Current TER less than QStart TER
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => 20,
                    'quarterStartStandardStarts' => 12,
                    'quarterStartXfer'           => 2,
                    'currentTer'                 => 18,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 5,
                )),
                array(
                    array('COMMCOURSE_CURRENT_TER_LESS_THAN_QSTART_TER', 18, 20),
                ),
                true,
            ),
            // Current Xfer less than QStart Xfer
            array(
                $this->arrayToObject(array(
                    'quarterStartTer'            => 12,
                    'quarterStartStandardStarts' => 12,
                    'quarterStartXfer'           => 2,
                    'currentTer'                 => 18,
                    'currentStandardStarts'      => 15,
                    'currentXfer'                => 0,
                )),
                array(
                    array('COMMCOURSE_CURRENT_XFER_LESS_THAN_QSTART_XFER', 0, 2),
                ),
                true,
            ),
        );
    }
}
