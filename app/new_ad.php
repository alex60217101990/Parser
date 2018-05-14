<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class new_ad extends Model
{
    protected $guard_name = 'web';
    protected $table = 'new_ads';
    protected $fillable =
        ['id', 'link', 'title_ad', 'body_ad', 'price',
            'ad_from', 'category', 'coop_with_realtors',
            'object_type', 'floor', 'num_of_storeys', 'total_area',
            'kitchen_area', 'number_of_rooms', 'layout', 'bathroom',
            'heating', 'repairs', 'appliances', 'comfort',
            'communications', 'infrastructure', 'landscape',
            'photo_counter', 'telephones', 'ad_added','house_type',
            'commission', 'dist_to_the_near_city','property_type',
            'land_area','buildings_on_plot','furnishing','multimedia'];

    public function allImg()
    {
        return $this->hasMany('App\new_ad_image');
    }
}
/*
 */