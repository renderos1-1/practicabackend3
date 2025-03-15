<?php

use function Pest\Laravel\getJson;

test('Error de autenticación al consultar el endpoint sin haber iniciado sesión', function () {
    $response = getJson('/api/v1/posts');

    $response->assertStatus(401);
});
