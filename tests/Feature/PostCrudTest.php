<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_it_can_fetch_a_paginated_list_of_posts(): void
    {
        Post::factory()->count(50)->create();

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

    public function it_creates_a_post_with_a_user()
    {
        $user = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $user->id]);

        // Assertions
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }

    public function test_it_can_fetch_a_post()
    {
        $post = Post::factory()->create();
        $response = $this->get(route('posts.show', $post->id));
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'user_id' => $post->user_id,
            'body' => $post->body,
            'status' => $post->status,
        ]);
    }

    public function test_it_return_404_post_not_found()
    {
        $response = $this->get(route('posts.show', 9899));
        $response->assertStatus(404);
    }

    public function test_it_update_a_post()
    {
        $user = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $user->id]);

        $data = [
            'title' => 'Updated Post Title',
            'body' => 'Updated Post Body',
            'status' => 0,
        ];

        $response = $this->actingAs($user)->putJson(route('posts.update', $post->id), $data);

        $updatedPost = Post::find($post->id);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Post updated successfully']);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'status' => $data['status'],
            'user_id' => $user->id,
        ]);

        $this->assertEquals($data['title'], $updatedPost->title);
        $this->assertEquals($data['body'], $updatedPost->body);
        $this->assertEquals($data['status'], $updatedPost->status);
        $this->assertEquals($user->id, $updatedPost->user_id);
    }

    public function test_it_can_delete_a_post_by_user()
    {
        $user = User::factory()->create();

        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->deleteJson(route('posts.destroy', $post->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id
        ]);
    }
}
