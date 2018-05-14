<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class users_avatar extends Model
{
    //
    protected $table = 'urers_avatars';
    protected $fillable = [
        'photo_name', 'url', 'user_id'
    ];

      /**
       * @param {void}
       * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
       */
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
