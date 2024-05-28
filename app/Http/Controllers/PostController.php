<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 15;
        $posts = Post::latest()->paginate($perPage);

        return PostResource::collection($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $post = new Post();
        $post->title = $request->title;
        $post->slug = Str::slug($request->title);
        $post->body = $request->body;
        $post->user_id = $request->user_id;
        $post->status = $request->status;

        if ($post->save()) {
            return response()->json(['success' => true, 'message' => 'Post saved successfully'], 201);
        }
        return response()->json(['success' => false, 'message' => 'Post save fail!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return response()->json($post, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $this->validate($request, [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $post->title = $request->title;
        $post->slug = Str::slug($request->title);
        $post->body = $request->body;
        $post->status = $request->status;

        if ($post->save()) {
            return response()->json(['success' => true, 'message' => 'Post updated successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Post update fail!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->delete()) {
            return response()->json(['success' => true, 'message' => 'Post deleted successfully']);
        }
        return response()->json(['success' => false, 'message' => 'Post delete fail!']);
    }
}
