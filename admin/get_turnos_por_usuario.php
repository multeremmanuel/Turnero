<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../conexion.php';

// Obtener turnos atendidos por usuario y caja hoy
try {
    $stmt = $pdo->prepare("
        SELECT 
            u.usuario, 
            c.nombre AS caja, 
            COUNT(a.id) AS turnos_atendidos
        FROM atencion a
        INNER JOIN usuarios u ON a.idUsuario = u.id
        LEFT JOIN cajas c ON a.idCaja = c.id
        WHERE a.atendido = 1 AND DATE(a.fechaAtencion) = CURDATE()
        GROUP BY a.idUsuario, a.idCaja
        ORDER BY turnos_atendidos DESC
    ");
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultado);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
