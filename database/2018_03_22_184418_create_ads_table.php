<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('link');
            $table->string('ad_from',100);
            $table->string('type_of_structure',200)->default('-');/*nullable();*/
            $table->string('type_of_apartment',200);
            $table->integer('number_of_rooms')->default(1);
            $table->integer('floor')->default(1);

            $table->string('address');

            $table->integer('storey_house')->default(1);
            $table->float('total_area',10,2);
            $table->float('living_space',10,2);
            $table->float('kitchen_area', 10,2);
            $table->decimal('price', 10, 2);
            $table->string('telephones');
            $table->integer('number_of_photos');
            $table->string('status');
            $table->integer('number_of_similar');

            $table->string('reference_point');
            $table->string('mediator');
            $table->string('exclusive');
            $table->string('cause_of_change');
            $table->boolean('exchange');
            $table->string('formula_of_exchange');
            $table->string('state');
            $table->string('source_of_information');
            $table->string('bathroom_type');
            $table->string('wall_material');
            $table->boolean('phone_line');
            $table->boolean('having_a_bath');
            $table->integer('number_of_balconies');
            $table->text('notes');
            $table->text('display_info');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ads');
    }
}
