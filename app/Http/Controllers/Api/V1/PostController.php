<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Display a listing of posts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Post::where('user_id', Auth::id())
            ->with(['categories', 'user']);

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        $posts = $query->latest()->get();

        // Transform posts to match test expectations
        $transformedPosts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'categories' => $post->categories->pluck('name')->toArray(),
                'user' => $post->user->name,
                'created_at' => $post->created_at
            ];
        });

        return response()->json(['data' => $transformedPosts]);
    }

    /**
     * Store a newly created post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
        ]);

        // Generate a slug
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;

        // Verify if slug exists, if so, make it unique by adding a number
        $count = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }

        // Create the post
        $post = Post::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'user_id' => Auth::id(),
        ]);

        // Attach categories
        $post->categories()->attach($validated['categories']);

        // Load relationships for response
        $post->load(['categories', 'user']);

        // Format the response to match test expectations
        $response = [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'content' => $post->content,
            'categories' => $post->categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name
                ];
            }),
            'user' => [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'email' => $post->user->email
            ],
            'created_at' => $post->created_at,
            'updated_at' => $post->updated_at
        ];

        return response()->json($response, 201);
    }

    /**
     * Display the specified post.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Post $post)
    {
        // Check if the post belongs to the authenticated user
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->load(['categories', 'user']);

        return response()->json($post);
    }

    /**
     * Update the specified post.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Post $post)
    {
        // Check if the post belongs to the authenticated user
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'excerpt' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
            'categories' => 'sometimes|required|array|min:1',
            'categories.*' => 'exists:categories,id',
        ]);

        if (isset($validated['title']) && $validated['title'] !== $post->title) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;

            $count = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }

            $validated['slug'] = $slug;
        }

        // Update the post
        $post->update($validated);

        // Update categories
        if (isset($validated['categories'])) {
            $post->categories()->sync($validated['categories']);
        }

        // Load relationships
        $post->load(['categories', 'user']);

        return response()->json($post);
    }

    /**
     * Remove the specified post.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(null, 204);
    }
}
