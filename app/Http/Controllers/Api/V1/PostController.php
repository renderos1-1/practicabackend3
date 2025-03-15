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
     * mostrar lista de posts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Post::where('user_id', Auth::id())
            ->with(['categories', 'user']);

        // Aplicar filtro de busqueda
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        $posts = $query->latest()->get();

        // Modificaciones para matchear la respuesta esperada en el JSON
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

        return response()->json($transformedPosts);
    }

    /**
     * almacenar un nuevo post.
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

        // Generar un slug
        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;

        //Verificar si existe el slug, si es asi, hacerlo unico agregando un numero
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

        // vincular las categorias
        $post->categories()->attach($validated['categories']);

        // cargar las relaciones para la respuesta
        $post->load(['categories', 'user']);

        // Modificaciones para matchear la respuesta esperada en el JSON
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
     * post especifico.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Post $post)
    {
        // verificar si el post pertenece al usuario autenticado
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->load(['categories', 'user']);

        return response()->json($post);
    }

    /**
     * Uactualizar post especifico.
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

        // actualizar el post
        $post->update($validated);

        // actualizar las categorias
        if (isset($validated['categories'])) {
            $post->categories()->sync($validated['categories']);
        }

        // cargar las relaciones
        $post->load(['categories', 'user']);

        return response()->json($post);
    }

    /**
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
