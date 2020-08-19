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

    ########### ----------------- Universal Role ----------------- ###########

    //Logout
    Route::post('logout', 'LoginController@logout');


    //User Profiles
    Route::get('userProfile', 'UserController@showUserProfile');
    Route::post('userProfileUpdate/{user}', 'UserController@userProfileUpdate');

    
    //Lesson
    #Detailed information on lesson
    Route::get('lessonGetInformation/{lesson}', 'LessonsController@lessonGetInformation');
    #Search the lesson via keyword + location
    Route::post('lessonSearch', 'LessonsController@lessonSearch');
    #Wishlist a lessson
    Route::post('lessonWishlist', 'LessonsController@lessonWishlist');
    Route::post('lessonWishlistRemove', 'LessonsController@lessonWishlistRemove');
    #Add review to lesson
    Route::post('lessonAddReview', 'LessonsController@lessonAddReview');
    #Create a lesson (Post a lesson @ Request for lesson)
    Route::post('lessonCreate', 'LessonsController@lessonCreate');
    #Create interested requestor to join requested lesson
    Route::post('lessonInterestedRequestorCreate', 'LessonsController@lessonInterestedRequestorCreate');

    #Get location between two point
    Route::post('getDistanceBetweenUserAndLessonLocation', 'LessonsController@getDistanceBetweenUserAndLessonLocation');
    


    ########### ----------------- Role for student ----------------- ###########
    Route::group(['middleware' => ['role:student']], function () {
        
    });



    ########### ----------------- Role for instructor ----------------- ###########
    Route::group(['middleware' => ['role:instructor']], function () {
        #Create @ delete lesson level
        Route::post('lessonLevelCreate', 'LessonsController@lessonLevelCreate');
        Route::post('lessonLevelRemove', 'LessonsController@lessonLevelRemove');
    });

    ##To be deleted @ unused route
    //Route::post('lessonRequestCreate', 'LessonsController@lessonRequestCreate');
});


