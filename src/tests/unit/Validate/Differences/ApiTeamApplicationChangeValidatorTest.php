<?php
namespace TmlpStats\Tests\Unit\Validate\Differences;

use Carbon\Carbon;
use TmlpStats\Domain\TeamApplication;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Differences\ApiTeamApplicationChangeValidator;

class ApiTeamApplicationChangeValidatorTest extends ApiValidatorTestAbstract
{
    protected $testClass = ApiTeamApplicationChangeValidator::class;

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'warning',
        'reference' => [
            'id' => null,
            'type' => 'TeamApplication',
        ],
    ];

    public function setUp()
    {
        parent::setUp();

        $this->statsReport->center->name = 'Atlanta';

        $this->dataTemplate = $this->getDataTemplate();
    }

    public function getDataTemplate()
    {
        return [
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
     * @dataProvider providerValidateDateChanges
     */
    public function testValidateDateChanges($data, $expectedMessages, $pastWeeks = [])
    {
        $data = $this->getTeamApplication($data);

        if ($pastWeeks) {
            $pastWeeks = [$this->getTeamApplication($pastWeeks)];
        }

        $validator = $this->getObjectMock();
        $result = $validator->run($data, $pastWeeks);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertTrue($result);
    }

    public function providerValidateDateChanges()
    {
        return [
            // Reg Date with no past weeks
            [
                [],
                [],
            ],
            // Reg Date did not change
            [
                [],
                [],
                [
                    'regDate' => '2016-08-22',
                ],
            ],
            // Reg Date changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_REG_DATE_CHANGED',
                        'reference.field' => 'regDate',
                    ]),
                ],
                [
                    'regDate' => '2016-08-27',
                ],
            ],

            // AppOut Date with no past weeks
            [
                [],
                [],
            ],
            // AppOut Date did not change
            [
                [],
                [],
                [
                    'appOutDate' => '2016-08-23',
                ],
            ],
            // AppOut Date changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPOUT_DATE_CHANGED',
                        'reference.field' => 'appOutDate',
                    ]),
                ],
                [
                    'appOutDate' => '2016-08-27',
                ],
            ],

            // AppIn Date with no past weeks
            [
                [],
                [],
            ],
            // AppIn Date did not change
            [
                [],
                [],
                [
                    'appInDate' => '2016-08-24',
                ],
            ],
            // AppIn Date changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPIN_DATE_CHANGED',
                        'reference.field' => 'appInDate',
                    ]),
                ],
                [
                    'appInDate' => '2016-08-27',
                ],
            ],

            // Appr Date with no past weeks
            [
                [],
                [],
            ],
            // Appr Date did not change
            [
                [],
                [],
                [
                    'apprDate' => '2016-08-25',
                ],
            ],
            // Appr Date changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_APPR_DATE_CHANGED',
                        'reference.field' => 'apprDate',
                    ]),
                ],
                [
                    'apprDate' => '2016-08-27',
                ],
            ],

            // Withdraw Date with no past weeks
            [
                [
                    'wdDate' => '2016-08-26',
                ],
                [],
            ],
            // Withdraw Date did not change
            [
                [
                    'wdDate' => '2016-08-26',
                ],
                [],
                [
                    'wdDate' => '2016-08-26',
                ],
            ],
            // Withdraw Date changed
            [
                [
                    'wdDate' => '2016-08-26',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_WD_DATE_CHANGED',
                        'reference.field' => 'wdDate',
                    ]),
                ],
                [
                    'wdDate' => '2016-08-27',
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerValidateQuarterChanges
     */
    public function testValidateQuarterChanges($data, $expectedMessages, $pastWeeks = [])
    {
        $data = $this->getTeamApplication($data);

        if ($pastWeeks) {
            $pastWeeks = [$this->getTeamApplication($pastWeeks)];
            $x = ($data->incomingQuarter === $pastWeeks[0]->incomingQuarter);
        }

        $validator = $this->getObjectMock();
        $result = $validator->run($data, $pastWeeks);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertTrue($result);
    }

    public function providerValidateQuarterChanges()
    {
        return [
            // Quarter to future quarter
            [
                [
                    'incomingQuarter' => 'future',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'TEAMAPP_INCOMING_QUARTER_CHANGED',
                        'reference.field' => 'incomingQuarter',
                    ]),
                ],
                [
                    'incomingQuarter' => 'next',
                ],
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

        $data = array_merge([], $this->getDataTemplate(), $data);

        return TeamApplication::fromArray($data);
    }
}
