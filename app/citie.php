<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class citie extends Model
{
    protected $table = 'cities';
    protected $fillable = [
        'city', 'img', 'population', 'region',
    ];
}
