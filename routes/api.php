<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', 'LoginController@login');
Route::post('/register', 'UserController@register');

Route::middleware('auth:api')->group(function () {
    // our routes to be protected will go in here
    Route::get('userProfile', 'UserController@showUserProfile');
    Route::post('userProfileUpdate/{user}', 'UserController@userProfileUpdate');

    Route::post('logout', 'LoginController@logout');

    Route::get('lessonGetInformation/{lesson}', 'LessonsController@lessonGetInformation');
    Route::post('lessonCreate', 'LessonsController@lessonCreate');
});


