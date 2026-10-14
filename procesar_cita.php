<?php
// procesar_cita.php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
session_start();

// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido. Usa POST.']);
    exit;
}

// Validar parámetro ID
if (!isset($_POST['id']) || $_POST['id'] === '') {
    echo json_encode(['error' => 'Debes enviar el campo id (ID de la cita).']);
    exit;
}

$id = (int)$_POST['id'];

// ==========================================
// Conexiones centralizadas (turnero + citas)
// ==========================================
require __DIR__ . '/conexion.php';

if (!isset($pdoEA) || !($pdoEA instanceof PDO)) {
    echo json_encode(['error' => 'conexion.php no definió $pdoEA correctamente.']);
    exit;
}

if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo json_encode(['error' => 'conexion.php no definió $pdo correctamente.']);
    exit;
}

// Buscar cita por ID
try {
    $stmt = $pdoEA->prepare("SELECT id, start_datetime FROM ea_appointments WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $cita = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en consulta a ea_appointments: '.$e->getMessage()]);
    exit;
}

// Configurar zona horaria
$serverTz = new DateTimeZone('America/Mexico_City');
date_default_timezone_set('America/Mexico_City');
$now = new DateTime('now', $serverTz);

// Función para validar rango
function estaEnRango(DateTime $start, DateTime $now) {
    $rango_inicio = (clone $start)->modify('-30 minutes');
    $rango_fin    = (clone $start)->modify('+10 minutes');
    if ($start->format('Y-m-d') !== $now->format('Y-m-d')) return false;
    return ($now >= $rango_inicio && $now <= $rango_fin);
}

// Determinar tipo y prioridad
$tipo = 'normal';
$prioridad = 0;

if ($cita) {
    $start = new DateTime($cita['start_datetime'], $serverTz);
    if (estaEnRango($start, $now)) {
        $tipo = 'prioritario';
        $prioridad = 2;
    }
}

// Generar turno consecutivo según tipo
$hoy = date('Y-m-d');
$turnoGenerado = '';

$pdo->beginTransaction();
try {
    if ($tipo === 'especial') {
        $prioridad = 3;
        $letra = 'M';
        $stmtLast = $pdo->prepare("SELECT turno FROM turnos WHERE tipo='especial' AND DATE(fechaRegistro)=:hoy ORDER BY id DESC LIMIT 1");
        $stmtLast->execute([':hoy' => $hoy]);
        $last = $stmtLast->fetch();
        $numero = ($last) ? intval(substr($last['turno'],1))+1 : 1;
        $turnoGenerado = $letra . str_pad($numero,3,'0',STR_PAD_LEFT);

    } elseif ($tipo === 'prioritario') {
        $prioridad = 2;
        $letra = 'S';
        $stmtLast = $pdo->prepare("SELECT turno FROM turnos WHERE tipo='prioritario' AND DATE(fechaRegistro)=:hoy ORDER BY id DESC LIMIT 1");
        $stmtLast->execute([':hoy' => $hoy]);
        $last = $stmtLast->fetch();
        $numero = ($last) ? intval(substr($last['turno'],1))+1 : 1;
        $turnoGenerado = $letra . str_pad($numero,3,'0',STR_PAD_LEFT);

    } else { // normal
        $prioridad = 0;
        $stmtLast = $pdo->prepare("SELECT turno FROM turnos WHERE tipo='normal' AND DATE(fechaRegistro)=:hoy ORDER BY id DESC LIMIT 1");
        $stmtLast->execute([':hoy' => $hoy]);
        $last = $stmtLast->fetch();
        $numero = ($last) ? intval($last['turno'])+1 : 1;
        $turnoGenerado = str_pad($numero,3,'0',STR_PAD_LEFT);
    }

    // Insertar turno
    $idUsuario = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;
    $sqlInsert = "INSERT INTO turnos (turno, tipo, atendido, fechaRegistro, prioridad, idUsuario)
                  VALUES (:turno, :tipo, 0, NOW(), :prioridad, :idUsuario)";
    $stmtIns = $pdo->prepare($sqlInsert);
    $stmtIns->bindValue(':turno', $turnoGenerado);
    $stmtIns->bindValue(':tipo', $tipo);
    $stmtIns->bindValue(':prioridad', $prioridad, PDO::PARAM_INT);
    if ($idUsuario !== null) {
        $stmtIns->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
    } else {
        $stmtIns->bindValue(':idUsuario', null, PDO::PARAM_NULL);
    }
    $stmtIns->execute();

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'tipo' => $tipo,
        'turno' => $turnoGenerado,
        'prioridad' => $prioridad
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Error al insertar turno: '.$e->getMessage()]);
    exit;
}