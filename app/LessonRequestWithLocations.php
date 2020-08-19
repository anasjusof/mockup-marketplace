<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonRequestWithLocations extends Model
{
    protected $guarded = [];

    public function requestedLesson()
    {
        return $this->belongsTo('App\LessonRequested', 'requested_lesson_id');
    }

    public function requestedLocation()
    {
        return $this->belongsTo('App\Locations', 'requested_location_id');
    }

}
