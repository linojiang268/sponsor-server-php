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

$factory->define(Sponsor\Models\Sponsor::class, function (Faker\Generator $faker) {
    return [
        'name'           => $faker->name,
        'email'          => $faker->email,
        'password'       => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
        'intro'          => str_random(256),
        'status'         => 1,
        'salt'           => str_random(16),
    ];
});

$factory->define(Sponsor\Models\Sponsorship::class, function (Faker\Generator $faker) {
    return [
        'name'                   => $faker->name,
        'sponsor_id'             => $faker->randomNumber(),
        'intro'                  => $faker->text,
        'application_start_date' => $faker->date('Y-m-d'),
        'application_end_date'   => $faker->date('Y-m-d'),
        'application_condition'  => $faker->text,
        'status'                 => Sponsor\Models\Sponsorship::STATUS_PENDING,
    ];
});

$factory->define(Sponsor\Models\SponsorshipApplication::class, function (Faker\Generator $faker) {
    return [
        'sponsorship_id' => $faker->randomNumber(),
        'team_id'        => $faker->randomNumber(),
        'status'         => Sponsor\Models\SponsorshipApplication::STATUS_PENDING,
    ];
});
$factory->define(Sponsor\Models\User::class, function ($faker) {
    return [
        'mobile'         => '1' . $faker->numerify('##########'),
        'salt'           => str_random(16),
        'password'       => str_random(32),
        'remember_token' => str_random(10),
        'nick_name'      => 'zhangsan',
        'status'         => \Sponsor\Entities\User::STATUS_NORMAL,
    ];
});
$factory->define(Sponsor\Models\Team::class, function ($faker) {
    return [
        'creator_id'    => $faker->randomNumber,
        'name'          => str_random(32),
        'email'         => str_random(64),
        'address'       => $faker->address,
        'contact_phone' => $faker->phoneNumber,
        'contact'       => str_random(32),
        'introduction'  => str_random(200),
        'status'        => \Sponsor\Entities\Team::STATUS_NORMAL,
    ];
});