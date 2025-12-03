<?php
// lc_dashboard/api/kpis_hoy.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

try {
    $pdo = Database::getConnection();

    // Hoy (puedes cambiarlo por fecha que recibas por GET)
    $hoy = date('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT
            fecha,
            ventabruta,
            ventaneta,
            ticketprom,
            clientes,
            coberturapresupuesto,
            acumulado,
            proyectado
        FROM kapeis
        WHERE fecha = :fecha
        LIMIT 1
    ");
    $stmt->execute([':fecha' => $hoy]);
    $row = $stmt->fetch();

    if (!$row) {
        json_response([
            'ok' => false,
            'message' => 'No hay KPIs capturados para la fecha ' . $hoy
        ], 404);
    }

    json_response([
        'ok' => true,
        'data' => $row
    ]);

} catch (Throwable $e) {
    if (APP_DEBUG) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 500);
    }
    json_response(['ok' => false, 'error' => 'Error interno'], 500);
}
