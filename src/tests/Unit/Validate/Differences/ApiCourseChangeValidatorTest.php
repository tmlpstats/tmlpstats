<?php
namespace TmlpStats\Tests\Unit\Validate\Differences;

use TmlpStats\Domain\Course;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Differences\ApiCourseChangeValidator;

class ApiCourseChangeValidatorTest extends ApiValidatorTestAbstract
{
    protected $testClass = ApiCourseChangeValidator::class;

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'warning',
        'reference' => [
            'id' => null,
            'type' => 'Course',
        ],
    ];

    public function setUp()
    {
        parent::setUp();

        $this->statsReport->center->name = 'Atlanta';

        $this->dataTemplate = [
            'startDate' => '2016-08-27',
            'location' => '',
            'type' => 'CAP',
            'quarterStartTer' => 15,
            'quarterStartStandardStarts' => 14,
            'quarterStartXfer' => 0,
            'currentTer' => 45,
            'currentStandardStarts' => 39,
            'currentXfer' => 1,
            'completedStandardStarts' => 39,
            'potentials' => 30,
            'registrations' => 25,
            'guestsPromised' => null,
            'guestsInvited' => null,
            'guestsConfirmed' => null,
            'guestsAttended' => null,
        ];
    }

    /**
     * @dataProvider providerValidateCourseStartDate
     */
    public function testValidateCourseStartDate($data, $expectedMessages, $pastWeeks = [])
    {
        $data = $this->getCourse($data);

        if ($pastWeeks) {
            $pastWeeks = [ $this->getCourse($pastWeeks) ];
        }

        $validator = $this->getObjectMock();
        $result = $validator->run($data, $pastWeeks);

        $this->assertMessages($expectedMessages, $validator->getMessages());
        $this->assertTrue($result);
    }

    public function providerValidateCourseStartDate()
    {
        return [
            // Start Date with no past weeks
            [
                [],
                [],
            ],
            // Start Date did not change
            [
                [],
                [],
                [
                    'startDate' => '2016-08-27',
                ],
            ],
            // Start Date changed
            [
                [],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'COURSE_START_DATE_CHANGED',
                        'reference.field' => 'startDate',
                    ]),
                ],
                [
                    'startDate' => '2016-09-06',
                ],
            ],
        ];
    }

    public function getCourse($data)
    {
        if (isset($data['__centerName'])) {
            $this->statsReport->center->name = $data['__centerName'];
            unset($data['__centerName']);
        }

        if (isset($data['__reportingDate'])) {
            $this->statsReport->reportingDate = $data['__reportingDate'];
            unset($data['__reportingDate']);
        }

        $data = array_merge($this->dataTemplate, $data);

        return Course::fromArray($data);
    }
}
