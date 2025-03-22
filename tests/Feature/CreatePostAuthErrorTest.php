<?php
use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use Pest\Laravel\test;


test('Verificar conexión a la base de datos', function () {
    $this->assertTrue(DB::connection()->getDatabaseName() != null);
    
    try {
        DB::select('SELECT 1');
        $this->assertTrue(true);
    } catch (\Exception $e) {
        $this->fail('La conexión a la base de datos falló: ' . $e->getMessage());
    }
    
    $this->assertTrue(Schema::hasTable('users'), 'La tabla "users" no existe');
    $this->assertTrue(Schema::hasTable('posts'), 'La tabla "posts" no existe');
    $this->assertTrue(Schema::hasTable('categories'), 'La tabla "categories" no existe');
});

test('Error de autenticación al consultar el EP sin haber iniciado sesión', function () {
    $response = $this->postJson('/api/v1/posts',[
        'title' => 'Mi nueva publicación',
        'excerpt' => 'Lorem ipsum sit amet',
        'content' => 'Lorem ipsum dolor sit amet...',
        'categories' => [1, 3],
    ]);

    $response->assertStatus(401);
});
