<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require 'conexion.php';

// Solo operadores pueden acceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'operador') {
    header('Location: ../login.php');
    exit;
}

// Procesar selección de caja
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCajaSeleccionada = $_POST['caja'];

    // Guardar en sesión
    $_SESSION['idCaja'] = $idCajaSeleccionada;

    // Redirigir a llamar_turno.php
    header('Location: llamar_turno.php');
    exit;
}

// Obtener cajas libres (idUsuario = 0)
$stmt = $pdo->query("SELECT id, nombre FROM cajas WHERE idUsuario = 0 ORDER BY id ASC");
$cajasLibres = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar Caja - Operador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Selecciona la Caja que Vas a Operar</h3>

    <?php if(empty($cajasLibres)): ?>
        <div class="alert alert-warning mt-3">
            No hay cajas libres en este momento. Por favor espera a que se desocupe alguna.
        </div>
    <?php else: ?>
        <form method="post" class="mt-3">
            <div class="mb-3">
                <label for="caja" class="form-label">Cajas Disponibles:</label>
                <select name="caja" id="caja" class="form-select" required>
                    <option value="">-- Selecciona una caja --</option>
                    <?php foreach($cajasLibres as $caja): ?>
                        <option value="<?= $caja['id'] ?>"><?= htmlspecialchars($caja['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Iniciar Turno</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
