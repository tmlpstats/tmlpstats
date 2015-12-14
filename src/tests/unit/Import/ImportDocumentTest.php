<?php
namespace TmlpStats\Tests\Import;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Message;
use TmlpStats\Util;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use stdClass;

class ImporterStub
{
    protected $data = null;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}

class ImportDocumentTest extends \TmlpStats\Tests\TestAbstract
{
    protected $testClass = 'TmlpStats\Import\Xlsx\ImportDocument\ImportDocument';

    /**
    * @dataProvider providerValidateReport
    */
    public function testValidateReport($runResults,
                                       $validateStatsReportResult,
                                       $validateTeamExpansionResult,
                                       $validateCenterGamesResult,
                                       $expectedResult)
    {
        // TODO: Move this to Validate\Relationships
        $this->markTestSkipped();

        $data = array(
            array(
                'one' => 1,
            ),
            array(
                'two' => 2,
            ),
            array(
                'three' => 3,
            ),
        );
        $importers = array(
            'importer0' => new ImporterStub($data),
            'importer1' => new ImporterStub($data),
            'importer2' => new ImporterStub($data),
        );
        $messages = array(
            'errors' => array(
                array('type' => 'error', 'message' => 'one'),
                array('type' => 'error', 'message' => 'two'),
                array('type' => 'error', 'message' => 'three'),
                array('type' => 'error', 'message' => 'four'),
                array('type' => 'error', 'message' => 'five'),
            ),
            'warnings' => array(
                array('type' => 'warning', 'message' => 'six'),
                array('type' => 'warning', 'message' => 'seven'),
                array('type' => 'warning', 'message' => 'eight'),
                array('type' => 'warning', 'message' => 'nine'),
            ),
        );

        $validator = $this->getValidatorMock(array(
            'run',
            'getMessages',
        ));
        $validator->expects($this->exactly(9))
                  ->method('run')
                  ->withConsecutive(
                        array(Util::arrayToObject($data[0])),
                        array(Util::arrayToObject($data[1])),
                        array(Util::arrayToObject($data[2])),
                        array(Util::arrayToObject($data[0])),
                        array(Util::arrayToObject($data[1])),
                        array(Util::arrayToObject($data[2])),
                        array(Util::arrayToObject($data[0])),
                        array(Util::arrayToObject($data[1])),
                        array(Util::arrayToObject($data[2]))
                    )
                  ->will($this->onConsecutiveCalls(
                        $runResults[0],
                        $runResults[1],
                        $runResults[2],
                        $runResults[3],
                        $runResults[4],
                        $runResults[5],
                        $runResults[6],
                        $runResults[7],
                        $runResults[8]
                    ));
        $validator->expects($this->exactly(9))
                  ->method('getMessages')
                  ->will($this->onConsecutiveCalls(
                        array($messages['errors'][0]),
                        array($messages['errors'][1]),
                        array($messages['errors'][2]),
                        array($messages['errors'][3]),
                        array($messages['errors'][4]),
                        array($messages['warnings'][0]),
                        array($messages['warnings'][1]),
                        array($messages['warnings'][2]),
                        array($messages['warnings'][3])
                    ));

        $importDoc = $this->getObjectMock(array(
            'getValidator',
            'validateStatsReport',
            'validateTeamExpansion',
            'validateCenterGames',
        ));
        $importDoc->expects($this->exactly(9))
                  ->method('getValidator')
                  ->withConsecutive(
                        array('importer0'),
                        array('importer0'),
                        array('importer0'),
                        array('importer1'),
                        array('importer1'),
                        array('importer1'),
                        array('importer2'),
                        array('importer2'),
                        array('importer2')
                    )
                  ->will($this->returnValue($validator));

        $importDoc->expects($this->once())
                  ->method('validateStatsReport')
                  ->will($this->returnValue($validateStatsReportResult));
        $importDoc->expects($this->once())
                  ->method('validateTeamExpansion')
                  ->will($this->returnValue($validateTeamExpansionResult));
        $importDoc->expects($this->once())
                  ->method('validateCenterGames')
                  ->will($this->returnValue($validateCenterGamesResult));

        $this->setProperty($importDoc, 'importers', $importers);

        $result = $this->runMethod($importDoc, 'validateReport');

        $resultMessages = $this->getProperty($importDoc, 'messages');

        $this->assertEquals($messages, $resultMessages);
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateReport()
    {
        return array(
            // all pass
            array(
                array(
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                ),
                true,
                true,
                true,
                true,
            ),
            // validator->run() fails for one test
            array(
                array(
                    false,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                ),
                true,
                true,
                true,
                false,
            ),
            // validator->run() fails for one test
            array(
                array(
                    true,
                    true,
                    true,
                    true,
                    false,
                    true,
                    true,
                    true,
                    true,
                ),
                true,
                true,
                true,
                false,
            ),
            // validator->run() fails for multiple test
            array(
                array(
                    false,
                    true,
                    true,
                    true,
                    false,
                    true,
                    true,
                    true,
                    false,
                ),
                true,
                true,
                true,
                false,
            ),
            // validator->validateStatsReport() fails
            array(
                array(
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                ),
                false,
                true,
                true,
                false,
            ),
            // validator->validateTeamExpansion() fails
            array(
                array(
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                ),
                true,
                false,
                true,
                false,
            ),
            // validator->validateCenterGames() fails
            array(
                array(
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                    true,
                ),
                true,
                true,
                false,
                false,
            ),
            // all fail
            array(
                array(
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                    false,
                ),
                false,
                false,
                false,
                false,
            ),
        );
    }


    /**
    * @dataProvider providerValidateTeamExpansion
    */
    public function testValidateTeamExpansion($reportingDate, $quarter, $importers, $messages, $expectedResult)
    {
        // TODO: Move this to Validate\Relationships
        $this->markTestSkipped();

        $importDoc = $this->getObjectMock(array('addMessage'));
        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                if (count($messages[$i]) == 5) {
                    $importDoc->expects($this->at($i))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1], $messages[$i][2], $messages[$i][3], $messages[$i][4]);
                } else if (count($messages[$i]) == 4) {
                    $importDoc->expects($this->at($i))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1], $messages[$i][2], $messages[$i][3]);
                } else {
                    $this->assertTrue(false, 'Invalid message count provided to test');
                }
            }
        } else {
            $importDoc->expects($this->never())
                      ->method('addMessage');
        }

        Log::shouldReceive('error');

        $this->setProperty($importDoc, 'reportingDate', $reportingDate);
        $this->setProperty($importDoc, 'importers', $importers);
        $this->setProperty($importDoc, 'quarter', $quarter);

        $result = $this->runMethod($importDoc, 'validateTeamExpansion');

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTeamExpansion()
    {
        $quarter = new stdClass;
        $quarter->startWeekendDate = Carbon::createFromDate(2015, 5, 29)->startOfDay();

        $reportingDate             = Carbon::createFromDate(2015, 6, 5)->startOfDay();
        $reportingDateSecondWeek   = Carbon::createFromDate(2015, 6, 12)->startOfDay();

        return array(
            // All null (nothing blows up)
            array(
                $reportingDate,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => null,
                            'incomingWeekend'  => null,
                            'incomingTeamYear' => null,
                            'appr'             => null,
                            'apprDate'         => null,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => null,
                            'quarterStartRegistered' => null,
                            'quarterStartApproved'   => null,
                        ),
                    )),
                ),
                array(),
                true,
            ),
            // RegDate formats
            array(
                $reportingDate,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => null,
                            'apprDate'         => null,
                        ),
                        array(
                            'regDate'          => '05/22/2015',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => null,
                            'apprDate'         => null,
                        ),
                        array(
                            'regDate'          => 'asdf',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => null,
                            'apprDate'         => null,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 0,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                    )),
                ),
                array(),
                true,
            ),
            // ApprDate formats
            array(
                $reportingDate,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '05/23/2015',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => 'asdf',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved'   => 2,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                    )),
                ),
                array(),
                true,
            ),
            // Date comparisons
            array(
                $reportingDate,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                    )),
                ),
                array(),
                true,
            ),
            // All the types
            array(
                $reportingDate,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                    )),
                ),
                array(),
                true,
            ),

            // First week, qStart registered incorrect
            array(
                $reportingDate,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved'   => 1,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Incoming T1', 1, 2),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Future T1', 1, 2),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Future T2', 1, 2),
                ),
                false,
            ),
            // First week, qStart approved incorrect
            array(
                $reportingDate,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 2,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 2,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 2,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Incoming T1', 2, 1),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Incoming T2', 2, 1),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Future T2', 2, 1),
                ),
                false,
            ),
            // First week, both qStart incorrect
            array(
                $reportingDate,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved'   => 2,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved'   => 2,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved'   => 2,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Future T1', 3, 2),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Future T1', 2, 1),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Incoming T2', 3, 2),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Incoming T2', 2, 1),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Future T2', 3, 2),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Future T2', 2, 1),
                ),
                false,
            ),

            // Current and Future total are same as quarter start
            array(
                $reportingDateSecondWeek,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                    )),
                ),
                array(),
                true,
            ),
            // Current and Future total are redistributed from quarter start
            array(
                $reportingDateSecondWeek,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 4,
                            'quarterStartApproved'   => 2,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved'   => 0,
                        ),
                    )),
                ),
                array(),
                true,
            ),
            // Registered totals don't match
            array(
                $reportingDateSecondWeek,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved'   => 1,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_T1_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 5, 4),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_T2_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 3, 4),
                ),
                true,
            ),
            // Approved totals don't match
            array(
                $reportingDateSecondWeek,
                $quarter,
                array(
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 1,
                            'appr'             => 1,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-04-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-04-23',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-05-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-05-30',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'current',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                        array(
                            'regDate'          => '2015-06-22',
                            'incomingWeekend'  => 'future',
                            'incomingTeamYear' => 2,
                            'appr'             => 2,
                            'apprDate'         => '2015-06-23',
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                   => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                        array(
                            'type'                   => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 0,
                        ),
                        array(
                            'type'                   => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 3,
                        ),
                        array(
                            'type'                   => 'Future T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved'   => 1,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_T1_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 1, 2),
                    array(ImportDocument::TAB_COURSES, 'IMPORTDOC_QSTART_T2_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 4, 2),
                ),
                true,
            ),
        );
    }

    /**
    * @dataProvider providerValidateCenterGames
    */
    public function testValidateCenterGames($reportingDate, $importers, $messages, $expectedResult)
    {
        // TODO: Move this to Validate\Relationships
        $this->markTestSkipped();

        $importDoc = $this->getObjectMock(array('addMessage'));
        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                if (count($messages[$i]) == 4) {
                    $importDoc->expects($this->at($i))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1], $messages[$i][2], $messages[$i][3]);
                } else {
                    $this->assertTrue(false, 'Invalid message count provided to test');
                }
            }
        } else {
            $importDoc->expects($this->never())
                      ->method('addMessage');
        }

        Log::shouldReceive('error');

        $this->setProperty($importDoc, 'reportingDate', $reportingDate);
        $this->setProperty($importDoc, 'importers', $importers);

        $result = $this->runMethod($importDoc, 'validateCenterGames');

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateCenterGames()
    {
        $reportingDate = Carbon::createFromDate(2015, 6, 12)->startOfDay();

        return array(
            // Empty importers (doesn't blow up)
            array(
                $reportingDate,
                array(
                    'centerStats' => new ImporterStub(array()),
                    'classList' => new ImporterStub(array()),
                    'commCourseInfo' => new ImporterStub(array()),
                    'tmlpRegistration' => new ImporterStub(array()),
                    'tmlpCourseInfo' => new ImporterStub(array()),
                ),
                array(),
                true,
            ),
            // All null (doesn't blow up)
            array(
                $reportingDate,
                array(
                    'centerStats' => new ImporterStub(array(
                        array(
                            'type'                       => null,
                            'reportingDate'              => null,
                            'cap'                        => null,
                            'cpc'                        => null,
                            't1x'                        => null,
                            't2x'                        => null,
                            'gitw'                       => null,
                        ),
                    )),
                    'classList' => new ImporterStub(array(
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => null,
                        ),
                    )),
                    'commCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => null,
                            'currentStandardStarts'      => null,
                            'quarterStartStandardStarts' => null,
                        ),
                    )),
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => null,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => null,
                            'quarterStartApproved'       => null,
                        ),
                    )),
                ),
                array(),
                true,
            ),

            // BFT - success
            array(
                $reportingDate,
                array(
                    'centerStats' => new ImporterStub(array(
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-05',
                        ),
                        array(
                            'type'                       => 'promise',
                            'reportingDate'              => '2015-06-12',
                        ),
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-12',
                            'cap'                        => 15,
                            'cpc'                        => 8,
                            't1x'                        => 4,
                            't2x'                        => 2,
                            'gitw'                       => 80,
                        ),
                    )),
                    'classList' => new ImporterStub(array(
                        array(
                            'wd'                         => 1,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 2,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 'R',
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'I',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                    )),
                    'commCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ),
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ),
                        array(
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ),
                    )),
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'Incoming T1',
                            'quarterStartApproved'       => 2,
                        ),
                        array(
                            'type'                       => 'Future T1',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Incoming T2',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Future T2',
                            'quarterStartApproved'       => 0,
                        ),
                    )),
                ),
                array(),
                true,
            ),
            // BFT - Incorrect CAP
            array(
                $reportingDate,
                array(
                    'centerStats' => new ImporterStub(array(
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-05',
                        ),
                        array(
                            'type'                       => 'promise',
                            'reportingDate'              => '2015-06-12',
                        ),
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-12',
                            'cap'                        => 12,
                            'cpc'                        => 8,
                            't1x'                        => 4,
                            't2x'                        => 2,
                            'gitw'                       => 80,
                        ),
                    )),
                    'classList' => new ImporterStub(array(
                        array(
                            'wd'                         => 1,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 2,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 'R',
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'I',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                    )),
                    'commCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ),
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ),
                        array(
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ),
                    )),
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'Incoming T1',
                            'quarterStartApproved'       => 2,
                        ),
                        array(
                            'type'                       => 'Future T1',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Incoming T2',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Future T2',
                            'quarterStartApproved'       => 0,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_CAP_ACTUAL_INCORRECT', 12, 15),
                ),
                false,
            ),
            // BFT - Incorrect CPC
            array(
                $reportingDate,
                array(
                    'centerStats' => new ImporterStub(array(
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-05',
                        ),
                        array(
                            'type'                       => 'promise',
                            'reportingDate'              => '2015-06-12',
                        ),
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-12',
                            'cap'                        => 15,
                            'cpc'                        => 10,
                            't1x'                        => 4,
                            't2x'                        => 2,
                            'gitw'                       => 80,
                        ),
                    )),
                    'classList' => new ImporterStub(array(
                        array(
                            'wd'                         => 1,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 2,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 'R',
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'I',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                    )),
                    'commCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ),
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ),
                        array(
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ),
                    )),
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'Incoming T1',
                            'quarterStartApproved'       => 2,
                        ),
                        array(
                            'type'                       => 'Future T1',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Incoming T2',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Future T2',
                            'quarterStartApproved'       => 0,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_CPC_ACTUAL_INCORRECT', 10, 8),
                ),
                false,
            ),
            // BFT - Incorrect T1x
            array(
                $reportingDate,
                array(
                    'centerStats' => new ImporterStub(array(
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-05',
                        ),
                        array(
                            'type'                       => 'promise',
                            'reportingDate'              => '2015-06-12',
                        ),
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-12',
                            'cap'                        => 15,
                            'cpc'                        => 8,
                            't1x'                        => 6,
                            't2x'                        => 2,
                            'gitw'                       => 80,
                        ),
                    )),
                    'classList' => new ImporterStub(array(
                        array(
                            'wd'                         => 1,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 2,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 'R',
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'I',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                    )),
                    'commCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ),
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ),
                        array(
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ),
                    )),
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'Incoming T1',
                            'quarterStartApproved'       => 2,
                        ),
                        array(
                            'type'                       => 'Future T1',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Incoming T2',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Future T2',
                            'quarterStartApproved'       => 0,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_T1X_ACTUAL_INCORRECT', 6, 4),
                ),
                true,
            ),
            // BFT - Incorrect T2x
            array(
                $reportingDate,
                array(
                    'centerStats' => new ImporterStub(array(
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-05',
                        ),
                        array(
                            'type'                       => 'promise',
                            'reportingDate'              => '2015-06-12',
                        ),
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-12',
                            'cap'                        => 15,
                            'cpc'                        => 8,
                            't1x'                        => 4,
                            't2x'                        => 5,
                            'gitw'                       => 80,
                        ),
                    )),
                    'classList' => new ImporterStub(array(
                        array(
                            'wd'                         => 1,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 2,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 'R',
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'I',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                    )),
                    'commCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ),
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ),
                        array(
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ),
                    )),
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'Incoming T1',
                            'quarterStartApproved'       => 2,
                        ),
                        array(
                            'type'                       => 'Future T1',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Incoming T2',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Future T2',
                            'quarterStartApproved'       => 0,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_T2X_ACTUAL_INCORRECT', 5, 2),
                ),
                true,
            ),
            // BFT - Incorrect GITW
            array(
                $reportingDate,
                array(
                    'centerStats' => new ImporterStub(array(
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-05',
                        ),
                        array(
                            'type'                       => 'promise',
                            'reportingDate'              => '2015-06-12',
                        ),
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-12',
                            'cap'                        => 15,
                            'cpc'                        => 8,
                            't1x'                        => 4,
                            't2x'                        => 2,
                            'gitw'                       => 85,
                        ),
                    )),
                    'classList' => new ImporterStub(array(
                        array(
                            'wd'                         => 1,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 2,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 'R',
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'I',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                    )),
                    'commCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ),
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ),
                        array(
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ),
                    )),
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'Incoming T1',
                            'quarterStartApproved'       => 2,
                        ),
                        array(
                            'type'                       => 'Future T1',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Incoming T2',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Future T2',
                            'quarterStartApproved'       => 0,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_GITW_ACTUAL_INCORRECT', 85, 80),
                ),
                false,
            ),
            // BFT - Incorrect CAP
            array(
                $reportingDate,
                array(
                    'centerStats' => new ImporterStub(array(
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-05',
                        ),
                        array(
                            'type'                       => 'promise',
                            'reportingDate'              => '2015-06-12',
                        ),
                        array(
                            'type'                       => 'actual',
                            'reportingDate'              => '2015-06-12',
                            'cap'                        => 12,
                            'cpc'                        => 10,
                            't1x'                        => 6,
                            't2x'                        => 5,
                            'gitw'                       => 85,
                        ),
                    )),
                    'classList' => new ImporterStub(array(
                        array(
                            'wd'                         => 1,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 2,
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => 'R',
                            'xferOut'                    => null,
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'I',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                        array(
                            'wd'                         => null,
                            'wbo'                        => null,
                            'xferOut'                    => null,
                            'gitw'                       => 'E',
                        ),
                    )),
                    'commCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ),
                        array(
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ),
                        array(
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ),
                    )),
                    'tmlpRegistration' => new ImporterStub(array(
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 1,
                            'incomingTeamYear'           => 1,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => null,
                            'incomingTeamYear'           => 2,
                        ),
                        array(
                            'appr'                       => 2,
                            'incomingTeamYear'           => 2,
                        ),
                    )),
                    'tmlpCourseInfo' => new ImporterStub(array(
                        array(
                            'type'                       => 'Incoming T1',
                            'quarterStartApproved'       => 2,
                        ),
                        array(
                            'type'                       => 'Future T1',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Incoming T2',
                            'quarterStartApproved'       => 1,
                        ),
                        array(
                            'type'                       => 'Future T2',
                            'quarterStartApproved'       => 0,
                        ),
                    )),
                ),
                array(
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_CAP_ACTUAL_INCORRECT', 12, 15),
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_CPC_ACTUAL_INCORRECT', 10, 8),
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_T1X_ACTUAL_INCORRECT', 6, 4),
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_T2X_ACTUAL_INCORRECT', 5, 2),
                    array(ImportDocument::TAB_WEEKLY_STATS, 'IMPORTDOC_GITW_ACTUAL_INCORRECT', 85, 80),
                ),
                false,
            ),
        );
    }

    protected function getValidatorMock($methods = array())
    {
        return $this->getMockBuilder('stdClass')
                    ->setMethods($methods)
                    ->getMock();
    }

    protected function getObjectMock($methods = array(), $unused = array())
    {
        $constructorArgs = array('');
        return $this->getMockBuilder($this->testClass)
                    ->setMethods($methods)
                    ->setConstructorArgs($constructorArgs)
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
