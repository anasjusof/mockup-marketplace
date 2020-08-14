<?php

namespace App\Http\Controllers;

use App\Lessons;
use App\Locations;
use App\TagsLesson;
use App\LessonWithTag;
use App\LessonWithLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonsController extends Controller
{
    public function lessonGetInformation($lesson_id)
    {
        $lesson_info = [];
        $status = false;

        $lesson = Lessons::with(['tags.tag', 'locations.location'])->find($lesson_id);

        if($lesson){
            $lesson_info = $lesson;
            $status = true;
        }

        return response()->json(['lesson_info' => $lesson_info, 'status' => $status]);
    }

    public function lessonCreate(Request $request)
    {
        $role = auth()->user()->getRoleNames();

        //If role is student, then cannot create a lesson, instead it will become requested lesson
        ($role == 'student') ? $instructor_id = auth()->user()->id : $instructor_id = 0;

        $lesson_request = [
            'name' => $request->name,
            'description' => $request->description,
            'instructor_id' => $instructor_id
        ];

        try{
            DB::transaction(function() use($lesson_request, $request){

                //Create lesson
                $lesson = Lessons::create($lesson_request);
    
                //Foreach new tag, create tag or else take created tag to be stored into lessonWithTag
                foreach($request->tag as $tag){
                    $tag = TagsLesson::firstOrCreate(['name' => $tag]);
    
                    LessonWithTag::firstOrCreate([
                        'lesson_id' => $lesson->id,
                        'tag_lesson_id' => $tag->id
                    ]);
                }
    
                //Foreach new locatiom, create new location or else take created location o be stored into lessonWithLocation
                foreach($request->location as $location){
                    $location = Locations::firstOrCreate(['name' => $location]);
    
                    LessonWithLocation::firstOrCreate([
                        'lesson_id' => $lesson->id,
                        'location_id' => $location->id
                    ]);          
                }
            });
    
            return response()->json(['message' => 'Lesson successfully created', 'success' => true]);
        }
        catch(\Exception $e){
            return response()->json(['message' => 'Fail to create lesson', 'success' => false]);
        }
        
    }
}


