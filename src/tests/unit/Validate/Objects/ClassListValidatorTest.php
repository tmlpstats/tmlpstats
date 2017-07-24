<?php
namespace TmlpStats\Tests\Unit\Validate\Objects;

use TmlpStats\Tests\Unit\Traits\MocksMessages;
use TmlpStats\Tests\Unit\Traits\MocksQuarters;
use TmlpStats\Tests\Unit\Traits\MocksSettings;
use TmlpStats\Util;
use TmlpStats\Tests\Mocks\MockContext;
use TmlpStats\Validate\Objects\ClassListValidator;
use Carbon\Carbon;
use stdClass;

class ClassListValidatorTest extends ObjectsValidatorTestAbstract
{
    use MocksSettings, MocksMessages, MocksQuarters;

    protected $instantiateApp = true;
    protected $testClass = ClassListValidator::class;

    protected $dataFields = [
        'firstName',
        'lastName',
        'teamYear',
        'wknd',
        'xferOut',
        'xferIn',
        'ctw',
        'wd',
        'wbo',
        'rereg',
        'excep',
        'travel',
        'room',
        'gitw',
        'tdo',
    ];

    protected $validateMethods = [
        'validateGitw',
        'validateTdo',
        'validateTeamYear',
        'validateTransfer',
        'validateWithdraw',
        'validateTravel',
    ];

