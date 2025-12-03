<?php
// lc_dashboard/api/limpieza_guardar.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    json_response(['ok' => false, 'error' => 'JSON inválido'], 400);
}

$semana = $data['semana'] ?? null;
$fila   = $data['filas']  ?? [];

if (!$semana) {
    json_response(['ok' => false, 'error' => 'Semana requerida'], 400);
}

try {
    $pdo = Database::getConnection();
    $pdo->beginTransaction();

    // Eliminamos rol anterior de esa semana (si quieres comportamiento “reemplazar”)
    $del = $pdo->prepare("DELETE FROM rol_limpieza WHERE semana = :semana");
    $del->execute([':semana' => $semana]);

    $ins = $pdo->prepare("
        INSERT INTO rol_limpieza
          (id, semana, dia, tarea, area_designada, persona, tipo_frecuencia, id_tarea, estado)
        VALUES
          (:id, :semana, :dia, :tarea, :area, :persona, :frecuencia, :id_tarea, :estado)
    ");

    foreach ($fila as $row) {
        foreach ($row['dias'] as $dia => $infoDia) {
            if (empty($infoDia['responsable'])) continue;

            $ins->execute([
                ':id'          => uniqid('RL_'),
                ':semana'      => $semana,
                ':dia'         => $dia,                       // Lun, Mar...
                ':tarea'       => $row['tarea'],
                ':area'        => $row['area'] ?? null,
                ':persona'     => $infoDia['id_colab'] ?? null,
                ':frecuencia'  => $row['frecuencia'] ?? null,
                ':id_tarea'    => $row['id_tarea'] ?? null,
                ':estado'      => !empty($infoDia['hecho']) ? 'REALIZADO' : 'PENDIENTE'
            ]);
        }
    }

    $pdo->commit();
    json_response(['ok' => true]);

} catch (Throwable $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if (APP_DEBUG) {
        json_response(['ok' => false, 'error' => $e->getMessage()], 500);
    }
    json_response(['ok' => false, 'error' => 'Error interno al guardar rol de limpieza'], 500);
}
