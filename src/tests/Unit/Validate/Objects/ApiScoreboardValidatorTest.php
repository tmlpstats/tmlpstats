<?php
namespace TmlpStats\Tests\Unit\Validate\Objects;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Domain;
use TmlpStats\Tests\Unit\Traits;
use TmlpStats\Tests\Unit\Validate\ApiValidatorTestAbstract;
use TmlpStats\Validate\Objects\ApiScoreboardValidator;



// TODO: update getScoreboard to create promises and actuals


class ApiScoreboardValidatorTest extends ApiValidatorTestAbstract
{
    use Traits\MocksSettings, Traits\MocksQuarters, Traits\MocksModel;

    protected $testClass = ApiScoreboardValidator::class;

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'error',
        'reference' => [
            'id' => '2016-09-02',
            'type' => 'scoreboard',
        ],
    ];

    public function setUp()
    {
        parent::setUp();

        $this->statsReport->center->name = 'Atlanta';

        $this->dataTemplate = [
            'week' => $this->reportingDate->toDateString(),
            'cap' => 20,
            'cpc' => 10,
            't1x' => 3,
            't2x' => 0,
            'gitw' => 90,
            'lf' => 8,
        ];
    }

    /**
     * @dataProvider providerRun
     */
    public function testRun($data, $expectedMessages, $expectedResult)
    {
        $data = $this->getScoreboard($data);

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
                    'cap' => null,
                    'cpc' => null,
                    't1x' => null,
                    't2x' => null,
                    'gitw' => null,
                    'lf' => null,
                ],
                [
                    // Promises
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 'cap',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 'cpc',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 't1x',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 't2x',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 'gitw',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 'lf',
                        'reference.promiseType' => 'promise',
                    ]),
                    // Actuals
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 'cap',
                        'reference.promiseType' => 'actual',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 'cpc',
                        'reference.promiseType' => 'actual',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 't1x',
                        'reference.promiseType' => 'actual',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 't2x',
                        'reference.promiseType' => 'actual',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 'gitw',
                        'reference.promiseType' => 'actual',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference.game' => 'lf',
                        'reference.promiseType' => 'actual',
                    ]),
                ],
                false,
            ],
            // Test all zero
            [
                [
                    'cap' => 0,
                    'cpc' => 0,
                    't1x' => 0,
                    't2x' => 0,
                    'gitw' => 0,
                    'lf' => 0,
                ],
                [],
                true,
            ],
            // Test all legal negative
            [
                [
                    'cap' => -10,
                    'cpc' => -10,
                    't1x' => -5,
                    't2x' => -5,
                    'gitw' => 0,
                    'lf' => 0,
                ],
                [],
                true,
            ],
            // Test Invalid cap
            [
                [
                    'cap' => 'asdf',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'cap',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'cap',
                        'reference.promiseType' => 'actual',
                    ]),
                ],
                false,
            ],
            // Test Invalid cpc
            [
                [
                    'cpc' => 'asdf',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'cpc',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'cpc',
                        'reference.promiseType' => 'actual',
                    ]),
                ],
                false,
            ],
            // Test Invalid t1x
            [
                [
                    't1x' => 'asdf',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 't1x',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 't1x',
                        'reference.promiseType' => 'actual',
                    ]),
                ],
                false,
            ],
            // Test Invalid t2x
            [
                [
                    't2x' => 'asdf',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 't2x',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 't2x',
                        'reference.promiseType' => 'actual',
                    ]),
                ],
                false,
            ],
            // Test Invalid gitw
            [
                [
                    'gitw' => 101,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'gitw',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'gitw',
                        'reference.promiseType' => 'actual',
                    ]),
                ],
                false,
            ],
            // Test Invalid gitw 1
            [
                [
                    'gitw' => -101,
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'gitw',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'gitw',
                        'reference.promiseType' => 'actual',
                    ]),
                ],
                false,
            ],
            // Test Invalid gitw
            [
                [
                    'gitw' => 'asdf',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'gitw',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'gitw',
                        'reference.promiseType' => 'actual',
                    ]),
                ],
                false,
            ],
            // Test Invalid lf
            [
                [
                    'lf' => 'asdf',
                ],
                [
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'lf',
                        'reference.promiseType' => 'promise',
                    ]),
                    $this->getMessageData($this->messageTemplate, [
                        'id' => 'GENERAL_INVALID_VALUE',
                        'reference.game' => 'lf',
                        'reference.promiseType' => 'actual',
                    ]),
                ],
                false,
            ],
            // Test actuals not required when prior week
            [
                [
                    'week' => '2016-09-09',
                    'promise' => [
                        'cap' => 20,
                        'cpc' => 10,
                        't1x' => 3,
                        't2x' => 0,
                        'gitw' => 90,
                        'lf' => 8,
                    ],
                    'actual' => [
                    ],
                ],
                [],
                true,
            ],
        ];
    }

    public function getScoreboard($data)
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

        // Convert to correct format, but allow input data to provide correct format
        if (!isset($data['promise'])) {
            $data['promise'] = [];

            foreach (Domain\Scoreboard::GAME_KEYS as $game) {
                $data['promise'][$game] = $data[$game];
            }
        }

        if (!isset($data['actual'])) {
            $data['actual'] = [];

            foreach (Domain\Scoreboard::GAME_KEYS as $game) {
                $data['actual'][$game] = $data[$game];
            }
        }

        // Remove unneeded meta values
        foreach (Domain\Scoreboard::GAME_KEYS as $game) {
            unset($data[$game]);
        }

        return Domain\Scoreboard::fromArray($data);
    }
}
