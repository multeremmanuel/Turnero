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
    if ($idCaja === 1) {
        // Caja 1: puede atender todos los turnos, pero siempre prioriza los tipo 3
        $sql = "SELECT id, turno, tipo, prioridad, fechaRegistro
                FROM turnos
                WHERE atendido = 0
                ORDER BY (tipo = 3) DESC, fechaRegistro ASC
                LIMIT 1";
    } else {
        // Otras cajas: solo turnos 0,1,2
        $sql = "SELECT id, turno, tipo, prioridad, fechaRegistro
                FROM turnos
                WHERE atendido = 0 AND tipo IN (0,1,2)
                ORDER BY tipo DESC, fechaRegistro ASC
                LIMIT 1";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $turno = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($turno) {
        echo json_encode([
            'id' => (int)$turno['id'],
            'turno' => $turno['turno'],
            'tipo' => (int)$turno['tipo'],
            'prioridad' => $turno['prioridad'], // texto: normal/prioritario/especial
            'fechaRegistro' => $turno['fechaRegistro']
        ]);
    } else {
        echo json_encode([
            'id' => null,
            'turno' => null,
            'tipo' => null,
            'prioridad' => null,
            'fechaRegistro' => null
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en consulta: '.$e->getMessage()]);
}
?>
