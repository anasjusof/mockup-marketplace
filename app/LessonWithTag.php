<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonWithTag extends Model
{
    protected $guarded = [];

    public function lesson(){
        return $this->belongsTo('App\Lessons', 'lesson_id');
    }

    public function tag(){
        return $this->belongsTo('App\TagsLesson', 'tag_lesson_id');
    }
}
