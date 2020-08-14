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

        //If user not found || request->password doest not meet the hashed password in datatabase
        // if(!$user || !Hash::check($request->password, $user->password)){
        //     throw ValidationException::withMessages([
        //         'email' => ['The provided credential is incorrect']
        //     ]);
        // }

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
        $user = auth()->user()->token();
        $user->revoke();

        return response()->json(
            [
                'success' => true,
                'message' => 'Logged out'
            ],
            200
        );
    }
}
