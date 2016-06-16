<?php
namespace TmlpStats\Tests\Unit\Validate\Objects;

use Carbon\Carbon;
use stdClass;
use TmlpStats as Models;
use TmlpStats\Api\Parsers;
use TmlpStats\Domain;
use TmlpStats\Tests\Unit\Traits\MocksMessages;
use TmlpStats\Tests\Unit\Traits\MocksModel;
use TmlpStats\Tests\Unit\Traits\MocksQuarters;
use TmlpStats\Tests\Unit\Traits\MocksSettings;
use TmlpStats\Validate\Objects\ApiTeamApplicationValidator;

class ApiTeamApplicationValidatorTest extends ObjectsValidatorTestAbstract
{
    use MocksSettings, MocksMessages, MocksQuarters, MocksModel;

    protected $instantiateApp = true;
    protected $testClass = ApiTeamApplicationValidator::class;

    protected $dataFields = [
        'firstName',
        'lastName',
        'teamYear',
        'regDate',
        'appOutDate',
        'appInDate',
        'apprDate',
        'wdDate',
        'travel',
        'room',
        'incomingQuarterId',
        'withdrawCodeId',
        'committedTeamMemberId',
    ];

    protected $validateMethods = [
        'validateApprovalProcess',
        'validateDates',
        'validateTravel',
        'validateReviewer',
    ];

    public function addModelParserMock($parserClass)
    {
        $mock = $this->getModelMock();

        $parser = $this->getMockBuilder($parserClass)
                ->setMethods(['fetch'])
                ->getMock();

        $parser->expects($this->any())
            ->method('fetch')
            ->will($this->returnCallback(function ($class, $id) use ($mock) {
                $mock->id = $id;
                return $mock;
            }));

        $this->app->bind($parserClass, function ($app) use ($parser) {
            return $parser;
        });
    }

