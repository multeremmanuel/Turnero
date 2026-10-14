<?php
session_start();
require '../conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
    echo json_encode([]);
    exit;
}

// Paginación
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Fecha actual
$hoy = date('Y-m-d');

// Consulta con prioridad y hora de registro
$stmt = $pdo->prepare("
    SELECT t.*, c.nombre AS cajaNombre,
    CASE
        WHEN t.atendido = 1 THEN 'atendido'
        WHEN t.idCaja IS NOT NULL AND t.atendido = 0 THEN 'atendiendo'
        ELSE 'cola'
    END AS estado
    FROM turnos t
    LEFT JOIN cajas c ON t.idCaja = c.id
    WHERE DATE(t.fechaRegistro) = :hoy
    ORDER BY t.prioridad DESC, t.fechaRegistro ASC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':hoy', $hoy);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($turnos);
