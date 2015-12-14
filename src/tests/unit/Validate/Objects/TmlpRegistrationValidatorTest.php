<?php
namespace TmlpStats\Tests\Validate\Objects;

use TmlpStats\ModelCache;
use TmlpStats\Tests\Traits\MocksSettings;
use TmlpStats\Util;
use TmlpStats\Validate\Objects\TmlpRegistrationValidator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use stdClass;

class TmlpRegistrationValidatorTest extends ObjectsValidatorTestAbstract
{
    use MocksSettings;

    protected $testClass = TmlpRegistrationValidator::class;

    protected $dataFields = [
        'firstName',
        'lastName',
        'weekendReg',
        'incomingWeekend',
        'incomingTeamYear',
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
    ];

    //
    // populateValidators()
    //
    public function testPopulateValidatorsSetsValidatorsForEachInput($data = null)
    {
        $data                   = new stdClass;
        $data->incomingTeamYear = 2;
        $data->bef              = 2;
        $data->dur              = null;
        $data->aft              = null;

        parent::testPopulateValidatorsSetsValidatorsForEachInput($data);
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun($data, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock(['addMessage', 'validate']);

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

        Log::shouldReceive('error');

        $result = $validator->run($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerRun()
    {
        return [
            // Test Required
            [
                Util::arrayToObject([
                    'firstName'               => null,
                    'lastName'                => null,
                    'weekendReg'              => null,
                    'incomingWeekend'         => null,
                    'incomingTeamYear'        => null,
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
                ]),
                [
                    ['INVALID_VALUE', 'First Name', '[empty]'],
                    ['INVALID_VALUE', 'Last Name', '[empty]'],
                    ['INVALID_VALUE', 'Weekend Reg', '[empty]'],
                    ['INVALID_VALUE', 'Incoming Weekend', '[empty]'],
                    ['INVALID_VALUE', 'Incoming Team Year', '[empty]'],
                    ['INVALID_VALUE', 'Reg Date', '[empty]'],
                ],
                false,
            ],
            // Test Valid (Variable 1)
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'before',
                    'incomingWeekend'         => 'current',
                    'incomingTeamYear'        => '1',
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
                ]),
                [],
                true,
            ],
            // Test Valid (Variable 2)
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'during',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [],
                true,
            ],
            // Test Valid (Variable 3)
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [],
                true,
            ],

