<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

try {
    $pdo = Database::getConnection();

    $query = $pdo->query("
        SELECT id, codigo, nombre, puesto, area, estado, fecha_ingreso, notas
        FROM colaboradores
        ORDER BY puesto ASC, nombre ASC
    ");

    $data = $query->fetchAll();

    json_response([
        'ok' => true,
        'colaboradores' => $data
    ]);
} catch (Throwable $e) {
    if (APP_DEBUG) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 500);
    }
    json_response(['ok' => false, 'error' => 'Error interno al obtener colaboradores'], 500);
}
