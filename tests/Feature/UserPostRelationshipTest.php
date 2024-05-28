<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserPostRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_have_multiple_posts(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->posts);
        $this->assertTrue($user->posts->contains($posts[0]));
        $this->assertTrue($user->posts->contains($posts[1]));
        $this->assertTrue($user->posts->contains($posts[2]));
    }

    /** @test */
    public function a_post_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }
}
