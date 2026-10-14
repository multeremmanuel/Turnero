<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require 'conexion.php';

if (!isset($_SESSION['idCaja'])) {
    echo json_encode(['error' => 'No hay caja seleccionada']);
    exit;
}

$idCaja = (int)$_SESSION['idCaja'];

try {
    $sql = "SELECT turno 
            FROM turnos 
            WHERE atendido = 1 AND idCaja = :idCaja 
            ORDER BY fechaAtencion DESC 
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':idCaja' => $idCaja]);
    $turno = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($turno) {
        echo json_encode([
            'turno' => $turno['turno']
        ]);
    } else {
        echo json_encode([
            'turno' => null
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en consulta: '.$e->getMessage()]);
}
?>
