<?php

namespace App;

use App\BlameableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonReviews extends Model
{
    use BlameableTrait, SoftDeletes;

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
