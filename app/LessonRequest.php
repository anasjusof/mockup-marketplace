<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonRequest extends Model
{

    protected $guarded = [];
    
    public function requestor()
    {
        return $this->belongsTo('App\User', 'instructor_id');
    }
    
    public function requestedTags()
    {
        return $this->hasMany('App\LessonRequestWithTag', 'lesson_id');
    }

    public function requestedLocations()
    {
        return $this->hasMany('App\LessonRequestWithLocation', 'lesson_id');
    }
}
