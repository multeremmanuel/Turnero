<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'operador') {
    exit("Acceso denegado");
}

if (!isset($_POST['idTurno'])) {
    exit("No se indicó el turno");
}

$idTurno = $_POST['idTurno'];
$idCaja = $_SESSION['idCaja'];
$idUsuario = $_SESSION['usuario_id'];

// Intentar marcar el turno como atendido
$stmtUpdate = $pdo->prepare("
    UPDATE turnos 
    SET atendido = 1, idCaja = :idCaja, idUsuario = :idUsuario, fechaAtencion = NOW()
    WHERE id = :id AND atendido = 0
");
$stmtUpdate->execute([
    ':idCaja' => $idCaja,
    ':idUsuario' => $idUsuario,
    ':id' => $idTurno
]);

if ($stmtUpdate->rowCount() > 0) {
    echo "✅ Turno llamado correctamente.";
} else {
    echo "⚠️ Este turno ya fue atendido por otro operador.";
}
