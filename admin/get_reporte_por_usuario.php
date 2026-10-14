<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../conexion.php';

try {
    // Últimos 7 días
    $hoy = date('Y-m-d');
    $fechas = [];
    for($i = 6; $i >= 0; $i--) { // de hace 6 días hasta hoy
        $fechas[] = date('Y-m-d', strtotime("-$i days"));
    }

    // Obtener todos los usuarios
    $stmt = $pdo->query("SELECT id, usuario FROM usuarios");
    $usuariosDB = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $usuarios = [];
    foreach($usuariosDB as $usuario) {
        $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF)); // color aleatorio

        $turnos = [];
        foreach($fechas as $fecha) {
            $stmtTurnos = $pdo->prepare("SELECT COUNT(*) as total FROM turnos WHERE idUsuario = ? AND DATE(fechaAtencion) = ?");
            $stmtTurnos->execute([$usuario['id'], $fecha]);
            $turnos[] = (int)$stmtTurnos->fetchColumn();
        }

        $usuarios[] = [
            'usuario' => $usuario['usuario'],
            'turnos' => $turnos,
            'color' => $color
        ];
    }

    // Devuelve JSON compatible con tu JS
    echo json_encode([
        'fechas' => $fechas,
        'usuarios' => $usuarios
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
