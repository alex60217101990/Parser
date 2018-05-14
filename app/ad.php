<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ad extends Model
{
    //
    protected $table = 'ads';
    protected $fillable = [
        'link', 'ad_from', 'type_of_structure', 'type_of_apartment', 'number_of_rooms',
        'floor', 'address','storey_house', 'total_area', 'living_space', 'kitchen_area',
        'price', 'telephones', 'number_of_photos', 'status', 'number_of_similar',
        'updated_at', 'created_at', 'cause_of_change', 'exchange', 'formula_of_exchange',
        'state', 'source_of_information', 'bathroom_type', 'wall_material', 'phone_line',
        'having_a_bath', 'number_of_balconies', 'notes', 'display_info'
    ];

    public function allImg()
    {
        return $this->hasMany('App\ad_image');
    }
}
