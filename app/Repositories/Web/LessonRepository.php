<?php

namespace App\Repositories\Web;

use App\Lessons;
use App\Locations;
use App\TagsLesson;
use App\LessonLevel;
use App\LessonRequest;
use App\LessonReviews;
use App\LessonWithTag;
use App\LessonWishlist;
use App\LessonWithLocation;
use Illuminate\Http\Request;
use App\LessonRequestWithTags;
use App\LessonInterestedRequestor;
use Illuminate\Support\Facades\DB;
use App\LessonRequestWithLocations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Traits\LogTrait;

class LessonRepository
{
    use LogTrait;

    public function lessonCreate($lesson_request, $request)
    {
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
        }
        catch(\Exception $e){
            $this->logError(
                'Lesson Create',
                'name : ' .  $request->name . 
                ', description : ' . $request->description . 
                ', user_id : '  . auth()->user()->id .
                ', post_or_request : ' . $request->post_or_request,
                $e
            );

            return [
                'status_code' => 400,
                'message' => trans('message.fail_create_lesson')
            ];
        }

        return [
            'status_code' => 200,
            'message' => trans('message.success_create_lesson')
        ];
    }

    public function lessonGetInformation($lesson_id)
    {
        $lesson_info = [];

        $lesson = Lessons::with(['tags.tag', 'locations.location'])->find($lesson_id);

        if($lesson){
            $lesson_info = $lesson;

            return [
                'status_code' => 200,
                'data' => $lesson_info
            ];
        }

        return [
            'status_code' => 400,
            'data' => $lesson_info
        ];
    }

    public function lessonWishlist($request)
    {
        //If lesson not found, should not be able to wishlist
        $lesson = Lessons::find($request->lesson_id);

        if(!$lesson)
        {
            return [
                'status_code' => 400,
                'message' => trans('message.failed_wishlist_lesson')
            ];
        }

        try{
            \DB::transaction(function() use ($request){
                LessonWishlist::firstOrCreate([
                    'user_id' => auth()->user()->id,
                    'lesson_id' => $request->lesson_id
                ]);
            });
        }
        catch(\Exception $e){

            $this->logError(
                'Lesson Wishlist',
                'user_id : ' . auth()->user()->id .
                ', lesson_id : ' . $request->lesson_id,
                $e
            );

            return [
                'status_code' => 400,
                'message' => trans('message.failed_wishlist_lesson')
            ];
        }

        return [
            'status_code' => 200,
            'message' => trans('message.success_wishlist_lesson')
        ];
    }

    public function lessonWishlistRemove($request)
    {
        try{
            $wishlist_lesson = LessonWishlist::where('lesson_id', $request->lesson_id)
                                                ->where('user_id', auth()->user()->id)
                                                ->first();

            if($wishlist_lesson){
                $wishlist_lesson->delete();
            }
            else{
                return [
                    'status_code' => 400,
                    'message' => trans('message.not_found_wishlist_lesson')
                ];
            }      
        }
        catch(\Exception $e){
            $this->logError(
                'Lesson Wishlist Remove',
                'lesson_id : ' . $request->lesson_id .
                'user_id : ' . auth()->user()->id,
                $e
            );

            return [
                'status_code' => 500,
                'message' => trans('message.something_when_wrong')
            ];
        }

        return [
            'status_code' => 200,
            'message' => trans('message.success_remove_wishlist_lesson')
        ];
    }

    public function lessonLevelCreate($request)
    {
        try{
            \DB::transaction(function() use ($request){
                LessonLevel::firstOrCreate(['type' => $request->lesson_level]);
            });
        }
        catch(\Exception $e){
            $this->logError(
                'Lesson Level Create',
                'lesson_type : ' . $request->lesson_level,
                $e
            );
            
            return [
                'status_code' => 400,
                'message' => trans('message.failed_create_lesson_type')
            ];

            return response()->json(['message' => trans('message.failed_create_lesson_type')], 400);
        }

        return [
            'status_code' => 200,
            'message' => trans('message.success_create_lesson_type')
        ];
    }

    public function lessonLevelRemove($request)
    {
        try{
            $lesson_level = LessonLevel::find($request->lesson_level_id);

            if($lesson_level){
                $lesson_level->delete();
            }
            else{
                return [
                    'status_code' => 400,
                    'message' => trans('message.not_found_lesson_type')
                ];
            }      
        }
        catch(\Exception $e){
            $this->logError(
                'Lesson Level Remove',
                'lesson_id : ' . $request->lesson_level_id,
                $e
            );

            return [
                'status_code' => 400,
                'message' => trans('message.failed_delete_lesson_type')
            ];
        }

        return [
            'status_code' => 200,
            'message' => trans('message.success_delete_lesson_type')
        ];
    }

    public function lessonAddReview($request)
    {
        $lesson = Lessons::find($request->lesson_id);

        if(!$lesson){
            return [
                'status_code' => 400,
                'message' => trans('message.failed_add_lesson_review_lesson_not_found')
            ];
        }

        try{
            DB::transaction(function() use ($request, $lesson)
            {
                $request['user_id'] = auth()->user()->id;

                LessonReviews::create([
                    'stars' => $request->stars,
                    'review' => $request->review,
                    'user_id' => auth()->user()->id,
                    'lesson_id' => $lesson->id
                ]);
            });
        }
        catch(\Exception $e){
            $this->logError(
                'Lesson Add Review',
                'stars : ' . $request->stars .
                ', review : ' . $request->review .
                ', user_id : ' . auth()->user()->id .
                ', lesson_id : ' . $request->lesson_id, 
                $e
            );

            return [
                'status_code' => 500,
                'message' => trans('message.failed_add_lesson_review')
            ];
        }

        return [
            'status_code' => 200,
            'message' => trans('message.success_add_lesson_review')
        ];
    }

    public function lessonInterestedRequestorCreate($request)
    {
        $lesson = Lessons::where('id', $request->lesson_id)->where('post_or_request', 2)->first();

        //If requested lesson requestor is not the same with the interested requestor, then only create
        //We dont want the requestor to be included into interested requestor
        if($lesson->user_id != auth()->user()->id){

            $create_data = [
                'user_id' => auth()->user()->id,
                'lesson_id' => $request->lesson_id
            ];

            try{
                \DB::transaction(function() use ($create_data){
                    LessonInterestedRequestor::create($create_data);
                });

            }
            catch(\Exception $e){
                $this->logError(
                    'Lesson Interested Requestor Create',
                    'lesson_id : ' . $request->lesson_id .
                    ', auth user : ' . auth()->user()->id,
                    $e
                );

                return [
                    'status_code' => 500,
                    'message' => trans('message.fail_create_lesson_interested_requestor')
                ];
            }

            return [
                'status_code' => 200,
                'message' => trans('message.success_create_lesson_interested_requestor')
            ];
            
        }

        return [
            'status_code' => 400,
            'message' => trans('message.success_fail_create_lesson_interested_requestor_on_same_user')
        ];
    }
}