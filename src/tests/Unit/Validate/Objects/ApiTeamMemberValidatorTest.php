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

            // Passes TDO true
            [
                [
                    'tdo' => true,
                ],
                [],
                true,
            ],
            // Passes TDO false
            [
                [
                    'tdo' => false,
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
    public function testValidateTransfer($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getTeamMember($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

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
            // Ctw set with comment
            [
                [
                    'ctw' => true,
                    'comment' => 'something about the ctw',
                ],
                [],
                true,
            ],
            // Ctw set without comment
            [
                [
                    'ctw' => true,
                    'comment' => null,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'CLASSLIST_CTW_COMMENT_MISSING',
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
