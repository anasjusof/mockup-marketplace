<?php

namespace Tests\Feature;

use App\User;
use App\Lessons;
use Tests\TestCase;
use Faker\Factory as Faker;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\WithFaker;

class LessonTest extends TestCase
{
    public function testLessonCreate()
    {
        $user = $this->UserInstructor(true);

        $faker = Faker::create();

        $payload = [
            'name' => $faker->text(20),
            'description' => $faker->text(50),
            'user_id' => $user->id,
            'post_or_request' => 1,
            'tag' => ['English'],
            'location' => ['Kuala Lumpur']
        ];

        $response = $this->json('POST', 'api/lessonCreate/', $payload);

        $response->assertStatus(200);
    }

    public function testLessonCreateFailedValidation()
    {
        $user = $this->UserInstructor(true);

        $faker = Faker::create();

        ## Name ##
        $payload = [
            'name' => /*$faker->text(20)*/ '',
            'description' => $faker->text(50),
            'user_id' => $user->id,
            'post_or_request' => 1,
            'tag' => ['English'],
            'location' => ['Kuala Lumpur']
        ];

        $response = $this->json('POST', 'api/lessonCreate/', $payload);

        $response->assertJson(['message' => trans('message.required_field_error')]);
        $response->assertStatus(422);

        ## Description ##
        $payload = [
            'name' => $faker->text(20),
            'description' => /*$faker->text(50)*/ '',
            'user_id' => $user->id,
            'post_or_request' => 1,
            'tag' => ['English'],
            'location' => ['Kuala Lumpur']
        ];

        $response = $this->json('POST', 'api/lessonCreate/', $payload);

        $response->assertJson(['message' => trans('message.required_field_error')]);
        $response->assertStatus(422);

        ## user_id ##
        $payload = [
            'name' => $faker->text(20),
            'description' => $faker->text(50),
            'user_id' => /*$user->id*/ '',
            'post_or_request' => 1,
            'tag' => ['English'],
            'location' => ['Kuala Lumpur']
        ];

        $response = $this->json('POST', 'api/lessonCreate/', $payload);

        $response->assertJson(['message' => trans('message.required_field_error')]);
        $response->assertStatus(422);

        ## post_or_request ##
        $payload = [
            'name' => $faker->text(20),
            'description' => $faker->text(50),
            'user_id' => $user->id,
            'post_or_request' => /*1*/ '',
            'tag' => ['English'],
            'location' => ['Kuala Lumpur']
        ];

        $response = $this->json('POST', 'api/lessonCreate/', $payload);

        $response->assertJson(['message' => trans('message.required_field_error')]);
        $response->assertStatus(422);

        ## Tag ##
        $payload = [
            'name' => $faker->text(20),
            'description' => $faker->text(50),
            'user_id' => $user->id,
            'post_or_request' => 1,
            'tag' => /*['English']*/ '',
            'location' => ['Kuala Lumpur']
        ];

        $response = $this->json('POST', 'api/lessonCreate/', $payload);

        $response->assertJson(['message' => trans('message.required_field_error')]);
        $response->assertStatus(422);

        ## Tag ##
        $payload = [
            'name' => $faker->text(20),
            'description' => $faker->text(50),
            'user_id' => $user->id,
            'post_or_request' => 1,
            'tag' => ['English'],
            'location' => /*['Kuala Lumpur']*/ ''
        ];

        $response = $this->json('POST', 'api/lessonCreate/', $payload);

        $response->assertJson(['message' => trans('message.required_field_error')]);
        $response->assertStatus(422);
    }

    public function testLessonGetInformationSuccess()
    {
        $this->testLessonCreate();

        $lesson = Lessons::orderBy('id', 'DESC')->first();

        $response = $this->json('GET', 'api/lessonGetInformation/' . $lesson->id );

        $response->assertStatus(200);
    }

    public function testLessonGetInformationFailOnNoLesson()
    {
        $user = $this->UserInstructor(true);

        $response = $this->json('GET', 'api/lessonGetInformation/0');

        $response->assertStatus(400);
    }

