<?php

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Storage;

$factory->define(\App\ad_image::class, function (Faker $faker) {
    $filePath = storage_path('app/public/images');
    if(!File::exists($filePath)){
        File::makeDirectory($filePath);  //follow the declaration to see the complete signature
    }
    $arr = explode("\\", $faker->image($filePath,400,300));
    return [
        'img_name' => ''.$arr[count($arr)-1],
        'img_path' => 'storage/images/'.$arr[count($arr)-1],
        'ad_id' => rand(1, 200),
    ];
});