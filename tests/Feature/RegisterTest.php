<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Faker\Factory as Faker;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\WithFaker;

class RegisterTest extends TestCase
{

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testRegisterSuccessOnInstructorRole()
    {
        $faker = Faker::create();

        $payload = [
            'email' => $faker->unique()->safeEmail,
            'name' => $faker->name,
            'password' => '123',
            'role' => 'instructor',
            'tag' => ['Music', 'English'],
            'location' => ['Kuala Lumpur'],
        ];

        $response = $this->json('POST', 'api/register', $payload);

        $expected_response = [
            'message' => trans('message.success_create_user')
        ];

        $response->assertStatus(200);
        $response->assertExactJson($expected_response);
    }

    public function testRegisterFailOnInstructorRoleNoTag()
    {
        $faker = Faker::create();

        $payload = [
            'email' => $faker->unique()->safeEmail,
            'name' => $faker->name,
            'password' => '123',
            'role' => 'instructor',
            //'tag' => ['Music', 'English'],
            'location' => ['Kuala Lumpur'],
        ];

        $response = $this->json('POST', 'api/register', $payload);

        $expected_response = [
            'message' => trans('message.fail_create_user')
        ];

        $response->assertStatus(400);
        $response->assertExactJson($expected_response);
    }

    public function testRegisterFailOnInstructorRoleNoLocation()
    {
        $faker = Faker::create();

        $payload = [
            'email' => $faker->unique()->safeEmail,
            'name' => $faker->name,
            'password' => '123',
            'role' => 'instructor',
            'tag' => ['Music', 'English'],
            //'location' => ['Kuala Lumpur'],
        ];

        $response = $this->json('POST', 'api/register', $payload);

        $expected_response = [
            'message' => trans('message.fail_create_user')
        ];

        $response->assertStatus(400);
        $response->assertExactJson($expected_response);
    }

    public function testRegisterSuccessOnStudentRole()
    {
        $faker = Faker::create();

        $payload = [
            'email' => $faker->unique()->safeEmail,
            'name' => $faker->name,
            'password' => '123',
            'role' => 'student'
        ];

        $response = $this->json('POST', 'api/register', $payload);

        $expected_response = [
            'message' => trans('message.success_create_user')
        ];

        $response->assertStatus(200);
        $response->assertExactJson($expected_response);
    }

    public function testRegisterFail()
    {
        $faker = Faker::create();

        $payload = [
            'email' => $faker->unique()->safeEmail,
            'name' => $faker->name,
            'password' => '123',
            'role' => 'instructor'
        ];

        $expected_mock_response = [
            'message' => trans('message.fail_create_user')
        ];

        $mock_base_service = \Mockery::mock('overload:App\Http\Controllers\RegisterController');
        $mock_base_service->shouldReceive('register')
            ->once()
            ->andThrow(new \Exception())
            ->andReturn($expected_mock_response);
            
        $expected_response = [
            'message' => trans('message.fail_create_user')
        ];

        $response = $this->json('POST', 'api/register', $payload); 
        
        $response->assertExactJson($expected_response);
    }

    public function testUserProfileShowSuccess()
    {
        $user = factory(User::class)->create();

        Passport::actingAs(
            $user/*,
            ['user']*/
        );

        $response = $this->json('GET', 'api/userProfileShow');

        $response->assertStatus(200);
    }

    public function testUserProfileShowFailOnUnauthenticatedUser()
    {
        $response = $this->json('GET', 'api/userProfileShow');

        $response->assertExactJson(['message' => 'Unauthenticated.']);
        $response->assertStatus(401);
    }

    public function testUserProfileUpdateSuccess()
    {
        $faker = Faker::create();

        $user = factory(User::class)->create();

        Passport::actingAs(
            $user/*,
            ['user']*/
        );

        $payload = [
            'name' => $faker->name,
            'password' => 'abc123'
        ];

        $response = $this->json('POST', 'api/userProfileUpdate/', $payload);

        $response->assertStatus(200);
    }


}