    /**
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     */
    public function testLessonWishlistSuccess()
    {
        $this->testLessonCreate();

        $lesson = Lessons::orderBy('id', 'DESC')->first();

        $expected_response = [
            'message' => trans('message.success_wishlist_lesson')
        ];

        $payload = [
            'lesson_id' => $lesson->id
        ];

        $response = $this->json('POST', 'api/lessonWishlist/', $payload);

        $response->assertStatus(200);
        $response->assertExactJson($expected_response);
    }

    public function testLessonWishlistFailOnWishlistNonExistLesson()
    {
        $user = $this->UserInstructor(true);

        $payload = [
            'lesson_id' => 0
        ];

        $expected_response = [
            'message' => trans('message.failed_wishlist_lesson')
        ];

        $response = $this->json('POST', 'api/lessonWishlist/', $payload);

        $response->assertStatus(400);
        $response->assertExactJson($expected_response);
    }

    public function testLessonWishlistRemoveSuccess()
    {
        $this->testLessonWishlistSuccess();
        
        $lesson = Lessons::orderBy('id', 'DESC')->first();

        $payload = [
            'lesson_id' => $lesson->id
        ];

        $expected_response = [
            'message' => trans('message.success_remove_wishlist_lesson')
        ];

        $response = $this->json('POST', 'api/lessonWishlistRemove/', $payload);

        $response->assertStatus(200);
        $response->assertExactJson($expected_response);
    }

