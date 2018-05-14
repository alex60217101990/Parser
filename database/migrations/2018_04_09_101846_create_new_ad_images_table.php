<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewAdImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('new_ad_images', function (Blueprint $table) {
            $table->increments('id');
            $table->string('img_name',300);
            $table->string('img_path', 300);
            $table->integer('ad_id')->unsigned();
            $table->foreign('ad_id')->references('id')->on('new_ads')
                ->onUpdate('cascade')
                ->onDelete('cascade');
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
        Schema::dropIfExists('new_ad_images');
    }
}
