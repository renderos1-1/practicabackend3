<?php

use function Pest\Laravel\getJson;

test('Error de autenticación al consultar el endpoint sin haber iniciado sesión', function () {
    $response = getJson('/api/v1/posts');

    $response->assertStatus(401);
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