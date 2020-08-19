<?php

namespace App\Http\Controllers;

use App\User;
use App\Locations;
use App\TagsLesson;
use App\UserWithLessonTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\UserWithAvailibilityLocation;

class UserController extends Controller
{
    public function register(Request $request)
    {    
        //Validate request
        $request->validate([
            'email' => ['email', 'required', 'unique:users'],
            'name' => ['required'],
            'password' => ['required'],
            'role' => ['required']
        ]);

        try{
            DB::transaction(function() use ($request){
                //Create user
                $user = User::create([
                    'email' => $request->email,
                    'name' => $request->name,
                    'password' => bcrypt($request->password)
                ]);

                $user->assignRole($request->role);
                
                //Insructor
                if($request->role == 'instructor'){
                    
                    //Save tag lesson
                    foreach($request->tag as $tag){
                        $tag = TagsLesson::firstOrCreate(['name' => $tag]);
        
                        UserWithLessonTag::firstOrCreate([
                            'user_id' => $user->id,
                            'tag_lesson_id' => $tag->id
                        ]);
                    }

                    //Save availibilty location
                    foreach($request->location as $location){
                        $location = Locations::firstOrCreate(['name' => $location]);
        
                        UserWithAvailibilityLocation::firstOrCreate([
                            'user_id' => $user->id,
                            'location_id' => $location->id
                        ]);          
                    }
                }
            });
        }
        catch(\Exception $e){
            $this->logError(
                'Create user',
                'Email : ' . $request->email,
                'Name : ' . $request->name
            );

            return response()->json(
                [
                    'message' => trans('message.fail_create_user')
                ],
                409
            );
        }

        return response()->json(
            [
                'message' => trans('message.success_create_user')
            ],
            200
        );
    }

    public function showUserProfile()
    {
        //$user = auth()->user();

        $user = User::with('availabilityLocations.location', 'lessons.lessonTag')->find(auth()->user()->id);

        return response()->json([ 'user_information' => $user], 200);
    }

    public function userProfileUpdate(Request $request, User $user)
    {
        $request->only([
            'name', 
            'password'
        ]);

        if($request['password']){
            $request['password'] = bcrypt($request['password']);
        }
        
        $request = array_filter($request->all());

        try{
            DB::transaction(function() use ($request, $user){
                $user->update($request);
            });
        }
        catch(\Exception $e){
            return response()->json(
                [
                    'message' => trans('message.fail_update_user')
                ], 400
            );
        }

        return response()->json(
            [
                'message' => trans('message.success_update_user')
            ], 200
        );

    }
}
