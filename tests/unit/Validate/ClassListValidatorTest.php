<?php
namespace TmlpStats\Tests\Validate;

use TmlpStats\Validate\ClassListValidator;
use Carbon\Carbon;
use stdClass;

class ClassListValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = 'TmlpStats\Validate\ClassListValidator';

    protected $dataFields = array(
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
    );

    public function testPopulateValidatorsSetsValidatorsForEachInput($data = null)
    {
        $data = new stdClass;
        $data->teamYear = 2;
        $data->wknd = 2;
        $data->xferIn = null;

        parent::testPopulateValidatorsSetsValidatorsForEachInput($data);
    }

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
                    'firstName'           => null,
                    'lastName'            => null,
                    'teamYear'            => null,
                    'wknd'                => null,
                    'xferOut'             => null,
                    'xferIn'              => null,
                    'ctw'                 => null,
                    'wd'                  => null,
                    'wbo'                 => null,
                    'rereg'               => null,
                    'excep'               => null,
                    'travel'              => null,
                    'room'                => null,
                    'gitw'                => null,
                    'tdo'                 => null,
                )),
                array(
                    array('INVALID_VALUE', 'First Name', '[empty]'),
                    array('INVALID_VALUE', 'Last Name', '[empty]'),
                    array('INVALID_VALUE', 'Team Year', '[empty]'),
                ),
                false,
            ),
            // Test Valid 1
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '1',
                    'wknd'                => '1',
                    'xferOut'             => '1',
                    'xferIn'              => '1',
                    'ctw'                 => '1',
                    'wd'                  => '1 AP',
                    'wbo'                 => '1',
                    'rereg'               => '1',
                    'excep'               => '1',
                    'travel'              => 'Y',
                    'room'                => 'y',
                    'gitw'                => 'E',
                    'tdo'                 => 'Y',
                )),
                array(),
                true,
            ),
            // Test Valid 2
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '2',
                    'wknd'                => '2',
                    'xferOut'             => '2',
                    'xferIn'              => '2',
                    'ctw'                 => '2',
                    'wd'                  => '2 FIN',
                    'wbo'                 => '2',
                    'rereg'               => '2',
                    'excep'               => '2',
                    'travel'              => 'Y',
                    'room'                => 'y',
                    'gitw'                => 'I',
                    'tdo'                 => 'N',
                )),
                array(),
                true,
            ),
            // Test Valid 3
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '2',
                    'wknd'                => 'R',
                    'xferOut'             => 'R',
                    'xferIn'              => 'R',
                    'ctw'                 => 'R',
                    'wd'                  => 'R T',
                    'wbo'                 => 'R',
                    'rereg'               => 'R',
                    'excep'               => 'R',
                    'travel'              => 'Y',
                    'room'                => 'y',
                    'gitw'                => 'I',
                    'tdo'                 => 'Y',
                )),
                array(),
                true,
            ),

            // Test Mismatched Team Year 1 & 2
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '1',
                    'wknd'                => '2',
                    'xferOut'             => '2',
                    'xferIn'              => '2',
                    'ctw'                 => '2',
                    'wd'                  => '2 AP',
                    'wbo'                 => '2',
                    'rereg'               => '2',
                    'excep'               => '2',
                    'travel'              => 'Y',
                    'room'                => 'y',
                    'gitw'                => 'E',
                    'tdo'                 => 'Y',
                )),
                array(
                    array('INVALID_VALUE', 'Wknd', '2'),
                    array('INVALID_VALUE', 'Xfer Out', '2'),
                    array('INVALID_VALUE', 'Xfer In', '2'),
                    array('INVALID_VALUE', 'Ctw', '2'),
                    array('INVALID_VALUE', 'Wbo', '2'),
                    array('INVALID_VALUE', 'Rereg', '2'),
                    array('INVALID_VALUE', 'Excep', '2'),
                ),
                false,
            ),
            // Test Mismatched Team Year 1 & R
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '1',
                    'wknd'                => 'R',
                    'xferOut'             => 'R',
                    'xferIn'              => 'R',
                    'ctw'                 => 'R',
                    'wd'                  => 'R AP',
                    'wbo'                 => 'R',
                    'rereg'               => 'R',
                    'excep'               => 'R',
                    'travel'              => 'Y',
                    'room'                => 'y',
                    'gitw'                => 'E',
                    'tdo'                 => 'Y',
                )),
                array(
                    array('INVALID_VALUE', 'Wknd', 'R'),
                    array('INVALID_VALUE', 'Xfer Out', 'R'),
                    array('INVALID_VALUE', 'Xfer In', 'R'),
                    array('INVALID_VALUE', 'Ctw', 'R'),
                    array('INVALID_VALUE', 'Wbo', 'R'),
                    array('INVALID_VALUE', 'Rereg', 'R'),
                    array('INVALID_VALUE', 'Excep', 'R'),
                ),
                false,
            ),
            // Test Mismatched Team Year 2 & 1
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '2',
                    'wknd'                => '1',
                    'xferOut'             => '1',
                    'xferIn'              => '1',
                    'ctw'                 => '1',
                    'wd'                  => '1 AP',
                    'wbo'                 => '1',
                    'rereg'               => '1',
                    'excep'               => '1',
                    'travel'              => 'Y',
                    'room'                => 'y',
                    'gitw'                => 'E',
                    'tdo'                 => 'Y',
                )),
                array(
                    array('INVALID_VALUE', 'Wknd', '1'),
                    array('INVALID_VALUE', 'Xfer Out', '1'),
                    array('INVALID_VALUE', 'Xfer In', '1'),
                    array('INVALID_VALUE', 'Ctw', '1'),
                    array('INVALID_VALUE', 'Wbo', '1'),
                    array('INVALID_VALUE', 'Rereg', '1'),
                    array('INVALID_VALUE', 'Excep', '1'),
                ),
                false,
            ),

            // Test Invalid Travel
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '1',
                    'wknd'                => '1',
                    'xferOut'             => '1',
                    'xferIn'              => '1',
                    'ctw'                 => '1',
                    'wd'                  => '1 AP',
                    'wbo'                 => '1',
                    'rereg'               => '1',
                    'excep'               => '1',
                    'travel'              => 'N',
                    'room'                => 'y',
                    'gitw'                => 'E',
                    'tdo'                 => 'Y',
                )),
                array(
                    array('INVALID_VALUE', 'Travel', 'N'),
                ),
                false,
            ),
            // Test Invalid Room
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '1',
                    'wknd'                => '1',
                    'xferOut'             => '1',
                    'xferIn'              => '1',
                    'ctw'                 => '1',
                    'wd'                  => '1 AP',
                    'wbo'                 => '1',
                    'rereg'               => '1',
                    'excep'               => '1',
                    'travel'              => 'Y',
                    'room'                => 0,
                    'gitw'                => 'E',
                    'tdo'                 => 'Y',
                )),
                array(
                    array('INVALID_VALUE', 'Room', '0'),
                ),
                false,
            ),

            // Test Invalid GITW
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '1',
                    'wknd'                => '1',
                    'xferOut'             => '1',
                    'xferIn'              => '1',
                    'ctw'                 => '1',
                    'wd'                  => '1 AP',
                    'wbo'                 => '1',
                    'rereg'               => '1',
                    'excep'               => '1',
                    'travel'              => 'Y',
                    'room'                => 'y',
                    'gitw'                => 0,
                    'tdo'                 => 'Y',
                )),
                array(
                    array('INVALID_VALUE', 'Gitw', '0'),
                ),
                false,
            ),
            // Test Invalid TDO
            array(
                $this->arrayToObject(array(
                    'firstName'           => 'Keith',
                    'lastName'            => 'Stone',
                    'teamYear'            => '1',
                    'wknd'                => '1',
                    'xferOut'             => '1',
                    'xferIn'              => '1',
                    'ctw'                 => '1',
                    'wd'                  => '1 AP',
                    'wbo'                 => '1',
                    'rereg'               => '1',
                    'excep'               => '1',
                    'travel'              => 'Y',
                    'room'                => 'y',
                    'gitw'                => 'E',
                    'tdo'                 => '0',
                )),
                array(
                    array('INVALID_VALUE', 'Tdo', '0'),
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
            'validateGitw',
            'validateTdo',
            'validateTeamYear',
            'validateTransfer',
            'validateWithdraw',
            'validateTravel',
        ));
        $validator->expects($this->once())
                  ->method('validateGitw')
                  ->will($this->returnValue($returnValues['validateGitw']));
        $validator->expects($this->once())
                  ->method('validateTdo')
                  ->will($this->returnValue($returnValues['validateTdo']));
        $validator->expects($this->once())
                  ->method('validateTeamYear')
                  ->will($this->returnValue($returnValues['validateTeamYear']));
        $validator->expects($this->once())
                  ->method('validateTransfer')
                  ->will($this->returnValue($returnValues['validateTransfer']));
        $validator->expects($this->once())
                  ->method('validateWithdraw')
                  ->will($this->returnValue($returnValues['validateWithdraw']));
        $validator->expects($this->once())
                  ->method('validateTravel')
                  ->will($this->returnValue($returnValues['validateTravel']));

        $result = $this->runMethod($validator, 'validate', array());

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateExtended()
    {
        return array(
            // Validate Succeeds
            array(
                array(
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ),
                true,
            ),
            // validateGitw fails
            array(
                array(
                    'validateGitw'     => false,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ),
                false,
            ),
            // validateTdo fails
            array(
                array(
                    'validateGitw'     => true,
                    'validateTdo'      => false,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ),
                false,
            ),
            // validateTeamYear fails
            array(
                array(
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => false,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ),
                false,
            ),
            // validateTransfer fails
            array(
                array(
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => false,
                    'validateWithdraw' => true,
                    'validateTravel'   => true,
                ),
                false,
            ),
            // validateWithdraw fails
            array(
                array(
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => false,
                    'validateTravel'   => true,
                ),
                false,
            ),
            // validateTravel fails
            array(
                array(
                    'validateGitw'     => true,
                    'validateTdo'      => true,
                    'validateTeamYear' => true,
                    'validateTransfer' => true,
                    'validateWithdraw' => true,
                    'validateTravel'   => false,
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
            'validateGitw',
            'validateTdo',
            'validateTeamYear',
            'validateTransfer',
            'validateWithdraw',
            'validateTravel',
        ));
        $validator->expects($this->once())
                  ->method('validateGitw')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateTdo')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateTeamYear')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateTransfer')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateWithdraw')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateTravel')
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
    * @dataProvider providerValidateGitw
    */
    public function testValidateGitw($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i))
                          ->method('addMessage')
                          ->with($messages[$i]);
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $result = $validator->validateGitw($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateGitw()
    {
        return array(
            // Passes Transfer Out does not have GITW set
            array(
                $this->arrayToObject(array(
                    'gitw'    => null,
                    'xferOut' => 2,
                    'wd'      => null,
                    'wbo'     => null,
                )),
                array(),
                true,
            ),
            // Passes WD does not have GITW set
            array(
                $this->arrayToObject(array(
                    'gitw'    => null,
                    'xferOut' => null,
                    'wd'      => 2,
                    'wbo'     => null,
                )),
                array(),
                true,
            ),
            // Passes WBO does not have GITW set
            array(
                $this->arrayToObject(array(
                    'gitw'    => null,
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => 2,
                )),
                array(),
                true,
            ),
            // Fails Transfer Out has GITW set
            array(
                $this->arrayToObject(array(
                    'gitw'    => 'E',
                    'xferOut' => 2,
                    'wd'      => null,
                    'wbo'     => null,
                )),
                array('CLASSLIST_GITW_LEAVE_BLANK'),
                false,
            ),
            // Fails Wd has GITW set
            array(
                $this->arrayToObject(array(
                    'gitw'    => 'E',
                    'xferOut' => null,
                    'wd'      => 2,
                    'wbo'     => null,
                )),
                array('CLASSLIST_GITW_LEAVE_BLANK'),
                false,
            ),
            // Fails WBO has GITW set
            array(
                $this->arrayToObject(array(
                    'gitw'    => 'E',
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => 2,
                )),
                array('CLASSLIST_GITW_LEAVE_BLANK'),
                false,
            ),

            // Passes GITW set
            array(
                $this->arrayToObject(array(
                    'gitw'    => 'I',
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => null,
                )),
                array(),
                true,
            ),
            // Fails when GITW not set
            array(
                $this->arrayToObject(array(
                    'gitw'    => null,
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => null,
                )),
                array('CLASSLIST_GITW_MISSING'),
                false,
            ),
        );
    }

    /**
    * @dataProvider providerValidateTdo
    */
    public function testValidateTdo($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i))
                          ->method('addMessage')
                          ->with($messages[$i]);
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $result = $validator->validateTdo($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTdo()
    {
        return array(
            // Passes Transfer Out does not have TDO set
            array(
                $this->arrayToObject(array(
                    'tdo'    => null,
                    'xferOut' => 2,
                    'wd'      => null,
                    'wbo'     => null,
                )),
                array(),
                true,
            ),
            // Passes WD does not have TDO set
            array(
                $this->arrayToObject(array(
                    'tdo'    => null,
                    'xferOut' => null,
                    'wd'      => 2,
                    'wbo'     => null,
                )),
                array(),
                true,
            ),
            // Passes WBO does not have TDO set
            array(
                $this->arrayToObject(array(
                    'tdo'    => null,
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => 2,
                )),
                array(),
                true,
            ),
            // Fails Transfer Out has TDO set
            array(
                $this->arrayToObject(array(
                    'tdo'    => 'E',
                    'xferOut' => 2,
                    'wd'      => null,
                    'wbo'     => null,
                )),
                array('CLASSLIST_TDO_LEAVE_BLANK'),
                false,
            ),
            // Fails Wd has TDO set
            array(
                $this->arrayToObject(array(
                    'tdo'    => 'E',
                    'xferOut' => null,
                    'wd'      => 2,
                    'wbo'     => null,
                )),
                array('CLASSLIST_TDO_LEAVE_BLANK'),
                false,
            ),
            // Fails WBO has TDO set
            array(
                $this->arrayToObject(array(
                    'tdo'    => 'E',
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => 2,
                )),
                array('CLASSLIST_TDO_LEAVE_BLANK'),
                false,
            ),

            // Passes TDO set
            array(
                $this->arrayToObject(array(
                    'tdo'    => 'I',
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => null,
                )),
                array(),
                true,
            ),
            // Fails when TDO not set
            array(
                $this->arrayToObject(array(
                    'tdo'    => null,
                    'xferOut' => null,
                    'wd'      => null,
                    'wbo'     => null,
                )),
                array('CLASSLIST_TDO_MISSING'),
                false,
            ),
        );
    }

    /**
    * @dataProvider providerValidateTeamYear
    */
    public function testValidateTeamYear($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i))
                          ->method('addMessage')
                          ->with($messages[$i][0], $messages[$i][1]);
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $result = $validator->validateTeamYear($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTeamYear()
    {
        return array(
            // Wknd and Xfer In are both not set
            array(
                $this->arrayToObject(array(
                    'wknd'     => null,
                    'xferIn'   => null,
                    'teamYear' => 1,
                )),
                array(
                    array('CLASSLIST_WKND_MISSING', 1),
                ),
                false,
            ),
            // Wknd and Xfer In are both set
            array(
                $this->arrayToObject(array(
                    'wknd'     => 2,
                    'xferIn'   => 2,
                    'teamYear' => 2,
                )),
                array(
                    array('CLASSLIST_WKND_XIN_ONLY_ONE', 2),
                ),
                false,
            ),
            // Wknd set and Xfer In not set
            array(
                $this->arrayToObject(array(
                    'wknd'     => 1,
                    'xferIn'   => null,
                    'teamYear' => 1,
                )),
                array(),
                true,
            ),
            // Wknd now set and Xfer In set
            array(
                $this->arrayToObject(array(
                    'wknd'     => null,
                    'xferIn'   => 1,
                    'teamYear' => 1,
                )),
                array(),
                true,
            ),
        );
    }

    /**
    * @dataProvider providerValidateTransfer
    */
    public function testValidateTransfer($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i))
                          ->method('addMessage')
                          ->with($messages[$i]);
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $result = $validator->validateTransfer($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTransfer()
    {
        return array(
            // Xfer In and Out null
            array(
                $this->arrayToObject(array(
                    'xferIn'  => null,
                    'xferOut' => null,
                    'comment' => null,
                )),
                array(),
                true,
            ),
            // Xfer In not null with comment
            array(
                $this->arrayToObject(array(
                    'xferIn'  => 1,
                    'xferOut' => null,
                    'comment' => 'Transfer to Vancouver on 5/15/15',
                )),
                array('CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER'),
                true,
            ),
            // Xfer In not null without comment
            array(
                $this->arrayToObject(array(
                    'xferIn'  => 1,
                    'xferOut' => null,
                    'comment' => null,
                )),
                array(
                    'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                    'CLASSLIST_XFER_COMMENT_MISSING',
                ),
                false,
            ),
            // Xfer Out not null with comment
            array(
                $this->arrayToObject(array(
                    'xferIn'  => null,
                    'xferOut' => 1,
                    'comment' => 'Transfer to Vancouver on 5/15/15',
                )),
                array('CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER'),
                true,
            ),
            // Xfer Out not null without comment
            array(
                $this->arrayToObject(array(
                    'xferIn'  => null,
                    'xferOut' => 1,
                    'comment' => null,
                )),
                array(
                    'CLASSLIST_XFER_CHECK_WITH_OTHER_CENTER',
                    'CLASSLIST_XFER_COMMENT_MISSING',
                ),
                false,
            ),
        );
    }

    /**
    * @dataProvider providerValidateWithdraw
    */
    public function testValidateWithdraw($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        if ($messages) {
            for ($i = 0; $i < count($messages); $i++) {
                $validator->expects($this->at($i))
                          ->method('addMessage')
                          ->with($messages[$i]);
            }
        } else {
            $validator->expects($this->never())
                      ->method('addMessage');
        }

        $result = $validator->validateWithdraw($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateWithdraw()
    {
        return array(
            // Wd, Wbo, Ctw null
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => null,
                    'teamYear' => 1,
                )),
                array(),
                true,
            ),

            // Wd set with comment
            array(
                $this->arrayToObject(array(
                    'wd'       => '1 OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                )),
                array(),
                true,
            ),
            // Wbo set with comment
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => 1,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                )),
                array(),
                true,
            ),
            // CTW set with comment
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'ctw'      => 1,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                )),
                array(),
                true,
            ),

            // Wd & Wbo set
            array(
                $this->arrayToObject(array(
                    'wd'       => '1 OOC',
                    'wbo'      => 1,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                )),
                array('CLASSLIST_WD_WBO_ONLY_ONE'),
                false,
            ),
            // Wd & CTW set
            array(
                $this->arrayToObject(array(
                    'wd'       => '1 OOC',
                    'wbo'      => null,
                    'ctw'      => 1,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                )),
                array('CLASSLIST_WD_CTW_ONLY_ONE'),
                false,
            ),
            // Wbo & CTW set
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => 1,
                    'ctw'      => 1,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                )),
                array('CLASSLIST_WD_CTW_ONLY_ONE'),
                false,
            ),

            // Wd set with matched team year
            array(
                $this->arrayToObject(array(
                    'wd'       => 'R OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 2,
                )),
                array(),
                true,
            ),
            // Wd set with mismatched team year
            array(
                $this->arrayToObject(array(
                    'wd'       => '2 OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                )),
                array('CLASSLIST_WD_DOESNT_MATCH_YEAR'),
                false,
            ),
            // Wd set with mismatched team year
            array(
                $this->arrayToObject(array(
                    'wd'       => 'R OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                )),
                array('CLASSLIST_WD_DOESNT_MATCH_YEAR'),
                false,
            ),
            // Wd set with mismatched team year
            array(
                $this->arrayToObject(array(
                    'wd'       => '1 OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => '5/15/2015',
                    'teamYear' => 2,
                )),
                array('CLASSLIST_WD_DOESNT_MATCH_YEAR'),
                false,
            ),

            // Wd set without comment
            array(
                $this->arrayToObject(array(
                    'wd'       => '1 OOC',
                    'wbo'      => null,
                    'ctw'      => null,
                    'comment'  => null,
                    'teamYear' => 1,
                )),
                array('CLASSLIST_WD_COMMENT_MISSING'),
                false,
            ),
            // Wbo set without comment
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => 1,
                    'ctw'      => null,
                    'comment'  => null,
                    'teamYear' => 1,
                )),
                array('CLASSLIST_WD_COMMENT_MISSING'),
                false,
            ),

            // Ctw set with comment
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'ctw'      => 1,
                    'comment'  => '5/15/2015',
                    'teamYear' => 1,
                )),
                array(),
                true,
            ),
            // Ctw set without comment
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'ctw'      => 1,
                    'comment'  => null,
                    'teamYear' => 1,
                )),
                array('CLASSLIST_CTW_COMMENT_MISSING'),
                false,
            ),
        );
    }

    /**
    * @dataProvider providerValidateTravel
    */
    public function testValidateTravel($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'getStatsReport',
            'addMessage',
        ));
        if ($statsReport) {
            $validator->expects($this->once())
                      ->method('getStatsReport')
                      ->will($this->returnValue($statsReport));
        } else {
            $validator->expects($this->never())
                      ->method('getStatsReport');
        }

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

        $result = $validator->validateTravel($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTravel()
    {
        $statsReport = new stdClass;
        $statsReport->quarter = new stdClass;

        $statsReport->reportingDate = Carbon::createFromDate(2015, 3, 13);
        $statsReport->quarter->classroom2Date = Carbon::createFromDate(2015, 4, 17);
        $statsReport->quarter->endWeekendDate = Carbon::createFromDate(2015, 5, 29);

        $statsReportOnClassroom2 = clone $statsReport;
        $statsReportOnClassroom2->reportingDate = Carbon::createFromDate(2015, 4, 17);

        $statsReportAfterClassroom2 = clone $statsReport;
        $statsReportAfterClassroom2->reportingDate = Carbon::createFromDate(2015, 4, 24);

        $statsReportLast2Weeks = clone $statsReport;
        $statsReportLast2Weeks->reportingDate = Carbon::createFromDate(2015, 5, 15);

        return array(
            // Before 2nd Classroom, all null
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => null,
                    'room'     => null,
                    'comment'  => null,
                )),
                $statsReport,
                array(),
                true,
            ),

            // Wd set and travel/room ignored
            array(
                $this->arrayToObject(array(
                    'wd'       => 1,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => null,
                    'room'     => null,
                    'comment'  => null,
                )),
                null,
                array(),
                true,
            ),
            // Wbo set and travel/room ignored
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => 1,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => null,
                    'room'     => null,
                    'comment'  => null,
                )),
                null,
                array(),
                true,
            ),
            // XferOut set and travel/room ignored
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => 1,
                    'ctw'      => null,
                    'travel'   => null,
                    'room'     => null,
                    'comment'  => null,
                )),
                null,
                array(),
                true,
            ),

            // On 2nd Classroom, all null
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => null,
                    'room'     => null,
                    'comment'  => null,
                )),
                $statsReportOnClassroom2,
                array(),
                true,
            ),

            // After 2nd Classroom, travel/room set
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => 'Y',
                    'room'     => 'y',
                    'comment'  => null,
                )),
                $statsReportAfterClassroom2,
                array(),
                true,
            ),
            // After 2nd Classroom, travel not set with comment
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => null,
                    'room'     => 'y',
                    'comment'  => 'Booked by 5/5/15',
                )),
                $statsReportAfterClassroom2,
                array('CLASSLIST_TRAVEL_COMMENT_REVIEW'),
                true,
            ),
            // After 2nd Classroom, travel not set without comment
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => null,
                    'room'     => 'y',
                    'comment'  => null,
                )),
                $statsReportAfterClassroom2,
                array('CLASSLIST_TRAVEL_COMMENT_MISSING'),
                false,
            ),
            // After 2nd Classroom, room not set with comment
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => 'Y',
                    'room'     => null,
                    'comment'  => 'Booked by 5/5/15',
                )),
                $statsReportAfterClassroom2,
                array('CLASSLIST_ROOM_COMMENT_REVIEW'),
                true,
            ),
            // After 2nd Classroom, room not set without comment
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => 'Y',
                    'room'     => null,
                    'comment'  => null,
                )),
                $statsReportAfterClassroom2,
                array('CLASSLIST_ROOM_COMMENT_MISSING'),
                false,
            ),

            // 2 Weeks before End, travel not set, ctw set
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => 1,
                    'travel'   => null,
                    'room'     => 'Y',
                    'comment'  => 'Booked by 5/5/15',
                )),
                $statsReportLast2Weeks,
                array(
                    'CLASSLIST_TRAVEL_COMMENT_REVIEW',
                ),
                true,
            ),
            // 2 Weeks before End, room not set, ctw set
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => 1,
                    'travel'   => 'Y',
                    'room'     => null,
                    'comment'  => 'Booked by 5/5/15',
                )),
                $statsReportLast2Weeks,
                array(
                    'CLASSLIST_ROOM_COMMENT_REVIEW',
                ),
                true,
            ),
            // 2 Weeks before End, travel & ctw not set
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => null,
                    'room'     => 'Y',
                    'comment'  => 'Booked by 5/5/15',
                )),
                $statsReportLast2Weeks,
                array(
                    'CLASSLIST_TRAVEL_COMMENT_REVIEW',
                    'CLASSLIST_TRAVEL_ROOM_CTW_MISSING',
                ),
                false,
            ),
            // 2 Weeks before End, room & ctw not set
            array(
                $this->arrayToObject(array(
                    'wd'       => null,
                    'wbo'      => null,
                    'xferOut'  => null,
                    'ctw'      => null,
                    'travel'   => 'Y',
                    'room'     => null,
                    'comment'  => 'Booked by 5/5/15',
                )),
                $statsReportLast2Weeks,
                array(
                    'CLASSLIST_ROOM_COMMENT_REVIEW',
                    'CLASSLIST_TRAVEL_ROOM_CTW_MISSING',
                ),
                false,
            ),
        );
    }
}