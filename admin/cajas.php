<?php
session_start();
require '../conexion.php';

// Solo admin puede acceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$mensaje = '';

// Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = strtoupper($_POST['nombre']); // Nombre en mayúsculas
    $stmt = $pdo->prepare("INSERT INTO cajas (nombre, idUsuario, fecha_de_registro) VALUES (:nombre, 0, NOW())");
    $stmt->execute([
        ':nombre' => $nombre
    ]);
    $mensaje = "Caja '$nombre' creada exitosamente.";
}

// Obtener todas las cajas
$stmt = $pdo->query("SELECT id, nombre, idUsuario, fecha_de_registro FROM cajas ORDER BY id ASC");
$cajas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Cajas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        #nombre { text-transform: uppercase; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="control_panel.php">Panel de Control</a>
        <div class="d-flex">
            <span class="navbar-text text-white me-3"><?= $_SESSION['usuario_nombre']; ?></span>
            <a href="../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h3>Gestión de Cajas</h3>

    <?php if($mensaje): ?>
        <div class="alert alert-success"><?= $mensaje ?></div>
    <?php endif; ?>

    <!-- Formulario para crear caja -->
    <form method="post" class="mb-4">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre de la Caja</label>
            <input type="text" name="nombre" id="nombre" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Crear Caja</button>
    </form>

    <!-- Tabla de cajas existentes -->
    <h4>Cajas Existentes</h4>
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>ID Usuario Asignado</th>
                <th>Fecha de Registro</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cajas as $caja): ?>
                <tr>
                    <td><?= $caja['id'] ?></td>
                    <td><?= $caja['nombre'] ?></td>
                    <td><?= $caja['idUsuario'] ?></td>
                    <td><?= $caja['fecha_de_registro'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    const nombreInput = document.getElementById('nombre');
    nombreInput.addEventListener('input', () => {
        nombreInput.value = nombreInput.value.toUpperCase();
    });
</script>
</body>
</html>
