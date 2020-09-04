<?php

namespace Tests;

use App\User;
use App\Locations;
use App\TagsLesson;
use App\UserWithLessonTag;

use Faker\Factory as Faker;
use Laravel\Passport\Passport;
use App\UserWithAvailibilityLocation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function UserStudent($actingAs = false)
    {
        $user = factory(User::class)->create();

        $user->assignRole('student');

        if($actingAs){
            Passport::actingAs(
                $user/*,
                ['user']*/
            );
        }

        return $user;
    }

    public function UserInstructor($actingAs = false)
    {
        $user = factory(User::class)->create();

        $user->assignRole('instructor');

        $tags = ['English'];

        $locations = ['Kuala Lumpur'];



        //Save tag lesson
        foreach($tags as $tag){
            $tag = TagsLesson::firstOrCreate(['name' => $tag]);

            UserWithLessonTag::firstOrCreate([
                'user_id' => $user->id,
                'tag_lesson_id' => $tag->id
            ]);
        }

        //Save availibilty location
        foreach($locations as $location){
            $location = Locations::firstOrCreate(['name' => $location]);

            UserWithAvailibilityLocation::firstOrCreate([
                'user_id' => $user->id,
                'location_id' => $location->id
            ]);          
        }

        if($actingAs){
            Passport::actingAs(
                $user/*,
                ['user']*/
            );
        }

        return $user;
    }
}
