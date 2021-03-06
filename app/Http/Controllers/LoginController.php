<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class LoginController extends Controller
{
    public function login(Request $request)
    {
        //Validate request
        $loginData = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        //Get user
        $user = User::where('email', $request->email)->first();

        //If auth attempt of login is false/failed, return response
        if(!auth()->attempt($loginData)){
            return response(['message' => 'Invalid credential']);
        }

        //Create the access token
        $access_token =  auth()->user()->createToken('AuthToken')->accessToken;

        return response([
            'user' => auth()->user(),
            'access_token' => $access_token
        ]);
    }

    public function logout(Request $request)
    {
        \DB::table('oauth_access_tokens')
        ->where('user_id', Auth::user()->id)
        ->update([
            'revoked' => true
        ]);

        return $this->formatResponse(trans('message.success_log_out'), 200);
    }
}
