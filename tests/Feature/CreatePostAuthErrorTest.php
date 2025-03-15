<?php
use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use Pest\Laravel\test;

test('Error de autenticación al consultar el EP sin haber iniciado sesión', function () {
    $response = $this->postJson('/api/v1/posts',[
        'title' => 'Mi nueva publicación',
        'excerpt' => 'Lorem ipsum sit amet',
        'content' => 'Lorem ipsum dolor sit amet...',
        'categories' => [1, 3],
    ]);

    $response->assertStatus(401);
});
