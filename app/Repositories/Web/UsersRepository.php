<?php

namespace App\Repositories\Web;

use App\User;
use App\Locations;
use App\TagsLesson;
use App\Traits\LogTrait;
use App\UserWithLessonTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\UserWithAvailibilityLocation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UsersRepository
{
    use LogTrait;
    
    public function register($request){

        try{
            DB::transaction(function() use ($request){
                //Create user
                $user = User::create([
                    'email' => $request->email,
                    'name' => $request->name,
                    'password' => bcrypt($request->password)
                ]);

                $user->assignRole($request->role);
                
                //Instructor
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
                'User Register',
                'email : ' . $request->email .
                ', name : ' . $request->name .
                ', password : ' . $request->password .
                ', role : ' . $request->role .
                ', tag : ' . json_encode($request->tag) .
                ', location : ' .  json_encode($request->location),
                $e
            );

            return [
                'status_code' => 400,
                'message' => trans('message.fail_create_user')
            ];
        }

        return [
            'status_code' => 200,
            'message' => trans('message.success_create_user')
        ];
    }

    public function userProfileShow(){
        try{
            $user = User::with('availabilityLocations', 'lessons.lessonTag')
                    ->where('id', auth()->user()->id)
                    ->first();
        }
        catch(ModelNotFoundException $e){
            $this->logError(
                'Show User Profile',
                'auth_user_id : ' . auth()->user()->id,
                $e
            );

            return [
                'status_code' => 400,
                'data' => []
            ];
        }

        return [
            'status_code' => 200,
            'data' => $user
        ];
    }

    public function userProfileUpdate($request){

        try{
            DB::transaction(function() use ($request){
                $user = User::find(auth()->user()->id);
                $user->update($request->toArray());
            });
        }
        catch(\Exception $e){
            
            $this->logError(
                'User Profile Update',
                'name : ' . $request->name .
                ', password : ' . $request->password,
                $e
            );

            return [
                'status_code' => 400,
                'message' => trans('message.fail_update_user')
            ];
        }

        return [
            'status_code' => 200,
            'message' => trans('message.success_update_user')
        ];
    }
    
}
