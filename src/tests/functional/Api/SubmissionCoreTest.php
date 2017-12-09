<?php
namespace TmlpStats\Tests\Functional\Api;

use App;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;
use TmlpStats\Tests\Mocks\MockContext;

class SubmissionCoreTest extends FunctionalTestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    protected $centerId = 1;

    public function setUp()
    {
        parent::setUp();

        $reportingDateStr = '2016-04-15';
        $this->reportingDate = Carbon::parse($reportingDateStr);

        $this->center = Models\Center::find($this->centerId);
        $this->quarter = Models\Quarter::year(2016)->quarterNumber(1)->first();

        // Order important: context should be installed before creating a CenterQuarter which relies on context
        $this->context = MockContext::defaults()->withUser($this->user)->withCenter($this->center)->install();
        $this->cq = Domain\CenterQuarter::ensure($this->center, $this->quarter);

        $this->api = App::make(Api\SubmissionCore::class);
    }

    public function testCompleteSubmissionFailsAuth()
    {
        $this->expectException(Api\Exceptions\UnauthorizedException::class);

        $this->context->withOverrideCan(function ($priv, $center) {
            return ($priv === 'submitOldStats' && $center->id == $this->center->id);
        })->install();

        $this->api->completeSubmission($this->center, $this->reportingDate, ['comment' => 'great success']);
    }

    /**
     * @dataProvider providerCompleteSubmissions
     */
    public function testCompleteSubmissionSucceeds($validationResults, $expectedResults)
    {
        $this->context->withOverrideCan(function ($priv, $center) {
            return (($priv === 'submitStats' || $priv === 'submitOldStats') && $center->id == $this->center->id);
        })->install();

        $validateApi = $this->getMockBuilder(Api\ValidationData::class)
                            ->setMethods(['validate'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $validateApi->expects($this->once())
                    ->method('validate')
                    ->with($this->equalTo($this->center), $this->equalTo($this->reportingDate))
                    ->willReturn($validationResults);

        App::bind(Api\ValidationData::class, function ($app) use ($validateApi) {
            return $validateApi;
        });

        $result = $this->api->completeSubmission($this->center, $this->reportingDate, ['comment' => 'great success']);

        $this->assertEquals($expectedResults, $result);
    }

    public function providerCompleteSubmissions()
    {
        return [
            // Validation succeeds and submission returns success
            // TODO: Disabled for now. Need to write full tests for submission flow
            // [
            //     [
            //         'success' => true,
            //         'valid' => true,
            //         'messages' => [],
            //     ],
            //     [
            //         'success' => true,
            //         'id' => $this->centerId,
            //     ],
            // ],
            // Validation fails and submission returns failure
            [
                [
                    'success' => true,
                    'valid' => false,
                    'messages' => ['messages'],
                ],
                [
                    'success' => false,
                    'id' => $this->centerId,
                    'message' => 'Validation failed. Please correct issues indicated on the Review page and try again.',
                ],
            ],
        ];
    }

    public function testSubmitApplications()
    {
        // Setup global data
        $lastWeekDate = $this->reportingDate->copy()->subWeek();
        $lastStatsReport = App::make(Api\LocalReport::class)->ensureStatsReport(
            $this->center,
            $lastWeekDate
        );
        $statsReport = App::make(Api\LocalReport::class)->ensureStatsReport(
            $this->center,
            $this->reportingDate
        );

        // Setup last week's report as "official"
        Models\GlobalReport::firstOrCreate([
            'reporting_date' => $lastWeekDate,
        ])->addCenterReport($lastStatsReport);

        $nextQuarter = $this->quarter->getNextQuarter();
        $twoQuartersFromNow = $this->quarter->getNextQuarter()->getNextQuarter();

        $reg1 = factory(Models\TmlpRegistration::class)->create();
        $reg2 = factory(Models\TmlpRegistration::class)->create();

        $member1 = factory(Models\TeamMember::class)->create();
        $member2 = factory(Models\TeamMember::class)->create();

        $expected = [
            // This app had data last week and wasn't updated this week
            1 => [
                'id' => $reg1->id,
                'firstName' => $reg1->firstName,
                'lastName' => $reg1->lastName,
                'center' => $this->center->id,
                'teamYear' => $reg1->teamYear,
                'isReviewer' => false,
                'regDate' => $reg1->regDate,
                'appOutDate' => $lastWeekDate->copy()->subDays(6),
                'appInDate' => $lastWeekDate->copy()->subDays(5),
                'apprDate' => $lastWeekDate->copy()->subDays(4),
                'wdDate' => null,
                'withdrawCode' => null,
                'committedTeamMember' => $member1->id,
                'incomingQuarter' => $nextQuarter->id,
                'comment' => 'a comment',
                'travel' => false,
                'room' => false,
            ],
            // This app had data last week and was updated this week
            2 => [
                'id' => $reg2->id,
                'firstName' => $reg2->firstName,
                'lastName' => $reg2->lastName,
                'center' => $this->center->id,
                'teamYear' => $reg2->teamYear,
                'isReviewer' => false,
                'regDate' => $reg2->regDate,
                'appOutDate' => $lastWeekDate->copy()->subDays(2),
                'appInDate' => $lastWeekDate->copy()->subDays(1),
                'apprDate' => $lastWeekDate,
                'wdDate' => null,
                'withdrawCode' => null,
                'committedTeamMember' => $member2->id,
                'incomingQuarter' => $twoQuartersFromNow->id,
                'comment' => 'another comment',
                'travel' => true,
                'room' => true,
            ],
            // This app is new this week
            3 => [
                'id' => 3,
                'firstName' => $this->faker->unique()->firstName(),
                'lastName' => $this->faker->unique()->lastName(),
                'center' => $this->center->id,
                'teamYear' => 1,
                'isReviewer' => false,
                'regDate' => $this->reportingDate->copy()->subDays(6),
                'appOutDate' => $this->reportingDate->copy()->subDays(5),
                'appInDate' => $this->reportingDate->copy()->subDays(4),
                'apprDate' => $this->reportingDate->copy()->subDays(3),
                'committedTeamMember' => null,
                'incomingQuarter' => $nextQuarter->id,
                'comment' => 'a new comment',
                'travel' => true,
                'room' => true,
            ],
        ];

        // Existing Registration 1, no updates this week
        $reg1Data = Models\TmlpRegistrationData::create([
            'stats_report_id' => $lastStatsReport->id,
            'tmlp_registration_id' => $expected[1]['id'],
            'incoming_quarter_id' => $expected[1]['incomingQuarter'],
            'committed_team_member_id' => $expected[1]['committedTeamMember'],
            'reg_date' => $expected[1]['regDate'],
            'app_out_date' => $expected[1]['appOutDate'],
            'app_in_date' => $expected[1]['appInDate'],
            'appr_date' => $expected[1]['apprDate'],
            'wd_date' => $expected[1]['wdDate'],
            'comment' => $expected[1]['comment'],
            'travel' => $expected[1]['travel'],
            'room' => $expected[1]['room'],
        ]);

        // Existing Registration 2
        $reg2Data = Models\TmlpRegistrationData::create([
            'stats_report_id' => $lastStatsReport->id,
            'tmlp_registration_id' => $expected[2]['id'],
            'incoming_quarter_id' => $expected[2]['incomingQuarter'],
            'committed_team_member_id' => $expected[2]['committedTeamMember'],
            'reg_date' => $expected[2]['regDate'],
            'app_out_date' => $expected[2]['appOutDate'],
            'app_in_date' => $expected[2]['appInDate'],
            'appr_date' => $expected[2]['apprDate'],
            'wd_date' => $expected[2]['wdDate'],
            'comment' => $expected[2]['comment'],
            'travel' => $expected[2]['travel'],
            'room' => $expected[2]['room'],
        ]);

        // Updates for second stash
        $expected[2] = array_merge($expected[2], [
            'firstName' => strtoupper($reg2->firstName),
            'wdDate' => $this->reportingDate->copy()->subDays(4),
            'withdrawCode' => 1,
            'comment' => 'someone withdrew',
        ]);

        $sd = App::make(Api\SubmissionData::class);

        // Create updated stash for Existing Registration 2
        $reg2Domain = Domain\TeamApplication::fromModel($reg2Data, $reg2);
        $reg2Domain->firstName = $expected[2]['firstName'];
        $reg2Domain->wdDate = $expected[2]['wdDate'];
        $reg2Domain->withdrawCode = Models\WithdrawCode::find($expected[2]['withdrawCode']);
        $reg2Domain->comment = $expected[2]['comment'];
        $sd->store($this->center, $this->reportingDate, $reg2Domain);

        // Create stash for new application
        $reg3Domain = Domain\TeamApplication::fromArray(
            array_merge($expected[3], ['id' => '-1234']) // fake ID since we're not using stash method
        );
        $sd->store($this->center, $this->reportingDate, $reg3Domain);

        // Submit the applications
        $apps = App::make(Api\Application::class)->allForCenter($this->center, $this->reportingDate, true);
        $this->api->submitApplications($this->center, $this->reportingDate, $statsReport, $apps);

        // Setup this week's report as "official"
        Models\GlobalReport::firstOrCreate([
            'reporting_date' => $this->reportingDate,
        ])->addCenterReport($statsReport);

        // verify resulting data is correct
        $persisted = App::make(Api\Application::class)->allForCenter($this->center, $this->reportingDate, false);

        foreach ($persisted as $id => $app) {
            foreach ($expected[$id] as $key => $value) {
                if (!is_object($app->$key) || ($app->$key instanceof Carbon)) {
                    $this->assertEquals($value, $app->$key, "App {$id} key {$key} doesn't match expected {$value}");
                } else {
                    $this->assertEquals($value, $app->$key->id, "App {$id} key {$key} doesn't match expected id {$value}");
                }
            }
        }
    }

    /**
     * @dataProvider providerSubmitTeamAccountabilities
     */
    public function testSubmitTeamAccountabilities($setAccountabilities)
    {
        $allAccountabilities = Models\Accountability::get()->keyBy('name');
        $teamMembers = [];
        $byName = [];
        $findAcc = function ($accName) use ($allAccountabilities) {
            return $allAccountabilities[$accName];
        };

        $reportNow = $this->reportingDate->copy()->setTime(15, 0, 0);
        $quarterEndDate = $this->cq->endWeekendDate->copy()->addDay()->setTime(12, 00, 00);

        // Create fake TMData
        foreach ($this->sequentialNamedTeam() as $tm) {
            $tmd = new Models\TeamMemberData(['at_weekend' => true]); // bogus
            $domain = Domain\TeamMember::fromModel($tmd, $tm);
            $domain->meta['personId'] = $tm->person->id;
            $domain->meta['personObj'] = $tm->person; // atypical, only used for this test.
            $teamMembers[$domain->id] = $domain;
            $byName[$tm->firstName] = $domain->id;
        }

        // Set accountabilities from input, and set up 'initial' accountabilities as desired.
        foreach ($setAccountabilities as $firstName => $data) {
            $tmDomain = $teamMembers[$byName[$firstName]];
            $initial = $setAccountabilities[$firstName]['initial'] = collect(array_get($data, 'initial', []))
                ->map(function ($x) use ($findAcc) {
                    return [
                        'acc' => $findAcc($x[0]),
                        'starts_at' => isset($x[1]) ? Carbon::parse($x[1]) : null,
                        'ends_at' => isset($x[2]) ? Carbon::parse($x[2]) : null,
                    ];
                });
            $setAccountabilities[$firstName]['initialAcc'] = $initial->pluck('acc');
            foreach ($initial as $m) {
                $tmDomain->meta['personObj']->addAccountability(
                    $m['acc'],
                    $m['starts_at'] ?: $this->cq->firstWeekDate,
                    $m['ends_at'] ?: $quarterEndDate
                );
            }

            if ($assigned = array_get($data, 'assign', null)) {
                $tmDomain->accountabilities = collect($assigned)
                    ->map($findAcc)
                    ->pluck('id')
                    ->sort()
                    ->all();
            } else {
                $tmDomain->accountabilities = [];
            }
        }

        // Actually run API
        $this->api->submitTeamAccountabilities($this->center, $this->reportingDate, $reportNow, $quarterEndDate, $teamMembers);

        $tomorrow = $this->reportingDate->copy()->addDay();
        $yesterday = $this->reportingDate->copy()->subDay();
        // Verify accountabilities were set effectively
        foreach ($setAccountabilities as $firstName => $data) {
            $tmDomain = $teamMembers[$byName[$firstName]];
            $person = $tmDomain->meta['personObj'];
            $current = $person->getAccountabilityIds($tomorrow);
            sort($current);
            $this->assertEquals($tmDomain->accountabilities, $current);
            // Ensure 'initial' accountabilities were ended or continued.
            foreach ($data['initial'] as $m) {
                if ($m['starts_at'] && $m['starts_at']->gte($tomorrow)) {
                    continue;
                }
                $acc = $m['acc'];
                $this->assertEquals(true, $person->hasAccountability($acc, $yesterday), "Expected {$firstName} to have accountability {$acc->name} yesterday");
                if (in_array($acc->id, $tmDomain->accountabilities)) {
                    $this->assertEquals(true, $person->hasAccountability($acc, $tomorrow));
                } else {
                    $this->assertEquals(false, $person->hasAccountability($acc, $tomorrow));
                }
            }
        }
    }

    public function providerSubmitTeamAccountabilities()
    {
        return [
            // set some accountabilities only with no initial values to fall bakc on
            [[
                'person1' => ['assign' => ['t1tl', 'cap']],
                'person2' => ['assign' => ['t2tl']],
                'person3' => ['assign' => ['cpc', 't1x']],
            ]],

            // second run: set with some existing initial values.
            [[
                'person1' => ['assign' => ['t1tl', 'cap']],
                'person2' => ['assign' => ['t2tl']],
                'person3' => ['assign' => ['cpc', 't1x']],
                'person4' => [
                    'initial' => [
                        ['cap', '2016-04-08'],
                        ['logistics', '2016-04-01'],
                    ],
                    'assign' => ['logistics'],
                ],
                'person5' => [
                    'initial' => [
                        ['gitw', '2016-04-01'],
                    ],
                    'assign' => ['gitw', 'lf'],
                ],
            ]],

            // Test some weird initial data
            [[
                'person1' => [
                    'initial' => [
                        ['cap', '2016-04-08', '2016-04-15'],
                        ['cap', '2016-04-15', '2016-04-22'],
                    ],
                    'assign' => ['t1tl', 'cap'],
                ],
                'person2' => [
                    'initial' => [
                        ['t1tl', '2016-04-01'],
                        ['logistics'],
                    ],
                    'assign' => [],
                ],
                'person3' => [
                    'initial' => [
                        ['t1tl', '2016-04-22'],
                    ],
                    'assign' => [],
                ],
            ]],
        ];
    }

    /**
     * @dataProvider providerSubmitTeamMembers
     */
    public function testSubmitTeamMembers($setTM)
    {
        $lastQuarter = Models\Quarter::year(2015)->quarterNumber(4)->firstOrFail();
        $statsReport = App::make(Api\LocalReport::class)->ensureStatsReport(
            $this->center,
            $this->reportingDate
        );
        $teamMembers = [];
        $byName = [];

        $reportNow = $this->reportingDate->copy()->setTime(15, 0, 0);
        $quarterEndDate = $this->cq->endWeekendDate->copy()->addDay()->setTime(12, 00, 00);

        // Create fake TMData to start
        foreach ($this->sequentialNamedTeam() as $tm) {
            $tmd = new Models\TeamMemberData(['at_weekend' => true]); // bogus
            $domain = Domain\TeamMember::fromModel($tmd, $tm);
            $domain->meta['personId'] = $tm->person->id;
            $domain->meta['personObj'] = $tm->person; // atypical, only used for this test.
            $domain->clearSetValues();
            $teamMembers[$domain->id] = $domain;
            $byName[$tm->firstName] = $domain->id;
        }

        // Set team member stashes from input
        $negativeId = -100;
        foreach ($setTM as $firstName => $data) {
            if ($tmId = $byName[$firstName] ?? null) {
                $tmDomain = $teamMembers[$tmId];
            } else {
                $tmDomain = Domain\TeamMember::fromArray([
                    'id' => --$negativeId,
                    'at_weekend' => true,
                    'is_reviewer' => false,
                    'firstName' => $firstName,
                    'lastName' => 'newPerson',
                    'teamYear' => 1,
                    'incomingQuarter' => $lastQuarter->id,
                ]);
                $teamMembers[$tmDomain->id] = $tmDomain;
            }

            if (isset($data['stashed'])) {
                $stashed = collect($data['stashed'])->map(function ($v, $k) {
                    return ($v instanceof \Closure) ? $v() : $v;
                });
                $tmDomain->updateFromArray($stashed->all());
            }
        }

        // Actually run API
        $this->api->submitTeamMembers($this->center, $this->reportingDate, $statsReport, $teamMembers);

        $allTmd = Models\TeamMemberData::byStatsReport($statsReport)
            ->get()
            ->keyBy('teamMemberId');

        // Run checks that are set
        foreach ($setTM as $firstName => $data) {
            if ($tmId = $byName[$firstName] ?? null) {
                $tmd = $allTmd->get($tmId);
            } else {
                $tmd = $allTmd->first(function ($k, $x) use ($firstName) {return $x->firstName == $firstName;});
            }
            $this->assertNotNull($tmd, "{$firstName} has missing TMData");

            if ($checker = $data['checks'] ?? null) {
                $checker($this, $tmd, $tmd->teamMember);
            }

        }
    }

    public function providerSubmitTeamMembers()
    {
        return [
            // First run, do a withdraw and some other things
            [[
                'person1' => [
                    'stashed' => ['withdrawCode' => 1, 'tdo' => 0, 'travel' => true, 'room' => false],
                    'checks' => function ($suite, $tmd, $tm) {
                        $suite->assertNotEmpty($tm->firstName);
                        $suite->assertNotEmpty($tm->lastName);
                        $suite->assertEquals(1, $tmd->withdrawCodeId);
                        $suite->assertEquals(1, $tmd->withdrawCode->id);
                        $suite->assertTrue($tmd->travel);
                        $suite->assertFalse($tmd->room);
                    },
                ],
                'person2' => [
                    'stashed' => ['gitw' => true, 'tdo' => 2, 'firstName' => 'Bob', 'teamYear' => 2],
                    'checks' => function ($suite, $tmd, $tm) {
                        $suite->assertEquals(null, $tmd->withdrawCode);
                        $suite->assertEquals(true, $tmd->gitw);
                        $suite->assertEquals(2, $tmd->tdo);
                        $suite->assertEquals(2, $tm->teamYear);
                        $suite->assertEquals('Bob', $tm->person->firstName);
                    },
                ],
                'person3' => [
                    'checks' => function ($suite, $tmd, $tm) {
                        $suite->assertEquals(false, $tmd->gitw);
                        $suite->assertEquals(0, $tmd->tdo);
                        $suite->assertEquals(0, $tmd->rppCap);
                        $suite->assertEquals(0, $tmd->rppCpc);
                        $suite->assertEquals(0, $tmd->rppLf);
                    },
                ],
                'person4' => [
                    'stashed' => [
                        'travel' => false, 'room' => true,
                        'rppCap' => 1, 'rppCpc' => 2, 'rppLf' => 3,
                        '_personId' => function () {
                            return factory(Models\Person::class)->create([
                                'firstName' => 'otherPerson',
                                'lastName' => 'otherPersonLastName',
                                'identifier' => 'op',
                            ])->id;
                        },
                    ],
                    'checks' => function ($suite, $tmd, $tm) {
                        $suite->assertFalse($tmd->travel);
                        $suite->assertTrue($tmd->room);
                        $suite->assertEquals(1, $tmd->rppCap);
                        $suite->assertEquals(2, $tmd->rppCpc);
                        $suite->assertEquals(3, $tmd->rppLf);
                        $suite->assertEquals('op', $tm->person->identifier);
                    },
                ],
                'person5' => [
                    // Testing accountabilities tests an issue we found with `fillModel` - worked but did odd things
                    'stashed' => ['accountabilities' => [7, 8]],
                ],
                // This checks that we can create new people too
                'newPerson1' => [
                    'stashed' => ['gitw' => false, 'tdo' => 1],
                    'checks' => function ($suite, $tmd, $tm) {
                        $suite->assertFalse($tmd->gitw);
                        $suite->assertEquals(1, $tmd->tdo);
                        $suite->assertGreaterThan(1, $tm->id);
                    },
                ],
            ]],

            // This checks a few edge cases like withdrawn new person
            [[
                'createPerson1' => [
                    'stashed' => ['withdrawCode' => 1],
                    'checks' => function ($suite, $tmd, $tm) {
                        $suite->assertFalse($tmd->gitw);
                    },
                ],
            ]],
        ];
    }

    public function sequentialNamedTeam()
    {
        $peeps = [];
        for ($i = 1; $i <= 10; $i++) {
            $person = factory(Models\Person::class)->create(['first_name' => "person{$i}"]);
            $teamMember = factory(Models\TeamMember::class, 'noPerson')->create(['person_id' => $person->id]);
            $teamMember->setRelation('person', $person);
            $peeps[] = $teamMember;
        }

        return $peeps;
    }
}
