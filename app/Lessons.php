<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lessons extends Model
{
    protected $guarded = [];

    public function instructor()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    
    public function tags()
    {
        return $this->hasMany('App\LessonWithTag', 'lesson_id');
    }

    public function locations()
    {
        return $this->hasMany('App\LessonWithLocation', 'lesson_id');
    }
}
