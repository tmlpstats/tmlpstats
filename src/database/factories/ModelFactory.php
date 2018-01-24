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
        'last_name' => $faker->unique()->lastName(),
        // 'phone'      => $faker->unique()->phoneNumber(),
        'email' => $faker->unique()->safeEmail(),
        'center_id' => 1,
        'identifier' => $faker->unique()->uuid(),
    ];
});

$factory->define(TmlpStats\User::class, function (Faker\Generator $faker) {
    $person = factory(TmlpStats\Person::class)->create();

    return [
        'email' => $person->email,
        'password' => bcrypt(str_random(10)),
        'person_id' => $person->id,
        'role_id' => 1,
        'last_login_at' => $faker->dateTimeThisMonth(),
    ];
});

$factory->define(TmlpStats\TmlpRegistration::class, function (Faker\Generator $faker) {
    $person = factory(TmlpStats\Person::class)->create();

    return [
        'person_id' => $person->id,
        'team_year' => $faker->numberBetween(1, 2),
        'reg_date' => Carbon::parse('2016-04-08'),
        'is_reviewer' => false,
    ];
});

$factory->define(TmlpStats\TeamMember::class, function (Faker\Generator $faker) {
    $person = factory(TmlpStats\Person::class)->create();

    return [
        'person_id' => $person->id,
        'team_year' => $faker->numberBetween(1, 2),
        'incoming_quarter_id' => 1,
        'is_reviewer' => false,
    ];
});

$factory->defineAs(TmlpStats\TeamMember::class, 'noPerson', function (Faker\Generator $faker) {
    return [
        'team_year' => $faker->numberBetween(1, 2),
        'incoming_quarter_id' => 1,
        'is_reviewer' => false,
    ];
});

$factory->define(TmlpStats\Course::class, function (Faker\Generator $faker) {
    return [
        'center_id' => $faker->numberBetween(1, 24),
        'start_date' => Carbon::parse('2016-04-23'),
        'type' => 'CAP',
    ];
});

$factory->define(TmlpStats\CourseData::class, function (Faker\Generator $faker) {
    return [
        'course_id' => factory(TmlpStats\Course::class)->create()->id,
        'quarter_start_ter' => 0, 'current_ter' => 0,
        'quarter_start_standard_starts' => 0, 'current_standard_starts' => 0,
        'quarter_start_xfer' => 0, 'current_xfer' => 0,
    ];
});
