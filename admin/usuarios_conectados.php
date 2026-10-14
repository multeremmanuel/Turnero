<?php
date_default_timezone_set('America/Mexico_City');
session_start();

require '../conexion.php';

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Definir tiempo de inactividad máximo (15 minutos)
$tiempo_inactividad = 15; // minutos
$limite_tiempo = date('Y-m-d H:i:s', strtotime("-$tiempo_inactividad minutes"));

// Actualizar usuarios que llevan más de X minutos sin actividad
$stmt = $pdo->prepare("UPDATE usuarios SET login = 0, login_fecha = NULL 
                       WHERE login = 1 AND login_fecha < :limite");
$stmt->execute([':limite' => $limite_tiempo]);

// Obtener usuarios conectados (solo los activos en los últimos 15 minutos)
$stmt = $pdo->query("SELECT nombre, usuario, login_fecha 
                     FROM usuarios 
                     WHERE login = 1 
                     ORDER BY login_fecha DESC");
$usuarios_conectados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular tiempo de conexión para cada usuario
foreach ($usuarios_conectados as &$usuario) {
    $login_time = strtotime($usuario['login_fecha']);
    $current_time = time();
    $diff_minutes = round(($current_time - $login_time) / 60);
    
    if ($diff_minutes < 1) {
        $usuario['tiempo'] = 'Ahora';
    } elseif ($diff_minutes < 60) {
        $usuario['tiempo'] = "Hace {$diff_minutes} min";
    } else {
        $horas = floor($diff_minutes / 60);
        $minutos = $diff_minutes % 60;
        $usuario['tiempo'] = "Hace {$horas}h {$minutos}min";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Usuarios Conectados</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    body {
        background-color: #f4f6f8;
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    .main-content { margin-left: 240px; padding: 22px; }
    .header { background: #fff; padding: 12px 16px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.06); margin-bottom: 18px; }
    .header h3 { margin: 0; font-size: 1.15rem; color: #333; }
    
    .container-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    .card-user {
        position: relative;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        text-align: center;
        transition: transform 0.2s ease;
        aspect-ratio: 1 / 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 15px;
        border: 2px solid #28a745;
    }
    .card-user:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .dot-online {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 14px;
        height: 14px;
        background: #28a745;
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(40,167,69,0.6);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
    .user-avatar {
        font-size: 2.5rem;
        margin-bottom: 5px;
        color: #005BAC;
    }
    .user-name {
        font-weight: 600;
        font-size: 0.85rem;
        color: #333;
        margin-bottom: 2px;
    }
    .user-username {
        font-size: 0.7rem;
        color: #666;
        margin-bottom: 5px;
    }
    .user-time {
        font-size: 0.7rem;
        color: #888;
        background: #f8f9fa;
        padding: 2px 10px;
        border-radius: 12px;
    }
    .no-users {
        grid-column: 1 / -1;
        text-align: center;
        padding: 40px;
        color: #6c757d;
    }
    .no-users i {
        font-size: 3rem;
        display: block;
        margin-bottom: 15px;
        color: #dee2e6;
    }
    .total-conectados {
        background: #28a745;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    .info-inactividad {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 15px;
        font-size: 0.9rem;
        color: #856404;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .btn-desconectar {
        position: absolute;
        bottom: 8px;
        right: 8px;
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        font-size: 12px;
        line-height: 24px;
        text-align: center;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
        padding: 0;
    }
    .btn-desconectar:hover {
        opacity: 1;
    }
</style>
</head>
<body>

<?php include 'menu_admin.php'; ?>

<div class="main-content">
    <div class="header">
        <h3><i class="bi bi-people-fill"></i> Usuarios Conectados</h3>
        <div>
            <span class="total-conectados">
                <i class="bi bi-person-check"></i> <?= count($usuarios_conectados) ?> conectados
            </span>
            <span style="margin-left: 15px;">
                Usuario: <strong><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></strong>
            </span>
        </div>
    </div>

    <div class="info-inactividad">
        <i class="bi bi-info-circle" style="font-size: 1.2rem;"></i>
        <div>
            <strong>Nota:</strong> Los usuarios se consideran desconectados automáticamente después de 
            <strong>15 minutos</strong> de inactividad. La página se actualiza cada 10 segundos.
        </div>
    </div>

    <div class="container-cards">
        <?php if (count($usuarios_conectados) > 0): ?>
            <?php foreach ($usuarios_conectados as $usuario): ?>
                <div class="card-user" id="user-<?= htmlspecialchars($usuario['usuario']) ?>">
                    <div class="dot-online"></div>
                    <div class="user-avatar">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <div class="user-name"><?= htmlspecialchars($usuario['nombre']) ?></div>
                    <div class="user-username">@<?= htmlspecialchars($usuario['usuario']) ?></div>
                    <div class="user-time">
                        <i class="bi bi-clock"></i> <?= $usuario['tiempo'] ?>
                    </div>
                    <?php if ($usuario['usuario'] != $_SESSION['usuario'] ?? 'admin'): ?>
                        <form method="post" style="position: absolute; bottom: 8px; right: 8px; margin: 0;">
                            <input type="hidden" name="desconectar_usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>">
                            <button type="submit" class="btn-desconectar" title="Desconectar usuario">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-users">
                <i class="bi bi-person-x"></i>
                <p>No hay usuarios conectados actualmente.</p>
                <small class="text-muted">Los usuarios se desconectan automáticamente después de 15 minutos de inactividad.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Script para auto-refresh cada 10 segundos -->
<script>
    setTimeout(function() {
        location.reload();
    }, 10000);
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>