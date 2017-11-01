<?php
namespace TmlpStats\Tests\Unit\Validate\Objects;

use Carbon\Carbon;
use TmlpStats\Domain\TeamMember;
use TmlpStats\Tests\Unit\Traits;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Objects\ApiTeamMemberValidator;

class ApiTeamMemberValidatorTest extends ApiValidatorTestAbstract
{
    use Traits\MocksSettings;

    protected $testClass = ApiTeamMemberValidator::class;

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'error',
        'reference' => [
            'id' => null,
            'type' => 'TeamMember',
        ],
    ];

    protected $accountabilities = [
        4 => 'Statistician',
        5 => 'Statistician Apprentice',
        6 => 'Team 1 Team Leader',
        7 => 'Team 2 Team Leader',
        8 => 'Program Manager',
        9 => 'Classroom Leader',
        11 => 'Access to Power',
        12 => 'Power to Create',
        13 => 'T1 Expansion',
        14 => 'T2 Expansion',
        15 => 'Game in the World',
        16 => 'Landmark Forum',
        17 => 'Logistics',
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
            'teamYear' => '1',
            'atWeekend' => true,
            'isReviewer' => false,
            'xferOut' => null,
            'xferIn' => null,
            'wbo' => null,
            'ctw' => null,
            'rereg' => null,
            'excep' => null,
            'travel' => false,
            'room' => false,
            'gitw' => false,
            'tdo' => 0,
            'withdrawCode' => null,
            'comment' => null,
        ];
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->clearSettings();
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamMember($data);

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
                    'firstName' => null,
                    'lastName' => null,
                    'teamYear' => null,
                    'atWeekend' => null,
                    'isReviewer' => null,
                    'xferOut' => null,
                    'xferIn' => null,
                    'wbo' => null,
                    'ctw' => null,
                    'rereg' => null,
                    'excep' => null,
                    'travel' => null,
                    'room' => null,
                    'gitw' => null,
                    'tdo' => null,
                    'withdrawCode' => null,
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
                        'reference.field' => 'atWeekend',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_GITW_MISSING',
                        'reference.field' => 'gitw',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_TDO_MISSING',
                        'reference.field' => 'tdo',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_WKND_MISSING',
                        'reference.field' => 'atWeekend',
                    ]),
                ],
                false,
            ],
            // Test Standard input
            [
                [],
                [],
                true,
            ],
            // Test Team 2
            [
                [
                    'teamYear' => '2',
                ],
                [],
                true,
            ],
            // Test Team 2 reviewer
            [
                [
                    'teamYear' => '2',
                    'isReviewer' => true,
                ],
                [],
                true,
            ],
            // Test rereg
            [
                [
                    'atWeekend' => false,
                    'rereg' => true,
                ],
                [],
                true,
            ],
            // Test excep set
            [
                [
                    'excep' => true,
                ],
                [],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateGitw
     */
    public function testValidateGitw($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamMember($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateGitw()
    {
        return [
            // Passes Transfer Out does not have GITW set
            [
                [
                    'gitw' => null,
                    'xferOut' => true,
                    'comment' => 'something about the transfer',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferOut',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // Passes WD does not have GITW set
            [
                [
                    'gitw' => null,
                    'withdrawCode' => 2,
                    'comment' => 'something about the withdraw',
                ],
                [],
                true,
            ],
            // Passes WBO does not have GITW set
            [
                [
                    'gitw' => null,
                    'wbo' => true,
                    'comment' => 'something about the withdraw',
                ],
                [],
                true,
            ],
            // Passes xferOut does not have GITW set
            [
                [
                    'gitw' => null,
                    'xferOut' => true,
                    'comment' => 'something about the withdraw',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferOut',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],

            // Passes GITW true
            [
                [
                    'gitw' => true,
                ],
                [],
                true,
            ],
            // Passes GITW false
            [
                [
                    'gitw' => false,
                ],
                [],
                true,
            ],
            // Fails when GITW not set
            [
                [
                    'gitw' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_GITW_MISSING',
                        'reference.field' => 'gitw',
                    ]),
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTdo
     */
    public function testValidateTdo($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamMember($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTdo()
    {
        return [
            // Passes Transfer Out does not have TDO set
            [
                [
                    'tdo' => null,
                    'xferOut' => true,
                    'comment' => 'something about the transfer',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferOut',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // Passes WD does not have TDO set
            [
                [
                    'tdo' => null,
                    'withdrawCode' => 2,
                    'comment' => 'something about the withdraw',
                ],
                [],
                true,
            ],
            // Passes WBO does not have GITW set
            [
                [
                    'tdo' => null,
                    'wbo' => true,
                    'comment' => 'something about the withdraw',
                ],
                [],
                true,
            ],
            // Passes xferOut does not have GITW set
            [
                [
                    'tdo' => null,
                    'xferOut' => true,
                    'comment' => 'something about the withdraw',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferOut',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],

            // Passes TDO one
            [
                [
                    'tdo' => 1,
                ],
                [],
                true,
            ],
            // Passes TDO multiple
            [
                [
                    'tdo' => 3,
                ],
                [],
                true,
            ],
            // Passes TDO none
            [
                [
                    'tdo' => 0,
                ],
                [],
                true,
            ],
            // Fails when TDO not set
            [
                [
                    'tdo' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_TDO_MISSING',
                        'reference.field' => 'tdo',
                    ]),
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTeamYear
     */
    public function testValidateTeamYear($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamMember($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTeamYear()
    {
        return [
            // Wknd, Xfer In, Rereg are not set
            [
                [
                    'atWeekend' => false,
                    'xferIn' => null,
                    'rereg' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_WKND_MISSING',
                        'reference.field' => 'atWeekend',
                    ]),
                ],
                false,
            ],
            // Wknd, Xfer In, Rereg are false
            [
                [
                    'atWeekend' => false,
                    'xferIn' => false,
                    'rereg' => false,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_WKND_MISSING',
                        'reference.field' => 'atWeekend',
                    ]),
                ],
                false,
            ],
            // Wknd, Xfer In, Rereg are set
            [
                [
                    'atWeekend' => true,
                    'xferIn' => true,
                    'rereg' => true,
                    'comment' => 'something about the transfer',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_WKND_XIN_REREG_ONLY_ONE',
                        'reference.field' => 'rereg',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferIn',
                        'level' => 'warning',
                    ]),
                ],
                false,
            ],
            // Wknd set and Xfer In/Rereg not set
            [
                [
                    'atWeekend' => true,
                    'xferIn' => null,
                    'rereg' => null,
                ],
                [],
                true,
            ],
            // Wknd/Rereg not set and Xfer In set
            [
                [
                    'atWeekend' => false,
                    'xferIn' => true,
                    'rereg' => null,
                    'comment' => 'something about the transfer',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferIn',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // Wknd/Xfer In not set and Rereg set
            [
                [
                    'atWeekend' => false,
                    'xferIn' => null,
                    'rereg' => true,
                ],
                [],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTransfer
     */
    public function testValidateTransfer($data, $expectedMessages, $expectedResult, $pastWeeks = [])
    {
        $data = $this->getTeamMember($data);

        if ($pastWeeks) {
            $pastWeeks = [ $this->getTeamMember($pastWeeks) ];
        }

        $validator = $this->getObjectMock();
        $result = $validator->run($data, $pastWeeks);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTransfer()
    {
        return [
            // Xfer In and Out null
            [
                [
                    'xferIn' => null,
                    'xferOut' => null,
                    'comment' => null,
                ],
                [],
                true,
            ],
            // Xfer In not null with comment
            [
                [
                    'atWeekend' => false,
                    'xferIn' => true,
                    'xferOut' => null,
                    'comment' => 'something about the transfer',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferIn',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // Xfer In not null without comment
            [
                [
                    'atWeekend' => false,
                    'xferIn' => true,
                    'xferOut' => null,
                    'comment' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferIn',
                        'level' => 'warning',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
            // Xfer Out not null with comment
            [
                [
                    'xferIn' => null,
                    'xferOut' => true,
                    'comment' => 'something about the transfer',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferOut',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // Xfer Out not null without comment
            [
                [
                    'xferIn' => null,
                    'xferOut' => true,
                    'comment' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferOut',
                        'level' => 'warning',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
            // Xfer Out and Xfer In
            [
                [
                    'atWeekend' => false,
                    'xferIn' => true,
                    'xferOut' => true,
                    'comment' => 'something about the transfer',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_ONLY_ONE',
                        'reference.field' => 'xferIn',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferIn',
                        'level' => 'warning',
                    ]),
                ],
                false,
            ],

            // Xfer In not null with comment
            [
                [
                    'atWeekend' => false,
                    'xferIn' => true,
                    'xferOut' => null,
                    'comment' => 'something about the transfer',
                ],
                [],
                true,
                [
                    'atWeekend' => false,
                    'xferIn' => true,
                    'xferOut' => null,
                    'comment' => 'something about the transfer',
                ],
            ],
            // Xfer In not null without comment
            [
                [
                    'atWeekend' => false,
                    'xferIn' => true,
                    'xferOut' => null,
                    'comment' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
                [
                    'atWeekend' => false,
                    'xferIn' => true,
                    'xferOut' => null,
                    'comment' => null,
                ],
            ],
            // Xfer Out not null with comment
            [
                [
                    'xferIn' => null,
                    'xferOut' => true,
                    'comment' => 'something about the transfer',
                ],
                [],
                true,
                [
                    'xferIn' => null,
                    'xferOut' => true,
                    'comment' => 'something about the transfer',
                ],
            ],
            // Xfer Out not null without comment
            [
                [
                    'xferIn' => null,
                    'xferOut' => true,
                    'comment' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
                [
                    'xferIn' => null,
                    'xferOut' => true,
                    'comment' => null,
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerValidateWithdraw
     */
    public function testValidateWithdraw($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamMember($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateWithdraw()
    {
        return [
            // Wd, Ctw null
            [
                [
                    'withdrawCode' => null,
                    'ctw' => null,
                    'wbo' => null,
                    'comment' => null,
                ],
                [],
                true,
            ],

            // Wd set with comment
            [
                [
                    'withdrawCode' => 1234,
                    'comment' => 'something about the wd',
                ],
                [],
                true,
            ],
            // CTW set with comment
            [
                [
                    'ctw' => true,
                    'comment' => 'something about the ctw',
                ],
                [],
                true,
            ],
            // WBO set with comment
            [
                [
                    'wbo' => true,
                    'comment' => 'something about the ctw',
                ],
                [],
                true,
            ],

            // Wd & CTW set
            [
                [
                    'withdrawCode' => 1234,
                    'ctw' => true,
                    'comment' => 'something about the wd',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_WD_CTW_ONLY_ONE',
                        'reference.field' => 'ctw',
                    ]),
                ],
                false,
            ],
            // Wd & WBO set
            [
                [
                    'withdrawCode' => 1234,
                    'wbo' => true,
                    'comment' => 'something about the wd',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_WD_CTW_ONLY_ONE',
                        'reference.field' => 'ctw',
                    ]),
                ],
                false,
            ],
            // WBO & CTW set
            [
                [
                    'wbo' => true,
                    'ctw' => true,
                    'comment' => 'something about the wd',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_WD_CTW_ONLY_ONE',
                        'reference.field' => 'ctw',
                    ]),
                ],
                false,
            ],

            // Wd set without comment
            [
                [
                    'withdrawCode' => 1234,
                    'comment' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_WD_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
            // Ctw set without comment
            [
                [
                    'ctw' => true,
                    'comment' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_CTW_WBO_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
            // WBO set without comment
            [
                [
                    'wbo' => true,
                    'comment' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_CTW_WBO_COMMENT_MISSING',
                        'reference.field' => 'comment',
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
        $data = $this->getTeamMember($data);

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
                    'comment' => 'promise with by when',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_TRAVEL_COMMENT_REVIEW',
                        'reference.field' => 'comment',
                        'level' => 'warning',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ROOM_COMMENT_REVIEW',
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
                    'withdrawCode' => 1234,
                    'comment' => 'something about the wd',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [],
                true,
            ],
            // validateTravel Ignored When Xfer out
            [
                [
                    'travel' => null,
                    'room' => null,
                    'xferOut' => true,
                    'comment' => 'something about the xfer',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferOut',
                        'level' => 'warning',
                    ]),
                ],
                true,
            ],
            // validateTravel Ignored When WBO Set
            [
                [
                    'travel' => null,
                    'room' => null,
                    'wbo' => true,
                    'comment' => 'something about the wd',
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
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_TRAVEL_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
            // ValidateTravel Fails When Missing Travel and comment is an empty string
            [
                [
                    'travel' => null,
                    'room' => true,
                    'comment' => '',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_TRAVEL_COMMENT_MISSING',
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
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ROOM_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
            // ValidateTravel Fails When Missing Room and comment is an empty string
            [
                [
                    'travel' => true,
                    'room' => null,
                    'comment' => '',
                    '__reportingDate' => Carbon::parse('2016-10-07'),
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ROOM_COMMENT_MISSING',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateAccountablilities
     */
    public function testValidateAccountablilities($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamMember($data);

        $validator = $this->getObjectMock(['getAccountability']);
        $validator->expects($this->any())
            ->method('getAccountability')
            ->will($this->returnCallback(function($id) {
                if (!isset($this->accountabilities[$id])) {
                    return null;
                }

                $accountability = new \stdClass();
                $accountability->display = $this->accountabilities[$id];
                return $accountability;
            }));

        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateAccountablilities()
    {
        return [
            // Validate no accountabilities
            [
                [],
                [],
                true,
            ],
            [
                [
                    'accountabilities' => null,
                ],
                [],
                true,
            ],
            [
                [
                    'accountabilities' => [],
                ],
                [],
                true,
            ],
            // Validate accountabilities not listed are ignored
            [
                [
                    'accountabilities' => [
                        11, 12, 13, 14, 15, 16, 17
                    ],
                ],
                [],
                true,
            ],
            // Has accountability and has contact info
            [
                [
                    'phone' => '555-555-5555',
                    'email' => 'test@tmlpstats.com',
                    'accountabilities' => [
                        4,
                    ],
                ],
                [],
                true,
            ],
            // Has accountability and missing email
            [
                [
                    'phone' => '555-555-5555',
                    'accountabilities' => [
                        4,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING',
                        'reference.field' => 'email',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            [
                [
                    'phone' => '555-555-5555',
                    'email' => null,
                    'accountabilities' => [
                        5,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING',
                        'reference.field' => 'email',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            [
                [
                    'phone' => '555-555-5555',
                    'email' => '',
                    'accountabilities' => [
                        6,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING',
                        'reference.field' => 'email',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            // Has accountability and missing phone
            [
                [
                    'email' => 'test@tmlpstats.com',
                    'accountabilities' => [
                        7,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_PHONE_MISSING',
                        'reference.field' => 'phone',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            [
                [
                    'phone' => null,
                    'email' => 'test@tmlpstats.com',
                    'accountabilities' => [
                        8,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_PHONE_MISSING',
                        'reference.field' => 'phone',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            [
                [
                    'phone' => '',
                    'email' => 'test@tmlpstats.com',
                    'accountabilities' => [
                        9,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_PHONE_MISSING',
                        'reference.field' => 'phone',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            // Has accountability and missing both
            [
                [
                    'accountabilities' => [
                        4,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_PHONE_MISSING',
                        'reference.field' => 'phone',
                        'level' => 'error',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING',
                        'reference.field' => 'email',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            [
                [
                    'phone' => null,
                    'email' => null,
                    'accountabilities' => [
                        4,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_PHONE_MISSING',
                        'reference.field' => 'phone',
                        'level' => 'error',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING',
                        'reference.field' => 'email',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            [
                [
                    'phone' => '',
                    'email' => '',
                    'accountabilities' => [
                        4,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_PHONE_MISSING',
                        'reference.field' => 'phone',
                        'level' => 'error',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING',
                        'reference.field' => 'email',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            // Has multiple accountabilities and only reports errors once
            [
                [
                    'accountabilities' => [
                        4, 5,
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_PHONE_MISSING',
                        'reference.field' => 'phone',
                        'level' => 'error',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_EMAIL_MISSING',
                        'reference.field' => 'email',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            // Has withdrawn, ignores contact info and throws error to remove accountabilities
            [
                [
                    'withdrawCode' => 1,
                    'comment' => 'withdraw message',
                    'accountabilities' => [
                        4
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_AND_WITHDRAWN',
                        'reference.field' => 'accountabilities',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            // Has transfered, ignores contact info and throws error to remove accountabilities
            [
                [
                    'xferOut' => true,
                    'comment' => 'xfer message',
                    'accountabilities' => [
                        4
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                        'reference.field' => 'xferOut',
                        'level' => 'warning',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_AND_WITHDRAWN',
                        'reference.field' => 'accountabilities',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            // Has wbo, ignores contact info and throws error to remove accountabilities
            [
                [
                    'wbo' => true,
                    'comment' => 'withdraw message',
                    'accountabilities' => [
                        4
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_ACCOUNTABLE_AND_WITHDRAWN',
                        'reference.field' => 'accountabilities',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            // Invalid accountability
            [
                [
                    'accountabilities' => [
                        1
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_UNKNOWN_ACCOUNTABILITY',
                        'reference.field' => 'accountability',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
            [
                [
                    'accountabilities' => [
                        1, 2, 3, 18
                    ],
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_UNKNOWN_ACCOUNTABILITY',
                        'reference.field' => 'accountability',
                        'level' => 'error',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_UNKNOWN_ACCOUNTABILITY',
                        'reference.field' => 'accountability',
                        'level' => 'error',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_UNKNOWN_ACCOUNTABILITY',
                        'reference.field' => 'accountability',
                        'level' => 'error',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_UNKNOWN_ACCOUNTABILITY',
                        'reference.field' => 'accountability',
                        'level' => 'error',
                    ]),
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateComment
     */
    public function testValidateComment($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamMember($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateComment()
    {
        return [
            // No comment
            [
                [
                    'comment' => null,
                ],
                [],
                true,
            ],
            // Short comment
            [
                [
                    'comment' => 'This is a great comment',
                ],
                [],
                true,
            ],
            // Long comment
            [
                [
                    'comment' => '01234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_COMMENT_TOO_LONG',
                        'reference.field' => 'comment',
                    ]),
                ],
                false,
            ],
        ];
    }

    public function getTeamMember($data)
    {
        if (isset($data['__reportingDate'])) {
            $this->statsReport->reportingDate = $data['__reportingDate'];
            unset($data['__reportingDate']);
        }

        $data = array_merge($this->dataTemplate, $data);

        return TeamMember::fromArray($data);
    }
}
