<?php
namespace TmlpStats\Tests\Functional\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

class AccountabilityMappingTest extends FunctionalTestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;
    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    public function setUp()
    {
        parent::setUp();

        $this->center = Models\Center::abbreviation('VAN')->first();
        $this->people = collect([]);
        $this->allAccountabilities = Models\Accountability::get()->keyBy('name');
    }

    protected function findAcc($accName)
    {
        return $this->allAccountabilities[$accName];
    }

    public function findPerson($name)
    {
        if (!$this->people->has($name)) {
            $this->people->put($name, factory(Models\Person::class)->create(['first_name' => 'person2', 'center_id' => $this->center->id]));
        }

        return $this->people->get($name);
    }

    protected function findPeople($names)
    {
        return collect($names)->map([$this, 'findPerson']);
    }

    /**
     * @dataProvider providerBulkSetEdgeCases
     */
    public function testbulkSetCenterAccountabilities_edgeCases($input)
    {
        $known = [];
        foreach ($input['initial'] as $k => list($person, $acc, $startsAt, $endsAt)) {
            $model = Models\AccountabilityMapping::create([
                'person_id' => $this->findPerson($person)->id,
                'accountability_id' => $this->findAcc($acc)->id,
                'center_id' => $this->center->id,
                'starts_at' => Carbon::parse($startsAt),
                'ends_at' => Carbon::parse($endsAt),
            ]);
            $known[$k] = $model->id;
        }
        $apply = [];
        foreach ($input['apply'] as $accName => $pNames) {
            $apply[$this->findAcc($accName)->id] = $this->findPeople($pNames)->pluck('id')->all();
        }

        $report = Models\AccountabilityMapping::bulkSetCenterAccountabilities($this->center, $input['startsAt'], $input['endsAt'], $apply);

        foreach ($input['report'] as $k => $v) {
            $this->assertEquals($v, $report[$k],
                "Unable to find JSON fragment report[{$k}] = " . print_r($v, true) . ' within [' . print_r($report, true) . '].');
        }

        if ($callback = array_get($input, 'extraChecks')) {
            $callback($this, $known);
        }
    }

    public function providerBulkSetEdgeCases()
    {
        return [
            [[
                'initial' => [
                    'r1' => ['person1', 't1tl', '2016-04-09', '2016-04-22'],
                ],
                'startsAt' => Carbon::parse('2016-04-15'),
                'endsAt' => Carbon::parse('2016-04-29'),
                'apply' => [
                    't1tl' => ['person1'],
                    'gitw' => ['person2'],
                ],
                'report' => ['shortened' => 1, 'deleted' => 0, 'created' => 1],
                'extraChecks' => function ($suite, $known) {
                    $r1 = Models\AccountabilityMapping::find($known['r1']);
                    $suite->assertTrue($r1->ends_at->eq(Carbon::parse('2016-04-29')));
                },
            ]],

            // Edge case: don't break "future" data.
            [[
                'initial' => [
                    'r1' => ['person1', 't1tl', '2016-04-09', '2016-04-22'],
                    'r2' => ['person2', 't1tl', '2016-04-22', '2016-05-01'],

                    'future1' => ['person7', 't1tl', '2016-06-01', '2016-09-01'],
                    'future2' => ['person7', 'gitw', '2016-06-01', '2016-09-01'],
                ],
                'startsAt' => Carbon::parse('2016-04-15'),
                'endsAt' => Carbon::parse('2016-06-01'),
                'apply' => [
                    't1tl' => ['person1'],
                    'gitw' => ['person2'],
                ],
                'report' => ['shortened' => 1, 'deleted' => 1, 'created' => 1],
                'extraChecks' => function ($suite, $known) {
                    $models = collect($known)->map(function ($x) {return Models\AccountabilityMapping::find($x);});
                    $r1 = $models->get('r1');
                    $suite->assertTrue($r1->ends_at->eq(Carbon::parse('2016-06-01')));
                    $suite->assertNull($models->get('r2'));
                },
            ]],

            // Edge case: end overlapping
            [[
                'initial' => [
                    'end1' => ['person1', 't1tl', '2016-05-09', '2016-06-15'],
                    'end2' => ['person2', 't1tl', '2016-05-09', '2016-06-15'],

                    'future1' => ['person7', 't1tl', '2016-06-21', '2016-09-01'],
                    'future2' => ['person7', 'gitw', '2016-06-21', '2016-09-01'],
                ],
                'startsAt' => Carbon::parse('2016-04-15'),
                'endsAt' => Carbon::parse('2016-06-01'),
                'apply' => [
                    't1tl' => ['person1'],
                    'gitw' => ['person2'],
                ],
                'report' => ['lengthened' => 1, 'shortened' => 1, 'deleted' => 0, 'created' => 1],
                'extraChecks' => function ($suite, $known) {
                    $models = collect($known)->map(function ($x) {return Models\AccountabilityMapping::find($x);});
                    $suite->assertTrue($models->get('end1')->starts_at->eq(Carbon::parse('2016-04-15')));
                    $suite->assertTrue($models->get('end2')->starts_at->eq(Carbon::parse('2016-06-01')));
                    $suite->assertNotNull($models->get('future1'));
                    $suite->assertNotNull($models->get('future2'));
                },
            ]],

            // detailed 'past' data
            [[
                'initial' => [
                    'p1' => ['person5', 't1tl', '2016-02-01', '2016-04-14'],
                    'p2' => ['person6', 'gitw', '2016-02-01', '2016-04-15'],
                    'p3' => ['person1', 'lf', '2016-02-01', '2016-04-15'],
                    'p4' => ['person2', 'cap', '2016-02-01', '2016-04-15'],

                    'r1' => ['person1', 't1tl', '2016-04-15', '2016-04-22'],
                    'r2' => ['person2', 'gitw', '2016-04-15', '2016-04-22'],
                    'r3' => ['person2', 'lf', '2016-04-15', '2016-04-22'],

                    'future1' => ['person7', 't1tl', '2016-06-01', '2016-09-01'],
                    'future2' => ['person7', 'gitw', '2016-06-01', '2016-09-01'],
                ],
                'startsAt' => Carbon::parse('2016-04-15'),
                'endsAt' => Carbon::parse('2016-06-01'),
                'apply' => [
                    't1tl' => ['person1'],
                    'cap' => ['person2'],
                    'gitw' => ['person2'],
                    'lf' => ['person3'],
                ],
                'report' => ['shortened' => 2, 'deleted' => 1, 'created' => 2],
                'extraChecks' => function ($suite, $known) {
                    $models = collect($known)->map(function ($x) {return Models\AccountabilityMapping::find($x);});
                    $suite->assertNotNull($models->get('p1'));
                    $suite->assertNotNull($models->get('p2'));
                    $suite->assertNotNull($models->get('p3'));
                    $suite->assertNotNull($models->get('p4'));
                    $suite->assertNotNull($models->get('r1'));
                    $suite->assertNotNull($models->get('r2'));
                    $suite->assertNull($models->get('r3'));
                    $suite->assertNotNull($models->get('future1'));
                },
            ]],
        ];
    }
}
