<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_it_can_fetch_a_paginated_list_of_posts(): void
    {
        $posts = Post::factory()->count(50)->create();

        $response = $this->get(route('posts.index', ['page' => 1, 'per_page' => 15]));
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'user_id', 'body', 'status', 'created_at', 'updated_at']
            ],
            'links',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]
        ]);
        $responseData = $response->json();
        $this->assertEquals(1, $responseData['meta']['current_page']);
        $this->assertEquals(15, $responseData['meta']['per_page']);
        $this->assertEquals(50, $responseData['meta']['total']);
    }

    public function test_it_can_store_a_post()
    {
        $data = [
            'title' => 'This is a test post',
            'slug' => 'this-is-a-test-post',
            'user_id' => 1,
            'body' => 'Lorem ipsum Plain password, it will be hashed by the controller. Prepare the data for the user to be created.',
            'status' => 1,
        ];

        $response = $this->post(route('posts.store'), $data);
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'User store successfully',
        ]);
    }
}
