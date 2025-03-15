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
