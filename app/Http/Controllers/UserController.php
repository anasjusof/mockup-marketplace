<?php

namespace App\Http\Controllers;

use App\User;
use App\Locations;
use App\TagsLesson;
use App\UserWithLessonTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\UserWithAvailibilityLocation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Repositories\Web\UsersRepository;



class UserController extends Controller
{
    private $usersRepository;

    public function __construct(UsersRepository $usersRepository){
        $this->usersRepository = $usersRepository;
    }

    public function register(Request $request)
    {    
        //Validate request
        $request->validate([
            'email' => ['email', 'required', 'unique:users'],
            'name' => ['required'],
            'password' => ['required'],
            'role' => ['required']
        ]);

        $response = $this->usersRepository->register($request);

        return $this->formatResponse($response['message'], $response['status_code']);
    }

    public function userProfileShow()
    {
        $user = $this->usersRepository->userProfileShow();

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

        $response = $this->usersRepository->userProfileUpdate($request, $user);

        return $this->formatResponse($response['message'], $response['status_code']);

    }
}
