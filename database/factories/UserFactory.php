<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

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

$factory->define(User::class, function (Faker $faker) {
    static $password;

    return [
        'username' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'nip' => $faker->unique()->randomNumber($nbDigits = 8),
        'jenis_kelamin' => $faker->randomElement($array = array ('Laki-laki', 'Perempuan')),
        'ttl' => $faker->dateTimeThisCentury->format('Y-m-d'),
        'full_name' => $faker->name,
        'phone' => '081352',
        'address' => $faker->address,
        'active' => '1',
        'password' => $password ?: $password = bcrypt('tes'), // password
        'created_at' => now()->toDateTimeString(),
        'created_by' => 1
    ];
});
