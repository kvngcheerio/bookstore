<?php

/* adding the super admin account on seed */
/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\Api\V1\Models\User::class, function () {
    return [
        'username' => '',
        'email' => '',
        'password' => 'default1234',
        //'role_id' => 1,
        'first_name' => '',
        'last_name' => 'Admin',
        'phone' => '',
        'is_active' => 1
    ];
});

$factory->define(App\Api\V1\Models\InternalUser::class, function () {
    return [
        "job_title" => "Librarian",
        "employed_date" => "2017-12-13",
        "user_id" => ''
    ];
});
