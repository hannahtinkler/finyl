<?php

use Illuminate\Support\Str;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'remember_token' => Str::random(10),
        'spotify_id' => $faker->numberBetween(1000000, 99999999),
        'spotify_access_token' => $faker->bothify('??##?#??##'),
        'spotify_refresh_token' => $faker->bothify('??##?#??##'),
    ];
});
