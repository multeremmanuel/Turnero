<?php
require 'conexion.php';

// Contar todos los turnos atendidos
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM turnos");
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

echo json_encode(['total' => $total]);
