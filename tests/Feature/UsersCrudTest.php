<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UsersCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_store_a_user(): void
    {
        // Arrange: Prepare the data for the user to be created
        $data = [
            'name' => 'Test user',
            'email' => 'test@example.com',
            'password' => 'password', // Plain password, it will be hashed by the controller
        ];

        // Act: Send a POST request to the user store endpoint
        $response = $this->post(route('users.store'), $data);

        // Assert: Check that the response is as expected
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'User store successfully',
        ]);

        // Assert: Verify that the user is stored in the database
        $this->assertDatabaseHas('users', [
            'name' => 'Test user',
            'email' => 'test@example.com'
        ]);
    }

//    /** @test */
    public function it_fails_to_store_a_user_with_missing_data()
    {
        // Arrange: Prepare incomplete data for the user to be created
        $data = [
            'name' => 'Test user',
            // 'email' is missing
            'password' => 'password',
        ];

        // Act: Send a POST request to the user store endpoint
        $response = $this->post(route('users.store'), $data);

        // Assert: Check that the response is as expected
        $response->assertStatus(422); // Unprocessable Entity due to validation failure
        $response->assertJson([
            'success' => false,
            'message' => 'User store fail!',
        ]);

        // Assert: Verify that the user is not stored in the database
        $this->assertDatabaseMissing('users', [
            'name' => 'Test user',
        ]);
    }

    public function test_it_can_fetch_a_user()
    {
        // Arrange: Create a new user
        $user = User::factory()->create();

        // Act: Send a GET request to the user fetch endpoint
        $response = $this->get(route('users.show', $user->id));

        // Assert: Check that the response is as expected
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);
    }

    public function test_it_returns_404_user_not_found()
    {
        // Act: Send a GET request to the user fetch endpoint
        $response = $this->get(route('users.show', 9999));

        // Assert: Check that the response is as expected
        $response->assertStatus(404);
        $response->assertJson([
            'message' => 'User not found'
        ]);
    }

    public function test_it_can_update_a_user()
    {
        // Arrange: Create a new user
        $user = User::factory()->create();

        // Prepare the data to update the user
        $data = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            // Include the password field only if it's meant to be updated, e.g., when validating
            'password' => 'newpassword',
        ];

        // Act: Send a PUT/PATCH request to the user update endpoint
        $response = $this->put(route('users.update', $user->id), $data);

        // Asset: Check that the response is as expected
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'User updated successfully',
        ]);

        // Assert: Verify that the user is updated in the database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        // Check if the password is hashed and updated
        $this->assertTrue(Hash::check('newpassword', $user->fresh()->password));
    }

    public function test_it_can_delete_a_user()
    {
        $user = User::factory()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id
        ]);

        $response = $this->delete(route('users.destroy', $user));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }

    public function it_fails_to_update_a_user_with_invalid_data()
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => 'not-an-email',
        ];

        $response = $this->put(route('users.update', $user->id), $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'email' => 'not-an-email',
        ]);
    }
}
