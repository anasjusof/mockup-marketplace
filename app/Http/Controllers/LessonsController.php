<?php

namespace App\Http\Controllers;

use App\Lessons;
use App\Locations;
use App\TagsLesson;
use App\LessonWithTag;
use App\LessonWishlist;
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

    public function lessonSearch(Request $request)
    {
        $request->validate([
            'keyword' => 'required',
        ]);

        ($request->location) ? $location_query = "AND locations.id = $request->location" : $location_query = '';
        
         $result = \DB::select(DB::raw(
                "SELECT lessons.name, lessons.id, locations.name as location_name
                
                FROM lessons
                JOIN lesson_with_tags ON lesson_with_tags.lesson_id = lessons.id
                JOIN tags_lessons ON lesson_with_tags.tag_lesson_id = tags_lessons.id
                
                JOIN lesson_with_locations ON lessons.id = lesson_with_locations.lesson_id
                JOIN locations ON lesson_with_locations.location_id = locations.id
                
                WHERE (tags_lessons.name LIKE '%$request->keyword%'
                OR lessons.name LIKE '%$request->keyword%') $location_query
                GROUP BY lesson_with_tags.lesson_id"
            ));

        return response()->json(['result' => $result]);
    }

    public function lessonWishlist(Request $request){
        $request->validate([
            'lesson_id' => 'required'
        ]);

        try{
            \DB::transaction(function() use ($request){
                LessonWishlist::create([
                    'user_id' => auth()->user()->id,
                    'lesson_id' => $request->lesson_id
                ]);
            });

            return response()->json(['message' => 'Lesson successfully wishlisted', 'success' => true]);
        }
        catch(\Exception $e){
            return response()->json(['message' => 'Failed to wishlisted lesson', 'success' => false]);
        }
    }

    public function lessonWishlistRemove(Request $request){
        $request->validate([
            'lesson_id' => 'required'
        ]);

        try{
            $wishlist_lesson = LessonWishlist::find($request->lesson_id);

            if($wishlist_lesson){
                $wishlist_lesson->delete();
            }
            else{
                return response()->json(['message' => 'Wishlist lesson not found', 'success' => false]);
            }            

            return response()->json(['message' => 'Lesson successfully removed from wishlist', 'success' => true]);
        }
        catch(\Exception $e){
            return response()->json(['message' => 'Failed to remove wishlisted lesson', 'success' => false]);
        }
    }
}