    public function setUp()
    {
        parent::setUp();

        $this->addModelParserMock(Parsers\CenterParser::class);
        $this->addModelParserMock(Parsers\QuarterParser::class);
        $this->addModelParserMock(Parsers\WithdrawCodeParser::class);
        $this->addModelParserMock(Parsers\TeamMemberParser::class);
        $this->addModelParserMock(Parsers\ApplicationParser::class);
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun($data, $messages, $expectedResult)
    {
        $data = Domain\TeamApplication::fromArray($data);

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
                    ['INVALID_VALUE', 'First Name', '[empty]'],
                    ['INVALID_VALUE', 'Last Name', '[empty]'],
                    ['INVALID_VALUE', 'Team Year', '[empty]'],
                    ['INVALID_VALUE', 'Reg Date', '[empty]'],
                    ['INVALID_VALUE', 'Incoming Quarter Id', '[empty]'],
                ],
                false,
            ],
            // Test Valid (Variable 1)
            [
                [
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'teamYear' => 1,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'appOutDate' => Carbon::parse('2015-01-01'),
                    'appInDate' => Carbon::parse('2015-01-01'),
                    'apprDate' => Carbon::parse('2015-01-01'),
                    'wdDate' => Carbon::parse('2015-01-01'),
                    'committedTeamMember' => 1234,
                    'withdrawCode' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [],
                true,
            ],
            // Test Valid (Variable 2)
            [
                [
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'teamYear' => 2,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => true,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'appOutDate' => Carbon::parse('2015-01-01'),
                    'appInDate' => Carbon::parse('2015-01-01'),
                    'apprDate' => Carbon::parse('2015-01-01'),
                    'wdDate' => Carbon::parse('2015-01-01'),
                    'committedTeamMember' => 1234,
                    'withdrawCode' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
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
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'teamYear' => 1,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'appOutDate' => Carbon::parse('2015-01-01'),
                    'appInDate' => Carbon::parse('2015-01-01'),
                    'apprDate' => Carbon::parse('2015-01-01'),
                    'wdDate' => Carbon::parse('2015-01-01'),
                    'committedTeamMember' => 1234,
                    'withdrawCode' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [
                    ['INVALID_VALUE', 'First Name', '[empty]'],
                ],
                false,
            ],
            // Test Invalid Last Name
            [
                [
                    'firstName' => 'Keith',
                    'lastName' => '',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'teamYear' => 1,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'appOutDate' => Carbon::parse('2015-01-01'),
                    'appInDate' => Carbon::parse('2015-01-01'),
                    'apprDate' => Carbon::parse('2015-01-01'),
                    'wdDate' => Carbon::parse('2015-01-01'),
                    'committedTeamMember' => 1234,
                    'withdrawCode' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [
                    ['INVALID_VALUE', 'Last Name', '[empty]'],
                ],
                false,
            ],
            // Test invalid TeamYear
            [
                [
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'teamYear' => 3,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'appOutDate' => Carbon::parse('2015-01-01'),
                    'appInDate' => Carbon::parse('2015-01-01'),
                    'apprDate' => Carbon::parse('2015-01-01'),
                    'wdDate' => Carbon::parse('2015-01-01'),
                    'committedTeamMember' => 1234,
                    'withdrawCode' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [
                    ['INVALID_VALUE', 'Team Year', 3],
                ],
                false,
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
        $data = Domain\TeamApplication::fromArray($data);

        $validator = $this->getObjectMock();

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->validateApprovalProcess($data);

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
                    'wdDate' => Carbon::parse('2015-01-28'),
                    'withdrawCode' => 1234,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [],
                true,
            ],
            // Withdraw and all steps complete
            [
                [
                    'appOutDate' => Carbon::parse('2015-01-13'),
                    'appInDate' => Carbon::parse('2015-01-20'),
                    'apprDate' => Carbon::parse('2015-01-27'),
                    'wdDate' => Carbon::parse('2015-01-28'),
                    'withdrawCode' => 1234,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [],
                true,
            ],
            // Withdraw and missing wd
            [
                [
                    'appOutDate' => Carbon::parse('2015-01-13'),
                    'appInDate' => Carbon::parse('2015-01-20'),
                    'apprDate' => Carbon::parse('2015-01-27'),
                    'wdDate' => Carbon::parse('2015-01-28'),
                    'withdrawCode' => null,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [
                    ['TEAMAPP_WD_CODE_MISSING'],
                ],
                false,
            ],
            // Withdraw and missing date
            [
                [
                    'appOutDate' => Carbon::parse('2015-01-13'),
                    'appInDate' => Carbon::parse('2015-01-20'),
                    'apprDate' => Carbon::parse('2015-01-27'),
                    'wdDate' => null,
                    'withdrawCode' => 1234,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [
                    ['TEAMAPP_WD_DATE_MISSING'],
                ],
                false,
            ],

            // Approved
            [
                [
                    'appOutDate' => Carbon::parse('2015-01-14'),
                    'appInDate' => Carbon::parse('2015-01-21'),
                    'apprDate' => Carbon::parse('2015-01-28'),
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [],
                true,
            ],
            // Approved and missing appInDate
            [
                [
                    'appOutDate' => Carbon::parse('2015-01-14'),
                    'appInDate' => null,
                    'apprDate' => Carbon::parse('2015-01-28'),
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [
                    ['TEAMAPP_APPR_MISSING_APPIN_DATE'],
                ],
                false,
            ],
            // Approved and missing appOutDate
            [
                [
                    'appOutDate' => null,
                    'appInDate' => Carbon::parse('2015-01-21'),
                    'apprDate' => Carbon::parse('2015-01-28'),
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [
                    ['TEAMAPP_APPR_MISSING_APPOUT_DATE'],
                ],
                false,
            ],

            // App In
            [
                [
                    'appOutDate' => Carbon::parse('2015-01-14'),
                    'appInDate' => Carbon::parse('2015-01-21'),
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [],
                true,
            ],
            // App In and missing appOutDate
            [
                [
                    'appOutDate' => null,
                    'appInDate' => Carbon::parse('2015-01-21'),
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [
                    ['TEAMAPP_APPIN_MISSING_APPOUT_DATE'],
                ],
                false,
            ],

            // App Out
            [
                [
                    'appOutDate' => Carbon::parse('2015-01-14'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
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
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'teamYear' => 1,
                    'committedTeamMember' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [],
                true,
            ],
            // Missing committed team member
            [
                [
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'teamYear' => 1,
                    'committedTeamMember' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                [
                    ['TMLPREG_NO_COMMITTED_TEAM_MEMBER'],
                ],
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
        $data = Domain\TeamApplication::fromArray($data);

        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->validateDates($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateDates()
    {
        $statsReport = new stdClass;
        $statsReport->center = new Models\Center();
        $statsReport->quarter = $this->getQuarterMock([], [
            'startWeekendDate' => Carbon::createFromDate(2014, 11, 14)->startOfDay(),
        ]);

        $statsReport->reportingDate = Carbon::createFromDate(2015, 1, 21);

        return [
            // Withdraw date OK
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2015-01-21'),
                    'withdrawCode' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // Withdraw and wdDate before regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2015-01-01'),
                    'withdrawCode' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_WD_DATE_BEFORE_REG_DATE'],
                ],
                false,
            ],
            // Withdraw and approve dates OK
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => Carbon::parse('2015-01-14'),
                    'wdDate' => Carbon::parse('2015-01-21'),
                    'withdrawCode' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // Withdraw and wdDate before apprDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => Carbon::parse('2015-01-21'),
                    'wdDate' => Carbon::parse('2015-01-14'),
                    'withdrawCode' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_WD_DATE_BEFORE_APPR_DATE'],
                ],
                false,
            ],
            // Withdraw and appIn dates OK
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => Carbon::parse('2015-01-14'),
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2015-01-21'),
                    'withdrawCode' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // Withdraw and wdDate before appInDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => Carbon::parse('2015-01-21'),
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2015-01-14'),
                    'withdrawCode' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_WD_DATE_BEFORE_APPIN_DATE'],
                ],
                false,
            ],
            // Withdraw and appOut dates OK
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-09'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2015-01-21'),
                    'withdrawCode' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // Withdraw and wdDate before appOutDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-21'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => Carbon::parse('2015-01-14'),
                    'withdrawCode' => 1234,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_WD_DATE_BEFORE_APPOUT_DATE'],
                ],
                false,
            ],

            // Approved date OK
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-09'),
                    'appInDate' => Carbon::parse('2015-01-14'),
                    'apprDate' => Carbon::parse('2015-01-21'),
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // Approved and apprDate before regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-09'),
                    'appInDate' => Carbon::parse('2015-01-14'),
                    'apprDate' => Carbon::parse('2015-01-01'),
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPR_DATE_BEFORE_REG_DATE'],
                    ['TMLPREG_APPR_DATE_BEFORE_APPIN_DATE'],
                    ['TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE'],
                ],
                false,
            ],
            // Approved and apprDate before appInDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-09'),
                    'appInDate' => Carbon::parse('2015-01-14'),
                    'apprDate' => Carbon::parse('2015-01-13'),
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPR_DATE_BEFORE_APPIN_DATE'],
                ],
                false,
            ],
            // Approved and apprDate before appOutDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-09'),
                    'appInDate' => Carbon::parse('2015-01-08'),
                    'apprDate' => Carbon::parse('2015-01-08'),
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPR_DATE_BEFORE_APPOUT_DATE'],
                ],
                false,
            ],

            // AppIn date OK
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-09'),
                    'appInDate' => Carbon::parse('2015-01-14'),
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // AppIn and appInDate before regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-09'),
                    'appInDate' => Carbon::parse('2015-01-01'),
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPIN_DATE_BEFORE_REG_DATE'],
                ],
                false,
            ],
            // AppIn and appInDate before appOutDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-09'),
                    'appInDate' => Carbon::parse('2015-01-08'),
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPIN_DATE_BEFORE_APPOUT_DATE'],
                ],
                false,
            ],

