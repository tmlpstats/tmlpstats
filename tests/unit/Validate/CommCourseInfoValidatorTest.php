<?php
namespace TmlpStatsTests\Validate;

use TmlpStats\Validate\CommCourseInfoValidator;
use Carbon\Carbon;
use stdClass;

class CommCourseInfoValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = 'TmlpStats\Validate\CommCourseInfoValidator';

    protected $dataFields = array(
        'startDate',
        'type',
        'statsReportId',
        'reportingDate',
        'courseId',
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
                    'type'                       => null,
                    'statsReportId'              => null,
                    'reportingDate'              => null,
                    'courseId'                   => null,
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
                    array('INVALID_VALUE', 'Stats Report Id', '[empty]'),
                    array('INVALID_VALUE', 'Reporting Date', '[empty]'),
                    array('INVALID_VALUE', 'Course Id', '[empty]'),
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
                    'type'                       => 'CAP',
                    'statsReportId'              => 1234,
                    'reportingDate'              => '2015-05-08',
                    'courseId'                   => 5678,
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
                    'type'                       => 'CPC',
                    'statsReportId'              => 1234,
                    'reportingDate'              => '2015-12-31',
                    'courseId'                   => 5678,
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
                    'type'                       => 'CPC',
                    'statsReportId'              => 1234,
                    'reportingDate'              => '2015-12-31',
                    'courseId'                   => 5678,
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
            // Test Invalid Reporting Date non-date
            array(
                $this->arrayToObject(array(
                    'startDate'                  => '2016-05-16',
                    'type'                       => 'CPC',
                    'statsReportId'              => 1234,
                    'reportingDate'              => 'asdf',
                    'courseId'                   => 5678,
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
                    array('INVALID_VALUE', 'Reporting Date', 'asdf'),
                ),
                false,
            ),
            // Test Invalid StatsReportId negative
            array(
                $this->arrayToObject(array(
                    'startDate'                  => '2016-05-16',
                    'type'                       => 'CPC',
                    'statsReportId'              => -1,
                    'reportingDate'              => '2015-12-31',
                    'courseId'                   => 5678,
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
                    array('INVALID_VALUE', 'Stats Report Id', '-1'),
                ),
                false,
            ),
            // Test Invalid StatsReportId non-numeric
            array(
                $this->arrayToObject(array(
                    'startDate'                  => '2016-05-16',
                    'type'                       => 'CPC',
                    'statsReportId'              => 'asdf',
                    'reportingDate'              => '2015-12-31',
                    'courseId'                   => 5678,
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
                    array('INVALID_VALUE', 'Stats Report Id', 'asdf'),
                ),
                false,
            ),
            // Test Invalid CourseId negative
            array(
                $this->arrayToObject(array(
                    'startDate'                  => '2016-05-16',
                    'type'                       => 'CPC',
                    'statsReportId'              => 5678,
                    'reportingDate'              => '2015-12-31',
                    'courseId'                   => '-1',
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
                    array('INVALID_VALUE', 'Course Id', '-1'),
                ),
                false,
            ),
            // Test Invalid CourseId non-numeric
            array(
                $this->arrayToObject(array(
                    'startDate'                  => '2016-05-16',
                    'type'                       => 'CPC',
                    'statsReportId'              => 5678,
                    'reportingDate'              => '2015-12-31',
                    'courseId'                   => 'asdf',
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
                    array('INVALID_VALUE', 'Course Id', 'asdf'),
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
                  ->with($data->statsReportId)
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
        $statsReport = new stdClass;
        $statsReport->reportingDate = Carbon::createFromDate(2015, 5, 8);

        return array(
            // Gracefully handle null start date (don't blow up - required validator will catch this error)
            array(
                $this->arrayToObject(array(
                    'statsReportId'           => null,
                    'startDate'               => null,
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
                    'statsReportId'           => 1234,
                    'startDate'               => '2015-06-06',
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
                    'statsReportId'           => 1234,
                    'startDate'               => '2015-05-01',
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
                    'statsReportId'           => 1234,
                    'startDate'               => '2015-05-01',
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
                    'statsReportId'           => 1234,
                    'startDate'               => '2015-05-01',
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
                    'statsReportId'           => 1234,
                    'startDate'               => '2015-05-01',
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
                    'statsReportId'           => 1234,
                    'startDate'               => '2015-05-01',
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
            // Course in past with more than 3 withdraw during course
            array(
                $this->arrayToObject(array(
                    'statsReportId'           => 1234,
                    'startDate'               => '2015-05-01',
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
            // Course in past with <= 3 withdraw during course
            array(
                $this->arrayToObject(array(
                    'statsReportId'           => 1234,
                    'startDate'               => '2015-05-01',
                    'completedStandardStarts' => 32,
                    'potentials'              => 30,
                    'registrations'           => 25,
                    'currentStandardStarts'   => 35,
                )),
                $statsReport,
                array(),
                true,
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
                  ->with($data->statsReportId)
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
                    'statsReportId' => null,
                    'startDate'     => null,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Start Date during quarter
            array(
                $this->arrayToObject(array(
                    'statsReportId' => 1234,
                    'startDate'     => '2015-02-27',
                )),
                $statsReport,
                array(),
                true,
            ),
            // Start Date before quarter
            array(
                $this->arrayToObject(array(
                    'statsReportId' => 1234,
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
        );
    }
}