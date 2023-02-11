<?php

require_once "Collection.php";

//usage examples

$users = [
  
    [
        "name" => "ahmed",
        "age" => 22
    ],
    [
        "name" => "mohamed",
        "age" => 25
    ],
    [
        "name" => "abdo",
        "age" => 25
    ]

];

$examples = [];

$examples[] =  Collection::make($users)->where(fn($user) => str_contains($user->name,  "me"))->map(function($user, $key) {
    return [
        "user_name" =>  $user->name,
        "Born At" => date("Y", strtotime("-" .$user->age. "year", time())),
    ];
});

$examples[] = Collection::make($users)->avg("age");

$examples[] = Collection::make($users)->pluck("age","name");

$examples[] = Collection::make($users)->groupByCallBack(fn($val) => substr($val->name, 0, 1));

$examples[] = Collection::make($users)->groupBy("age")->reverse();

$examples[] = Collection::make($users)->where(fn($user) => $user->age > 20)->first();

$examples[] = Collection::make($users)->lastWhere(fn($user) => $user->age > 20);


Collection::make($examples)->each(function($example) {
    echo "<pre>";
        var_dump($example);
    echo "</pre>";
    echo str_repeat("__", 50);
});





echo "<pre>";
var_dump(dot($users));
echo "</pre>";




