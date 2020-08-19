<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonReviews extends Model
{
    protected $guarded = [];

    public function lesson()
    {
        return $this->belongsTo('App\Lessons');
    }

    public function reviewByUser()
    {
        return $this->belongsTo('App\User');
    }
}
