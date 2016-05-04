<?php
namespace TmlpStats\Tests\Unit\Validate\Relationships;

use Carbon\Carbon;
use TmlpStats\Tests\Unit\Validate\ValidatorTestAbstract;
use TmlpStats\Validate\Relationships\CenterGamesValidator;

use stdClass;

class CenterGamesValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = CenterGamesValidator::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($statsReport, $data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(['addMessage'], [$statsReport]);
        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                if (count($messages[$i]) == 3) {
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

        $result = $validator->run($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        $statsReport = new stdClass;
        $statsReport->reportingDate = Carbon::createFromDate(2015, 6, 12)->startOfDay();

        return [
            // Empty importers (doesn't blow up)
            [
                $statsReport,
                [
                    'centerStats'      => [],
                    'classList'        => [],
                    'commCourseInfo'   => [],
                    'tmlpRegistration' => [],
                    'tmlpCourseInfo'   => [],
                ],
                [],
                true,
            ],
            // All null (doesn't blow up)
            [
                $statsReport,
                [
                    'centerStats'      => [
                        [
                            'type'          => null,
                            'reportingDate' => null,
                            'cap'           => null,
                            'cpc'           => null,
                            't1x'           => null,
                            't2x'           => null,
                            'gitw'          => null,
                        ],
                    ],
                    'classList'        => [
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => null,
                        ],
                    ],
                    'commCourseInfo'   => [
                        [
                            'type'                       => null,
                            'currentStandardStarts'      => null,
                            'quarterStartStandardStarts' => null,
                        ],
                    ],
                    'tmlpRegistration' => [
                        [
                            'appr'             => null,
                            'incomingTeamYear' => null,
                        ],
                    ],
                    'tmlpCourseInfo'   => [
                        [
                            'type'                 => null,
                            'quarterStartApproved' => null,
                        ],
                    ],
                ],
                [],
                true,
            ],

            // BFT - success
            [
                $statsReport,
                [
                    'centerStats'      => [
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-05',
                        ],
                        [
                            'type'          => 'promise',
                            'reportingDate' => '2015-06-12',
                        ],
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-12',
                            'cap'           => 15,
                            'cpc'           => 8,
                            't1x'           => 4,
                            't2x'           => 2,
                            'gitw'          => 80,
                        ],
                    ],
                    'classList'        => [
                        [
                            'wd'      => 1,
                            'wbo'     => null,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 2,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 'R',
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'I',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                    ],
                    'commCourseInfo'   => [
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ],
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ],
                        [
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ],
                    ],
                    'tmlpRegistration' => [
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                    ],
                    'tmlpCourseInfo'   => [
                        [
                            'type'                 => 'Incoming T1',
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type'                 => 'Future T1',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Incoming T2',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Future T2',
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [],
                true,
            ],
            // BFT - Incorrect CAP
            [
                $statsReport,
                [
                    'centerStats'      => [
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-05',
                        ],
                        [
                            'type'          => 'promise',
                            'reportingDate' => '2015-06-12',
                        ],
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-12',
                            'cap'           => 12,
                            'cpc'           => 8,
                            't1x'           => 4,
                            't2x'           => 2,
                            'gitw'          => 80,
                        ],
                    ],
                    'classList'        => [
                        [
                            'wd'      => 1,
                            'wbo'     => null,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 2,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 'R',
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'I',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                    ],
                    'commCourseInfo'   => [
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ],
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ],
                        [
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ],
                    ],
                    'tmlpRegistration' => [
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                    ],
                    'tmlpCourseInfo'   => [
                        [
                            'type'                 => 'Incoming T1',
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type'                 => 'Future T1',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Incoming T2',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Future T2',
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_CAP_ACTUAL_INCORRECT', 12, 15],
                ],
                false,
            ],
            // BFT - Incorrect CPC
            [
                $statsReport,
                [
                    'centerStats'      => [
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-05',
                        ],
                        [
                            'type'          => 'promise',
                            'reportingDate' => '2015-06-12',
                        ],
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-12',
                            'cap'           => 15,
                            'cpc'           => 10,
                            't1x'           => 4,
                            't2x'           => 2,
                            'gitw'          => 80,
                        ],
                    ],
                    'classList'        => [
                        [
                            'wd'      => 1,
                            'wbo'     => null,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 2,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 'R',
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'I',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                    ],
                    'commCourseInfo'   => [
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ],
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ],
                        [
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ],
                    ],
                    'tmlpRegistration' => [
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                    ],
                    'tmlpCourseInfo'   => [
                        [
                            'type'                 => 'Incoming T1',
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type'                 => 'Future T1',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Incoming T2',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Future T2',
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_CPC_ACTUAL_INCORRECT', 10, 8],
                ],
                false,
            ],
            // BFT - Incorrect T1x
            [
                $statsReport,
                [
                    'centerStats'      => [
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-05',
                        ],
                        [
                            'type'          => 'promise',
                            'reportingDate' => '2015-06-12',
                        ],
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-12',
                            'cap'           => 15,
                            'cpc'           => 8,
                            't1x'           => 6,
                            't2x'           => 2,
                            'gitw'          => 80,
                        ],
                    ],
                    'classList'        => [
                        [
                            'wd'      => 1,
                            'wbo'     => null,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 2,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 'R',
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'I',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                    ],
                    'commCourseInfo'   => [
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ],
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ],
                        [
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ],
                    ],
                    'tmlpRegistration' => [
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                    ],
                    'tmlpCourseInfo'   => [
                        [
                            'type'                 => 'Incoming T1',
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type'                 => 'Future T1',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Incoming T2',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Future T2',
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_T1X_ACTUAL_INCORRECT', 6, 4],
                ],
                false,
            ],
            // BFT - Incorrect T2x
            [
                $statsReport,
                [
                    'centerStats'      => [
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-05',
                        ],
                        [
                            'type'          => 'promise',
                            'reportingDate' => '2015-06-12',
                        ],
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-12',
                            'cap'           => 15,
                            'cpc'           => 8,
                            't1x'           => 4,
                            't2x'           => 5,
                            'gitw'          => 80,
                        ],
                    ],
                    'classList'        => [
                        [
                            'wd'      => 1,
                            'wbo'     => null,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 2,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 'R',
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'I',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                    ],
                    'commCourseInfo'   => [
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ],
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ],
                        [
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ],
                    ],
                    'tmlpRegistration' => [
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                    ],
                    'tmlpCourseInfo'   => [
                        [
                            'type'                 => 'Incoming T1',
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type'                 => 'Future T1',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Incoming T2',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Future T2',
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_T2X_ACTUAL_INCORRECT', 5, 2],
                ],
                false,
            ],
            // BFT - Incorrect GITW
            [
                $statsReport,
                [
                    'centerStats'      => [
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-05',
                        ],
                        [
                            'type'          => 'promise',
                            'reportingDate' => '2015-06-12',
                        ],
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-12',
                            'cap'           => 15,
                            'cpc'           => 8,
                            't1x'           => 4,
                            't2x'           => 2,
                            'gitw'          => 85,
                        ],
                    ],
                    'classList'        => [
                        [
                            'wd'      => 1,
                            'wbo'     => null,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 2,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 'R',
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'I',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                    ],
                    'commCourseInfo'   => [
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ],
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ],
                        [
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ],
                    ],
                    'tmlpRegistration' => [
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                    ],
                    'tmlpCourseInfo'   => [
                        [
                            'type'                 => 'Incoming T1',
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type'                 => 'Future T1',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Incoming T2',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Future T2',
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_GITW_ACTUAL_INCORRECT', 85, 80],
                ],
                false,
            ],
            // BFT - Incorrect CAP
            [
                $statsReport,
                [
                    'centerStats'      => [
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-05',
                        ],
                        [
                            'type'          => 'promise',
                            'reportingDate' => '2015-06-12',
                        ],
                        [
                            'type'          => 'actual',
                            'reportingDate' => '2015-06-12',
                            'cap'           => 12,
                            'cpc'           => 10,
                            't1x'           => 6,
                            't2x'           => 5,
                            'gitw'          => 85,
                        ],
                    ],
                    'classList'        => [
                        [
                            'wd'      => 1,
                            'wbo'     => null,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 2,
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => 'R',
                            'xferOut' => null,
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'I',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                        [
                            'wd'      => null,
                            'wbo'     => null,
                            'xferOut' => null,
                            'gitw'    => 'E',
                        ],
                    ],
                    'commCourseInfo'   => [
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 20,
                            'quarterStartStandardStarts' => 8,
                        ],
                        [
                            'type'                       => 'CAP',
                            'currentStandardStarts'      => 5,
                            'quarterStartStandardStarts' => 2,
                        ],
                        [
                            'type'                       => 'CPC',
                            'currentStandardStarts'      => 8,
                            'quarterStartStandardStarts' => 0,
                        ],
                    ],
                    'tmlpRegistration' => [
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 1,
                            'incomingTeamYear' => 1,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => null,
                            'incomingTeamYear' => 2,
                        ],
                        [
                            'appr'             => 2,
                            'incomingTeamYear' => 2,
                        ],
                    ],
                    'tmlpCourseInfo'   => [
                        [
                            'type'                 => 'Incoming T1',
                            'quarterStartApproved' => 2,
                        ],
                        [
                            'type'                 => 'Future T1',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Incoming T2',
                            'quarterStartApproved' => 1,
                        ],
                        [
                            'type'                 => 'Future T2',
                            'quarterStartApproved' => 0,
                        ],
                    ],
                ],
                [
                    ['IMPORTDOC_CAP_ACTUAL_INCORRECT', 12, 15],
                    ['IMPORTDOC_CPC_ACTUAL_INCORRECT', 10, 8],
                    ['IMPORTDOC_T1X_ACTUAL_INCORRECT', 6, 4],
                    ['IMPORTDOC_T2X_ACTUAL_INCORRECT', 5, 2],
                    ['IMPORTDOC_GITW_ACTUAL_INCORRECT', 85, 80],
                ],
                false,
            ],
        ];
    }
}