    public function testPopulateValidatorsSetsValidatorsForEachInput($data = null)
    {
        $data           = new stdClass;
        $data->teamYear = 2;
        $data->wknd     = 2;
        $data->xferIn   = null;

        parent::testPopulateValidatorsSetsValidatorsForEachInput($data);
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(['addMessage', 'validate']);

        $i = 0;
        $this->setupMessageMocks($validator, $messages, $i);

        $validator->expects($this->at($i))
                  ->method('validate')
                  ->with($data);

        $result = $validator->run($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerRun()
    {
        return [
            // Test Required
            [
                Util::arrayToObject([
                    'firstName' => null,
                    'lastName'  => null,
                    'teamYear'  => null,
                    'wknd'      => null,
                    'xferOut'   => null,
                    'xferIn'    => null,
                    'ctw'       => null,
                    'wd'        => null,
                    'wbo'       => null,
                    'rereg'     => null,
                    'excep'     => null,
                    'travel'    => null,
                    'room'      => null,
                    'gitw'      => null,
                    'tdo'       => null,
                ]),
                [
                    ['INVALID_VALUE', 'First Name', '[empty]'],
                    ['INVALID_VALUE', 'Last Name', '[empty]'],
                    ['INVALID_VALUE', 'Team Year', '[empty]'],
                ],
                false,
            ],
            // Test Valid 1
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '1',
                    'wknd'      => '1',
                    'xferOut'   => '1',
                    'xferIn'    => '1',
                    'ctw'       => '1',
                    'wd'        => '1 AP',
                    'wbo'       => '1',
                    'rereg'     => '1',
                    'excep'     => '1',
                    'travel'    => 'Y',
                    'room'      => 'y',
                    'gitw'      => 'E',
                    'tdo'       => 'Y',
                ]),
                [],
                true,
            ],
            // Test Valid 2
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '2',
                    'wknd'      => '2',
                    'xferOut'   => '2',
                    'xferIn'    => '2',
                    'ctw'       => '2',
                    'wd'        => '2 FIN',
                    'wbo'       => '2',
                    'rereg'     => '2',
                    'excep'     => '2',
                    'travel'    => 'Y',
                    'room'      => 'y',
                    'gitw'      => 'I',
                    'tdo'       => 'N',
                ]),
                [],
                true,
            ],
            // Test Valid 3
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '2',
                    'wknd'      => 'R',
                    'xferOut'   => 'R',
                    'xferIn'    => 'R',
                    'ctw'       => 'R',
                    'wd'        => 'R T',
                    'wbo'       => 'R',
                    'rereg'     => 'R',
                    'excep'     => 'R',
                    'travel'    => 'Y',
                    'room'      => 'y',
                    'gitw'      => 'I',
                    'tdo'       => 'Y',
                ]),
                [],
                true,
            ],

            // Test Mismatched Team Year 1 & 2
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '1',
                    'wknd'      => '2',
                    'xferOut'   => '2',
                    'xferIn'    => '2',
                    'ctw'       => '2',
                    'wd'        => '2 AP',
                    'wbo'       => '2',
                    'rereg'     => '2',
                    'excep'     => '2',
                    'travel'    => 'Y',
                    'room'      => 'y',
                    'gitw'      => 'E',
                    'tdo'       => 'Y',
                ]),
                [
                    ['INVALID_VALUE', 'Wknd', '2'],
                    ['INVALID_VALUE', 'Xfer Out', '2'],
                    ['INVALID_VALUE', 'Xfer In', '2'],
                    ['INVALID_VALUE', 'Ctw', '2'],
                    ['INVALID_VALUE', 'Wbo', '2'],
                    ['INVALID_VALUE', 'Rereg', '2'],
                    ['INVALID_VALUE', 'Excep', '2'],
                ],
                false,
            ],
            // Test Mismatched Team Year 1 & R
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '1',
                    'wknd'      => 'R',
                    'xferOut'   => 'R',
                    'xferIn'    => 'R',
                    'ctw'       => 'R',
                    'wd'        => 'R AP',
                    'wbo'       => 'R',
                    'rereg'     => 'R',
                    'excep'     => 'R',
                    'travel'    => 'Y',
                    'room'      => 'y',
                    'gitw'      => 'E',
                    'tdo'       => 'Y',
                ]),
                [
                    ['INVALID_VALUE', 'Wknd', 'R'],
                    ['INVALID_VALUE', 'Xfer Out', 'R'],
                    ['INVALID_VALUE', 'Xfer In', 'R'],
                    ['INVALID_VALUE', 'Ctw', 'R'],
                    ['INVALID_VALUE', 'Wbo', 'R'],
                    ['INVALID_VALUE', 'Rereg', 'R'],
                    ['INVALID_VALUE', 'Excep', 'R'],
                ],
                false,
            ],
            // Test Mismatched Team Year 2 & 1
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '2',
                    'wknd'      => '1',
                    'xferOut'   => '1',
                    'xferIn'    => '1',
                    'ctw'       => '1',
                    'wd'        => '1 AP',
                    'wbo'       => '1',
                    'rereg'     => '1',
                    'excep'     => '1',
                    'travel'    => 'Y',
                    'room'      => 'y',
                    'gitw'      => 'E',
                    'tdo'       => 'Y',
                ]),
                [
                    ['INVALID_VALUE', 'Wknd', '1'],
                    ['INVALID_VALUE', 'Xfer Out', '1'],
                    ['INVALID_VALUE', 'Xfer In', '1'],
                    ['INVALID_VALUE', 'Ctw', '1'],
                    ['INVALID_VALUE', 'Wbo', '1'],
                    ['INVALID_VALUE', 'Rereg', '1'],
                    ['INVALID_VALUE', 'Excep', '1'],
                ],
                false,
            ],

            // Test Invalid Travel
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '1',
                    'wknd'      => '1',
                    'xferOut'   => '1',
                    'xferIn'    => '1',
                    'ctw'       => '1',
                    'wd'        => '1 AP',
                    'wbo'       => '1',
                    'rereg'     => '1',
                    'excep'     => '1',
                    'travel'    => 'N',
                    'room'      => 'y',
                    'gitw'      => 'E',
                    'tdo'       => 'Y',
                ]),
                [
                    ['INVALID_VALUE', 'Travel', 'N'],
                ],
                false,
            ],
            // Test Invalid Room
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '1',
                    'wknd'      => '1',
                    'xferOut'   => '1',
                    'xferIn'    => '1',
                    'ctw'       => '1',
                    'wd'        => '1 AP',
                    'wbo'       => '1',
                    'rereg'     => '1',
                    'excep'     => '1',
                    'travel'    => 'Y',
                    'room'      => 0,
                    'gitw'      => 'E',
                    'tdo'       => 'Y',
                ]),
                [
                    ['INVALID_VALUE', 'Room', '0'],
                ],
                false,
            ],

            // Test Invalid GITW
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '1',
                    'wknd'      => '1',
                    'xferOut'   => '1',
                    'xferIn'    => '1',
                    'ctw'       => '1',
                    'wd'        => '1 AP',
                    'wbo'       => '1',
                    'rereg'     => '1',
                    'excep'     => '1',
                    'travel'    => 'Y',
                    'room'      => 'y',
                    'gitw'      => 0,
                    'tdo'       => 'Y',
                ]),
                [
                    ['INVALID_VALUE', 'Gitw', '0'],
                ],
                false,
            ],
            // Test Invalid TDO
            [
                Util::arrayToObject([
                    'firstName' => 'Keith',
                    'lastName'  => 'Stone',
                    'teamYear'  => '1',
                    'wknd'      => '1',
                    'xferOut'   => '1',
                    'xferIn'    => '1',
                    'ctw'       => '1',
                    'wd'        => '1 AP',
                    'wbo'       => '1',
                    'rereg'     => '1',
                    'excep'     => '1',
                    'travel'    => 'Y',
                    'room'      => 'y',
                    'gitw'      => 'E',
                    'tdo'       => '0',
                ]),
                [
                    ['INVALID_VALUE', 'Tdo', '0'],
                ],
                false,
            ],
        ];
    }

    public function providerValidate()
    {
        return [
            // Validate Succeeds
            [
                [
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ],
                true,
            ],
            // validateGitw fails
            [
                [
                    'validateGitw'     => false,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ],
                false,
            ],
            // validateTdo fails
            [
                [
                    'validateGitw'     => true,
                    'validateTdo'      => false,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ],
                false,
            ],
            // validateTeamYear fails
            [
                [
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => false,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ],
                false,
            ],
            // validateTransfer fails
            [
                [
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => false,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ],
                false,
            ],
            // validateWithdraw fails
            [
                [
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => false,
                    'validateTravel'   => true,
                ],
                false,
            ],
            // validateTravel fails
            [
                [
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => false,
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateGitw
     */
    public function testValidateGitw($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->validateGitw($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateGitw()
    {
        return [
            // Passes Transfer Out does not have GITW set
            [
                Util::arrayToObject([
                    'gitw'    => null,
                    'xferOut' => 2,
                    'wd'      => null,
                    'wbo'     => null,
                ]),
                [],
                true,
            ],
            // Passes WD does not have GITW set
            [
                Util::arrayToObject([
                    'gitw'    => null,
                    'xferOut' => null,
                    'wd'      => 2,
                    'wbo'     => null,
                ]),
                [],
                true,
            ],
            // Passes WBO does not have GITW set
            [
                Util::arrayToObject([
                    'gitw'    => null,
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => 2,
                ]),
                [],
                true,
            ],
            // Fails Transfer Out has GITW set
            [
                Util::arrayToObject([
                    'gitw'    => 'E',
                    'xferOut' => 2,
                    'wd'      => null,
                    'wbo'     => null,
                ]),
                [
                    ['CLASSLIST_GITW_LEAVE_BLANK'],
                ],
                false,
            ],
            // Fails Wd has GITW set
            [
                Util::arrayToObject([
                    'gitw'    => 'E',
                    'xferOut' => null,
                    'wd'      => 2,
                    'wbo'     => null,
                ]),
                [
                    ['CLASSLIST_GITW_LEAVE_BLANK'],
                ],
                false,
            ],
            // Fails WBO has GITW set
            [
                Util::arrayToObject([
                    'gitw'    => 'E',
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => 2,
                ]),
                [
                    ['CLASSLIST_GITW_LEAVE_BLANK'],
                ],
                false,
            ],

            // Passes GITW set
            [
                Util::arrayToObject([
                    'gitw'    => 'I',
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => null,
                ]),
                [],
                true,
            ],
            // Fails when GITW not set
            [
                Util::arrayToObject([
                    'gitw'    => null,
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => null,
                ]),
                [
                    ['CLASSLIST_GITW_MISSING'],
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTdo
     */
    public function testValidateTdo($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->validateTdo($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTdo()
    {
        return [
            // Passes Transfer Out does not have TDO set
            [
                Util::arrayToObject([
                    'tdo'     => null,
                    'xferOut' => 2,
                    'wd'      => null,
                    'wbo'     => null,
                ]),
                [],
                true,
            ],
            // Passes WD does not have TDO set
            [
                Util::arrayToObject([
                    'tdo'     => null,
                    'xferOut' => null,
                    'wd'      => 2,
                    'wbo'     => null,
                ]),
                [],
                true,
            ],
            // Passes WBO does not have TDO set
            [
                Util::arrayToObject([
                    'tdo'     => null,
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => 2,
                ]),
                [],
                true,
            ],
            // Fails Transfer Out has TDO set
            [
                Util::arrayToObject([
                    'tdo'     => 'E',
                    'xferOut' => 2,
                    'wd'      => null,
                    'wbo'     => null,
                ]),
                [
                    ['CLASSLIST_TDO_LEAVE_BLANK'],
                ],
                false,
            ],
            // Fails Wd has TDO set
            [
                Util::arrayToObject([
                    'tdo'     => 'E',
                    'xferOut' => null,
                    'wd'      => 2,
                    'wbo'     => null,
                ]),
                [
                    ['CLASSLIST_TDO_LEAVE_BLANK'],
                ],
                false,
            ],
            // Fails WBO has TDO set
            [
                Util::arrayToObject([
                    'tdo'     => 'E',
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => 2,
                ]),
                [
                    ['CLASSLIST_TDO_LEAVE_BLANK'],
                ],
                false,
            ],

            // Passes TDO set
            [
                Util::arrayToObject([
                    'tdo'     => 'I',
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => null,
                ]),
                [],
                true,
            ],
            // Fails when TDO not set
            [
                Util::arrayToObject([
                    'tdo'     => null,
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => null,
                ]),
                [
                    ['CLASSLIST_TDO_MISSING'],
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTeamYear
     */
    public function testValidateTeamYear($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->validateTeamYear($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTeamYear()
    {
        return [
            // Wknd, Xfer In, Rereg are both not set
            [
                Util::arrayToObject([
                    'wknd'     => null,
                    'xferIn'   => null,
                    'rereg'    => null,
                    'teamYear' => 1,
                ]),
                [
                    ['CLASSLIST_WKND_MISSING', 1],
                ],
                false,
            ],
            // Wknd, Xfer In, Rereg are set
            [
                Util::arrayToObject([
                    'wknd'     => 2,
                    'xferIn'   => 2,
                    'rereg'    => 2,
                    'teamYear' => 2,
                ]),
                [
                    ['CLASSLIST_WKND_XIN_REREG_ONLY_ONE', 2],
                ],
                false,
            ],
            // Wknd set and Xfer In/Rereg not set
            [
                Util::arrayToObject([
                    'wknd'     => 1,
                    'xferIn'   => null,
                    'rereg'    => null,
                    'teamYear' => 1,
                ]),
                [],
                true,
            ],
            // Wknd/Rereg not set and Xfer In set
            [
                Util::arrayToObject([
                    'wknd'     => null,
                    'xferIn'   => 1,
                    'rereg'    => null,
                    'teamYear' => 1,
                ]),
                [],
                true,
            ],
            // Wknd/Xfer In not set and Rereg set
            [
                Util::arrayToObject([
                    'wknd'     => null,
                    'xferIn'   => null,
                    'rereg'    => 1,
                    'teamYear' => 1,
                ]),
                [],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTransfer
     */
    public function testValidateTransfer($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->validateTransfer($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTransfer()
    {
        return [
            // Xfer In and Out null
            [
                Util::arrayToObject([
                    'xferIn'  => null,
                    'xferOut' => null,
                    'comment' => null,
                ]),
                [],
                true,
            ],
            // Xfer In not null with comment
            [
                Util::arrayToObject([
                    'xferIn'  => 1,
                    'xferOut' => null,
                    'comment' => 'Transfer to Vancouver on 5/15/15',
                ]),
                [
                    ['CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER'],
                ],
                true,
            ],
            // Xfer In not null without comment
            [
                Util::arrayToObject([
                    'xferIn'  => 1,
                    'xferOut' => null,
                    'comment' => null,
                ]),
                [
                    ['CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER'],
                    ['CLASSLIST_XFER_COMMENT_MISSING'],
                ],
                false,
            ],
            // Xfer Out not null with comment
            [
                Util::arrayToObject([
                    'xferIn'  => null,
                    'xferOut' => 1,
                    'comment' => 'Transfer to Vancouver on 5/15/15',
                ]),
                [
                    ['CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER'],
                ],
                true,
            ],
            // Xfer Out not null without comment
            [
                Util::arrayToObject([
                    'xferIn'  => null,
                    'xferOut' => 1,
                    'comment' => null,
                ]),
                [
                    ['CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER'],
                    ['CLASSLIST_XFER_COMMENT_MISSING'],
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateWithdraw
     */
    public function testValidateWithdraw($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->validateWithdraw($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateWithdraw()
    {
        return [
            // Wd, Wbo, Ctw null
            [
                Util::arrayToObject([
                    'wd'       => null,
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => null,
                    'teamYear' => 1,
                ]),
                [],
                true,
            ],

            // Wd set with comment
            [
                Util::arrayToObject([
                    'wd'       => '1 OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                ]),
                [],
                true,
            ],
            // Wbo set with comment
            [
                Util::arrayToObject([
                    'wd'       => null,
                    'wbo'      => 1,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                ]),
                [],
                true,
            ],
            // CTW set with comment
            [
                Util::arrayToObject([
                    'wd'       => null,
                    'wbo'      => null,
                    'ctw'      => 1,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                ]),
                [],
                true,
            ],

            // Wd & Wbo set
            [
                Util::arrayToObject([
                    'wd'       => '1 OOC',
                    'wbo'      => 1,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                ]),
                [
                    ['CLASSLIST_WD_WBO_ONLY_ONE'],
                ],
                false,
            ],
            // Wd & CTW set
            [
                Util::arrayToObject([
                    'wd'       => '1 OOC',
                    'wbo'      => null,
                    'ctw'      => 1,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                ]),
                [
                    ['CLASSLIST_WD_CTW_ONLY_ONE'],
                ],
                false,
            ],
            // Wbo & CTW set
            [
                Util::arrayToObject([
                    'wd'       => null,
                    'wbo'      => 1,
                    'ctw'      => 1,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                ]),
                [
                    ['CLASSLIST_WD_CTW_ONLY_ONE'],
                ],
                false,
            ],

            // Wd set with matched team year
            [
                Util::arrayToObject([
                    'wd'       => 'R OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 2,
                ]),
                [],
                true,
            ],
            // Wd set with mismatched team year
            [
                Util::arrayToObject([
                    'wd'       => '2 OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                ]),
                [
                    ['CLASSLIST_WD_DOESNT_MATCH_YEAR'],
                ],
                false,
            ],
            // Wd set with mismatched team year
            [
                Util::arrayToObject([
                    'wd'       => 'R OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                ]),
                [
                    ['CLASSLIST_WD_DOESNT_MATCH_YEAR'],
                ],
                false,
            ],
            // Wd set with mismatched team year
            [
                Util::arrayToObject([
                    'wd'       => '1 OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 2,
                ]),
                [
                    ['CLASSLIST_WD_DOESNT_MATCH_YEAR'],
                ],
                false,
            ],

            // Wd set without comment
            [
                Util::arrayToObject([
                    'wd'       => '1 OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => null,
                    'teamYear' => 1,
                ]),
                [
                    ['CLASSLIST_WD_COMMENT_MISSING'],
                ],
                false,
            ],
            // Wbo set without comment
            [
                Util::arrayToObject([
                    'wd'       => null,
                    'wbo'      => 1,
                    'ctw'      => null,
                    'comment'  => null,
                    'teamYear' => 1,
                ]),
                [
                    ['CLASSLIST_WD_COMMENT_MISSING'],
                ],
                false,
            ],

            // Ctw set with comment
            [
                Util::arrayToObject([
                    'wd'       => null,
                    'wbo'      => null,
                    'ctw'      => 1,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                ]),
                [],
                true,
            ],
            // Ctw set without comment
            [
                Util::arrayToObject([
                    'wd'       => null,
                    'wbo'      => null,
                    'ctw'      => 1,
                    'comment'  => null,
                    'teamYear' => 1,
                ]),
                [
                    ['CLASSLIST_CTW_COMMENT_MISSING'],
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTravel
     */
    public function testValidateTravel($data, $statsReportInput, $messages, $travelDueBy, $expectedResult)
    {

        $statsReport = new stdClass;
        $statsReport->quarter = $this->getQuarterMock([], $statsReportInput['dates']);
        $statsReport->center  = null;
        $statsReport->reportingDate = $statsReportInput['reportingDate'];

        $ctx = MockContext::defaults()->install();
        $this->unsetSetting('travelDueByDate');
        if ($travelDueBy) {
            $ctx->withSetting('travelDueByDate', $travelDueBy);
            $this->setSetting('travelDueByDate', $travelDueBy);
        } else {
            $ctx->withSetting('travelDueByDate', null);
        }

        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);
        $this->setupMessageMocks($validator, $messages);
        $this->mockRegionQuarter([]);
        $result = $validator->validateTravel($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTravel()
    {
        $statsReport = [
            'dates' => [
                'startWeekendDate' => Carbon::createFromDate(2015, 4, 3)->startOfDay(),
                'classroom2Date' => Carbon::createFromDate(2015, 4, 17)->startOfDay(),
                'endWeekendDate' => Carbon::createFromDate(2015, 5, 29)->startOfDay(),
            ],
            'reportingDate' => Carbon::createFromDate(2015, 3, 13)->startOfDay(),
        ];

        $statsReportOnClassroom2 = [
            'dates' => $statsReport['dates'],
            'reportingDate' => Carbon::createFromDate(2015, 4, 17)->startOfDay(),
        ];

        $statsReportAfterClassroom2 = [
            'dates' => $statsReport['dates'],
            'reportingDate' => Carbon::createFromDate(2015, 4, 24)->startOfDay(),
        ];

        $statsReportLast2Weeks = [
            'dates' => $statsReport['dates'],
            'reportingDate' => Carbon::createFromDate(2015, 5, 15)->startOfDay(),
        ];

        $statsReportWithClassroom1 = [
            'dates' => [
                'startWeekendDate' => Carbon::createFromDate(2015, 4, 3)->startOfDay(),
                'classroom1Date' => Carbon::createFromDate(2015, 4, 17)->startOfDay(),
            ],
            'reportingDate' => Carbon::createFromDate(2015, 3, 13)->startOfDay(),
        ];

        $statsReportWithClassroom3 = [
            'reportingDate' => Carbon::createFromDate(2015, 3, 13)->startOfDay(),
            'dates' =>[
                'startWeekendDate' => Carbon::createFromDate(2015, 4, 3)->startOfDay(),
                'classroom3Date' => Carbon::createFromDate(2015, 4, 17)->startOfDay(),
            ],
        ];

        return [
            // Before 2nd Classroom, all null
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => null,
                    'comment' => null,
                ]),
                $statsReport,
                [],
                'classroom2Date',
                true,
            ],

            // Wd set and travel/room ignored
            [
                Util::arrayToObject([
                    'wd'      => 1,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => null,
                    'comment' => null,
                ]),
                null,
                [],
                null,
                true,
            ],
            // Wbo set and travel/room ignored
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => 1,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => null,
                    'comment' => null,
                ]),
                null,
                [],
                null,
                true,
            ],
            // XferOut set and travel/room ignored
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => 1,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => null,
                    'comment' => null,
                ]),
                null,
                [],
                null,
                true,
            ],

            // On 2nd Classroom, all null
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => null,
                    'comment' => null,
                ]),
                $statsReportOnClassroom2,
                [],
                'classroom2Date',
                true,
            ],

            // After 2nd Classroom, travel/room set
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => 'Y',
                    'room'    => 'y',
                    'comment' => null,
                ]),
                $statsReportAfterClassroom2,
                [],
                'classroom2Date',
                true,
            ],
            // After 2nd Classroom, travel not set with comment
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => 'y',
                    'comment' => 'Booked by 5/5/15',
                ]),
                $statsReportAfterClassroom2,
                [
                    ['CLASSLIST_TRAVEL_COMMENT_REVIEW'],
                ],
                'classroom2Date',
                true,
            ],
            // After 2nd Classroom, travel not set without comment
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => 'y',
                    'comment' => null,
                ]),
                $statsReportAfterClassroom2,
                [
                    ['CLASSLIST_TRAVEL_COMMENT_MISSING'],
                ],
                'classroom2Date',
                false,
            ],
            // After 2nd Classroom, room not set with comment
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => 'Y',
                    'room'    => null,
                    'comment' => 'Booked by 5/5/15',
                ]),
                $statsReportAfterClassroom2,
                [
                    ['CLASSLIST_ROOM_COMMENT_REVIEW'],
                ],
                'classroom2Date',
                true,
            ],
            // After 2nd Classroom, room not set without comment
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => 'Y',
                    'room'    => null,
                    'comment' => null,
                ]),
                $statsReportAfterClassroom2,
                [
                    ['CLASSLIST_ROOM_COMMENT_MISSING'],
                ],
                'classroom2Date',
                false,
            ],

            // isTimeToCheck with setting = date
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => null,
                    'comment' => null,
                ]),
                $statsReport,
                [],
                '2015-04-17',
                true,
            ],
            // isTimeToCheck with setting = classroom1
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => null,
                    'comment' => null,
                ]),
                $statsReportWithClassroom1,
                [],
                'classroom1Date',
                true,
            ],
            // isTimeToCheck with setting = classroom3
            [
                Util::arrayToObject([
                    'wd'      => null,
                    'wbo'     => null,
                    'xferOut' => null,
                    'ctw'     => null,
                    'travel'  => null,
                    'room'    => null,
                    'comment' => null,
                ]),
                $statsReportWithClassroom3,
                [],
                'classroom3Date',
                true,
            ],
        ];
    }
}
