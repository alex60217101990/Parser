<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class ad_image extends Model
{
    //
    use HasRoles;

    protected $guard_name = 'web';
    protected $table = 'ad_images';
    protected $fillable = [
        'img_name', 'img_path', 'ad_id'
    ];

    public function getAd(){
        $this->belongsTo('App\ad');
    }
}
