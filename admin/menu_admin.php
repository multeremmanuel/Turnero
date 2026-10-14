<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
?>

<!-- === Estilos globales de menú === -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background-color: #f4f6f8;
}

/* Sidebar */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 240px;
    height: 100vh;
    background-color: #2f353a;
    display: flex;
    flex-direction: column;
    box-shadow: 3px 0 15px rgba(0,0,0,0.25);
    z-index: 100;
}

.sidebar-header {
    text-align: center;
    padding: 20px;
    background: #272b2f;
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    border-bottom: 1px solid rgba(255,255,255,0.08);
}

.menu {
    flex: 1;
    padding-top: 10px;
}

.menu a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    margin: 6px 10px;
    font-size: 0.88rem;
    color: #cfd3d7;
    border-radius: 6px;
    text-decoration: none;
    transition: all .15s ease-in-out;
}

.menu a:hover { background-color: #3a4045; color: #fff; transform: translateX(2px); }
.menu a.active { background-color: #005BAC; color: #fff; box-shadow: 0 0 10px rgba(0,91,172,0.45); }

/* Contenido principal */
.main-content {
    margin-left: 240px;
    padding: 22px;
}

/* Header simple */
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

/* Responsive */
@media (max-width: 880px) {
    .main-content { padding: 14px; }
}
</style>

<!-- === Sidebar === -->
<div class="sidebar">
    <div class="sidebar-header">Admin Panel</div>
    <div class="menu">
        <a href="control_panel.php" class="<?= basename($_SERVER['PHP_SELF']) == 'control_panel.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Panel
        </a>
        <a href="historico_turnos.php" class="<?= basename($_SERVER['PHP_SELF']) == 'historico_turnos.php' ? 'active' : '' ?>">
            <i class="bi bi-clock-history"></i> Histórico Turnos
        </a>
        <a href="crear_usuario.php" class="<?= basename($_SERVER['PHP_SELF']) == 'crear_usuario.php' ? 'active' : '' ?>">
            <i class="bi bi-person-plus-fill"></i> Crear Usuario
        </a>
        <a href="modificar_usuario.php" class="<?= basename($_SERVER['PHP_SELF']) == 'modificar_usuario.php' ? 'active' : '' ?>">
            <i class="bi bi-pencil-square"></i> Modificar Usuario
        </a>
        <a href="cambiar_rol.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cambiar_rol.php' ? 'active' : '' ?>">
            <i class="bi bi-shield-lock-fill"></i> Cambiar Rol
        </a>

        <!-- 🔹 Botón de Reportes -->
        <a href="reportes.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-fill"></i> Reportes
        </a>

        <!-- 🟢 Botón de Usuarios Conectados -->
        <a href="usuarios_conectados.php" class="<?= basename($_SERVER['PHP_SELF']) == 'usuarios_conectados.php' ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i> Usuarios Conectados
        </a>

        <a href="../logout.php">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
    </div>
</div>
