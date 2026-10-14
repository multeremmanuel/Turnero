<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nombre']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require '../conexion.php';
require_once('tcpdf/tcpdf.php');

$tipo = $_GET['tipo'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

if (!$tipo || !$fecha_inicio || !$fecha_fin) {
    die('Faltan parámetros para generar el reporte.');
}

// Crear PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Turnero');
$pdf->SetMargins(15, 30, 15); // margen superior más grande para la cabecera
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// === Cabecera con logo ===
$logo = 'logo2025.png'; // Asegúrate de tener el archivo en la misma carpeta
$pdf->Image($logo, 15, 10, 30); // x=15mm, y=10mm, ancho=30mm
$pdf->SetY(15); // bajar debajo del logo

// === Obtener datos según tipo ===
$data = [];
$columnas = [];
$titulo = '';

try {
    if ($tipo == 'usuario') {
        $stmt = $pdo->prepare("
            SELECT u.usuario, COUNT(t.id) AS total
            FROM usuarios u
            LEFT JOIN turnos t ON u.id = t.idUsuario AND DATE(t.fechaAtencion) BETWEEN :fi AND :ff
            GROUP BY u.id, u.usuario
            ORDER BY total DESC
        ");
        $stmt->execute(['fi' => $fecha_inicio, 'ff' => $fecha_fin]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $titulo = "Reporte de Usuario del $fecha_inicio al $fecha_fin";
        $columnas = ['Usuario', 'Total Turnos'];
    } elseif ($tipo == 'caja') {
        $stmt = $pdo->prepare("
            SELECT c.nombre, COUNT(t.id) AS total
            FROM cajas c
            LEFT JOIN turnos t ON c.id = t.idCaja AND DATE(t.fechaAtencion) BETWEEN :fi AND :ff
            GROUP BY c.id, c.nombre
            ORDER BY total DESC
        ");
        $stmt->execute(['fi' => $fecha_inicio, 'ff' => $fecha_fin]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $titulo = "Reporte de Caja del $fecha_inicio al $fecha_fin";
        $columnas = ['Caja', 'Total Turnos'];
    } elseif ($tipo == 'dia') {
        $stmt = $pdo->prepare("
            SELECT DATE(t.fechaAtencion) AS dia, COUNT(t.id) AS total
            FROM turnos t
            WHERE DATE(t.fechaAtencion) BETWEEN :fi AND :ff
            GROUP BY dia
            ORDER BY dia ASC
        ");
        $stmt->execute(['fi' => $fecha_inicio, 'ff' => $fecha_fin]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $titulo = "Reporte por Día del $fecha_inicio al $fecha_fin";
        $columnas = ['Día', 'Total Turnos'];
    } else {
        die('Tipo de reporte no válido.');
    }
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}

// === Título centrado ===
$pdf->SetFont('', 'B', 14);
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(10);
$pdf->Cell(0, 10, $titulo, 0, 1, 'C');

// === Tabla centrada ===
$w_total = 120; // ancho total de la tabla centrada
$w = [$w_total*0.6, $w_total*0.4]; // 2 columnas proporcionadas

$pdf->SetFont('', 'B', 11);
$pdf->SetFillColor(0, 70, 127);
$pdf->SetTextColor(255, 255, 255);

// Encabezado
$x = ($pdf->getPageWidth() - $w_total) / 2; // posición x para centrar
$pdf->SetX($x);
for ($i = 0; $i < count($columnas); $i++) {
    $pdf->Cell($w[$i], 8, $columnas[$i], 1, 0, 'C', 1);
}
$pdf->Ln();

// Cuerpo
$pdf->SetFont('', '', 10);
$pdf->SetTextColor(0, 0, 0);
$fill = false;
$max_valor = 0;
foreach ($data as $row) { $max_valor = max($max_valor, $row['total']); }
foreach ($data as $row) {
    $pdf->SetX($x);
    if ($row['total'] == $max_valor && $max_valor > 0) {
        $pdf->SetFillColor(242, 139, 130);
        $resaltar = true;
    } else {
        $pdf->SetFillColor($fill ? 240 : 255);
        $resaltar = false;
    }

    $pdf->Cell($w[0], 7, $row[array_keys($row)[0]], 1, 0, 'C', true);
    if ($resaltar) $pdf->SetFont('', 'B');
    $pdf->Cell($w[1], 7, $row['total'], 1, 0, 'C', true);
    if ($resaltar) $pdf->SetFont('', '');
    $pdf->Ln();
    $fill = !$fill;
}

// === Pie de página con usuario que imprimió ===
$pdf->Ln(5);
$usuario = $_SESSION['usuario_nombre'] ?? 'Usuario desconocido';
$pdf->SetFont('', 'I', 9);
$pdf->Cell(0, 5, "Reporte generado por: $usuario | Fecha: " . date('Y-m-d H:i:s'), 0, 1, 'C');

// Salida PDF
$pdf->Output("reporte_$tipo.pdf", 'I');
