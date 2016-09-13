<?php
namespace TmlpStats\Tests\Unit\Validate\Relationships;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Domain;
use TmlpStats\Tests\Unit\Traits;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Relationships\ApiCenterGamesValidator;
use stdClass;

class ApiCenterGamesValidatorTest extends ApiValidatorTestAbstract
{
    use Traits\MocksModel;

    protected $testClass = ApiCenterGamesValidator::class;

    protected $defaultObjectMethods = ['getQuarterStartingApprovedApplications'];

    protected $courseData = [];
    protected $scoreboardData = [];
    protected $teamApplicationData = [];
    protected $teamMemberData = [];

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'error',
        'reference' => [
            'id' => '2016-09-02',
            'type' => 'scoreboard',
            'promiseType' => 'actual',
        ],
    ];

    public function setUp()
    {
        parent::setUp();

        // Individual item templates
        $courseTemplate = [
            'startDate' => null,
            'location' => null,
            'type' => null,
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
        ];

        $applicationTemplate = [
            'firstName' => '',
            'lastName' => '',
            'email' => 'unit_test@tmlpstats.com',
            'center' => 1234,
            'incomingQuarter' => 1234,
            'teamYear' => null,
            'phone' => null,
            'isReviewer' => false,
            'tmlpRegistration' => 1234,
            'regDate' => null,
            'appOutDate' => null,
            'appInDate' => null,
            'apprDate' => null,
            'wdDate' => null,
            'withdrawCode' => null,
            'committedTeamMember' => null,
            'comment' => null,
            'travel' => null,
            'room' => null,
        ];

        $teamMemberTemplate = [
            'firstName' => '',
            'lastName' => '',
            'teamYear' => '1',
            'atWeekend' => true,
            'isReviewer' => false,
            'xferOut' => null,
            'xferIn' => null,
            'ctw' => null,
            'rereg' => null,
            'excep' => null,
            'travel' => false,
            'room' => false,
            'gitw' => false,
            'tdo' => false,
            'withdrawCode' => null,
            'comment' => null,
        ];

        // Data arrays to validate
        $this->courseData = [
            array_merge($courseTemplate, [
                'startDate' => '2016-08-27',
                'type' => 'CAP',
                'quarterStartTer' => 35,
                'quarterStartStandardStarts' => 34,
                'quarterStartXfer' => 0,
                'currentTer' => 40,
                'currentStandardStarts' => 38,
                'currentXfer' => 1,
                'completedStandardStarts' => 38,
                'potentials' => 30,
                'registrations' => 25,
            ]),
            array_merge($courseTemplate, [
                'startDate' => '2016-09-17',
                'type' => 'CAP',
                'quarterStartTer' => 20,
                'quarterStartStandardStarts' => 18,
                'quarterStartXfer' => 0,
                'currentTer' => 24,
                'currentStandardStarts' => 22,
                'currentXfer' => 1,
            ]),
            array_merge($courseTemplate, [
                'startDate' => '2016-11-12',
                'type' => 'CPC',
                'quarterStartTer' => 0,
                'quarterStartStandardStarts' => 0,
                'quarterStartXfer' => 0,
                'currentTer' => 3,
                'currentStandardStarts' => 3,
                'currentXfer' => 0,
            ]),
        ];

        $this->scoreboardData = [
            [
                'week' => '2016-08-26',
                'games' => [
                    'cap' => [
                        'promise' => 5,
                        'actual' => 6,
                    ],
                    'cpc' => [
                        'promise' => 2,
                        'actual' => 0,
                    ],
                    't1x' => [
                        'promise' => 1,
                        'actual' => 0,
                    ],
                    't2x' => [
                        'promise' => 1,
                        'actual' => 1,
                    ],
                    'gitw' => [
                        'promise' => 85,
                        'actual' => 79,
                    ],
                    'lf' => [
                        'promise' => 4,
                        'actual' => 3,
                    ],
                ],
            ],
            [
                'week' => '2016-09-02',
                'games' => [
                    'cap' => [
                        'promise' => 9,
                        'actual' => 8,
                    ],
                    'cpc' => [
                        'promise' => 5,
                        'actual' => 3,
                    ],
                    't1x' => [
                        'promise' => 2,
                        'actual' => 1,
                    ],
                    't2x' => [
                        'promise' => 2,
                        'actual' => 1,
                    ],
                    'gitw' => [
                        'promise' => 85,
                        'actual' => 75,
                    ],
                    'lf' => [
                        'promise' => 6,
                        'actual' => 5,
                    ],
                ],
            ],
            [
                'week' => '2016-09-09',
                'games' => [
                    'cap' => [
                        'promise' => 12,
                        'actual' => 12,
                    ],
                    'cpc' => [
                        'promise' => 6,
                        'actual' => 4,
                    ],
                    't1x' => [
                        'promise' => 2,
                        'actual' => 2,
                    ],
                    't2x' => [
                        'promise' => 2,
                        'actual' => 1,
                    ],
                    'gitw' => [
                        'promise' => 85,
                        'actual' => 88,
                    ],
                    'lf' => [
                        'promise' => 8,
                        'actual' => 6,
                    ],
                ],
            ],
        ];

        $this->teamApplicationData = [
            array_merge($applicationTemplate, [
                'firstName' => 'Keith',
                'lastName' => 'Stone',
                'teamYear' => 1,
                'regDate' => Carbon::parse('2016-08-12'),
                'appOutDate' => Carbon::parse('2016-08-13'),
                'appInDate' => Carbon::parse('2016-08-14'),
                'apprDate' => Carbon::parse('2016-08-15'),
            ]),
            array_merge($applicationTemplate, [
                'firstName' => 'Ann',
                'lastName' => 'Stone',
                'teamYear' => 1,
                'regDate' => Carbon::parse('2016-08-22'),
                'appOutDate' => Carbon::parse('2016-08-23'),
                'appInDate' => Carbon::parse('2016-08-24'),
                'apprDate' => Carbon::parse('2016-08-25'),
            ]),
            array_merge($applicationTemplate, [
                'firstName' => 'Bob',
                'lastName' => 'Stone',
                'teamYear' => 1,
                'regDate' => Carbon::parse('2016-09-01'),
            ]),
            array_merge($applicationTemplate, [
                'firstName' => 'Jenn',
                'lastName' => 'Stone',
                'teamYear' => 2,
                'regDate' => Carbon::parse('2016-08-21'),
                'appOutDate' => Carbon::parse('2016-08-23'),
                'appInDate' => Carbon::parse('2016-08-24'),
                'apprDate' => Carbon::parse('2016-08-25'),
            ]),
        ];

        $this->teamMemberData = [
            array_merge($teamMemberTemplate, [
                'firstName' => 'Keith',
                'lastName' => 'Stone',
                'gitw' => false,
            ]),
            array_merge($teamMemberTemplate, [
                'firstName' => 'Ann',
                'lastName' => 'Stone',
                'gitw' => true,
            ]),
            array_merge($teamMemberTemplate, [
                'firstName' => 'Bob',
                'lastName' => 'Stone',
                'gitw' => true,
            ]),
            array_merge($teamMemberTemplate, [
                'firstName' => 'Jenn',
                'lastName' => 'Stone',
                'gitw' => true,
            ]),
            array_merge($teamMemberTemplate, [
                'firstName' => 'Krista',
                'lastName' => 'Stone',
                'gitw' => false,
                'xferOut' => true,
            ]),
            array_merge($teamMemberTemplate, [
                'firstName' => 'Nick',
                'lastName' => 'Stone',
                'gitw' => false,
                'withdrawCode' => 1,
            ]),
        ];

        // Turn data arrays into the appropriate object types
        $this->data = [
            'course' => [],
            'teamApplication' => [],
            'teamMember' => [],
        ];

        $this->qStartApps = [];

        foreach ($this->courseData as $course) {
            $this->data['course'][] = Domain\Course::fromArray($course);
        }

        foreach ($this->teamMemberData as $teamMember) {
            $this->data['teamMember'][] = Domain\TeamMember::fromArray($teamMember);
        }

        foreach ($this->teamApplicationData as $app) {
            $appDomain = Domain\TeamApplication::fromArray($app);
            $this->data['teamApplication'][] = $appDomain;

            // Create a list of quarter starting approved applications for function mock
            if ($appDomain->apprDate && $appDomain->apprDate->lte(Carbon::parse('2016-08-19'))) {
                $appModel = new stdClass();
                $registration = new stdClass;
                $registration->teamYear = $app['teamYear'];

                $appModel->registration = $registration;
                foreach ($app as $field => $value) {
                    $appModel->$field = $value;
                }

                $this->qStartApps[] = $appModel;
            }
        }
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun($reportedActuals, $expectedMessages, $expectedResult)
    {
        foreach ($this->scoreboardData as $scoreboard) {
            if (Carbon::parse($scoreboard['week'])->eq($this->reportingDate)) {
                foreach ($reportedActuals as $game => $value) {
                    $scoreboard['games'][$game]['actual'] = $value;
                }
            }

            $this->data['scoreboard'][] = Domain\Scoreboard::fromArray($scoreboard);
        }

        $validator = $this->getObjectMock();
        $validator->expects($this->once())
            ->method('getQuarterStartingApprovedApplications')
            ->willReturn($this->qStartApps);

        $result = $validator->run($this->data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerRun()
    {
        return [
            [
                [],
                [],
                true,
            ],
            [
                [
                    'cap' => 7,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_CAP_ACTUAL_INCORRECT',
                        'reference.game' => 'cap',
                    ]),
                ],
                false,
            ],
            [
                [
                    'cpc' => 2,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_CPC_ACTUAL_INCORRECT',
                        'reference.game' => 'cpc',
                    ]),
                ],
                false,
            ],
            [
                [
                    't1x' => 2,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_T1X_ACTUAL_INCORRECT',
                        'reference.game' => 't1x',
                    ]),
                ],
                false,
            ],
            [
                [
                    't2x' => 2,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_T2X_ACTUAL_INCORRECT',
                        'reference.game' => 't2x',
                    ]),
                ],
                false,
            ],
            [
                [
                    'gitw' => 90,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_GITW_ACTUAL_INCORRECT',
                        'reference.game' => 'gitw',
                    ]),
                ],
                false,
            ],
            [
                [
                    'cap' => 9,
                    'cpc' => 4,
                    't1x' => 0,
                    't2x' => 0,
                    'gitw' => 60,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_CAP_ACTUAL_INCORRECT',
                        'reference.game' => 'cap',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_CPC_ACTUAL_INCORRECT',
                        'reference.game' => 'cpc',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_T1X_ACTUAL_INCORRECT',
                        'reference.game' => 't1x',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_T2X_ACTUAL_INCORRECT',
                        'reference.game' => 't2x',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CENTERGAME_GITW_ACTUAL_INCORRECT',
                        'reference.game' => 'gitw',
                    ]),
                ],
                false,
            ],
        ];
    }
}
