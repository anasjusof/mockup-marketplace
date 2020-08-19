<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LessonRequestWithTags extends Model
{
    protected $guarded = [];

    public function lessonRequest(){
        return $this->belongsTo('App\LessonRequest', 'requested_lesson_id');
    }

    public function tag(){
        return $this->belongsTo('App\TagsLesson', 'tag_requested_lesson_id');
    }
}
