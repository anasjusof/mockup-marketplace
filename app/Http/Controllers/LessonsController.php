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
use App\Traits\LogTrait;

use App\Repositories\Web\LessonRepository;

class LessonsController extends Controller
{
    use LogTrait;

    private $lessonRepository;

    public function __construct(LessonRepository $lessonRepository){
        $this->lessonRepository = $lessonRepository;
    }

    public function lessonGetInformation($lesson_id)
    {
        $response = $this->lessonRepository->lessonGetInformation($lesson_id);
        
        return $this->formatResponse('', $response['status_code'], $response['data']);
        
    }

    public function lessonCreate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'user_id' => 'required',
            'post_or_request' => 'required',
            'tag' => 'required',
            'location' => 'required'
        ]);

        $role = auth()->user()->getRoleNames();

        //If role is student, then cannot create a lesson, instead it will become requested lesson
        ($role[0] == 'student') ? $request->post_or_request = 2 : $request->post_or_request = $request->post_or_request;

        $lesson_request = [
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => auth()->user()->id,
            'post_or_request' => $request->post_or_request
        ];

        $response = $this->lessonRepository->lessonCreate($lesson_request, $request);

        return $this->formatResponse($response['message'], $response['status_code']);
        
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

        return response()->json(
            [
                'lesson_list' => $result
            ],
            200
        );
    }

    public function lessonWishlist(Request $request){
        $request->validate([
            'lesson_id' => 'required'
        ]);

        $response = $this->lessonRepository->lessonWishlist($request);

        return $this->formatResponse($response['message'], $response['status_code']);
    }

    public function lessonWishlistRemove(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required'
        ]);

        $response = $this->lessonRepository->lessonWishlistRemove($request);

        return $this->formatResponse($response['message'], $response['status_code']);

    }

    public function lessonLevelCreate(Request $request)
    {
        $request->validate([
            'lesson_level' => 'required'
        ]);

        $response = $this->lessonRepository->lessonLevelCreate($request);

        return $this->formatResponse($response['message'], $response['status_code']);
    }

    public function lessonLevelRemove(Request $request)
    {
        $request->validate([
            'lesson_level_id' => 'required'
        ]);

        $response = $this->lessonRepository->lessonLevelRemove($request);

        return $this->formatResponse($response['message'], $response['status_code']);
    }

    public function lessonAddReview(Request $request)
    {
        $request->validate([
            'stars' => 'required',
            'review' => 'required',
            'lesson_id' => 'required'
        ]);

        $response = $this->lessonRepository->lessonAddReview($request);

        return $this->formatResponse($response['message'], $response['status_code']);
    }

    public function lessonReviewRemove(Request $request)
    {
        LessonReviews::find($request->review_id)->delete();

        return 'deleted';
    }
    
    public function lessonInterestedRequestorCreate(Request $request){
        $request->validate([
            'lesson_id' => 'required'
        ]);

        $response = $this->lessonRepository->lessonInterestedRequestorCreate($request);

        return $this->formatResponse($response['message'], $response['status_code']);
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

    // public function lessonRequestCreate(Request $request)
    // {

    //     $lesson_request = [
    //         'name' => $request->name,
    //         'description' => $request->description,
    //         'requestor_id' => auth()->user()->id
    //     ];

    //     try{
    //         DB::transaction(function() use($lesson_request, $request){
                
    //             //Create lesson
    //             $lesson = LessonRequest::create($lesson_request);
    
    //             //Foreach new tag, create tag or else take created tag to be stored into lessonWithTag
    //             foreach($request->tag as $tag){
    //                 $tag = TagsLesson::firstOrCreate(['name' => $tag]);
    
    //                 LessonRequestWithTags::firstOrCreate([
    //                     'requested_lesson_id' => $lesson->id,
    //                     'tag_requested_lesson_id' => $tag->id
    //                 ]);
    //             }
    
    //             //Foreach new locatiom, create new location or else take created location o be stored into lessonWithLocation
    //             foreach($request->location as $location){
    //                 $location = Locations::firstOrCreate(['name' => $location]);
    
    //                 LessonRequestWithLocations::firstOrCreate([
    //                     'requested_lesson_id' => $lesson->id,
    //                     'requested_location_id' => $location->id
    //                 ]);          
    //             }
    //         });
    //     }
    //     catch(\Exception $e){
    //         $this->logError(
    //             'Lesson Request Create',
    //             'name : ' . $request->name .
    //             ', description : ' . $request->description .
    //             ', requestor_id : ' . auth()->user()->id .
    //             ', tag : ' . $request->tag . 
    //             ', location : ' . $request->location,
    //             $e
    //         );

    //         return response()->json(['message' => trans('message.fail_create_lesson')], 400);
    //     }

    //     return response()->json(['message' => trans('message.success_create_lesson')], 200);
    // }
}


