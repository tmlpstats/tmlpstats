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
        $this->context = MockContext::defaults()->withCenter($this->center)->install();
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
            $setAccountabilities[$firstName]['initial'] = $initial = collect(array_get($data, 'initial', []))->map($findAcc);
            foreach ($initial as $acc) {
                $tmDomain->meta['personObj']->addAccountability($acc, $this->cq->firstWeekDate, $quarterEndDate);
            }

            if ($assigned = array_get($data, 'assign', null)) {
                $tmDomain->accountabilities = collect($assigned)
                    ->map($findAcc)
                    ->pluck('id')
                    ->sort()
                    ->all();
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
            foreach ($data['initial'] as $acc) {
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
                    'initial' => ['cap', 'logistics'],
                    'assign' => ['logistics'],
                ],
                'person5' => [
                    'initial' => ['gitw'],
                    'assign' => ['gitw', 'lf'],
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
