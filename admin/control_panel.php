<?php
session_start();
require '../conexion.php';

// Solo admin puede acceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

// Obtener todos los usuarios
$stmt = $pdo->query("SELECT id, nombre, usuario, rol, fecha_alta FROM usuarios ORDER BY id ASC");
$usuarios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Panel de Control - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f6f8;
}

/* Ajuste para el contenido desplazado por el sidebar */
.main-content {
    margin-left: 240px; /* Debe coincidir con el ancho de menu_admin.php */
    padding: 30px;
}

/* Barra superior */
.navbar-custom {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    padding: 0 30px;
    background-color: #fff;
    height: 60px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.navbar-custom span {
    margin-right: 20px;
    font-size: 0.9rem;
    color: #333;
}

/* Tabla */
.table-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

table thead {
    background-color: #2c2c2c;
    color: #fff;
    font-weight: 600;
}

table tbody tr:hover {
    background-color: #f0f4f8;
}

h4 {
    margin-bottom: 15px;
    color: #333;
    font-weight: 600;
}
</style>
</head>
<body>

<?php include 'menu_admin.php'; ?>

<div class="main-content">
    <div class="navbar-custom">
        <span>Usuario: <?= htmlspecialchars($_SESSION['usuario_nombre']); ?></span>
        <a href="../logout.php" class="btn btn-outline-primary btn-sm">Cerrar Sesión</a>
    </div>

    <h4>Usuarios Registrados</h4>
    <div class="table-container">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Fecha de Alta</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($usuarios as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']); ?></td>
                        <td><?= htmlspecialchars($user['nombre']); ?></td>
                        <td><?= htmlspecialchars($user['usuario']); ?></td>
                        <td><?= ucfirst(htmlspecialchars($user['rol'])); ?></td>
                        <td><?= htmlspecialchars($user['fecha_alta']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
