<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWithAvailibilityLocation extends Model
{
    protected $guarded = [];

    public function location()
    {
        return $this->belongsTo('App\Locations', 'location_id');
    }
}
