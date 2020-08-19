<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonInterestedRequestor extends Model
{
    protected $guarded = [];

    public function requestor(){
        $this->belongsTo('App/Users');
    }

    public function requestedLesson(){
        $this->belongsTo('App/Lessons');
    }
}
