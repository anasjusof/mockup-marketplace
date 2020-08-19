<?php

namespace App\Http\Controllers;

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

class LessonsController extends Controller
{
    public function lessonGetInformation($lesson_id)
    {
        $lesson_info = [];
        $status = false;

        $lesson = Lessons::with(['tags.tag', 'locations.location'])->find($lesson_id);

        if($lesson){
            $lesson_info = $lesson;
            
            return response()->json(
                [
                    'lesson_info' => $lesson_info
                ],
                200
            );
        }

        return response()->json(
            [
                'lesson_info' => $lesson_info 
            ],
            400
        );
    }

    public function lessonCreate(Request $request)
    {
        $role = auth()->user()->getRoleNames();

        //If role is student, then cannot create a lesson, instead it will become requested lesson
        ($role[0] == 'student') ? $request->post_or_request = 2 : $request->post_or_request = 1;

        $lesson_request = [
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => auth()->user()->id,
            'post_or_request' => $request->post_or_request
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
        }
        catch(\Exception $e){
            return response()->json(['message' => trans('message.fail_create_lesson')], 400);
        }

        return response()->json(['message' => trans('message.success_create_lesson')], 200);
        
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
                LessonWishlist::firstOrCreate([
                    'user_id' => auth()->user()->id,
                    'lesson_id' => $request->lesson_id
                ]);
            });
        }
        catch(\Exception $e){
            return response()->json(['message' => trans('message.failed_wishlist_lesson')], 400);
        }

        return response()->json(['message' => trans('message.success_wishlist_lesson')], 200);
    }

    public function lessonWishlistRemove(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required'
        ]);

        try{
            $wishlist_lesson = LessonWishlist::where('lesson_id', $request->lesson_id)
                                                ->where('user_id', auth()->user()->id)
                                                ->first();

            if($wishlist_lesson){
                $wishlist_lesson->delete();
            }
            else{
                return response()->json(['message' => trans('message.not_found_wishlist_lesson')], 400);
            }      
        }
        catch(\Exception $e){
            return response()->json(['message' => trans('message.failed_remove_wishlist_lesson')], 400);
        }

        return response()->json(['message' => trans('message.success_remove_wishlist_lesson')], 200);

    }

    public function lessonLevelCreate(Request $request)
    {
        $request->validate([
            'lesson_type' => 'required'
        ]);

        try{
            \DB::transaction(function() use ($request){

                LessonLevel::firstOrCreate(['type' => $request->lesson_type]);
            });
        }
        catch(\Exception $e){
            return response()->json(['message' => trans('message.failed_create_lesson_type')], 400);
        }

        return response()->json(['message' => trans('message.success_create_lesson_type')], 200);
    }

    public function lessonLevelRemove(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required'
        ]);

        try{
            $lesson_level = LessonLevel::find($request->lesson_id);

            if($lesson_level){
                $lesson_level->delete();
            }
            else{
                return response()->json(['message' => trans('message.not_found_lesson_type')], 400);
            }      
        }
        catch(\Exception $e){
            return response()->json(['message' => trans('message.failed_delete_lesson_type')], 400);
        }

        return response()->json(['message' => trans('message.success_delete_lesson_type')], 200);
    }

    public function lessonAddReview(Request $request)
    {
        $request->validate([
            'stars' => 'required'
        ]);

        try{
            DB::transaction(function() use ($request)
            {
                $request['user_id'] = auth()->user()->id;

                LessonReviews::create([
                    'stars' => $request->stars,
                    'review' => $request->review,
                    'user_id' => auth()->user()->id
                ]);
            });
        }
        catch(\Exception $e){
            return response()->json(['message' => trans('message.failed_add_lesson_review')], 400);
        }

        return response()->json(['message' => trans('message.success_add_lesson_review')], 200);
    }

    public function lessonRequestCreate(Request $request)
    {

        $lesson_request = [
            'name' => $request->name,
            'description' => $request->description,
            'requestor_id' => auth()->user()->id
        ];

        try{
            DB::transaction(function() use($lesson_request, $request){
                
                //Create lesson
                $lesson = LessonRequest::create($lesson_request);
    
                //Foreach new tag, create tag or else take created tag to be stored into lessonWithTag
                foreach($request->tag as $tag){
                    $tag = TagsLesson::firstOrCreate(['name' => $tag]);
    
                    LessonRequestWithTags::firstOrCreate([
                        'requested_lesson_id' => $lesson->id,
                        'tag_requested_lesson_id' => $tag->id
                    ]);
                }
    
                //Foreach new locatiom, create new location or else take created location o be stored into lessonWithLocation
                foreach($request->location as $location){
                    $location = Locations::firstOrCreate(['name' => $location]);
    
                    LessonRequestWithLocations::firstOrCreate([
                        'requested_lesson_id' => $lesson->id,
                        'requested_location_id' => $location->id
                    ]);          
                }
            });
        }
        catch(\Exception $e){
            return response()->json(['message' => trans('message.fail_create_lesson')], 400);
        }

        return response()->json(['message' => trans('message.success_create_lesson')], 200);
    }
    
    public function lessonInterestedRequestorCreate(Request $request){
        $request->validate([
            'lesson_id' => 'required'
        ]);

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
                return response()->json(['message' => trans('message.fail_create_lesson_interested_requestor')], 400);
            }

            return response()->json(['message' => trans('message.success_create_lesson_interested_requestor')], 200);
        }
        return response()->json(['message' => trans('message.success_fail_create_lesson_interested_requestor_on_same_user')], 200);
    }

    public function getDistanceBetweenUserAndLessonLocation(Request $request){
        // $request->validate([
        //     'from' => 'required',
        //     'to' => 'required'
        // ]);

        // $endpoint = "https://www.mapquestapi.com/directions/v2/route";
        // $client = new \GuzzleHttp\Client();
        
        // $payload = [
        //     'key' => env('MAPQUEST_KEY'),
        //     'from' => $request->from,
        //     'to' => $request->to,
        //     'outFormat' => 'json',
        //     'ambiguities' => 'ignore',
        //     'routeType' => 'fastest',
        //     'doReverseGeocode' => 'false',
        //     'enhancedNarrative' => 'false',
        //     'avoidTimedConditions' => 'false'
        // ];

        // $response = $client->request('POST', $endpoint, ['query' => $payload]);
        // $contents = $response->getBody()->getContents();
        // $contents = json_decode($contents, true);
        
        // $distance_between_two_point = $contents['route']['distance'];

        // return $distance_between_two_point;

        $message = [
            'function' => 'getDistanceBetweenUserAndLessonLocation',
            'message' => 'please enable to code first'
        ];

        Log::error($message);

        return 'enable the code first';
    }
}


