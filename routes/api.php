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
    
    Route::get('lessonGetInformation/{lesson}', 'LessonsController@lessonGetInformation');

    Route::post('lessonSearch', 'LessonsController@lessonSearch');

    Route::post('lessonWishlist', 'LessonsController@lessonWishlist');
    Route::post('lessonWishlistRemove', 'LessonsController@lessonWishlistRemove');

    //role for student
    Route::group(['middleware' => ['role:student']], function () {
        
    });

    //role for instructor
    Route::group(['middleware' => ['role:instructor']], function () {
        Route::post('lessonCreate', 'LessonsController@lessonCreate');
    });
});


