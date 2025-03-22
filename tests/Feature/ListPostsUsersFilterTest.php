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

    // cambiar termino de busqueda para que coincida con el titulo del post
    $response = $this->actingAs($user)->getJson('/api/v1/posts?search=Post');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['title' => 'Post'])  // cambiando 'Mi primer post'
        ->assertJsonMissing(['title' => 'Post de otro usuario']);
});

test('Database connection is working properly', function () {
    $this->assertTrue(DB::connection()->getDatabaseName() != null);

    try {
        DB::select('SELECT 1');
        $this->assertTrue(true);
    } catch (\Exception $e) {
        $this->fail('La conexión a la base de datos falló: ' . $e->getMessage());
    }
});