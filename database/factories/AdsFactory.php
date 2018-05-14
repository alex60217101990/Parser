<?php

use Faker\Generator as Faker;

$factory->define(\App\ad::class, function (Faker $faker) {
    $arr = ['Кирпичный', 'Монолитный', null ];
    $arr1 = ['Вторчный рынок', 'Новостройки'];
    $arr0 = ['Совпало количество комнат.', 'Совпал номер телефона.', 'Совпала этажность дома.'];
    $arr2 = [5, 9, 12, 16, 24, 25];
    $arr4 = [];
    for($i = 0; $i<mt_rand(1, 4); $i++) {
        array_push($arr4, '' . $faker->e164PhoneNumber . ' ');
    }
    return [
        //
        'link' => $faker->url,
        'ad_from' => 'Бизнес',
        'type_of_structure' => array_rand($arr, 1),//array_rand($arr, 1),
        'type_of_apartment' => array_rand($arr1, 1),//array_rand($arr1, 1),
        'number_of_rooms' => mt_rand(1, 4),
        'floor' => mt_rand(1, 25),
        'storey_house' => $faker->randomElement($arr2, $count = 1),//array_rand($arr2, 1),
        'total_area' => (mt_rand (20*10, 120*10) / 10),
        'living_space' => (mt_rand (17*10, 110*10) / 10),
        'kitchen_area' => (mt_rand (5*10, 15*10) / 10),
        'price' => (mt_rand (1*10, 100000*10) / 10),
        'telephones' => implode($arr4),
        'number_of_photos' => mt_rand(0, 3),
        'status' => implode($faker->randomElements($arr0, $count = mt_rand(1,3))),
        'number_of_similar' => mt_rand(0, 10)
    ];
});