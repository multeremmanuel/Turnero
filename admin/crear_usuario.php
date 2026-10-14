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
    $nombre = strtoupper($_POST['nombre']);
    $usuario = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, usuario, password, rol, fecha_alta) VALUES (:nombre, :usuario, :password, :rol, NOW())");
    $stmt->execute([
        ':nombre' => $nombre,
        ':usuario' => $usuario,
        ':password' => $password,
        ':rol' => $rol
    ]);

    $mensaje = "Usuario '$usuario' creado exitosamente.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Usuario - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background-color: #f4f6f8;
}

.main-content {
    margin-left: 240px; /* Debe coincidir con el ancho de menu_admin.php */
    padding: 22px;
}

.header {
    background: #ffffff;
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 18px;
}

.header h3 { margin: 0; font-size: 1.15rem; color: #333; }

.form-card {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    max-width: 600px;
}

.form-label {
    font-weight: 600;
    color: #333;
}

.form-control, .form-select {
    border-radius: 8px;
    padding: 10px 12px;
}

.btn-success {
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 600;
}

.alert-custom {
    margin-bottom: 18px;
}

#nombre { text-transform: uppercase; }
</style>
</head>
<body>

<?php include 'menu_admin.php'; ?>

<div class="main-content">
    <!-- Header como en cambiar_rol.php -->
    <div class="header">
        <h3>Crear Nuevo Usuario</h3>
        <div>Usuario conectado: <strong><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></div>
    </div>

    <div class="form-card">
        <?php if($mensaje): ?>
            <div class="alert alert-success alert-custom"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <form method="post" class="mt-3">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="">-- Selecciona un rol --</option>
                    <option value="admin">Admin</option>
                    <option value="operador">Operador</option>
                    <option value="despachador">Despachador</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success">Crear Usuario</button>
        </form>
    </div>
</div>

<script>
const nombreInput = document.getElementById('nombre');
nombreInput.addEventListener('input', () => {
    nombreInput.value = nombreInput.value.toUpperCase();
});
</script>

</body>
</html>
