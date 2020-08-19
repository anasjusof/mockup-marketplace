<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonRequestWithLocation extends Model
{
    public function lesson()
    {
        return $this->belongsTo('App\LessonRequest', 'requested_lesson_id');
    }

    public function location()
    {
        return $this->belongsTo('App\Locations', 'requested_location_id');
    }
}
