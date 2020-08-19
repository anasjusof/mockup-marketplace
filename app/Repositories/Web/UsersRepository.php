<?php

namespace App\Repositories\Web;

use App\User;
use App\Locations;
use App\TagsLesson;
use App\UserWithLessonTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\UserWithAvailibilityLocation;

class UsersRepository
{

    protected $user;
    protected $lessonRepository;

	public function __construct(User $user)
	{
        $this->user = $user;
	}

    public function find($userId)
    {
        $user = User::find($userId);
        return $user;
    }

    public function findByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function create($request)
    {
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
}
