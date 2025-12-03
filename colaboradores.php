<?php
require_once 'conexion.php';

$query = $conn->query("
    SELECT id, codigo, nombre, puesto, area, estado, fecha_ingreso, notas
    FROM colaboradores
    ORDER BY puesto ASC, nombre ASC
");

$data = [];

while ($row = $query->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "ok" => true,
    "colaboradores" => $data
]);
