<?php

use Illuminate\Database\Seeder;

class ads_new extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $faker = Faker\Factory::create();
        DB::table('ads')->update([
            'address'=>$faker->address,
            'reference_point'=>$faker->streetName,
            'mediator'=>$faker->name($gender = null),
            'exclusive'=>$faker->name($gender = null),
            'cause_of_change'=>$faker->sentence($nbWords = 6, $variableNbWords = true),
            'exchange'=>$faker->randomElement([true,false]),
            'formula_of_exchange'=>$faker->word,
            'state'=>$faker->word,
            'source_of_information'=>$faker->randomElement(['OLX','Prom.ua']),
            'bathroom_type'=> $faker->randomElement(['cohesive','separate']),
            'wall_material'=>$faker->randomElement(['brick', 'concrete', 'gypsum board']),
            'phone_line'=>$faker->randomElement([true, false]),
            'having_a_bath'=>$faker->randomElement([true, false]),
            'number of balconies'=>$faker->randomDigit,
            'notes'=>$faker->text($maxNbChars = 300),
            'display_info'=>$faker->text($maxNbChars = 200)
        ]);
    }
}
