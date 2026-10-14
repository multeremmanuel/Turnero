<?php
session_start();
require '../conexion.php';

// Solo admin puede acceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

$mensaje = '';
$selUsuario = '';
$selRol = '';

// Procesar formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $rol = $_POST['rol'] ?? '';

    if ($usuario && $rol) {
        $stmt = $pdo->prepare("UPDATE usuarios 
                               SET rol = :rol, fecha_actualizacion = NOW() 
                               WHERE usuario = :usuario");
        $stmt->execute([
            ':rol' => $rol,
            ':usuario' => $usuario
        ]);

        $mensaje = "El rol del usuario '" . htmlspecialchars($usuario) . "' ha sido cambiado a '" . htmlspecialchars($rol) . "'.";
        $selUsuario = $usuario;
        $selRol = $rol;
    } else {
        $mensaje = "Selecciona usuario y rol.";
    }
}

// Obtener todos los usuarios
$stmtUsuarios = $pdo->query("SELECT usuario, nombre, rol FROM usuarios ORDER BY nombre");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cambiar Rol - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background-color: #f4f6f8;
}

.main-content {
    margin-left: 240px;
    padding: 22px;
}

/* Header */
.header {
    background: #ffffff;
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.header h3 { margin: 0; font-size: 1.15rem; color: #333; }

/* Card del formulario */
.form-card {
    margin-top: 18px;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    max-width: 760px;
}

.form-label {
    font-size: 0.92rem;
    font-weight: 600;
    color: #333;
}

.form-select, .form-control {
    border-radius: 8px;
    padding: 10px 12px;
}

/* Botón Cambiar Rol azul PAN */
button.btn.btn-change {
    background-color: #005BAC !important; /* azul PAN */
    color: #fff !important;               /* texto blanco */
    font-weight: 600;
    border: none !important;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 0.95rem;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.15s ease;
}

button.btn.btn-change:hover {
    background-color: #004a91 !important; /* azul más oscuro al pasar mouse */
    transform: translateY(-1px);
}

.alert-custom {
    margin-top: 12px;
}

.current-role {
    margin-top: 8px;
    font-size: 0.9rem;
    color: #555;
    display: inline-block;
}

@media (max-width: 880px) {
    .main-content { margin-left: 0; padding: 14px; }
    .form-card { max-width: 100%; }
}
</style>
</head>
<body>

<?php include 'menu_admin.php'; ?>

<div class="main-content">
    <div class="header">
        <h3>Cambiar Rol de Usuario</h3>
        <div>Usuario conectado: <strong><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></div>
    </div>

    <div class="form-card">
        <?php if ($mensaje): ?>
            <div class="alert alert-success alert-custom" role="alert">
                <?= htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="mt-3">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <select name="usuario" id="usuario" class="form-select" required>
                    <option value="">-- Selecciona un usuario --</option>
                    <?php foreach($usuarios as $u): 
                        $usuario_val = $u['usuario'];
                        $nombre = $u['nombre'];
                        $rol_actual = $u['rol'];
                        $selected = ($selUsuario && $selUsuario === $usuario_val) ? 'selected' : '';
                    ?>
                        <option value="<?= htmlspecialchars($usuario_val) ?>" data-role="<?= htmlspecialchars($rol_actual) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($nombre) ?> (<?= htmlspecialchars($usuario_val) ?>) - Rol actual: <?= htmlspecialchars($rol_actual) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="current-role" id="current-role">
                    <?php if ($selUsuario): 
                        foreach ($usuarios as $uu) {
                            if ($uu['usuario'] === $selUsuario) {
                                echo 'Rol seleccionado: <strong>' . htmlspecialchars($uu['rol']) . '</strong>';
                                break;
                            }
                        }
                    endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="rol" class="form-label">Nuevo Rol</label>
                <select name="rol" id="rol" class="form-select" required>
                    <option value="">-- Selecciona un rol --</option>
                    <option value="admin" <?= $selRol === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="despachador" <?= $selRol === 'despachador' ? 'selected' : '' ?>>Despachador</option>
                    <option value="operador" <?= $selRol === 'operador' ? 'selected' : '' ?>>Operador</option>
                </select>
            </div>

            <button type="submit" class="btn btn-change">Cambiar Rol</button>
        </form>
    </div>
</div>

<script>
document.getElementById('usuario').addEventListener('change', function() {
    const sel = this.options[this.selectedIndex];
    const role = sel.getAttribute('data-role') || 'N/D';
    document.getElementById('current-role').innerHTML = 'Rol actual: <strong>' + role + '</strong>';
});
</script>

</body>
</html>
