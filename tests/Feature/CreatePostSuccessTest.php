<?php

use App\Models\User;
use App\Models\Category;
use Pest\Laravel\test;

test('crear post exitosamente', function () {
    $user = User::factory()->create();
    $categories= Category::factory()->count(2)->create();

    $response = $this->actingAs($user)->postJson('/api/v1/posts',[
        'title' => 'Mi nueva publicacion',
        'excerpt' => 'Lorem ipsum sit amet',
        'content' => 'Lorem ipsum dolor sit amet...',
        'categories' => $categories->pluck('id')->toArray(),
    ]);

    $response->assertStatus(201) -> assertJsonStructure([
        'id', 'title', 'slug', 'excerpt', 'content', 'categories', 'user', 'created_at', 'updated_at'
    ]);
});
