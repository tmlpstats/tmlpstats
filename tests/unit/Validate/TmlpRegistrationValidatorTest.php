<?php
namespace TmlpStatsTests\Validate;

use TmlpStats\Validate\TmlpRegistrationValidator;
use Carbon\Carbon;
use stdClass;

class TmlpRegistrationValidatorTest extends ValidatorTestAbstract
{
    protected $testClass = 'TmlpStats\Validate\TmlpRegistrationValidator';

    protected $dataFields = array(
        'firstName',
        'lastName',
        'weekendReg',
        'incomingWeekend',
        'incomingTeamYear',
        'isReviewer',
        'bef',
        'dur',
        'aft',
        'appOut',
        'appIn',
        'appr',
        'wd',
        'regDate',
        'appOutDate',
        'appInDate',
        'apprDate',
        'wdDate',
        'committedTeamMemberName',
        'travel',
        'room',
        'tmlpRegistrationId',
        'statsReportId',
    );

    //
    // populateValidators()
    //
    public function testPopulateValidatorsSetsValidatorsForEachInput()
    {
        $data = new stdClass;
        $data->incomingTeamYear = 2;
        $data->bef = 2;
        $data->dur = null;
        $data->aft = null;

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
                    'firstName'               => null,
                    'lastName'                => null,
                    'weekendReg'              => null,
                    'incomingWeekend'         => null,
                    'incomingTeamYear'        => null,
                    'isReviewer'              => null,
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => null,
                    'appOut'                  => null,
                    'appIn'                   => null,
                    'appr'                    => null,
                    'wd'                      => null,
                    'regDate'                 => null,
                    'appOutDate'              => null,
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'wdDate'                  => null,
                    'committedTeamMemberName' => null,
                    'travel'                  => null,
                    'room'                    => null,
                    'tmlpRegistrationId'      => null,
                    'statsReportId'           => null,
                )),
                array(
                    array('INVALID_VALUE', 'First Name', '[empty]'),
                    array('INVALID_VALUE', 'Last Name', '[empty]'),
                    array('INVALID_VALUE', 'Weekend Reg', '[empty]'),
                    array('INVALID_VALUE', 'Incoming Weekend', '[empty]'),
                    array('INVALID_VALUE', 'Incoming Team Year', '[empty]'),
                    array('INVALID_VALUE', 'Is Reviewer', '[empty]'),
                    array('INVALID_VALUE', 'Reg Date', '[empty]'),
                    array('INVALID_VALUE', 'Tmlp Registration Id', '[empty]'),
                    array('INVALID_VALUE', 'Stats Report Id', '[empty]'),
                ),
                false,
            ),
            // Test Valid (Variable 1)
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'before',
                    'incomingWeekend'         => 'current',
                    'incomingTeamYear'        => '1',
                    'isReviewer'              => '0',
                    'bef'                     => '1',
                    'dur'                     => '1',
                    'aft'                     => '1',
                    'appOut'                  => '1',
                    'appIn'                   => '1',
                    'appr'                    => '1',
                    'wd'                      => '1 AP',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(),
                true,
            ),
            // Test Valid (Variable 2)
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'during',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => '2',
                    'dur'                     => '2',
                    'aft'                     => '2',
                    'appOut'                  => '2',
                    'appIn'                   => '2',
                    'appr'                    => '2',
                    'wd'                      => '2 FIN',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(),
                true,
            ),
            // Test Valid (Variable 3)
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(),
                true,
            ),

            // Test Invalid First Name
            array(
                $this->arrayToObject(array(
                    'firstName'               => '',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'before',
                    'incomingWeekend'         => 'current',
                    'incomingTeamYear'        => '1',
                    'isReviewer'              => '0',
                    'bef'                     => '1',
                    'dur'                     => '1',
                    'aft'                     => '1',
                    'appOut'                  => '1',
                    'appIn'                   => '1',
                    'appr'                    => '1',
                    'wd'                      => '1 AP',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'First Name', '[empty]'),
                ),
                false,
            ),
            // Test Invalid Last Name
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => '',
                    'weekendReg'              => 'before',
                    'incomingWeekend'         => 'current',
                    'incomingTeamYear'        => '1',
                    'isReviewer'              => '0',
                    'bef'                     => '1',
                    'dur'                     => '1',
                    'aft'                     => '1',
                    'appOut'                  => '1',
                    'appIn'                   => '1',
                    'appr'                    => '1',
                    'wd'                      => '1 AP',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Last Name', '[empty]'),
                ),
                false,
            ),
            // Test Invalid weekendReg
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'sometime',
                    'incomingWeekend'         => 'current',
                    'incomingTeamYear'        => '1',
                    'isReviewer'              => '0',
                    'bef'                     => '1',
                    'dur'                     => '1',
                    'aft'                     => '1',
                    'appOut'                  => '1',
                    'appIn'                   => '1',
                    'appr'                    => '1',
                    'wd'                      => '1 AP',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Weekend Reg', 'sometime'),
                ),
                false,
            ),
            // Test Invalid incomingWeekend
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'before',
                    'incomingWeekend'         => 'past',
                    'incomingTeamYear'        => '1',
                    'isReviewer'              => '0',
                    'bef'                     => '1',
                    'dur'                     => '1',
                    'aft'                     => '1',
                    'appOut'                  => '1',
                    'appIn'                   => '1',
                    'appr'                    => '1',
                    'wd'                      => '1 AP',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Incoming Weekend', 'past'),
                ),
                false,
            ),

            // Test Mismatched Incoming year 1 & R
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '1',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Bef', 'R'),
                    array('INVALID_VALUE', 'Dur', 'R'),
                    array('INVALID_VALUE', 'Aft', 'R'),
                    array('INVALID_VALUE', 'App Out', 'R'),
                    array('INVALID_VALUE', 'App In', 'R'),
                    array('INVALID_VALUE', 'Appr', 'R'),
                ),
                false,
            ),
            // Test Mismatched Incoming year 1 & 2
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '1',
                    'isReviewer'              => '0',
                    'bef'                     => '2',
                    'dur'                     => '2',
                    'aft'                     => '2',
                    'appOut'                  => '2',
                    'appIn'                   => '2',
                    'appr'                    => '2',
                    'wd'                      => '2 WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Bef', '2'),
                    array('INVALID_VALUE', 'Dur', '2'),
                    array('INVALID_VALUE', 'Aft', '2'),
                    array('INVALID_VALUE', 'App Out', '2'),
                    array('INVALID_VALUE', 'App In', '2'),
                    array('INVALID_VALUE', 'Appr', '2'),
                ),
                false,
            ),
            // Test Mismatched Incoming year 2 & 1
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => '1',
                    'dur'                     => '1',
                    'aft'                     => '1',
                    'appOut'                  => '1',
                    'appIn'                   => '1',
                    'appr'                    => '1',
                    'wd'                      => '1 WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Bef', '1'),
                    array('INVALID_VALUE', 'Dur', '1'),
                    array('INVALID_VALUE', 'Aft', '1'),
                    array('INVALID_VALUE', 'App Out', '1'),
                    array('INVALID_VALUE', 'App In', '1'),
                    array('INVALID_VALUE', 'Appr', '1'),
                ),
                false,
            ),
            // Test Incoming year 2 is valid with R
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(),
                true,
            ),

            // Test Invalid regDate
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => 'asdf',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Reg Date', 'asdf'),
                ),
                false,
            ),
            // Test Invalid appOutDate
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => 'asdf',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'App Out Date', 'asdf'),
                ),
                false,
            ),
            // Test Invalid appInDate
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => 'asdf',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'App In Date', 'asdf'),
                ),
                false,
            ),
            // Test Invalid apprDate
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => 'asdf',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Appr Date', 'asdf'),
                ),
                false,
            ),
            // Test Invalid wdDate
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => 'asdf',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Wd Date', 'asdf'),
                ),
                false,
            ),


            // Test Invalid committedTeamMemberName
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'H',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Travel', 'H'),
                ),
                false,
            ),
            // Test Invalid committedTeamMemberName
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'H',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Room', 'H'),
                ),
                false,
            ),


            // Test Invalid tmlpRegistrationid
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 'asdf',
                    'statsReportId'           => 5678,
                )),
                array(
                    array('INVALID_VALUE', 'Tmlp Registration Id', 'asdf'),
                ),
                false,
            ),
            // Test Invalid statsReportId
            array(
                $this->arrayToObject(array(
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
                    'isReviewer'              => '0',
                    'bef'                     => 'R',
                    'dur'                     => 'R',
                    'aft'                     => 'R',
                    'appOut'                  => 'R',
                    'appIn'                   => 'R',
                    'appr'                    => 'R',
                    'wd'                      => 'R WB',
                    'regDate'                 => '2015-01-01',
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => '2015-01-01',
                    'wdDate'                  => '2015-01-01',
                    'committedTeamMemberName' => 'Jeff B',
                    'travel'                  => 'Y',
                    'room'                    => 'y',
                    'tmlpRegistrationId'      => 1234,
                    'statsReportId'           => -1234,
                )),
                array(
                    array('INVALID_VALUE', 'Stats Report Id', '-1234'),
                ),
                false,
            ),
        );
    }

    //
    // validate()
    //

    /**
    * @dataProvider providerValidateExtended
    */
    public function testValidateExtended($returnValues, $expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'validateWeekendReg',
            'validateApprovalProcess',
            'validateDates',
            'validateComment',
            'validateTravel',
        ));
        $validator->expects($this->once())
                  ->method('validateWeekendReg')
                  ->will($this->returnValue($returnValues['validateWeekendReg']));
        $validator->expects($this->once())
                  ->method('validateApprovalProcess')
                  ->will($this->returnValue($returnValues['validateApprovalProcess']));
        $validator->expects($this->once())
                  ->method('validateDates')
                  ->will($this->returnValue($returnValues['validateDates']));
        $validator->expects($this->once())
                  ->method('validateComment')
                  ->will($this->returnValue($returnValues['validateComment']));
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
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => true,
                    'validateDates'           => true,
                    'validateComment'         => true,
                    'validateTravel'          => true,
                ),
                true,
            ),
            // validateWeekendReg fails
            array(
                array(
                    'validateWeekendReg'      => false,
                    'validateApprovalProcess' => true,
                    'validateDates'           => true,
                    'validateComment'         => true,
                    'validateTravel'          => true,
                ),
                false,
            ),
            // validateApprovalProcess fails
            array(
                array(
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => false,
                    'validateDates'           => true,
                    'validateComment'         => true,
                    'validateTravel'          => true,
                ),
                false,
            ),
            // validateDates fails
            array(
                array(
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => true,
                    'validateDates'           => false,
                    'validateComment'         => true,
                    'validateTravel'          => true,
                ),
                false,
            ),
            // validateComment fails
            array(
                array(
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => true,
                    'validateDates'           => true,
                    'validateComment'         => false,
                    'validateTravel'          => true,
                ),
                false,
            ),
            // validateTravel fails
            array(
                array(
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => true,
                    'validateDates'           => true,
                    'validateComment'         => true,
                    'validateTravel'          => false,
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
            'validateWeekendReg',
            'validateApprovalProcess',
            'validateDates',
            'validateComment',
            'validateTravel',
        ));
        $validator->expects($this->once())
                  ->method('validateWeekendReg')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateApprovalProcess')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateDates')
                  ->will($this->returnValue(true));
        $validator->expects($this->once())
                  ->method('validateComment')
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

    //
    // validateWeekendReg()
    //
    public function testValidateWeekendRegPassesForExactlyOne()
    {
        $data = new stdClass;
        $data->incomingTeamYear = 2;
        $data->bef = 2;
        $data->dur = null;
        $data->aft = null;

        $validator = $this->getObjectMock();
        $validator->expects($this->never())
                  ->method('addMessage');

        $result = $validator->validateWeekendReg($data);

        $this->assertTrue($result);
    }

    /**
    * @dataProvider providerValidateWeekendRegFails
    */
    public function testValidateWeekendRegFailsWhenBothBefAndDurSet($data)
    {
        $validator = $this->getObjectMock();
        $validator->expects($this->once())
                  ->method('addMessage')
                  ->with(
                        $this->equalTo('TMLPREG_MULTIPLE_WEEKENDREG'),
                        $this->equalTo($data->incomingTeamYear)
                    );

        $result = $validator->validateWeekendReg($data);

        $this->assertFalse($result);
    }

    public function providerValidateWeekendRegFails()
    {
        return array(
            // validateWeekendReg Fails When Both Bef And Dur Set
            array(
                $this->arrayToObject(array(
                    'incomingTeamYear' => 2,
                    'bef' => 2,
                    'dur' => 2,
                    'aft' => null,
                )),
            ),
            // validateWeekendReg Fails When Both Bef And Aft Set
            array(
                $this->arrayToObject(array(
                    'incomingTeamYear' => 2,
                    'bef' => 2,
                    'dur' => null,
                    'aft' => 2,
                )),
            ),
            // validateWeekendReg Fails When Both Dur And Aft Set
            array(
                $this->arrayToObject(array(
                    'incomingTeamYear' => 2,
                    'bef' => null,
                    'dur' => 2,
                    'aft' => 2,
                )),
            ),
        );
    }

    //
    // validateApprovalProcess()
    //

    /**
    * @dataProvider providerValidateApprovalProcess
    */
    public function testValidateApprovalProcess($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock();

        if (!$messages) {
            $validator->expects($this->never())
                      ->method('addMessage');
        } else {
            for ($i = 0; $i < count($messages); $i++) {
                if (is_array($messages[$i])) {
                    // Some messages have multiple arguments
                    $validator->expects($this->at($i))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1], $messages[$i][2]);
                } else {
                    // Other messages have only 1
                    $validator->expects($this->at($i))
                              ->method('addMessage')
                              ->with($messages[$i]);
                }
            }
        }
        $result = $validator->validateApprovalProcess($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateApprovalProcess()
    {
        return array(
            // Withdraw and no other steps complete
            array(
                $this->arrayToObject(array(
                    'wd'                      => '1 T',
                    'wdDate'                  => '2015-01-28',
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => null,
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                array(),
                true,
            ),
            // Withdraw and all steps complete
            array(
                $this->arrayToObject(array(
                    'wd'                      => '1 T',
                    'wdDate'                  => '2015-01-28',
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-13',
                    'appIn'                   => null,
                    'appInDate'               => '2015-01-20',
                    'appr'                    => null,
                    'apprDate'                => '2015-01-27',
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => null,
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                array(),
                true,
            ),
            // Withdraw and missing wd
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => '2015-01-01',
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                array(
                    array('TMLPREG_WD_MISSING', 'wd', 1),
                ),
                false,
            ),
            // Withdraw and missing date
            array(
                $this->arrayToObject(array(
                    'wd'                      => '1 T',
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                array(
                    array('TMLPREG_WD_DATE_MISSING', 'wd', 1),
                ),
                false,
            ),
            // Withdraw and mismatched team year
            array(
                $this->arrayToObject(array(
                    'wd'                      => '1 T',
                    'wdDate'                  => '2015-01-01',
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 2,
                    'bef'                     => 2,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                array('TMLPREG_WD_DOESNT_MATCH_INCOMING_YEAR'),
                false,
            ),
            // Withdraw and appOut set
            array(
                $this->arrayToObject(array(
                    'wd'                      => '1 T',
                    'wdDate'                  => '2015-01-01',
                    'appOut'                  => 1,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                array(
                    array('TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR', 'wd', 1),
                ),
                false,
            ),
            // Withdraw and appIn set
            array(
                $this->arrayToObject(array(
                    'wd'                      => '1 T',
                    'wdDate'                  => '2015-01-01',
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => 1,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                array(
                    array('TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR', 'wd', 1),
                ),
                false,
            ),
            // Withdraw and appr set
            array(
                $this->arrayToObject(array(
                    'wd'                      => '1 T',
                    'wdDate'                  => '2015-01-01',
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => 1,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                array(
                    array('TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR', 'wd', 1),
                ),
                false,
            ),

            // Approved
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => null,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => 2,
                    'apprDate'                => '2015-01-28',
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(),
                true,
            ),
            // Approved and missing appr
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => null,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => null,
                    'apprDate'                => '2015-01-28',
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(
                    array('TMLPREG_APPR_MISSING', 'appr', 1),
                ),
                false,
            ),
            // Approved and missing apprDate
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => null,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => 1,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(
                    array('TMLPREG_APPR_DATE_MISSING', 'appr', 1),
                ),
                false,
            ),
            // Approved and missing appInDate
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => 1,
                    'apprDate'                => '2015-01-28',
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array('TMLPREG_APPR_MISSING_APPIN_DATE'),
                false,
            ),
            // Approved and missing appOutDate
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => 1,
                    'apprDate'                => '2015-01-28',
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array('TMLPREG_APPR_MISSING_APPOUT_DATE'),
                false,
            ),
            // Approved and appOut set
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => 1,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => null,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => 1,
                    'apprDate'                => '2015-01-28',
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(
                    array('TMLPREG_APPR_ONLY_ONE_YEAR_INDICATOR', 'appr', 1),
                ),
                false,
            ),
            // Approved and appIn set
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => 1,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => 1,
                    'apprDate'                => '2015-01-28',
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(
                    array('TMLPREG_APPR_ONLY_ONE_YEAR_INDICATOR', 'appr', 1),
                ),
                false,
            ),

            // App In
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => 1,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(),
                true,
            ),
            // App In and missing appIn
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => null,
                    'appInDate'               => '2015-01-28',
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(
                    array('TMLPREG_APPIN_MISSING', 'in', 1),
                ),
                false,
            ),
            // App In and missing appInDate
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => 1,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(
                    array('TMLPREG_APPIN_DATE_MISSING', 'in', 1),
                ),
                false,
            ),
            // App In and missing appOutDate
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => 1,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array('TMLPREG_APPIN_MISSING_APPOUT_DATE'),
                false,
            ),
            // App In and appOut set
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => 1,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => 1,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(
                    array('TMLPREG_APPIN_ONLY_ONE_YEAR_INDICATOR', 'in', 1),
                ),
                false,
            ),

            // App Out
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => 1,
                    'appInDate'               => '2015-01-21',
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(),
                true,
            ),
            // App Out and missing appOut
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(
                    array('TMLPREG_APPOUT_MISSING', 'out', 1),
                ),
                false,
            ),
            // App Out and missing appOutDate
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => 1,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => 1,
                )),
                array(
                    array('TMLPREG_APPOUT_DATE_MISSING', 'out', 1),
                ),
                false,
            ),

            // No approval steps complete
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => 'Keith Stone',
                    'incomingTeamYear'        => null,
                )),
                array(),
                true,
            ),
            // Missing committed team member
            array(
                $this->arrayToObject(array(
                    'wd'                      => null,
                    'wdDate'                  => null,
                    'appOut'                  => null,
                    'appOutDate'              => null,
                    'appIn'                   => null,
                    'appInDate'               => null,
                    'appr'                    => null,
                    'apprDate'                => null,
                    'committedTeamMemberName' => null,
                    'incomingTeamYear'        => null,
                )),
                array('TMLPREG_NO_COMMITTED_TEAM_MEMBER'),
                false,
            ),
        );
    }


    //
    // validateDates()
    //

    /**
    * @dataProvider providerValidateDates
    */
    public function testValidateDates($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'getStatsReport',
            'addMessage',
        ));
        $validator->expects($this->once())
                  ->method('getStatsReport')
                  ->will($this->returnValue($statsReport));

        if (!$messages) {
            $validator->expects($this->never())
                      ->method('addMessage');
        } else {
            $sequence = 1;
            for ($i = 0; $i < count($messages); $i++) {

                if (!is_array($messages[$i])) {
                    // Other messages have only 1
                    $validator->expects($this->at($i+$sequence))
                              ->method('addMessage')
                              ->with($messages[$i]);
                } else if (count($messages[$i]) == 2) {
                    // Some messages have multiple arguments
                    $validator->expects($this->at($i+$sequence))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1]);
                } else {
                    // Some messages have multiple arguments
                    $validator->expects($this->at($i+$sequence))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1], $messages[$i][2]);
                }
            }
        }
        $result = $validator->validateDates($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateDates()
    {
        $statsReport = new stdClass;
        $statsReport->quarter = new stdClass;

        $statsReport->reportingDate = Carbon::createFromDate(2015, 1, 21);
        $statsReport->quarter->startWeekendDate = Carbon::createFromDate(2014, 11, 14);

        return array(
            // Withdraw date OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => '2015-01-21',
                    'appOutDate'              => null,
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Withdraw date invalid and doesn't blow up
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => 'asdf',
                    'appOutDate'              => null,
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Withdraw and wdDate before regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => '2015-01-01',
                    'appOutDate'              => null,
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_WD_DATE_BEFORE_REG_DATE'),
                false,
            ),
            // Withdraw and approve dates OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => '2015-01-21',
                    'appOutDate'              => null,
                    'appInDate'               => null,
                    'apprDate'                => '2015-01-14',
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Withdraw and wdDate before apprDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => '2015-01-14',
                    'appOutDate'              => null,
                    'appInDate'               => null,
                    'apprDate'                => '2015-01-21',
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_WD_DATE_BEFORE_APPR_DATE'),
                false,
            ),
            // Withdraw and appIn dates OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => '2015-01-21',
                    'appOutDate'              => null,
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Withdraw and wdDate before appInDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => '2015-01-14',
                    'appOutDate'              => null,
                    'appInDate'               => '2015-01-21',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_WD_DATE_BEFORE_APPIN_DATE'),
                false,
            ),
            // Withdraw and appOut dates OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => '2015-01-21',
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Withdraw and wdDate before appOutDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => '2015-01-14',
                    'appOutDate'              => '2015-01-21',
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_WD_DATE_BEFORE_APPOUT_DATE'),
                false,
            ),

            // Approved date OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => '2015-01-21',
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Approved invalid and doesn't blow up
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => 'asdf',
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Approved and apprDate before regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => '2015-01-01',
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_APPR_DATE_BEFORE_REG_DATE'),
                false,
            ),
            // Approved and appIn dates OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => '2015-01-21',
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Approved and apprDate before appInDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => '2015-01-13',
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_APPR_DATE_BEFORE_APPIN_DATE'),
                false,
            ),
            // Approved and appOut dates OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => '2015-01-21',
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Approved and apprDate before appOutDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-08',
                    'apprDate'                => '2015-01-08',
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE'),
                false,
            ),

            // AppIn date OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // AppIn date invalid and doesn't blow up
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => 'asdf',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // AppIn and appInDate before regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-01',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_APPIN_DATE_BEFORE_REG_DATE'),
                false,
            ),
            // AppIn and appOut dates OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // AppIn and appInDate before appOutDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => '2015-01-08',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_APPIN_DATE_BEFORE_APPOUT_DATE'),
                false,
            ),

            // AppOut date OK
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-09',
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // AppOut date invalid and doesn't blow up
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => 'asdf',
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // AppOut and appOutDate before regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-01',
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_APPOUT_DATE_BEFORE_REG_DATE'),
                false,
            ),

            // RegDate before weekend start
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2014-11-07',
                    'appInDate'               => '2014-11-07',
                    'apprDate'                => '2014-11-07',
                    'regDate'                 => '2014-11-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                $statsReport,
                array(),
                true,
            ),
            // RegDate invalid and doesn't blow up
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2014-11-07',
                    'appInDate'               => '2014-11-07',
                    'apprDate'                => '2014-11-07',
                    'regDate'                 => 'asdf',
                    'incomingWeekend'         => 'current',
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                $statsReport,
                array(),
                true,
            ),
            // RegDate not before weekend start
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2014-11-21',
                    'appInDate'               => '2014-11-21',
                    'apprDate'                => '2014-11-21',
                    'regDate'                 => '2014-11-21',
                    'incomingWeekend'         => 'current',
                    'bef'                     => 1,
                    'dur'                     => null,
                    'aft'                     => null,
                )),
                $statsReport,
                array(
                    array('TMLPREG_BEF_REG_DATE_NOT_BEFORE_WEEKEND', 'Nov 14, 2014', 1),
                ),
                false,
            ),
            // RegDate during weekend start
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2014-11-16',
                    'appInDate'               => '2014-11-16',
                    'apprDate'                => '2014-11-16',
                    'regDate'                 => '2014-11-16',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => 2,
                    'aft'                     => null,
                )),
                $statsReport,
                array(),
                true,
            ),
            // RegDate not during weekend start
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2014-11-21',
                    'appInDate'               => '2014-11-21',
                    'apprDate'                => '2014-11-21',
                    'regDate'                 => '2014-11-21',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => 2,
                    'aft'                     => null,
                )),
                $statsReport,
                array(
                    array('TMLPREG_DUR_REG_DATE_NOT_DURING_WEEKEND', 'Nov 14, 2014', 2),
                ),
                false,
            ),
            // RegDate after weekend start
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2014-11-21',
                    'appInDate'               => '2014-11-21',
                    'apprDate'                => '2014-11-21',
                    'regDate'                 => '2014-11-21',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 'R',
                )),
                $statsReport,
                array(),
                true,
            ),
            // RegDate not after weekend start
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2014-11-07',
                    'appInDate'               => '2014-11-07',
                    'apprDate'                => '2014-11-07',
                    'regDate'                 => '2014-11-07',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 'R',
                )),
                $statsReport,
                array(
                    array('TMLPREG_AFT_REG_DATE_NOT_AFTER_WEEKEND', 'Nov 14, 2014', 'R'),
                ),
                false,
            ),

            // AppOut within 2 days of regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-15',
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-14',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // AppOut not within 2 days of regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => null,
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-14',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(
                    array('TMLPREG_APPOUT_LATE', TmlpRegistrationValidator::MAX_DAYS_TO_SEND_APPLICATION_OUT),
                ),
                true,
            ),
            // AppIn within 14 days of regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-15',
                    'appInDate'               => '2015-01-21',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-14',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // AppIn not within 14 days of regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-02',
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-01',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(
                    array('TMLPREG_APPIN_LATE', TmlpRegistrationValidator::MAX_DAYS_TO_APPROVE_APPLICATION),
                ),
                true,
            ),
            // Appr within 14 days of regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-15',
                    'appInDate'               => '2015-01-18',
                    'apprDate'                => '2015-01-21',
                    'regDate'                 => '2015-01-14',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Appr not within 14 days of regDate
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-02',
                    'appInDate'               => '2015-01-03',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-01',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(
                    array('TMLPREG_APPR_LATE', TmlpRegistrationValidator::MAX_DAYS_TO_APPROVE_APPLICATION),
                ),
                true,
            ),

            // RegDate in future
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => null,
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-02-01',
                    'incomingWeekend'         => 'future',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_REG_DATE_IN_FUTURE'),
                false,
            ),
            // WdDate in future
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => '2015-02-14',
                    'appOutDate'              => '2015-01-14',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => '2015-01-14',
                    'regDate'                 => '2015-01-14',
                    'incomingWeekend'         => 'future',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_WD_DATE_IN_FUTURE'),
                false,
            ),
            // ApprDate in future
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appInDate'               => '2015-01-14',
                    'apprDate'                => '2015-02-14',
                    'regDate'                 => '2015-01-14',
                    'incomingWeekend'         => 'future',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_APPR_DATE_IN_FUTURE'),
                false,
            ),
            // AppInDate in future
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-01-14',
                    'appInDate'               => '2015-02-14',
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-14',
                    'incomingWeekend'         => 'future',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_APPIN_DATE_IN_FUTURE'),
                false,
            ),
            // AppOutDate in future
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => null,
                    'appOutDate'              => '2015-02-14',
                    'appInDate'               => null,
                    'apprDate'                => null,
                    'regDate'                 => '2015-01-14',
                    'incomingWeekend'         => 'future',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array('TMLPREG_APPOUT_DATE_IN_FUTURE'),
                false,
            ),

            // Invalid dates don't blow up
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => 'asdf',
                    'appOutDate'              => 'asdf',
                    'appInDate'               => 'asdf',
                    'apprDate'                => 'asdf',
                    'regDate'                 => 'asdf',
                    'incomingWeekend'         => 'current',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
            // Invalid dates don't blow up
            array(
                $this->arrayToObject(array(
                    'wdDate'                  => 'asdf',
                    'appOutDate'              => 'asdf',
                    'appInDate'               => 'asdf',
                    'apprDate'                => 'asdf',
                    'regDate'                 => 'asdf',
                    'incomingWeekend'         => 'future',
                    'bef'                     => null,
                    'dur'                     => null,
                    'aft'                     => 1,
                )),
                $statsReport,
                array(),
                true,
            ),
        );
    }

    //
    // validateComment()
    //

    /**
    * @dataProvider providerValidateCommentPasses
    */
    public function testValidateCommentPasses($data)
    {
        $validator = $this->getObjectMock();
        $validator->expects($this->never())
                  ->method('addMessage');

        $result = $validator->validateComment($data);

        $this->assertTrue($result);
    }

    public function providerValidateCommentPasses()
    {
        return array(
            // validateComment Passes When Incoming Weekend Equals Current
            array(
                $this->arrayToObject(array(
                    'comment'         => null,
                    'incomingWeekend' => 'current',
                    'wd'              => null,
                )),
            ),
            // validateComment Passes When Comment Provided
            array(
                $this->arrayToObject(array(
                    'comment'         => 'Nov 2015',
                    'incomingWeekend' => 'future',
                    'wd'              => null,
                )),
            ),
            // validateComment Passes Ignored When Wd Set
            array(
                $this->arrayToObject(array(
                    'comment'         => null,
                    'incomingWeekend' => 'future',
                    'wd'              => 2,
                )),
            ),
        );
    }

    public function testvalidateCommentFailsWhenNoCommentProvidedForFutureIncomingWeekend()
    {
        $data = new stdClass;
        $data->comment         = null;
        $data->incomingWeekend = 'future';
        $data->wd              = null;

        $validator = $this->getObjectMock();
        $validator->expects($this->once())
                  ->method('addMessage')
                  ->with($this->equalTo('TMLPREG_COMMENT_MISSING_FUTURE_WEEKEND'));

        $result = $validator->validateComment($data);

        $this->assertFalse($result);
    }

    //
    // validateTravel()
    //

    /**
    * @dataProvider providerValidateTravelPasses
    */
    public function testValidateTravelPasses($data, $statsReport)
    {
        $statsReport->reportingDate = Carbon::createFromDate(2015, 4, 10);

        $validator = $this->getObjectMock(array('getStatsReport'));
        $validator->expects($this->once())
                  ->method('getStatsReport')
                  ->will($this->returnValue($statsReport));
        $validator->expects($this->never())
                  ->method('addMessage');

        $result = $validator->validateTravel($data);

        $this->assertTrue($result);
    }

    public function providerValidateTravelPasses()
    {
        $statsReport = new stdClass;
        $statsReport->quarter = new stdClass;

        $statsReport->reportingDate = Carbon::createFromDate(2015, 5, 8);
        $statsReport->quarter->classroom2Date = Carbon::createFromDate(2015, 4, 17);

        return array(
            // validateTravel Passes When Before Second Classroom
            array(
                $this->arrayToObject(array(
                    'travel'          => null,
                    'room'            => null,
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                )),
                $statsReport,
            ),
            // validateTravel Passes When Travel And Room Complete
            array(
                $this->arrayToObject(array(
                    'travel'          => 'Y',
                    'room'            => 'Y',
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                )),
                $statsReport,
            ),
            // validateTravel Passes When Comments Provided
            array(
                $this->arrayToObject(array(
                    'travel'          => null,
                    'room'            => null,
                    'comment'         => 'Travel and rooming booked by May 4',
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                )),
                $statsReport,
            ),
        );
    }

    /**
    * @dataProvider providerValidateTravelIgnored
    */
    public function testValidateTravelIgnoredWhenWdSet($data)
    {
        $validator = $this->getObjectMock(array('getStatsReport'));
        $validator->expects($this->never())
                  ->method('getStatsReport');

        $result = $validator->validateTravel($data);

        $this->assertTrue($result);
    }

    public function providerValidateTravelIgnored()
    {
        return array(
            // validateTravel Ignored When Wd Set
            array(
                $this->arrayToObject(array(
                    'travel'          => null,
                    'room'            => null,
                    'comment'         => null,
                    'wd'              => 1,
                    'incomingWeekend' => 'current',
                )),
            ),
            // validateTravel Ignored When Incoming Weekend Equals Future
            array(
                $this->arrayToObject(array(
                    'travel'          => null,
                    'room'            => null,
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'future',
                )),
            ),
        );
    }

    /**
    * @dataProvider providerValidateTravelFails
    */
    public function testValidateTravelFails($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(array(
            'getStatsReport',
            'addMessage',
        ));
        $validator->expects($this->once())
                  ->method('getStatsReport')
                  ->will($this->returnValue($statsReport));

        for ($i = 0; $i < count($messages); $i++) {
            $validator->expects($this->at($i+1))
                      ->method('addMessage')
                      ->with($messages[$i]);
        }

        $result = $validator->validateTravel($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTravelFails()
    {
        $statsReport = new stdClass;
        $statsReport->quarter = new stdClass;

        $statsReport->reportingDate = Carbon::createFromDate(2015, 5, 8);
        $statsReport->quarter->classroom2Date = Carbon::createFromDate(2015, 4, 17);
        $statsReport->quarter->endWeekendDate = Carbon::createFromDate(2015, 5, 29);

        $statsReportLastTwoWeeks = clone $statsReport;
        $statsReportLastTwoWeeks->reportingDate = Carbon::createFromDate(2015, 5, 15);

        return array(
            // ValidateTravel Fails When Missing Travel
            array(
                $this->arrayToObject(array(
                    'travel'          => null,
                    'room'            => 'Y',
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                    'appr'            => null,
                )),
                $statsReport,
                array('TMLPREG_TRAVEL_COMMENT_MISSING'),
                false,
            ),
            // ValidateTravel Fails When Missing Room
            array(
                $this->arrayToObject(array(
                    'travel'          => 'Y',
                    'room'            => null,
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                    'appr'            => null,
                )),
                $statsReport,
                array('TMLPREG_ROOM_COMMENT_MISSING'),
                false,
            ),
            // ValidateTravel Throws Warning When Missing Room In Last 2 Weeks
            array(
                $this->arrayToObject(array(
                    'travel'          => null,
                    'room'            => 'Y',
                    'comment'         => 'By 5/15/2015',
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                    'appr'            => 1,
                )),
                $statsReportLastTwoWeeks,
                array('TMLPREG_TRAVEL_COMMENT_REVIEW', 'TMLPREG_TRAVEL_ROOM_CTW_COMMENT_REVIEW'),
                true,
            ),
            // ValidateTravel Throws Warning When Missing Room In Last 2 Weeks
            array(
                $this->arrayToObject(array(
                    'travel'          => 'Y',
                    'room'            => null,
                    'comment'         => 'By 5/15/2015',
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                    'appr'            => 1,
                )),
                $statsReportLastTwoWeeks,
                array('TMLPREG_ROOM_COMMENT_REVIEW', 'TMLPREG_TRAVEL_ROOM_CTW_COMMENT_REVIEW'),
                true,
            ),
        );
    }

    //
    // getWeekendReg()
    //
    /**
    * @dataProvider providerGetWeekendReg
    */
    public function testGetWeekendReg($data, $expected)
    {
        $validator = $this->getObjectMock();

        $this->assertEquals($expected, $validator->getWeekendReg($data));
    }

    public function providerGetWeekendReg()
    {
        return array(
            // Get bef
            array(
                $this->arrayToObject(array(
                    'bef' => 1,
                    'dur' => null,
                    'aft' => null,
                )),
                1,
            ),
            // Get dur
            array(
                $this->arrayToObject(array(
                    'bef' => null,
                    'dur' => 2,
                    'aft' => null,
                )),
                2,
            ),
            // Get aft
            array(
                $this->arrayToObject(array(
                    'bef' => null,
                    'dur' => null,
                    'aft' => 'R',
                )),
                'R',
            ),

        );
    }

    //
    // Helpers
    //
    protected function getObjectMock($methods = array())
    {
        $defaultMethods = array(
            'addMessage'
        );
        $methods = $this->mergeMockMethods($defaultMethods, $methods);

        return parent::getObjectMock($methods);
    }

    protected function arrayToObject($array)
    {
        $object = new stdClass;
        foreach ($array as $key => $value) {
            $object->$key = $value;
        }
        return $object;
    }
}
