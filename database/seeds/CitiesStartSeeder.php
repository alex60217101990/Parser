<?php

use Illuminate\Database\Seeder;
use App\citie;

class CitiesStartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cities')->delete();

        $Cities = array(
            ['city' => 'London', 'img' => '../img/London.png', 'population' => 8788000, 'region' => 'Europe'],
            ['city' => 'Hong-Kong','img' => '../img/Hong_Kong.png', 'population' => 7347000, 'region' => 'Asia'],
            ['city' => 'New York','img' => '../img/New_York.png', 'population' => 8405837, 'region' => 'North America'],
            ['city' => 'Moscow','img' => '../img/Moscow.png', 'population' => 12380664, 'region' => 'East Europe'],
            ['city' => 'Paris','img' => '../img/Paris.png', 'population' => 2196936, 'region' => 'Europe'],
        );

        // Loop through each user above and create the record for them in the database
        foreach ($Cities as $city)
        {
            citie::create($city);
        }

    }
}
