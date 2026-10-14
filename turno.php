<?php
session_start();
require 'conexion.php';

// FORZAR ZONA HORARIA A CIUDAD DE MEXICO
date_default_timezone_set('America/Mexico_City');

// -------------------- CONFIGURACIÓN DE TIPOS DE TURNO --------------------
// Aquí cambias la letra cuando te lo pidan (M, CJ, JLM, etc.)
$config = [
    'especial'    => ['letra' => 'CJ',  'prioridad' => 3],
    'prioritario' => ['letra' => 'S', 'prioridad' => 2],
    'normal'      => ['letra' => '',   'prioridad' => 0],
];

// -------------------- VALIDAR TIPO --------------------
if (!isset($_REQUEST['tipo']) || empty($_REQUEST['tipo'])) {
    die("❌ Tipo de turno no especificado.");
}

$tipo = $_REQUEST['tipo'];

if (!isset($config[$tipo])) {
    die("❌ Tipo de turno inválido.");
}

$letra     = $config[$tipo]['letra'];
$prioridad = $config[$tipo]['prioridad'];

// -------------------- GENERAR TURNO --------------------
$hoy = date('Y-m-d');

$stmt = $pdo->prepare("SELECT turno FROM turnos 
                       WHERE tipo = :tipo 
                         AND DATE(fechaRegistro) = :hoy
                       ORDER BY id DESC LIMIT 1");
$stmt->execute([':tipo' => $tipo, ':hoy' => $hoy]);
$ultimo = $stmt->fetch();

// Extraer solo los dígitos del final, sin importar cuántas letras tenga
$numero = 1;
if ($ultimo && !empty($ultimo['turno'])) {
    if (preg_match('/(\d+)$/', $ultimo['turno'], $m)) {
        $numero = intval($m[1]) + 1;
    }
}

$turnoFormateado = $letra . str_pad($numero, 3, '0', STR_PAD_LEFT);

// Insertar turno
$sql = "INSERT INTO turnos (turno, tipo, atendido, fechaRegistro, prioridad) 
        VALUES (:turno, :tipo, 0, NOW(), :prioridad)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':turno'     => $turnoFormateado,
    ':tipo'      => $tipo,
    ':prioridad' => $prioridad
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Turno Generado</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color:#f8f9fa; }
.container { max-width:600px; margin-top:50px; }
.card-header { font-size:2rem; padding:1.5rem; }
.card-body { font-size:1.5rem; padding:2rem; }
.btn { font-size:1.5rem; padding:1rem; width:100%; }
</style>
</head>
<body>
<div class="container">
<div class="card shadow text-center">
<div class="card-header text-white" style="background-color:#28a745;">✅ Turno Generado</div>
<div class="card-body">
<p>Su turno es:</p>
<h1><strong><?= htmlspecialchars($turnoFormateado) ?></strong></h1>
<p>Tipo de turno: <strong><?= ucfirst($tipo) ?></strong></p>
<p><?= date("d/m/Y H:i:s") ?></p>
<div class="d-grid gap-3 mt-4">
<button onclick="imprimir()" class="btn btn-primary">🖨️ Imprimir Turno</button>
<a href="registrar_turno.php" class="btn btn-success">➕ Generar Nuevo Turno</a>
</div>
</div>
</div>
</div>

<script>
function imprimir() {
    let ESC = "\x1B";
    let ticket = "";

    // Centrar texto
    ticket += ESC + "\x61" + "\x01"; // centrar
    ticket += ESC + "!" + "\x38";    // doble altura + doble ancho
    ticket += " SU TURNO: <?= $turnoFormateado ?>\n\n";

    // Texto un poco más pequeño
    ticket += ESC + "!" + "\x22";    // tamaño medio
    ticket += "TURNERO\n\n";

    // Lema de la administración
    ticket += ESC + "!" + "\x00";    // tamaño normal
    ticket += "POR LA CIUDAD QUE MERECEMOS\n\n";

    // Fecha y hora actual
    ticket += "<?= date('d/m/Y H:i:s') ?>\n\n";

    // Volver a alineación izquierda
    ticket += ESC + "\x61" + "\x00";

    // Saltos de línea extra al final
    ticket += "\n\n";

    // Escapar caracteres para RawBT
    let urlTicket = encodeURIComponent(ticket);

    // Enviar a RawBT
    window.location.href = "rawbt:" + urlTicket;
}
</script>
</body>
</html>