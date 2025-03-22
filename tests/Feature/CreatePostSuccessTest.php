<?php

use App\Models\User;
use App\Models\Category;
use Pest\Laravel\test;

test('crear post exitosamente', function () {
    $this->assertNotNull(DB::connection());
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


test('Database connection is working properly', function () {
    $this->assertTrue(DB::connection()->getDatabaseName() != null);

    try {
        DB::select('SELECT 1');
        $this->assertTrue(true);
    } catch (\Exception $e) {
        $this->fail('La conexión a la base de datos falló: ' . $e->getMessage());
    }
});