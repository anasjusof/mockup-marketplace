<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWithLessonTag extends Model
{
    protected $guarded = [];

    public function lessonTag(){
        return $this->belongsTo('App\TagsLesson', 'tag_lesson_id');
    }
}
