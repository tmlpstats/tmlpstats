<?php
namespace TmlpStats\Tests\Unit\Validate\Relationships;

use Carbon\Carbon;
use Faker\Factory;
use stdClass;
use TmlpStats\Domain;
use TmlpStats\Tests\Unit\Traits;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Relationships\ApiAccountabilityValidator;

class ApiAccountabilityValidatorTest extends ApiValidatorTestAbstract
{
    use Traits\MocksModel;

    protected $testClass = ApiAccountabilityValidator::class;

    protected $defaultObjectMethods = ['getAccountability'];

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'error',
        'reference' => [
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

    protected $teamMemberTemplate = [
        'firstName' => '',
        'lastName' => '',
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

    /**
     * @dataProvider providerMissingAccountabilities
     */
    public function testRunWithAllValidAccountabilities($missingList)
    {
        $faker = Factory::create();

        $data = [
            'TeamMember' => [],
        ];
        foreach ($this->accountabilities as $id => $display) {
            if (in_array($id, $missingList)) {
                continue;
            }

            $teamMember = array_merge($this->teamMemberTemplate, [
                'firstName' => $faker->unique()->firstName(),
                'lastName' => $faker->lastName(),
                'accountabilities' => [$id],
            ]);

            $data['TeamMember'][] = Domain\TeamMember::fromArray($teamMember);
        };

        $validator = $this->getObjectMock();
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

        $messages = [];
        if ($missingList) {
            $messages = [
                $this->getMessageData($this->messageTemplate, [
                    'id' => 'CLASSLIST_MISSING_ACCOUNTABLE',
                    'reference.id' => 4,
                    'level' => 'warning',
                ]),
                $this->getMessageData($this->messageTemplate, [
                    'id' => 'CLASSLIST_MISSING_ACCOUNTABLE',
                    'reference.id' => 6,
                    'level' => 'warning',
                ]),
            ];
        }

        $this->assertMessages($messages, $validator->getMessages());
        $this->assertTrue($result);
    }

    public function providerMissingAccountabilities()
    {
        return [
            [[]],
            [[4, 5, 6]],
        ];
    }

    public function testRunWithDuplicateAccountabilities()
    {
        $faker = Factory::create();

        $data = [
            'TeamMember' => [],
        ];

        foreach ($this->accountabilities as $id => $display) {
            $teamMember = array_merge($this->teamMemberTemplate, [
                'firstName' => $faker->unique()->firstName(),
                'lastName' => $faker->lastName(),
                'accountabilities' => [4, 5, $id],
            ]);

            $data['TeamMember'][] = Domain\TeamMember::fromArray($teamMember);
        };

        $validator = $this->getObjectMock();
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

        $this->assertMessages([
            $this->getMessageData($this->messageTemplate, [
                'id' => 'CLASSLIST_MULTIPLE_ACCOUNTABLES',
                'reference.id' => 4,
                'level' => 'error',
            ]),
            $this->getMessageData($this->messageTemplate, [
                'id' => 'CLASSLIST_MULTIPLE_ACCOUNTABLES',
                'reference.id' => 5,
                'level' => 'error',
            ]),
        ], $validator->getMessages());
        $this->assertTrue($result);
    }
}