    public function testLessonWishlistRemoveFailOnNonExistLesson()
    {
        $user = $this->UserInstructor(true);

        $payload = [
            'lesson_id' => 0
        ];

        $expected_response = [
            'message' => trans('message.not_found_wishlist_lesson')
        ];

        $response = $this->json('POST', 'api/lessonWishlistRemove/', $payload);

        $response->assertStatus(400);
        $response->assertExactJson($expected_response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLessonWishlistRemoveFailOnServerError()
    {
        $this->UserInstructor(true);
        
        $lesson = Lessons::orderBy('id', 'DESC')->first();

        $payload = [
            'lesson_id' => 1
        ];

        $expected_response = [
            'message' => trans('message.something_when_wrong')
        ];

        $expected_mock_response = [
            'status_code' => 500,
            'message' => trans('message.something_when_wrong')
        ];

        $mock_helper = \Mockery::mock('overload:App\Repositories\Web\LessonRepository');
        $mock_helper->shouldReceive('lessonWishlistRemove')
                    ->andThrow(new \Exception())
                    ->andReturn($expected_mock_response);

        $response = $this->json('POST', 'api/lessonWishlistRemove/', $payload); //dd($response);

        $response->assertStatus(500);
        $response->assertExactJson($expected_response);
    }

    public function testLessonAddReviewSuccess()
    {
        $this->testLessonCreate();

        $faker = Faker::create();

        $lesson = Lessons::orderBy('id', 'DESC')->first();

        $payload = [
            'stars' => 4,
            'lesson_id' => $lesson->id,
            'review' => $faker->text(20)
        ];

        $expected_response = [
            'message' => trans('message.success_add_lesson_review')
        ];

        $response = $this->json('POST', 'api/lessonAddReview/', $payload);

        $response->assertStatus(200);
        $response->assertExactJson($expected_response);
    }

    public function testLessonAddReviewFailOnNonExistLesson()
    {
        $this->UserInstructor(true);

        $faker = Faker::create();

        $payload = [
            'stars' => 4,
            'lesson_id' => 0,
            'review' => $faker->text(20)
        ];

        $expected_response = [
            'message' => trans('message.failed_add_lesson_review_lesson_not_found')
        ];

        $response = $this->json('POST', 'api/lessonAddReview/', $payload);

        $response->assertStatus(400);
        $response->assertExactJson($expected_response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLessonAddReviewFailOnServerError()
    {
        $this->UserInstructor(true);

        $faker = Faker::create();

        $payload = [
            'stars' => 4,
            'lesson_id' => 0,
            'review' => $faker->text(20)
        ];

        $expected_response = [
            'message' => trans('message.failed_add_lesson_review')
        ];

        $expected_mock_response = [
            'status_code' => 500,
            'message' => trans('message.failed_add_lesson_review')
        ];

        $mock_helper = \Mockery::mock('overload:App\Repositories\Web\LessonRepository');
        $mock_helper->shouldReceive('lessonAddReview')
                    ->andThrow(new \Exception())
                    ->andReturn($expected_mock_response);

        $response = $this->json('POST', 'api/lessonAddReview/', $payload);

        $response->assertStatus(500);
        $response->assertExactJson($expected_response);
    }

    public function testLessonInterestedRequestorCreateSuccess()
    {
        //Create user 1 and create request lesson 
        $user1 = $this->UserInstructor(true);

        $faker = Faker::create();

        $payload = [
            'name' => $faker->text(20),
            'description' => $faker->text(50),
            'user_id' => $user1->id,
            'post_or_request' => 2,
            'tag' => ['English'],
            'location' => ['Kuala Lumpur']
        ];

        $response = $this->json('POST', 'api/lessonCreate/', $payload);

        $response->assertStatus(200);

        //Create user 2 and user 2 will join as interested requestor
        $user2 = $this->UserInstructor(true);

        $lesson = Lessons::where('post_or_request', 2)->orderBy('id', 'DESC')->first();

        $payload = [
            'lesson_id' => $lesson->id
        ];

        $expected_response = [
            'message' => trans('message.success_create_lesson_interested_requestor')
        ];

        $response = $this->json('POST', 'api/lessonInterestedRequestorCreate/', $payload);

        $response->assertStatus(200);
        $response->assertExactJson($expected_response);
    }

    public function testLessonInterestedRequestorCreateFailOnSameUserJoinAsInterestedRequestor()
    {
        $user = $this->UserInstructor(true);

        $faker = Faker::create();

        $payload = [
            'name' => $faker->text(20),
            'description' => $faker->text(50),
            'user_id' => $user->id,
            'post_or_request' => 2,
            'tag' => ['English'],
            'location' => ['Kuala Lumpur']
        ];

        $response = $this->json('POST', 'api/lessonCreate/', $payload);

        $response->assertStatus(200);

        $lesson = Lessons::where('post_or_request', 2)->orderBy('id', 'DESC')->first();

        $payload = [
            'lesson_id' => $lesson->id
        ];

        $expected_response = [
            'message' => trans('message.success_fail_create_lesson_interested_requestor_on_same_user')
        ];

        $response = $this->json('POST', 'api/lessonInterestedRequestorCreate/', $payload);

        $response->assertStatus(400);
        $response->assertExactJson($expected_response);
    }

    /**
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     */
    public function testLessonInterestedRequestorCreateFailOnServerError()
    {
        //Create user 1 and create request lesson 
        $user1 = $this->UserInstructor(true);

        $faker = Faker::create();

        $payload = [
            'name' => $faker->text(20),
            'description' => $faker->text(50),
            'user_id' => $user1->id,
            'post_or_request' => 2,
            'tag' => ['English'],
            'location' => ['Kuala Lumpur']
        ];

        $response1 = $this->json('POST', 'api/lessonCreate/', $payload);

        $response1->assertStatus(200);

        //Create user 2 and user 2 will join as interested requestor
        $user2 = $this->UserInstructor(true);

        $lesson = Lessons::where('post_or_request', 2)->orderBy('id', 'DESC')->first();

        $payload = [
            'lesson_id' => $lesson->id
        ];

        $expected_response = [
            'message' => trans('message.fail_create_lesson_interested_requestor')
        ];

        $expected_mock_response = [
            'status_code' => 500,
            'message' => trans('message.fail_create_lesson_interested_requestor')
        ];

        $mock_helper = \Mockery::mock('overload:App\Repositories\Web\LessonRepository');
        $mock_helper->shouldReceive('lessonInterestedRequestorCreate')
                    ->andThrow(new \Exception())
                    ->andReturn($expected_mock_response);

        $response = $this->json('POST', 'api/lessonInterestedRequestorCreate/', $payload);

        $response->assertStatus(500);
        $response->assertExactJson($expected_response);
    }
}
