<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColsToNewAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('new_ads', function (Blueprint $table) {
            $table->string('house_type')->after('ad_added');
            $table->string('commission')->after('ad_added');
            $table->string('dist_to_the_near_city')->after('ad_added');
            $table->string('property_type')->after('ad_added');
            $table->string('land_area')->after('ad_added');
            $table->string('buildings_on_plot')->after('ad_added');
            $table->string('furnishing')->after('ad_added');
            $table->string('multimedia')->after('ad_added');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
