<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testRegisterSuccess()
    {
        $payload = [
            'email' => '123@123.com',
            'name' => '123',
            'password' => '123'
        ];

        $response = $this->json('POST', 'api/register', $payload);

        $expected_response = [
            'success' => true,
            'message' => 'User successfully created'
        ];

        $response->assertStatus(200);
        $response->assertExactJson($expected_response);
    }

    public function testRegisterFail()
    {
        $payload = [
            'email' => '123@123.com',
            'name' => '123',
            'password' => '123'
        ];

        $expected_mock_response = [
            'success' => false,
            'message' => 'Failed to create user'
        ];

        $mock_base_service = \Mockery::mock('overload:App\Http\Controllers\RegisterController');
        $mock_base_service->shouldReceive('register')
            ->once()
            ->andThrow(new \Exception())
            ->andReturn($expected_mock_response);
            
        $expected_response = [
            'success' => false,
            'message' => 'Failed to create user',
        ];

        $response = $this->json('POST', 'api/register', $payload); 
        
        $response->assertExactJson($expected_response);
    }
}
