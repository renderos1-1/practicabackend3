<?php

use App\Models\User;
use App\Models\Post;
use Pest\Laravel\test;

test('Listar todos los posts del usuario sin filtro', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

   
    Post::factory()->count(3)->create([
        'title' => 'Post del usuario',
        'content' => 'Contenido del usuario',
        'user_id' => $user->id,
    ]);

   
    Post::factory()->count(2)->create([
        'title' => 'Post de otro usuario',
        'content' => 'Contenido ajeno',
        'user_id' => $otherUser->id,
    ]);

    
    $response = $this->actingAs($user)->getJson('/api/v1/posts');

    
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data') 
        ->assertJsonMissing(['user_id' => $otherUser->id]); 
});
