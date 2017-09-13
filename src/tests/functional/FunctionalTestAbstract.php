<?php
namespace TmlpStats\Tests\Functional;

use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Tests\TestAbstract;

class FunctionalTestAbstract extends TestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $faker;
    protected $user;
    protected $desiredRole = 'administrator'; // For some reason, the default factory is admin

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
        $this->user = $this->createUser($this->desiredRole, true);

        Models\ModelCache::create()->flush();
    }

    protected function createUser(string $desiredRole, bool $beUser = false): Models\User
    {
        $role = Models\Role::firstOrCreate(['name' => $desiredRole]);
        $user = factory(Models\User::class)->create(['role_id' => $role->id]);
        if ($beUser) {
            $this->be($user);
        }

        return $user;
    }

    /**
     * Takes a multidimensional array, and replaces values using the replace array.
     *
     * Replace should use the dot notation similar to what's returned by calling array_dot($array);
     *
     * @param $array    array with hierarchical data
     * @param $replace  array with dotted replacement data
     *
     * @return mixed
     */
    public function replaceInto($array, $replace)
    {
        foreach ($replace as $key => $value) {
            array_set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Check if $expected exists within the actual result. Matches array hierarchy
     *
     * @param array|null $expected
     *
     * @return $this|void
     */
    public function seeJsonHas(array $expected = null)
    {
        if (is_null($expected)) {
            $this->assertJson(
                $this->response->getContent(), "JSON was not returned from [{$this->currentUri}]."
            );

            return $this;
        }

        $actual = json_decode($this->response->getContent(), true);
        if (is_null($actual) || $actual === false) {
            return $this->fail('Invalid JSON was returned from the route. Perhaps an exception was thrown?');
        }

        $expected = array_dot($expected);
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, array_get($actual, $key), "Unable to find JSON fragment actual[{$key}] = " . print_r($value, true) . ' within [' . print_r($actual, true) . '].');
        }

        return $this;
    }

    public function getReport($reportingDate, $data = [])
    {
        if (!isset($this->center)) {
            throw new Exception('$this->center must be set.');
        }
        if (!isset($this->quarter)) {
            throw new Exception('$this->quarter must be set.');
        }

        $reportData = array_merge([
            'center_id' => $this->center->id,
            'quarter_id' => $this->quarter->id,
            'reporting_date' => $reportingDate,
            'submitted_at' => "{$reportingDate} 18:59:00",
            'version' => 'test',
        ], $data);

        return Models\StatsReport::firstOrCreate($reportData);
    }

    public function getGlobalReport($reportingDate, $reports = [])
    {
        if (!isset($this->center)) {
            throw new Exception('$this->center must be set.');
        }
        if (!isset($this->user)) {
            throw new Exception('$this->user must be set.');
        }

        $globalReport = Models\GlobalReport::firstOrCreate([
            'reporting_date' => $reportingDate,
            'quarter_id' => $this->quarter->id,
            'user_id' => $this->user->id,
        ]);

        foreach ($reports as $report) {
            $globalReport->addCenterReport($report);
        }

        return $globalReport;
    }
}
