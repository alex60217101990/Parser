<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class proxy_test_list extends Model
{
    protected $guard_name = 'web';
    protected $table = 'proxy_test_list';
    protected $fillable =
        ['id', 'ip', 'proxy_type', 'bool_https', 'port','rating'];
}
