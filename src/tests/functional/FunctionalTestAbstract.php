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

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
        $this->user = factory(Models\User::class)->create();
        $this->be($this->user);
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
            $this->assertEquals($value, array_get($actual, $key), "Unable to find JSON fragment actual[{$key}] = {$value} within [" . print_r($actual, true) . "].");
        }

        return $this;
    }
}
