<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class new_ad_image extends Model
{
    protected $guard_name = 'web';
    protected $table = 'new_ad_images';
    protected $fillable = [
        'img_name', 'img_path', 'ad_id'
    ];
}
