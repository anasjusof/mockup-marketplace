<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonLevel extends Model
{
    protected $guarded = [];

    use SoftDeletes;
}
