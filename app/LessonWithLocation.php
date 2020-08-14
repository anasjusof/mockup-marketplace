<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonWithLocation extends Model
{
    protected $guarded = [];

    public function lesson()
    {
        return $this->belongsTo('App\Lesson', 'lesson_id');
    }

    public function location()
    {
        return $this->belongsTo('App\Locations', 'location_id');
    }
}
