<?php
namespace TmlpStats\Tests\Functional\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

class AccountabilityTest extends FunctionalTestAbstract
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
    }

    public function testRemoveAccountabilityFromCenter_except()
    {
        list($acc, $person, $person2) = $this->twoPeopleWithAccountabilities();
        $this->assertEquals(1, count($person->getAccountabilityIds(Carbon::parse('2017-02-01'))));

        // Remove the accountability excluding one person.
        $changed = Models\Accountability::removeAccountabilityFromCenter($acc->id, $this->center->id, Carbon::parse('2017-01-30'), $person->id);
        $this->assertEquals(1, $changed);
        $this->assertEquals(1, count($person->getAccountabilityIds(Carbon::parse('2017-02-01')))); // Accountability still exists for this person.
        $this->assertEquals(0, count($person2->getAccountabilityIds(Carbon::parse('2017-02-01')))); // but not for person 2
        $this->assertEquals(1, count($person2->getAccountabilityIds(Carbon::parse('2017-01-29')))); // but still exists as of 01-29
    }

    public function testRemoveAccountabilityFromCenter_noExcept()
    {
        list($acc, $person, $person2) = $this->twoPeopleWithAccountabilities();

        // Remove the accountability.
        $changed = Models\Accountability::removeAccountabilityFromCenter($acc->id, $this->center->id, Carbon::parse('2017-01-30'));
        $this->assertEquals(2, $changed);
        $this->assertEquals(0, count($person->getAccountabilityIds(Carbon::parse('2017-02-01')))); // Removed.
        $this->assertEquals(1, count($person->getAccountabilityIds(Carbon::parse('2017-01-29')))); // but still exists as of 01-29
        $this->assertEquals(0, count($person2->getAccountabilityIds(Carbon::parse('2017-02-01')))); // Same for person 2.
        $this->assertEquals(1, count($person2->getAccountabilityIds(Carbon::parse('2017-01-29')))); // but still exists as of 01-29
    }

    // factory helper - two people with the same accountability. Shouldn't happen often, but we're testing all edge cases here.
    public function twoPeopleWithAccountabilities()
    {
        $acc = Models\Accountability::name('statistician')->first();
        $person = factory(Models\Person::class)->create(['center_id' => $this->center->id]);
        $person2 = factory(Models\Person::class)->create(['center_id' => $this->center->id]);

        $person->addAccountability($acc, Carbon::parse('2017-01-01'), Carbon::parse('2017-06-01'));
        $person2->addAccountability($acc, Carbon::parse('2017-01-01'), Carbon::parse('2017-07-01'));

        return [$acc, $person, $person2];
    }
}
