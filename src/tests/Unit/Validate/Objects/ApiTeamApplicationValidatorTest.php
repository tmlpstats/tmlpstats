<?php
namespace TmlpStats\Tests\Unit\Validate\Objects;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Domain;
use TmlpStats\Tests\Unit\Traits;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Objects\ApiTeamApplicationValidator;

class ApiTeamApplicationValidatorTest extends ApiValidatorTestAbstract
{
    use Traits\MocksSettings, Traits\MocksQuarters, Traits\MocksModel;

    protected $instantiateApp = true;
    protected $testClass = ApiTeamApplicationValidator::class;

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'error',
        'reference' => [
            'id' => null,
            'type' => 'teamApplication',
        ],
    ];

    public function setUp()
    {
        parent::setUp();

        // When using Settings, we need center to be null to avoid db lookups
        $this->statsReport->center = null;

        $this->setSetting('travelDueByDate', 'classroom2Date');

        $this->dataTemplate = [
            'firstName' => 'Keith',
            'lastName' => 'Stone',
            'email' => 'unit_test@tmlpstats.com',
            'center' => 1234,
            'teamYear' => 1,
            'regDate' => Carbon::parse('2016-08-22'),
            'isReviewer' => false,
            'phone' => '555-555-5555',
            'tmlpRegistration' => 1234,
            'appOutDate' => Carbon::parse('2016-08-23'),
            'appInDate' => Carbon::parse('2016-08-24'),
            'apprDate' => Carbon::parse('2016-08-25'),
            'wdDate' => null,
            'withdrawCode' => null,
            'committedTeamMember' => 1234,
            'incomingQuarter' => 1234,
            'comment' => 'asdf qwerty',
            'travel' => true,
            'room' => true,
        ];
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamApplication($data);

        $validator = $this->getObjectMock(['isStartingNextQuarter', 'isTimeToCheckTravel']);

        $validator->expects($this->any())
            ->method('isStartingNextQuarter')
            ->willReturn(true);

        $validator->expects($this->any())
            ->method('isTimeToCheckTravel')
            ->willReturn(false);

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
                    'firstName' => null,
                    'lastName' => null,
                    'email' => null,
                    'center' => null,
                    'teamYear' => null,
                    'regDate' => null,
                    'isReviewer' => null,
                    'phone' => null,
                    'tmlpRegistration' => null,
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'committedTeamMember' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => null,
                    'comment' => null,
                    'travel' => null,
                    'room' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'firstName',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'lastName',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'teamYear',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'regDate',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'incomingQuarterId',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_NO_COMMITTED_TEAM_MEMBER',
                        'reference.field' => 'committedTeamMemberId',
                    ]),
                ],
                false,
            ],
            // Test Valid (Variable set 1)
            [
                [
                ],
                [],
                true,
            ],
            // Test Valid (Variable set 2)
            [
                [
                    'wdDate' => Carbon::parse('2016-08-26'),
                    'withdrawCode' => 1234,
                    'teamYear' => 2,
                    'isReviewer' => true,
                    'travel' => false,
                    'room' => false,
                ],
                [],
                true,
            ],

            // Test Invalid First Name
            [
                [
                    'firstName' => '',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'firstName',
                    ]),
                ],
                false,
            ],
            // Test Invalid Last Name
            [
                [
                    'lastName' => '',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'lastName',
                    ]),
                ],
                false,
            ],
            // Test invalid TeamYear
            [
                [
                    'teamYear' => 3,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.field' => 'teamYear',
                    ]),
                ],
                false,
            ],
        ];
    }


    /**
     * @dataProvider providerValidateApprovalProcess
     */
    public function testValidateApprovalProcess($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamApplication($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateApprovalProcess()
    {
        return [
            // Withdraw and no other steps complete
            [
                [
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2016-08-22'),
                    'withdrawCode' => 1234,
                ],
                [],
                true,
            ],
            // Withdraw and all steps complete
            [
                [
                    'appOutDate' => Carbon::parse('2016-08-22'),
                    'appInDate' => Carbon::parse('2016-08-23'),
                    'apprDate' => Carbon::parse('2016-08-27'),
                    'wdDate' => Carbon::parse('2016-08-28'),
                    'withdrawCode' => 1234,
                ],
                [],
                true,
            ],
            // Withdraw and missing wd
            [
                [
                    'wdDate' => Carbon::parse('2016-08-28'),
                    'withdrawCode' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_WD_CODE_MISSING',
                        'reference.field' => 'withdrawCodeId',
                    ]),
                ],
                false,
            ],
            // Withdraw and missing date
            [
                [
                    'wdDate' => null,
                    'withdrawCode' => 1234,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_WD_DATE_MISSING',
                        'reference.field' => 'wdDate',
                    ]),
                ],
                false,
            ],

            // Approved
            [
                [
                    'appOutDate' => Carbon::parse('2016-08-22'),
                    'appInDate' => Carbon::parse('2016-08-23'),
                    'apprDate' => Carbon::parse('2016-08-27'),
                ],
                [],
                true,
            ],
            // Approved and missing appInDate
            [
                [
                    'appOutDate' => Carbon::parse('2016-08-22'),
                    'appInDate' => null,
                    'apprDate' => Carbon::parse('2016-08-27'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPIN_DATE_MISSING',
                        'reference.field' => 'appInDate',
                    ]),
                ],
                false,
            ],
            // Approved and missing appOutDate
            [
                [
                    'appOutDate' => null,
                    'appInDate' => Carbon::parse('2016-08-23'),
                    'apprDate' => Carbon::parse('2016-08-27'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPOUT_DATE_MISSING',
                        'reference.field' => 'appOutDate',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPOUT_LATE',
                        'reference.field' => 'appOutDate',
                        'level' => 'warning',
                    ]),
                ],
                false,
            ],

            // App In
            [
                [
                    'appOutDate' => Carbon::parse('2016-08-22'),
                    'appInDate' => Carbon::parse('2016-08-23'),
                    'apprDate' => null,
                ],
                [],
                true,
            ],
            // App In and missing appOutDate
            [
                [
                    'appOutDate' => null,
                    'appInDate' => Carbon::parse('2016-08-23'),
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPOUT_DATE_MISSING',
                        'reference.field' => 'appOutDate',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPOUT_LATE',
                        'reference.field' => 'appOutDate',
                        'level' => 'warning',
                    ]),
                ],
                false,
            ],

            // App Out
            [
                [
                    'appOutDate' => Carbon::parse('2016-08-22'),
                    'appInDate' => null,
                    'apprDate' => null,
                ],
                [],
                true,
            ],

            // No approval steps complete
            [
                [
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPOUT_LATE',
                        'reference.field' => 'appOutDate',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // Missing committed team member
            [
                [
                    'committedTeamMember' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_NO_COMMITTED_TEAM_MEMBER',
                        'reference.field' => 'committedTeamMemberId',
                    ]),
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateDates
     */
    public function testValidateDates($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamApplication($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateDates()
    {
        return [
            // Withdraw date OK
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2016-08-25'),
                    'withdrawCode' => 1234,
                ],
                [],
                true,
            ],
            // Withdraw and wdDate before regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2016-08-21'),
                    'withdrawCode' => 1234,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_WD_DATE_BEFORE_REG_DATE',
                        'reference.field' => 'wdDate',
                    ]),
                ],
                false,
            ],
            // Withdraw and approve dates OK
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => Carbon::parse('2016-08-25'),
                    'wdDate' => Carbon::parse('2016-08-26'),
                    'withdrawCode' => 1234,
                ],
                [],
                true,
            ],
            // Withdraw and wdDate before apprDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => Carbon::parse('2016-08-25'),
                    'wdDate' => Carbon::parse('2016-08-24'),
                    'withdrawCode' => 1234,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_WD_DATE_BEFORE_APPR_DATE',
                        'reference.field' => 'wdDate',
                    ]),
                ],
                false,
            ],
            // Withdraw and appIn dates OK
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => null,
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2016-08-26'),
                    'withdrawCode' => 1234,
                ],
                [],
                true,
            ],
            // Withdraw and wdDate before appInDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => null,
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2016-08-23'),
                    'withdrawCode' => 1234,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_WD_DATE_BEFORE_APPIN_DATE',
                        'reference.field' => 'wdDate',
                    ]),
                ],
                false,
            ],
            // Withdraw and appOut dates OK
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2016-08-26'),
                    'withdrawCode' => 1234,
                ],
                [],
                true,
            ],
            // Withdraw and wdDate before appOutDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2016-08-22'),
                    'withdrawCode' => 1234,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_WD_DATE_BEFORE_APPOUT_DATE',
                        'reference.field' => 'wdDate',
                    ]),
                ],
                false,
            ],

            // Approved date OK
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => Carbon::parse('2016-08-25'),
                ],
                [],
                true,
            ],
            // Approved and apprDate before regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => Carbon::parse('2016-08-21'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPR_DATE_BEFORE_REG_DATE',
                        'reference.field' => 'apprDate',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPR_DATE_BEFORE_APPIN_DATE',
                        'reference.field' => 'apprDate',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPR_DATE_BEFORE_APPOUT_DATE',
                        'reference.field' => 'apprDate',
                    ]),
                ],
                false,
            ],
            // Approved and apprDate before appInDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => Carbon::parse('2016-08-23'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPR_DATE_BEFORE_APPIN_DATE',
                        'reference.field' => 'apprDate',
                    ]),
                ],
                false,
            ],
            // Approved and apprDate before appOutDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => Carbon::parse('2016-08-22'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPR_DATE_BEFORE_APPIN_DATE',
                        'reference.field' => 'apprDate',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPR_DATE_BEFORE_APPOUT_DATE',
                        'reference.field' => 'apprDate',
                    ]),
                ],
                false,
            ],

            // AppIn date OK
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => null,
                ],
                [],
                true,
            ],
            // AppIn and appInDate before regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-21'),
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPIN_DATE_BEFORE_REG_DATE',
                        'reference.field' => 'appInDate',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPIN_DATE_BEFORE_APPOUT_DATE',
                        'reference.field' => 'appInDate',
                    ]),
                ],
                false,
            ],
            // AppIn and appInDate before appOutDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-22'),
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPIN_DATE_BEFORE_APPOUT_DATE',
                        'reference.field' => 'appInDate',
                    ]),
                ],
                false,
            ],

            // AppOut date OK
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => null,
                    'apprDate' => null,
                ],
                [],
                true,
            ],
            // AppOut and appOutDate before regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-21'),
                    'appInDate' => null,
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPOUT_DATE_BEFORE_REG_DATE',
                        'reference.field' => 'appOutDate',
                    ]),
                ],
                false,
            ],

            // AppOut within 2 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => null,
                    'apprDate' => null,
                ],
                [],
                true,
            ],
            // AppOut not within 2 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPOUT_LATE',
                        'reference.field' => 'appOutDate',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // AppIn within 14 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => null,
                ],
                [],
                true,
            ],
            // AppIn not within 14 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-17'),
                    'appOutDate' => Carbon::parse('2016-08-18'),
                    'appInDate' => null,
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPIN_LATE',
                        'reference.field' => 'appInDate',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // Appr within 14 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => Carbon::parse('2016-09-02'),
                ],
                [],
                true,
            ],
            // Appr not within 14 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2016-08-17'),
                    'appOutDate' => Carbon::parse('2016-08-18'),
                    'appInDate' => Carbon::parse('2016-08-19'),
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPR_LATE',
                        'reference.field' => 'apprDate',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],

            // RegDate in future
            [
                [
                    'regDate' => Carbon::parse('2016-09-09'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_REG_DATE_IN_FUTURE',
                        'reference.field' => 'regDate',
                    ]),
                ],
                false,
            ],
            // WdDate in future
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => Carbon::parse('2016-08-25'),
                    'wdDate' => Carbon::parse('2016-09-09'),
                    'withdrawCode' => 1234,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_WD_DATE_IN_FUTURE',
                        'reference.field' => 'wdDate',
                    ]),
                ],
                false,
            ],
            // ApprDate in future
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-08-24'),
                    'apprDate' => Carbon::parse('2016-09-09'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPR_DATE_IN_FUTURE',
                        'reference.field' => 'apprDate',
                    ]),
                ],
                false,
            ],
            // AppInDate in future
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-08-23'),
                    'appInDate' => Carbon::parse('2016-09-09'),
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPIN_DATE_IN_FUTURE',
                        'reference.field' => 'appInDate',
                    ]),
                ],
                false,
            ],
            // AppOutDate in future
            [
                [
                    'regDate' => Carbon::parse('2016-08-22'),
                    'appOutDate' => Carbon::parse('2016-09-09'),
                    'appInDate' => null,
                    'apprDate' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPOUT_DATE_IN_FUTURE',
                        'reference.field' => 'appOutDate',
                    ]),
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTravel
     */
    public function testValidateTravel($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamApplication($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTravel()
    {
        return [
            // validateTravel Passes When Before Second Classroom
            [
                [
                    'travel' => null,
                    'room' => null,
                    'comment' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 'next',
                    '__reportingDate' => Carbon::parse('2016-09-02'),
                ],
                [],
                true,
            ],
            // validateTravel Passes When Travel And Room Complete
            [
                [
                    'travel' => true,
                    'room' => true,
                    'comment' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 'next',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [],
                true,
            ],
            // validateTravel Passes When Comments Provided
            [
                [
                    'travel' => null,
                    'room' => null,
                    'comment' => 'Travel and rooming booked by May 4',
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 'next',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_TRAVEL_COMMENT_REVIEW',
                        'reference.field' => 'comment',
                        'level' => 'warning',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_ROOM_COMMENT_REVIEW',
                        'reference.field' => 'comment',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // validateTravel Ignored When Wd Set
            [
                [
                    'travel' => null,
                    'room' => null,
                    'comment' => null,
                    'wdDate' => Carbon::parse('2016-08-26'),
                    'withdrawCode' => 1234,
                    'incomingQuarter' => 'next',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [],
                true,
            ],
            // validateTravel Ignored When Incoming Weekend Equals Future
            [
                [
                    'travel' => null,
                    'room' => null,
                    'comment' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 'future',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [],
                true,
            ],
            // ValidateTravel Fails When Missing Travel
            [
                [
                    'travel' => null,
                    'room' => true,
                    'comment' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 'next',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_TRAVEL_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
            // ValidateTravel Fails When Missing Room
            [
                [
                    'travel' => true,
                    'room' => null,
                    'comment' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 'next',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_ROOM_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateReviewer
     */
    public function testValidateReviewer($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamApplication($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateReviewer()
    {
        return [
            // Team 1 and not a reviewer
            [
                [
                    'teamYear' => 1,
                    'isReviewer' => false,
                ],
                [],
                true,
            ],
            // Team 2 and not a reviewer
            [
                [
                    'teamYear' => 2,
                    'isReviewer' => false,
                ],
                [],
                true,
            ],
            // Team 1 and a reviewer
            [
                [
                    'teamYear' => 1,
                    'isReviewer' => true,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_REVIEWER_TEAM1',
                        'reference.field' => 'isReviewer',
                    ]),
                ],
                false,
            ],
            // Team 2 and not a reviewer
            [
                [
                    'teamYear' => 2,
                    'isReviewer' => true,
                ],
                [],
                true,
            ],
        ];
    }

    //
    // Helpers
    //

    /**
     * @dataProvider providerIsStartingNextQuarter
     */
    public function testIsStartingNextQuarter($data, $expected)
    {
        $data = $this->getTeamApplication($data);

        $validator = $this->getObjectMock();
        $result = $validator->isStartingNextQuarter($data);

        $this->assertEquals($expected, $result);
    }

    public function providerIsStartingNextQuarter()
    {
        return [
            // Is Starting Next Quarter
            [
                [
                    'incomingQuarter' => 'next',
                ],
                true,
            ],
            // Not Starting Next Quarter
            [
                [
                    'incomingQuarter' => 'future',
                ],
                false,
            ],
        ];
    }

    public function getTeamApplication($data)
    {
        if (isset($data['__reportingDate'])) {
            $this->statsReport->reportingDate = $data['__reportingDate'];
            unset($data['__reportingDate']);
        }

        if (isset($data['incomingQuarter'])) {
            if ($data['incomingQuarter'] == 'next') {
                $data['incomingQuarter'] = $this->nextQuarter->id;
            } else if ($data['incomingQuarter'] == 'future') {
                $data['incomingQuarter'] = $this->futureQuarter->id;
            }
        }

        $data = array_merge($this->dataTemplate, $data);

        return Domain\TeamApplication::fromArray($data);
    }
}
