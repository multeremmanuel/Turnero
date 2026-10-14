<?php
require 'conexion.php';

// Último turno llamado
$stmtUltimo = $pdo->prepare("
    SELECT t.turno, t.prioridad, c.nombre AS caja
    FROM turnos t
    LEFT JOIN cajas c ON t.idCaja = c.id
    WHERE t.atendido = 1
    ORDER BY t.fechaAtencion DESC
    LIMIT 1
");
$stmtUltimo->execute();
$ultimo = $stmtUltimo->fetch(PDO::FETCH_ASSOC);

// Siguiente turno
$stmtSiguiente = $pdo->prepare("
    SELECT t.turno, t.prioridad
    FROM turnos t
    WHERE t.atendido = 0
    ORDER BY prioridad DESC, fechaRegistro ASC
    LIMIT 1
");
$stmtSiguiente->execute();
$siguiente = $stmtSiguiente->fetch(PDO::FETCH_ASSOC);

// Turnos en curso (últimos 5)
$stmtEnCurso = $pdo->prepare("
    SELECT t.turno, t.prioridad, c.nombre AS caja
    FROM turnos t
    LEFT JOIN cajas c ON t.idCaja = c.id
    WHERE t.atendido = 1
    ORDER BY t.fechaAtencion DESC
    LIMIT 6 OFFSET 1
");
$stmtEnCurso->execute();
$enCurso = $stmtEnCurso->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'ultimo' => $ultimo,
    'siguiente' => $siguiente,
    'enCurso' => $enCurso
]);
