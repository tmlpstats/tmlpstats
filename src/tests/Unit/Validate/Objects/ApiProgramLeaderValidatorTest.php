<?php
namespace TmlpStats\Tests\Unit\Validate\Objects;

use Carbon\Carbon;
use TmlpStats\Domain;
use TmlpStats\Tests\Unit\Traits;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Objects\ApiProgramLeaderValidator;

class ApiProgramLeaderValidatorTest extends ApiValidatorTestAbstract
{
    use Traits\MocksSettings;

    protected $testClass = ApiProgramLeaderValidator::class;

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'error',
        'reference' => [
            'id' => null,
            'type' => 'ProgramLeader',
        ],
    ];

    public function setUp()
    {
        parent::setUp();
        $this->statsReport->center = null;

        $this->setSetting('bouncedEmails', '');

        $this->dataTemplate = [
            'firstName' => 'Keith',
            'lastName' => 'Stone',
            'phone' => '555-555-5555',
            'email' => 'fake-pl@tmlpstats.com',
            'accountability' => 'programManager',
            'attendingWeekend' => true,
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
        $data = $this->getProgramLeader($data);

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
                    'phone' => null,
                    'email' => null,
                    'accountability' => null,
                    'attendingWeekend' => null,
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
                        'reference.field' => 'phone',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'email',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'accountability',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.field' => 'attendingWeekend',
                    ]),
                ],
                false,
            ],
            // Test Program Manager
            [
                [],
                [],
                true,
            ],
            // Test Classroom Leader
            [
                [
                    'accountability' => 'classroomLeader',
                ],
                [],
                true,
            ],
            // Test Not attending weekend
            [
                [
                    'attendingWeekend' => false,
                ],
                [],
                true,
            ],
            // Test Invalid email
            [
                [
                    'email' => 'asdf',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.field' => 'email',
                    ]),
                ],
                false,
            ],
            // Test Invalid phone
            [
                [
                    'phone' => 'asdf',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.field' => 'phone',
                    ]),
                ],
                false,
            ],
        ];
    }

    /**
     * @dataProvider providerValidateEmail
     */
    public function testValidateEmail($data, $bouncedEmails, $expectedMessages, $expectedResult)
    {
        $this->setSetting('bouncedEmails', $bouncedEmails);

        $data = $this->getProgramLeader($data);

        $validator = $this->getObjectMock();
        $result = $validator->run($data);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidateEmail()
    {
        return [
            // No bounced emails
            [
                [],
                '',
                [],
                true,
            ],
            // Has bounced emails but doesn't match
            [
                [],
                'some-other@tmlpstats.com',
                [],
                true,
            ],
            // Has multple bounced emails but doesn't match
            [
                [],
                'some-other@tmlpstats.com,and-another@tmlpstats.com,and-finally@tmlpstats.com',
                [],
                true,
            ],
            // Matches bounced email
            [
                [
                    'email' => 'a-match@tmlpstats.com',
                ],
                'some-other@tmlpstats.com,a-match@tmlpstats.com',
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'PROGRAMLEADER_BOUNCED_EMAIL',
                        'level' => 'warning',
                        'reference.field' => 'email',
                    ]),
                ],
                true,
            ],
        ];
    }

    public function getProgramLeader($data)
    {
        if (isset($data['__reportingDate'])) {
            $this->statsReport->reportingDate = $data['__reportingDate'];
            unset($data['__reportingDate']);
        }

        $data = array_merge($this->dataTemplate, $data);

        return Domain\ProgramLeader::fromArray($data);
    }
}
