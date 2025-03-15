<?php
use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use Pest\Laravel\test;

test('Crear post exitosamente aunque el slug este repetido.', function () {

    $user = User::factory()->create();
    $categories = Category::factory()->count(2)->create();
    

    Post::factory()->create(['title' => 'Mi nueva publicación', 'user_id' => $user->id]);

    $response = $this->actingAs($user)->postJson('/api/v1/posts', [
        'title' => 'Mi nueva publicación',
        'excerpt' => 'Lorem ipsum sit amet',
        'content' => 'Lorem ipsum dolor sit amet...',
        'categories' => $categories->pluck('id')->toArray(),
    ]);

    $response->assertStatus(201);
});