            // Test Invalid First Name
            [
                Util::arrayToObject([
                    'firstName'               => '',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'before',
                    'incomingWeekend'         => 'current',
                    'incomingTeamYear'        => '1',
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
                ]),
                [
                    ['INVALID_VALUE', 'First Name', '[empty]'],
                ],
                false,
            ],
            // Test Invalid Last Name
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => '',
                    'weekendReg'              => 'before',
                    'incomingWeekend'         => 'current',
                    'incomingTeamYear'        => '1',
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
                ]),
                [
                    ['INVALID_VALUE', 'Last Name', '[empty]'],
                ],
                false,
            ],
            // Test Invalid weekendReg
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'sometime',
                    'incomingWeekend'         => 'current',
                    'incomingTeamYear'        => '1',
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
                ]),
                [
                    ['INVALID_VALUE', 'Weekend Reg', 'sometime'],
                ],
                false,
            ],
            // Test Invalid incomingWeekend
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'before',
                    'incomingWeekend'         => 'past',
                    'incomingTeamYear'        => '1',
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
                ]),
                [
                    ['INVALID_VALUE', 'Incoming Weekend', 'past'],
                ],
                false,
            ],

            // Test Mismatched Incoming year 1 & R
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '1',
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
                ]),
                [
                    ['INVALID_VALUE', 'Bef', 'R'],
                    ['INVALID_VALUE', 'Dur', 'R'],
                    ['INVALID_VALUE', 'Aft', 'R'],
                    ['INVALID_VALUE', 'App Out', 'R'],
                    ['INVALID_VALUE', 'App In', 'R'],
                    ['INVALID_VALUE', 'Appr', 'R'],
                ],
                false,
            ],
            // Test Mismatched Incoming year 1 & 2
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '1',
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
                ]),
                [
                    ['INVALID_VALUE', 'Bef', '2'],
                    ['INVALID_VALUE', 'Dur', '2'],
                    ['INVALID_VALUE', 'Aft', '2'],
                    ['INVALID_VALUE', 'App Out', '2'],
                    ['INVALID_VALUE', 'App In', '2'],
                    ['INVALID_VALUE', 'Appr', '2'],
                ],
                false,
            ],
            // Test Mismatched Incoming year 2 & 1
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [
                    ['INVALID_VALUE', 'Bef', '1'],
                    ['INVALID_VALUE', 'Dur', '1'],
                    ['INVALID_VALUE', 'Aft', '1'],
                    ['INVALID_VALUE', 'App Out', '1'],
                    ['INVALID_VALUE', 'App In', '1'],
                    ['INVALID_VALUE', 'Appr', '1'],
                ],
                false,
            ],
            // Test Incoming year 2 is valid with R
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [],
                true,
            ],

            // Test Invalid regDate
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [
                    ['INVALID_VALUE', 'Reg Date', 'asdf'],
                ],
                false,
            ],
            // Test Invalid appOutDate
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [
                    ['INVALID_VALUE', 'App Out Date', 'asdf'],
                ],
                false,
            ],
            // Test Invalid appInDate
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [
                    ['INVALID_VALUE', 'App In Date', 'asdf'],
                ],
                false,
            ],
            // Test Invalid apprDate
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [
                    ['INVALID_VALUE', 'Appr Date', 'asdf'],
                ],
                false,
            ],
            // Test Invalid wdDate
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [
                    ['INVALID_VALUE', 'Wd Date', 'asdf'],
                ],
                false,
            ],


            // Test Invalid committedTeamMemberName
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [
                    ['INVALID_VALUE', 'Travel', 'H'],
                ],
                false,
            ],
            // Test Invalid committedTeamMemberName
            [
                Util::arrayToObject([
                    'firstName'               => 'Keith',
                    'lastName'                => 'Stone',
                    'weekendReg'              => 'after',
                    'incomingWeekend'         => 'future',
                    'incomingTeamYear'        => '2',
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
                ]),
                [
                    ['INVALID_VALUE', 'Room', 'H'],
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
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => true,
                    'validateDates'           => true,
                    'validateComment'         => true,
                    'validateTravel'          => true,
                ],
                true,
            ],
            // validateWeekendReg fails
            [
                [
                    'validateWeekendReg'      => false,
                    'validateApprovalProcess' => true,
                    'validateDates'           => true,
                    'validateComment'         => true,
                    'validateTravel'          => true,
                ],
                false,
            ],
            // validateApprovalProcess fails
            [
                [
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => false,
                    'validateDates'           => true,
                    'validateComment'         => true,
                    'validateTravel'          => true,
                ],
                false,
            ],
            // validateDates fails
            [
                [
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => true,
                    'validateDates'           => false,
                    'validateComment'         => true,
                    'validateTravel'          => true,
                ],
                false,
            ],
            // validateComment fails
            [
                [
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => true,
                    'validateDates'           => true,
                    'validateComment'         => false,
                    'validateTravel'          => true,
                ],
                false,
            ],
            // validateTravel fails
            [
                [
                    'validateWeekendReg'      => true,
                    'validateApprovalProcess' => true,
                    'validateDates'           => true,
                    'validateComment'         => true,
                    'validateTravel'          => false,
                ],
                false,
            ],
        ];
    }

    //
    // validateWeekendReg()
    //
    public function testValidateWeekendRegPassesForExactlyOne()
    {
        $data                   = new stdClass;
        $data->incomingTeamYear = 2;
        $data->bef              = 2;
        $data->dur              = null;
        $data->aft              = null;

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
        return [
            // validateWeekendReg Fails When Both Bef And Dur Set
            [
                Util::arrayToObject([
                    'incomingTeamYear' => 2,
                    'bef'              => 2,
                    'dur'              => 2,
                    'aft'              => null,
                ]),
            ],
            // validateWeekendReg Fails When Both Bef And Aft Set
            [
                Util::arrayToObject([
                    'incomingTeamYear' => 2,
                    'bef'              => 2,
                    'dur'              => null,
                    'aft'              => 2,
                ]),
            ],
            // validateWeekendReg Fails When Both Dur And Aft Set
            [
                Util::arrayToObject([
                    'incomingTeamYear' => 2,
                    'bef'              => null,
                    'dur'              => 2,
                    'aft'              => 2,
                ]),
            ],
        ];
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
        return [
            // Withdraw and no other steps complete
            [
                Util::arrayToObject([
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
                ]),
                [],
                true,
            ],
            // Withdraw and all steps complete
            [
                Util::arrayToObject([
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
                ]),
                [],
                true,
            ],
            // Withdraw and missing wd
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_WD_MISSING', 'wd', 1],
                ],
                false,
            ],
            // Withdraw and missing date
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_WD_DATE_MISSING', 'wd', 1],
                ],
                false,
            ],
            // Withdraw and mismatched team year
            [
                Util::arrayToObject([
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
                ]),
                ['TMLPREG_WD_DOESNT_MATCH_INCOMING_YEAR'],
                false,
            ],
            // Withdraw and appOut set
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR', 'wd', 1],
                ],
                false,
            ],
            // Withdraw and appIn set
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR', 'wd', 1],
                ],
                false,
            ],
            // Withdraw and appr set
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_WD_ONLY_ONE_YEAR_INDICATOR', 'wd', 1],
                ],
                false,
            ],

            // Approved
            [
                Util::arrayToObject([
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
                ]),
                [],
                true,
            ],
            // Approved and missing appr
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_APPR_MISSING', 'appr', 1],
                ],
                false,
            ],
            // Approved and missing apprDate
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_APPR_DATE_MISSING', 'appr', 1],
                ],
                false,
            ],
            // Approved and missing appInDate
            [
                Util::arrayToObject([
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
                ]),
                ['TMLPREG_APPR_MISSING_APPIN_DATE'],
                false,
            ],
            // Approved and missing appOutDate
            [
                Util::arrayToObject([
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
                ]),
                ['TMLPREG_APPR_MISSING_APPOUT_DATE'],
                false,
            ],
            // Approved and appOut set
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_APPR_ONLY_ONE_YEAR_INDICATOR', 'appr', 1],
                ],
                false,
            ],
            // Approved and appIn set
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_APPR_ONLY_ONE_YEAR_INDICATOR', 'appr', 1],
                ],
                false,
            ],

            // App In
            [
                Util::arrayToObject([
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
                ]),
                [],
                true,
            ],
            // App In and missing appIn
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_APPIN_MISSING', 'in', 1],
                ],
                false,
            ],
            // App In and missing appInDate
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_APPIN_DATE_MISSING', 'in', 1],
                ],
                false,
            ],
            // App In and missing appOutDate
            [
                Util::arrayToObject([
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
                ]),
                ['TMLPREG_APPIN_MISSING_APPOUT_DATE'],
                false,
            ],
            // App In and appOut set
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_APPIN_ONLY_ONE_YEAR_INDICATOR', 'in', 1],
                ],
                false,
            ],

            // App Out
            [
                Util::arrayToObject([
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
                ]),
                [],
                true,
            ],
            // App Out and missing appOut
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_APPOUT_MISSING', 'out', 1],
                ],
                false,
            ],
            // App Out and missing appOutDate
            [
                Util::arrayToObject([
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
                ]),
                [
                    ['TMLPREG_APPOUT_DATE_MISSING', 'out', 1],
                ],
                false,
            ],

            // No approval steps complete
            [
                Util::arrayToObject([
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
                ]),
                [],
                true,
            ],
            // Missing committed team member
            [
                Util::arrayToObject([
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
                ]),
                ['TMLPREG_NO_COMMITTED_TEAM_MEMBER'],
                false,
            ],
        ];
    }


    //
    // validateDates()
    //

    /**
     * @dataProvider providerValidateDates
     */
    public function testValidateDates($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);

        if (!$messages) {
            $validator->expects($this->never())
                      ->method('addMessage');
        } else {
            $sequence = 0;
            for ($i = 0; $i < count($messages); $i++) {

                if (!is_array($messages[$i])) {
                    // Other messages have only 1
                    $validator->expects($this->at($i + $sequence))
                              ->method('addMessage')
                              ->with($messages[$i]);
                } else if (count($messages[$i]) == 2) {
                    // Some messages have multiple arguments
                    $validator->expects($this->at($i + $sequence))
                              ->method('addMessage')
                              ->with($messages[$i][0], $messages[$i][1]);
                } else {
                    // Some messages have multiple arguments
                    $validator->expects($this->at($i + $sequence))
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
        $statsReport          = new stdClass;
        $statsReport->quarter = new stdClass;

        $statsReport->reportingDate             = Carbon::createFromDate(2015, 1, 21);
        $statsReport->quarter->startWeekendDate = Carbon::createFromDate(2014, 11, 14);

        return [
            // Withdraw date OK
            [
                Util::arrayToObject([
                    'wdDate'          => '2015-01-21',
                    'appOutDate'      => null,
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Withdraw date invalid and doesn't blow up
            [
                Util::arrayToObject([
                    'wdDate'          => 'asdf',
                    'appOutDate'      => null,
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Withdraw and wdDate before regDate
            [
                Util::arrayToObject([
                    'wdDate'          => '2015-01-01',
                    'appOutDate'      => null,
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_WD_DATE_BEFORE_REG_DATE'],
                false,
            ],
            // Withdraw and approve dates OK
            [
                Util::arrayToObject([
                    'wdDate'          => '2015-01-21',
                    'appOutDate'      => null,
                    'appInDate'       => null,
                    'apprDate'        => '2015-01-14',
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Withdraw and wdDate before apprDate
            [
                Util::arrayToObject([
                    'wdDate'          => '2015-01-14',
                    'appOutDate'      => null,
                    'appInDate'       => null,
                    'apprDate'        => '2015-01-21',
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_WD_DATE_BEFORE_APPR_DATE'],
                false,
            ],
            // Withdraw and appIn dates OK
            [
                Util::arrayToObject([
                    'wdDate'          => '2015-01-21',
                    'appOutDate'      => null,
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Withdraw and wdDate before appInDate
            [
                Util::arrayToObject([
                    'wdDate'          => '2015-01-14',
                    'appOutDate'      => null,
                    'appInDate'       => '2015-01-21',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_WD_DATE_BEFORE_APPIN_DATE'],
                false,
            ],
            // Withdraw and appOut dates OK
            [
                Util::arrayToObject([
                    'wdDate'          => '2015-01-21',
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Withdraw and wdDate before appOutDate
            [
                Util::arrayToObject([
                    'wdDate'          => '2015-01-14',
                    'appOutDate'      => '2015-01-21',
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_WD_DATE_BEFORE_APPOUT_DATE'],
                false,
            ],

            // Approved date OK
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => '2015-01-21',
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Approved invalid and doesn't blow up
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => 'asdf',
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Approved and apprDate before regDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => '2015-01-01',
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [
                    'TMLPREG_APPR_DATE_BEFORE_REG_DATE',
                    'TMLPREG_APPR_DATE_BEFORE_APPIN_DATE',
                    'TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE',
                ],
                false,
            ],
            // Approved and appIn dates OK
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => '2015-01-21',
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Approved and apprDate before appInDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => '2015-01-13',
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_APPR_DATE_BEFORE_APPIN_DATE'],
                false,
            ],
            // Approved and appOut dates OK
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => '2015-01-21',
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Approved and apprDate before appOutDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-08',
                    'apprDate'        => '2015-01-08',
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE'],
                false,
            ],

            // AppIn date OK
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // AppIn date invalid and doesn't blow up
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => 'asdf',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // AppIn and appInDate before regDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-01',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_APPIN_DATE_BEFORE_REG_DATE'],
                false,
            ],
            // AppIn and appOut dates OK
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // AppIn and appInDate before appOutDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => '2015-01-08',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_APPIN_DATE_BEFORE_APPOUT_DATE'],
                false,
            ],

            // AppOut date OK
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-09',
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // AppOut date invalid and doesn't blow up
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => 'asdf',
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // AppOut and appOutDate before regDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-01',
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_APPOUT_DATE_BEFORE_REG_DATE'],
                false,
            ],

            // RegDate before weekend start
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2014-11-07',
                    'appInDate'       => '2014-11-07',
                    'apprDate'        => '2014-11-07',
                    'regDate'         => '2014-11-07',
                    'incomingWeekend' => 'current',
                    'bef'             => 1,
                    'dur'             => null,
                    'aft'             => null,
                ]),
                $statsReport,
                [],
                true,
            ],
            // RegDate invalid and doesn't blow up
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2014-11-07',
                    'appInDate'       => '2014-11-07',
                    'apprDate'        => '2014-11-07',
                    'regDate'         => 'asdf',
                    'incomingWeekend' => 'current',
                    'bef'             => 1,
                    'dur'             => null,
                    'aft'             => null,
                ]),
                $statsReport,
                [],
                true,
            ],
            // RegDate not before weekend start
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2014-11-21',
                    'appInDate'       => '2014-11-21',
                    'apprDate'        => '2014-11-21',
                    'regDate'         => '2014-11-21',
                    'incomingWeekend' => 'current',
                    'bef'             => 1,
                    'dur'             => null,
                    'aft'             => null,
                ]),
                $statsReport,
                [
                    ['TMLPREG_BEF_REG_DATE_NOT_BEFORE_WEEKEND', 'Nov 14, 2014', 1],
                ],
                false,
            ],
            // RegDate during weekend start
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2014-11-16',
                    'appInDate'       => '2014-11-16',
                    'apprDate'        => '2014-11-16',
                    'regDate'         => '2014-11-16',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => 2,
                    'aft'             => null,
                ]),
                $statsReport,
                [],
                true,
            ],
            // RegDate not during weekend start
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2014-11-21',
                    'appInDate'       => '2014-11-21',
                    'apprDate'        => '2014-11-21',
                    'regDate'         => '2014-11-21',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => 2,
                    'aft'             => null,
                ]),
                $statsReport,
                [
                    ['TMLPREG_DUR_REG_DATE_NOT_DURING_WEEKEND', 'Nov 14, 2014', 2],
                ],
                false,
            ],
            // RegDate after weekend start
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2014-11-21',
                    'appInDate'       => '2014-11-21',
                    'apprDate'        => '2014-11-21',
                    'regDate'         => '2014-11-21',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 'R',
                ]),
                $statsReport,
                [],
                true,
            ],
            // RegDate not after weekend start
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2014-11-07',
                    'appInDate'       => '2014-11-07',
                    'apprDate'        => '2014-11-07',
                    'regDate'         => '2014-11-07',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 'R',
                ]),
                $statsReport,
                [
                    ['TMLPREG_AFT_REG_DATE_NOT_AFTER_WEEKEND', 'Nov 14, 2014', 'R'],
                ],
                false,
            ],

            // AppOut within 2 days of regDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-15',
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-14',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // AppOut not within 2 days of regDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => null,
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-14',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [
                    ['TMLPREG_APPOUT_LATE', TmlpRegistrationValidator::MAX_DAYS_TO_SEND_APPLICATION_OUT],
                ],
                true,
            ],
            // AppIn within 14 days of regDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-15',
                    'appInDate'       => '2015-01-21',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-14',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // AppIn not within 14 days of regDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-02',
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-01',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [
                    ['TMLPREG_APPIN_LATE', TmlpRegistrationValidator::MAX_DAYS_TO_APPROVE_APPLICATION],
                ],
                true,
            ],
            // Appr within 14 days of regDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-15',
                    'appInDate'       => '2015-01-18',
                    'apprDate'        => '2015-01-21',
                    'regDate'         => '2015-01-14',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Appr not within 14 days of regDate
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-02',
                    'appInDate'       => '2015-01-03',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-01',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [
                    ['TMLPREG_APPR_LATE', TmlpRegistrationValidator::MAX_DAYS_TO_APPROVE_APPLICATION],
                ],
                true,
            ],

            // RegDate in future
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => null,
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-02-01',
                    'incomingWeekend' => 'future',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_REG_DATE_IN_FUTURE'],
                false,
            ],
            // WdDate in future
            [
                Util::arrayToObject([
                    'wdDate'          => '2015-02-14',
                    'appOutDate'      => '2015-01-14',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => '2015-01-14',
                    'regDate'         => '2015-01-14',
                    'incomingWeekend' => 'future',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_WD_DATE_IN_FUTURE'],
                false,
            ],
            // ApprDate in future
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-14',
                    'appInDate'       => '2015-01-14',
                    'apprDate'        => '2015-02-14',
                    'regDate'         => '2015-01-14',
                    'incomingWeekend' => 'future',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_APPR_DATE_IN_FUTURE'],
                false,
            ],
            // AppInDate in future
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-01-14',
                    'appInDate'       => '2015-02-14',
                    'apprDate'        => null,
                    'regDate'         => '2015-01-14',
                    'incomingWeekend' => 'future',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_APPIN_DATE_IN_FUTURE'],
                false,
            ],
            // AppOutDate in future
            [
                Util::arrayToObject([
                    'wdDate'          => null,
                    'appOutDate'      => '2015-02-14',
                    'appInDate'       => null,
                    'apprDate'        => null,
                    'regDate'         => '2015-01-14',
                    'incomingWeekend' => 'future',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                ['TMLPREG_APPOUT_DATE_IN_FUTURE'],
                false,
            ],

            // Invalid dates don't blow up
            [
                Util::arrayToObject([
                    'wdDate'          => 'asdf',
                    'appOutDate'      => 'asdf',
                    'appInDate'       => 'asdf',
                    'apprDate'        => 'asdf',
                    'regDate'         => 'asdf',
                    'incomingWeekend' => 'current',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
            // Invalid dates don't blow up
            [
                Util::arrayToObject([
                    'wdDate'          => 'asdf',
                    'appOutDate'      => 'asdf',
                    'appInDate'       => 'asdf',
                    'apprDate'        => 'asdf',
                    'regDate'         => 'asdf',
                    'incomingWeekend' => 'future',
                    'bef'             => null,
                    'dur'             => null,
                    'aft'             => 1,
                ]),
                $statsReport,
                [],
                true,
            ],
        ];
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
        return [
            // validateComment Passes When Incoming Weekend Equals Current
            [
                Util::arrayToObject([
                    'comment'         => null,
                    'incomingWeekend' => 'current',
                    'wd'              => null,
                ]),
            ],
            // validateComment Passes When Comment Provided
            [
                Util::arrayToObject([
                    'comment'         => 'Nov 2015',
                    'incomingWeekend' => 'future',
                    'wd'              => null,
                ]),
            ],
            // validateComment Passes Ignored When Wd Set
            [
                Util::arrayToObject([
                    'comment'         => null,
                    'incomingWeekend' => 'future',
                    'wd'              => 2,
                ]),
            ],
        ];
    }

    public function testvalidateCommentFailsWhenNoCommentProvidedForFutureIncomingWeekend()
    {
        $data                  = new stdClass;
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

        $validator = $this->getObjectMock([], [$statsReport]);
        $validator->expects($this->never())
                  ->method('addMessage');

        $result = $validator->validateTravel($data);

        $this->assertTrue($result);
    }

    public function providerValidateTravelPasses()
    {
        $this->setSetting('travelDueByDate', 'classroom2Date');

        $statsReport          = new stdClass;
        $statsReport->quarter = new stdClass;
        $statsReport->center  = null;

        $statsReport->reportingDate           = Carbon::createFromDate(2015, 5, 8);
        $statsReport->quarter->classroom2Date = Carbon::createFromDate(2015, 4, 17);

        return [
            // validateTravel Passes When Before Second Classroom
            [
                Util::arrayToObject([
                    'travel'          => null,
                    'room'            => null,
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                ]),
                $statsReport,
            ],
            // validateTravel Passes When Travel And Room Complete
            [
                Util::arrayToObject([
                    'travel'          => 'Y',
                    'room'            => 'Y',
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                ]),
                $statsReport,
            ],
            // validateTravel Passes When Comments Provided
            [
                Util::arrayToObject([
                    'travel'          => null,
                    'room'            => null,
                    'comment'         => 'Travel and rooming booked by May 4',
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                ]),
                $statsReport,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTravelIgnored
     */
    public function testValidateTravelIgnoredWhenWdSet($data)
    {
        $validator = $this->getObjectMock([]);

        $result = $validator->validateTravel($data);

        $this->assertTrue($result);
    }

    public function providerValidateTravelIgnored()
    {
        return [
            // validateTravel Ignored When Wd Set
            [
                Util::arrayToObject([
                    'travel'          => null,
                    'room'            => null,
                    'comment'         => null,
                    'wd'              => 1,
                    'incomingWeekend' => 'current',
                ]),
            ],
            // validateTravel Ignored When Incoming Weekend Equals Future
            [
                Util::arrayToObject([
                    'travel'          => null,
                    'room'            => null,
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'future',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTravelFails
     */
    public function testValidateTravelFails($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);

        for ($i = 0; $i < count($messages); $i++) {
            $validator->expects($this->at($i))
                      ->method('addMessage')
                      ->with($messages[$i]);
        }

        $result = $validator->validateTravel($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTravelFails()
    {
        $this->setSetting('travelDueByDate', 'classroom2Date');

        $statsReport          = new stdClass;
        $statsReport->quarter = new stdClass;
        $statsReport->center  = null;

        $statsReport->reportingDate           = Carbon::createFromDate(2015, 5, 8);
        $statsReport->quarter->classroom2Date = Carbon::createFromDate(2015, 4, 17);
        $statsReport->quarter->endWeekendDate = Carbon::createFromDate(2015, 5, 29);

        $statsReportLastTwoWeeks                = clone $statsReport;
        $statsReportLastTwoWeeks->reportingDate = Carbon::createFromDate(2015, 5, 15);

        return [
            // ValidateTravel Fails When Missing Travel
            [
                Util::arrayToObject([
                    'travel'          => null,
                    'room'            => 'Y',
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                    'appr'            => null,
                ]),
                $statsReport,
                ['TMLPREG_TRAVEL_COMMENT_MISSING'],
                false,
            ],
            // ValidateTravel Fails When Missing Room
            [
                Util::arrayToObject([
                    'travel'          => 'Y',
                    'room'            => null,
                    'comment'         => null,
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                    'appr'            => null,
                ]),
                $statsReport,
                ['TMLPREG_ROOM_COMMENT_MISSING'],
                false,
            ],
            // ValidateTravel Throws Warning When Missing Room In Last 2 Weeks
            [
                Util::arrayToObject([
                    'travel'          => null,
                    'room'            => 'Y',
                    'comment'         => 'By 5/15/2015',
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                    'appr'            => 1,
                ]),
                $statsReportLastTwoWeeks,
                ['TMLPREG_TRAVEL_COMMENT_REVIEW'],
                true,
            ],
            // ValidateTravel Throws Warning When Missing Room In Last 2 Weeks
            [
                Util::arrayToObject([
                    'travel'          => 'Y',
                    'room'            => null,
                    'comment'         => 'By 5/15/2015',
                    'wd'              => null,
                    'incomingWeekend' => 'current',
                    'appr'            => 1,
                ]),
                $statsReportLastTwoWeeks,
                ['TMLPREG_ROOM_COMMENT_REVIEW'],
                true,
            ],
        ];
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
        return [
            // Get bef
            [
                Util::arrayToObject([
                    'bef' => 1,
                    'dur' => null,
                    'aft' => null,
                ]),
                1,
            ],
            // Get dur
            [
                Util::arrayToObject([
                    'bef' => null,
                    'dur' => 2,
                    'aft' => null,
                ]),
                2,
            ],
            // Get aft
            [
                Util::arrayToObject([
                    'bef' => null,
                    'dur' => null,
                    'aft' => 'R',
                ]),
                'R',
            ],

        ];
    }
}
