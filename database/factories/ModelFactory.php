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


/** @var \Illuminate\Database\Eloquent\Factory $factory */
//users
$factory->define(App\Api\V1\Models\User::class, function (Faker\Generator $faker) {
    $faker->addProvider(new Faker\Provider\en_NG\Person($faker));
    static $password;
    return [
        'username' => $faker->unique()->userName,
        'email' => $faker->unique()->safeEmail,        
        'password' => $password ?: $password = 'default1234',
        'first_name' => $faker->firstName(),        
        'last_name' => $faker->lastName,        
        'phone' => $faker->unique()->randomNumber(9),
    ];
});

//internal_users
$factory->define(App\Api\V1\Models\InternalUser::class, function (Faker\Generator $faker) {
    return [
        'employed_date' => '2017-12-13',
        'job_title' => 'Librarian',
    ];
});



//books
$factory->define(App\Api\V1\Models\Book::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->text(20),
        'short_description' => $faker->text(50),
        'full_description' => $faker->text(100),
        'author_name' => $faker->text(30),
        'page_count' => $faker->int(11),
        'price' => $faker->randomFloat(2, 0, 5000),
        'sku' => $faker->domainWord
    ];
});