            // AppOut date OK
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-09'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // AppOut and appOutDate before regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => Carbon::parse('2015-01-01'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPOUT_DATE_BEFORE_REG_DATE'],
                ],
                false,
            ],

            // AppOut within 2 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-14'),
                    'appOutDate' => Carbon::parse('2015-01-15'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // AppOut not within 2 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-14'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPOUT_LATE', ApiTeamApplicationValidator::MAX_DAYS_TO_SEND_APPLICATION_OUT],
                ],
                true,
            ],
            // AppIn within 14 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-14'),
                    'appOutDate' => Carbon::parse('2015-01-15'),
                    'appInDate' => Carbon::parse('2015-01-21'),
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // AppIn not within 14 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-01'),
                    'appOutDate' => Carbon::parse('2015-01-02'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPIN_LATE', ApiTeamApplicationValidator::MAX_DAYS_TO_APPROVE_APPLICATION],
                ],
                true,
            ],
            // Appr within 14 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-14'),
                    'appOutDate' => Carbon::parse('2015-01-15'),
                    'appInDate' => Carbon::parse('2015-01-18'),
                    'apprDate' => Carbon::parse('2015-01-21'),
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [],
                true,
            ],
            // Appr not within 14 days of regDate
            [
                [
                    'regDate' => Carbon::parse('2015-01-01'),
                    'appOutDate' => Carbon::parse('2015-01-02'),
                    'appInDate' => Carbon::parse('2015-01-03'),
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPR_LATE', ApiTeamApplicationValidator::MAX_DAYS_TO_APPROVE_APPLICATION],
                ],
                true,
            ],

            // RegDate in future
            [
                [
                    'regDate' => Carbon::parse('2015-02-01'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_REG_DATE_IN_FUTURE'],
                ],
                false,
            ],
            // WdDate in future
            [
                [
                    'regDate' => Carbon::parse('2015-01-14'),
                    'appOutDate' => Carbon::parse('2015-01-14'),
                    'appInDate' => Carbon::parse('2015-01-14'),
                    'apprDate' => Carbon::parse('2015-01-14'),
                    'wdDate' => Carbon::parse('2015-02-14'),
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_WD_DATE_IN_FUTURE'],
                ],
                false,
            ],
            // ApprDate in future
            [
                [
                    'regDate' => Carbon::parse('2015-01-14'),
                    'appOutDate' => Carbon::parse('2015-01-14'),
                    'appInDate' => Carbon::parse('2015-01-14'),
                    'apprDate' => Carbon::parse('2015-02-14'),
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPR_DATE_IN_FUTURE'],
                ],
                false,
            ],
            // AppInDate in future
            [
                [
                    'regDate' => Carbon::parse('2015-01-14'),
                    'appOutDate' => Carbon::parse('2015-01-14'),
                    'appInDate' => Carbon::parse('2015-02-14'),
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPIN_DATE_IN_FUTURE'],
                ],
                false,
            ],
            // AppOutDate in future
            [
                [
                    'regDate' => Carbon::parse('2015-01-14'),
                    'appOutDate' => Carbon::parse('2015-02-14'),
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'tmlpRegistration' => 1234,
                    'incomingQuarter' => 1234,
                    'committedTeamMember' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                [
                    ['TMLPREG_APPOUT_DATE_IN_FUTURE'],
                ],
                false,
            ],
        ];
    }

    //
    // validateTravel()
    //

    /**
     * @dataProvider providerValidateTravelPasses
     */
    public function testValidateTravelPasses($data, $statsReport)
    {
        $data = Domain\TeamApplication::fromArray($data);

        $this->setSetting('travelDueByDate', 'classroom2Date');

        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);

        $validator->expects($this->never())
            ->method('addMessage');

        $result = $validator->validateTravel($data);

        $this->assertTrue($result);
    }

    public function providerValidateTravelPasses()
    {
        $nextQuarter = $this->getModelMock();
        $futureQuarter = $this->getModelMock();

        $statsReport = new stdClass;
        $statsReport->quarter = $this->getQuarterMock([], [
            'classroom2Date' => Carbon::createFromDate(2015, 4, 17)->startOfDay(),
            'nextQuarter' => $nextQuarter,
        ]);
        $statsReport->center = null;

        $statsReport->reportingDate = Carbon::createFromDate(2015, 4, 10);

        return [
            // validateTravel Passes When Before Second Classroom
            [
                [
                    'travel' => null,
                    'room' => null,
                    'comment' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => $nextQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                ],
                $statsReport,
            ],
            // validateTravel Passes When Travel And Room Complete
            [
                [
                    'travel' => true,
                    'room' => true,
                    'comment' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => $nextQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                ],
                $statsReport,
            ],
            // validateTravel Passes When Comments Provided
            [
                [
                    'travel' => null,
                    'room' => null,
                    'comment' => 'Travel and rooming booked by May 4',
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => $nextQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                ],
                $statsReport,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTravelIgnored
     */
    public function testValidateTravelIgnoredWhenWdSet($data, $statsReport)
    {
        $data = Domain\TeamApplication::fromArray($data);

        $this->setSetting('travelDueByDate', 'classroom2Date');

        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);

        $validator->expects($this->never())
            ->method('addMessage');

        $result = $validator->validateTravel($data);

        $this->assertTrue($result);
    }

    public function providerValidateTravelIgnored()
    {
        $nextQuarter = $this->getModelMock();
        $futureQuarter = $this->getModelMock();

        $statsReport = new stdClass;
        $statsReport->quarter = $this->getQuarterMock([], [
            'classroom2Date' => Carbon::createFromDate(2015, 4, 17)->startOfDay(),
            'nextQuarter' => $nextQuarter,
        ]);
        $statsReport->center = null;

        $statsReport->reportingDate = Carbon::createFromDate(2015, 5, 8);

        return [
            // validateTravel Ignored When Wd Set
            [
                [
                    'travel' => null,
                    'room' => null,
                    'comment' => null,
                    'wdDate' => Carbon::parse('2015-01-01'),
                    'withdrawCode' => 1234,
                    'incomingQuarter' => $nextQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                ],
                $statsReport,
            ],
            // validateTravel Ignored When Incoming Weekend Equals Future
            [
                [
                    'travel' => null,
                    'room' => null,
                    'comment' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => $futureQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                ],
                $statsReport,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateTravelFails
     */
    public function testValidateTravelFails($data, $statsReport, $messages, $expectedResult)
    {
        $data = Domain\TeamApplication::fromArray($data);

        $this->setSetting('travelDueByDate', 'classroom2Date');

        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->validateTravel($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateTravelFails()
    {
        $nextQuarter = $this->getModelMock();
        $futureQuarter = $this->getModelMock();

        $statsReport = new stdClass;
        $statsReport->quarter = $this->getQuarterMock([], [
            'classroom2Date' => Carbon::createFromDate(2015, 4, 17)->startOfDay(),
            'endWeekendDate' => Carbon::createFromDate(2015, 5, 29)->startOfDay(),
            'nextQuarter' => $nextQuarter,
        ]);
        $statsReport->center = null;

        $statsReport->reportingDate = Carbon::createFromDate(2015, 5, 8);

        $statsReportLastTwoWeeks = clone $statsReport;
        $statsReportLastTwoWeeks->reportingDate = Carbon::createFromDate(2015, 5, 15);

        return [
            // ValidateTravel Fails When Missing Travel
            [
                [
                    'travel' => null,
                    'room' => true,
                    'comment' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => $nextQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                ],
                $statsReport,
                [
                    ['TEAMAPP_TRAVEL_COMMENT_MISSING'],
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
                    'incomingQuarter' => $nextQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                ],
                $statsReport,
                [
                    ['TEAMAPP_ROOM_COMMENT_MISSING'],
                ],
                false,
            ],
            // ValidateTravel Throws Warning When Missing Room In Last 2 Weeks
            [
                [
                    'travel' => null,
                    'room' => true,
                    'comment' => 'By 5/15/2015',
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => $nextQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                ],
                $statsReportLastTwoWeeks,
                [
                    ['TEAMAPP_TRAVEL_COMMENT_REVIEW'],
                ],
                true,
            ],
            // ValidateTravel Throws Warning When Missing Room In Last 2 Weeks
            [
                [
                    'travel' => true,
                    'room' => null,
                    'comment' => 'By 5/15/2015',
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => $nextQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'teamYear' => 1,
                    'center' => 1234,
                    'isReviewer' => false,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                ],
                $statsReportLastTwoWeeks,
                [
                    ['TEAMAPP_ROOM_COMMENT_REVIEW'],
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateReviewer
     */
    public function testValidateReviewer($data, $messages, $expectedResult)
    {
        $data = Domain\TeamApplication::fromArray($data);

        $validator = $this->getObjectMock([
            'addMessage',
        ]);

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->validateReviewer($data);

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
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 1234,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                    'travel' => null,
                    'room' => true,
                    'comment' => null,
                ],
                [],
                true,
            ],
            // Team 2 and not a reviewer
            [
                [
                    'teamYear' => 2,
                    'isReviewer' => false,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 1234,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                    'travel' => null,
                    'room' => true,
                    'comment' => null,
                ],
                [],
                true,
            ],
            // Team 1 and a reviewer
            [
                [
                    'teamYear' => 1,
                    'isReviewer' => true,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 1234,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                    'travel' => null,
                    'room' => true,
                    'comment' => null,
                ],
                [
                    ['TEAMAPP_REVIEWER_TEAM1'],
                ],
                false,
            ],
            // Team 2 and not a reviewer
            [
                [
                    'teamYear' => 2,
                    'isReviewer' => true,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'phone' => '555-555-5555',
                    'center' => 1234,
                    'regDate' => Carbon::parse('2015-01-07'),
                    'appOutDate' => null,
                    'appInDate' => null,
                    'apprDate' => null,
                    'wdDate' => null,
                    'withdrawCode' => null,
                    'incomingQuarter' => 1234,
                    'tmlpRegistration' => 1234,
                    'committedTeamMember' => 1234,
                    'travel' => null,
                    'room' => true,
                    'comment' => null,
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
    public function testIsStartingNextQuarter($data, $statsReport, $expected)
    {
        $data = Domain\TeamApplication::fromArray($data);

        $validator = $this->getObjectMock([], [$statsReport]);

        $this->assertEquals($expected, $validator->isStartingNextQuarter($data));
    }

    public function providerIsStartingNextQuarter()
    {
        $nextQuarter = $this->getModelMock();
        $futureQuarter = $this->getModelMock();

        $statsReport = new stdClass;
        $statsReport->quarter = $this->getQuarterMock([], [
            'nextQuarter' => $nextQuarter,
        ]);
        return [
            // Is Starting Next Quarter
            [
                [
                    'incomingQuarter' => $nextQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'teamYear' => 1,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'appOutDate' => Carbon::parse('2015-01-01'),
                    'appInDate' => Carbon::parse('2015-01-01'),
                    'apprDate' => Carbon::parse('2015-01-01'),
                    'wdDate' => Carbon::parse('2015-01-01'),
                    'committedTeamMember' => 1234,
                    'withdrawCode' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                true,
            ],
            // Not Starting Next Quarter
            [
                [
                    'incomingQuarter' => $futureQuarter->id,
                    // the below values are not referenced and don't change
                    'firstName' => 'Keith',
                    'lastName' => 'Stone',
                    'email' => 'unit_test@tmlpstats.com',
                    'center' => 1234,
                    'teamYear' => 1,
                    'regDate' => Carbon::parse('2015-01-01'),
                    'isReviewer' => false,
                    'phone' => '555-555-5555',
                    'tmlpRegistration' => 1234,
                    'appOutDate' => Carbon::parse('2015-01-01'),
                    'appInDate' => Carbon::parse('2015-01-01'),
                    'apprDate' => Carbon::parse('2015-01-01'),
                    'wdDate' => Carbon::parse('2015-01-01'),
                    'committedTeamMember' => 1234,
                    'withdrawCode' => 1234,
                    'comment' => 'asdf qwerty',
                    'travel' => true,
                    'room' => true,
                ],
                $statsReport,
                false,
            ],
        ];
    }
}
