<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_ads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('link')->unique();
            $table->string('title_ad');
            $table->text('body_ad');
            $table->string('price');
            $table->string('ad_from');
            $table->string('category');
            $table->string('coop_with_realtors');
            $table->string('object_type');
            $table->integer('floor');
            $table->integer('num_of_storeys');
            $table->double('total_area');
            $table->double('kitchen_area');
            $table->integer('number_of_rooms');
            $table->string('layout');
            $table->string('bathroom');
            $table->string('heating');
            $table->string('repairs');
            $table->string('appliances');
            $table->string('comfort');
            $table->string('communications');
            $table->string('infrastructure');
            $table->string('landscape');
            $table->integer('photo_counter');
            $table->string('telephones');
            $table->string('ad_added');
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
        Schema::dropIfExists('new_ads');
    }
}
