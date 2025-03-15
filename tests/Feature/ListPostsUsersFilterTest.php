<?php
use App\Models\User;
use App\Models\Post;
use Pest\Laravel\test;

test('Listar todos los posts del usuario con filtro', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Post::factory()->create([
        'title' => 'Post',
        'content' => 'Texto texto texto texto',
        'user_id' => $user->id,
    ]);

    Post::factory()->create([
        'title' => 'Post de otro usuario',
        'content' => 'Contenido ajeno',
        'user_id' => $otherUser->id,
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/posts?search=primer');

    $response->assertStatus(200)
        ->assertJsonCount(1) 
        ->assertJsonFragment(['title' => 'Mi primer post'])
        ->assertJsonMissing(['title' => 'Post de otro usuario']); 
});
