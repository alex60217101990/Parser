<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class proxy_list extends Model
{
    protected $guard_name = 'web';
    protected $table = 'proxy_list';
    protected $fillable =
        ['id', 'ip', 'port', 'counter'];
}
