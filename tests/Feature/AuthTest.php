<?php

use App\Models\User;

test('Allows Login successfully', function () {
    $user = User::factory()->create([
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $this->assertAuthenticatedAs($user);
});

test('Return authenticated user information', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/api/v1/auth/me');

    $response->assertStatus(200)
        ->assertJson([
            'profile' => [
                'email' => $user->email,
            ]
        ]);
});

test('Allow successfully Logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->assertAuthenticated();

    $response = $this->post('/api/v1/auth/logout');

    $response->assertStatus(200);
    $this->assertGuest();
});

test('Database connection is working properly', function () {
    $this->assertTrue(DB::connection()->getDatabaseName() != null);

    try {
        DB::select('SELECT 1');
        $this->assertTrue(true);
    } catch (\Exception $e) {
        $this->fail('La conexión a la base de datos falló: ' . $e->getMessage());
    }

    $this->assertTrue(Schema::hasTable('users'), 'La tabla "users" no existe');
});
