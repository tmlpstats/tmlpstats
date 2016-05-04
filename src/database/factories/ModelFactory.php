<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

use Carbon\Carbon;

$factory->define(TmlpStats\Person::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->unique()->firstName(),
        'last_name'  => $faker->unique()->lastName(),
        'phone'      => $faker->unique()->phoneNumber(),
        'email'      => $faker->unique()->safeEmail(),
        'center_id'  => 1,
        'identifier' => $faker->unique()->uuid(),
    ];
});

$factory->define(TmlpStats\User::class, function (Faker\Generator $faker) {
    $person = factory(TmlpStats\Person::class)->create();

    return [
        'email'         => $person->email,
        'password'      => bcrypt(str_random(10)),
        'person_id'     => $person->id,
        'role_id'       => 1,
        'last_login_at' => $faker->dateTimeThisMonth(),
    ];
});

$factory->define(TmlpStats\TmlpRegistration::class, function (Faker\Generator $faker) {
    $person = factory(TmlpStats\Person::class)->create();

    return [
        'person_id'   => $person->id,
        'team_year'   => $faker->randomKey([1, 2]),
        'reg_date'    => Carbon::parse('2016-04-08'),
        'is_reviewer' => false,
    ];
});

$factory->define(TmlpStats\TeamMember::class, function (Faker\Generator $faker) {
    $person = factory(TmlpStats\Person::class)->create();

    return [
        'person_id'           => $person->id,
        'team_year'           => $faker->randomKey([1, 2]),
        'incoming_quarter_id' => 1,
    ];
});

$factory->define(TmlpStats\Course::class, function (Faker\Generator $faker) {
    return [
        'center_id'  => $faker->numberBetween(1, 24),
        'start_date' => Carbon::parse('2016-04-23'),
        'type'       => 'CAP',
    ];
});
