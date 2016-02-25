<?php
namespace TmlpStats\Tests\Validate\Relationships;

use Carbon\Carbon;
use Log;
use stdClass;
use TmlpStats\Center;
use TmlpStats\Tests\Traits\MocksQuarters;
use TmlpStats\Tests\Validate\ValidatorTestAbstract;
use TmlpStats\Validate\Relationships\TeamExpansionValidator;

class TeamExpansionValidatorTest extends ValidatorTestAbstract
{
    use MocksQuarters;

    protected $instantiateApp = true;
    protected $testClass = TeamExpansionValidator::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($statsReport, $data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(['addMessage'], [$statsReport]);
        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                if (count($messages[$i]) == 4) {
                    $validator->expects($this->at($i))
                        ->method('addMessage')
                        ->with($messages[$i][0], $messages[$i][1], $messages[$i][2], $messages[$i][3]);
                } else if (count($messages[$i]) == 3) {
                    $validator->expects($this->at($i))
                        ->method('addMessage')
                        ->with($messages[$i][0], $messages[$i][1], $messages[$i][2]);
                } else {
                    $this->assertTrue(false, 'Invalid message count provided to test');
                }
            }
        } else {
            $validator->expects($this->never())
                ->method('addMessage');
        }

        Log::shouldReceive('error');

        $result = $validator->run($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        $statsReport = new stdClass;
        $statsReport->center = new Center();
        $statsReport->quarter = $this->getQuarterMock([], [
            'startWeekendDate' => Carbon::createFromDate(2015, 5, 29)->startOfDay(),
        ]);

        $statsReport->reportingDate = Carbon::createFromDate(2015, 6, 5)->startOfDay();

        $statsReportDateSecondWeek = clone $statsReport;
        $statsReportDateSecondWeek->reportingDate = Carbon::createFromDate(2015, 6, 12)->startOfDay();

        return [
            // All null (nothing blows up)
            [
                $statsReport,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => null,
                            'incomingWeekend' => null,
                            'incomingTeamYear' => null,
                            'appr' => null,
                            'apprDate' => null,
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => null,
                            'quarterStartRegistered' => null,
                            'quarterStartApproved' => null,
                        ],
                    ],
                ],
                [],
                true,
            ],
            // RegDate formats
            [
                $statsReport,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => null,
                            'apprDate' => null,
                        ],
                        [
                            'regDate' => '05/22/2015',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => null,
                            'apprDate' => null,
                        ],
                        [
                            'regDate' => 'asdf',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => null,
                            'apprDate' => null,
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 0,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [],
                true,
            ],
            // ApprDate formats
            [
                $statsReport,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '05/23/2015',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => 'asdf',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [],
                true,
            ],
            // Date comparisons
            [
                $statsReport,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [],
                true,
            ],
            // All the types
            [
                $statsReport,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                    ],
                ],
                [],
                true,
            ],

            // First week, qStart registered incorrect
            [
                $statsReport,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved' => 1,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Incoming T1', 1, 2],
                    ['IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Future T1', 1, 2],
                    ['IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Future T2', 1, 2],
                ],
                false,
            ],
            // First week, qStart approved incorrect
            [
                $statsReport,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 2,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Incoming T1', 2, 1],
                    ['IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Incoming T2', 2, 1],
                    ['IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Future T2', 2, 1],
                ],
                false,
            ],
            // First week, both qStart incorrect
            [
                $statsReport,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved' => 2,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Future T1', 3, 2],
                    ['IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Future T1', 2, 1],
                    ['IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Incoming T2', 3, 2],
                    ['IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Incoming T2', 2, 1],
                    ['IMPORTDOC_QSTART_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 'Future T2', 3, 2],
                    ['IMPORTDOC_QSTART_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 'Future T2', 2, 1],
                ],
                false,
            ],

            // Current and Future total are same as quarter start
            [
                $statsReportDateSecondWeek,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                    ],
                ],
                [],
                true,
            ],
            // Current and Future total are redistributed from quarter start
            [
                $statsReportDateSecondWeek,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 4,
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 0,
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [],
                true,
            ],
            // Registered totals don't match
            [
                $statsReportDateSecondWeek,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 3,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 1,
                            'quarterStartApproved' => 1,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_QSTART_T1_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 5, 4],
                    ['IMPORTDOC_QSTART_T2_TER_DOESNT_MATCH_REG_BEFORE_WEEKEND', 3, 4],
                ],
                true,
            ],
            // Approved totals don't match
            [
                $statsReportDateSecondWeek,
                [
                    'tmlpRegistration' => [
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 1,
                            'appr' => 1,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-04-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-04-23',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-05-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-05-30',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'current',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                        [
                            'regDate' => '2015-06-22',
                            'incomingWeekend' => 'future',
                            'incomingTeamYear' => 2,
                            'appr' => 2,
                            'apprDate' => '2015-06-23',
                        ],
                    ],
                    'tmlpCourseInfo' => [
                        [
                            'type' => 'Incoming T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type' => 'Future T1',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 0,
                        ],
                        [
                            'type' => 'Incoming T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 3,
                        ],
                        [
                            'type' => 'Future T2',
                            'quarterStartRegistered' => 2,
                            'quarterStartApproved' => 1,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_QSTART_T1_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 1, 2],
                    ['IMPORTDOC_QSTART_T2_APPROVED_DOESNT_MATCH_APPR_BEFORE_WEEKEND', 4, 2],
                ],
                true,
            ],
        ];
    }

}
