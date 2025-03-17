<?php
use App\Models\User;
use Pest\Laravel\test;

test('Error de validación al crear post por falta de datos requeridos', function () {
    $this->assertNotNull(DB::connection());
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/v1/posts', []);

    $response->assertStatus(422)
    ->assertJsonValidationErrors(['title', 'excerpt', 'content', 'categories']);
});
