<?php
// Duración de la sesión: ~2.7 horas (10000 segundos)
date_default_timezone_set('America/Mexico_City');

ini_set('session.gc_maxlifetime', 10000);
session_set_cookie_params(10000);
session_start();

require 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];

    // Validar que los campos no estén vacíos
    if (empty($usuario) || empty($password)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        // Buscar usuario por nombre de usuario Y que esté activo (status = 1)
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :usuario AND status = 1 LIMIT 1");
        $stmt->execute([':usuario' => $usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Verificar que el usuario esté activo (doble verificación)
            if ($user['status'] != 1) {
                $error = "Tu cuenta está desactivada. Contacta al administrador.";
            } else {
                // Actualizar login = 1 y login_fecha = NOW()
                $stmtLogin = $pdo->prepare("UPDATE usuarios SET login = 1, login_fecha = NOW() WHERE id = :id");
                $stmtLogin->execute([':id' => $user['id']]);

                // Guardar datos en sesión
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['usuario'] = $user['usuario']; // Guardar también el nombre de usuario
                $_SESSION['rol'] = $user['rol'];

                // Redirigir según el rol
                if ($user['rol'] === 'admin') {
                    header('Location: admin/control_panel.php');
                } elseif ($user['rol'] === 'operador') {
                    header('Location: seleccionar_caja.php');
                } elseif ($user['rol'] === 'despachador') {
                    header('Location: registrar_turno.php');
                } else {
                    $error = "Rol de usuario no válido";
                    session_destroy();
                }
                exit;
            }
        } else {
            $error = "Usuario o contraseña incorrectos, o cuenta desactivada.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login - Sistema de Turnos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
body {
    background-color: #f4f6f8;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

.login-container {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #f4f6f8 0%, #e9ecef 100%);
}

.card-login {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    max-width: 420px;
    width: 100%;
    overflow: hidden;
}

.card-login .card-header {
    background: linear-gradient(135deg, #005BAC 0%, #004a91 100%);
    color: #fff;
    text-align: center;
    padding: 25px 20px;
    border-bottom: none;
}

.card-login .card-header h3 {
    margin: 0;
    font-weight: 600;
    font-size: 1.5rem;
}

.card-login .card-header p {
    margin: 5px 0 0;
    opacity: 0.8;
    font-size: 0.9rem;
}

.card-body {
    padding: 30px 25px;
}

.form-label {
    font-weight: 600;
    color: #333;
    font-size: 0.9rem;
}

.form-control {
    border-radius: 8px;
    padding: 10px 14px;
    border: 1.5px solid #dee2e6;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus {
    border-color: #005BAC;
    box-shadow: 0 0 0 3px rgba(0, 91, 172, 0.15);
}

.btn-login {
    background: linear-gradient(135deg, #005BAC 0%, #004a91 100%);
    color: #fff;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    padding: 12px 16px;
    width: 100%;
    transition: all 0.3s ease;
    cursor: pointer;
    font-size: 1rem;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 91, 172, 0.3);
}

.btn-login:active {
    transform: translateY(0);
}

.alert-custom {
    margin-bottom: 20px;
    border-radius: 8px;
    padding: 12px 16px;
}

.alert-custom i {
    margin-right: 8px;
}

.footer-login {
    text-align: center;
    margin-top: 20px;
    font-size: 0.8rem;
    color: #6c757d;
}

.footer-login a {
    color: #005BAC;
    text-decoration: none;
}

.footer-login a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<div class="login-container">
    <div class="card card-login">
        <div class="card-header">
            <h3><i class="bi bi-clock-history"></i> Sistema de Turnos</h3>
            <p>Inicia sesión para continuar</p>
        </div>
        <div class="card-body">
            <?php if($error): ?>
                <div class="alert alert-danger alert-custom">
                    <i class="bi bi-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="usuario" class="form-label">
                        <i class="bi bi-person"></i> Usuario
                    </label>
                    <input type="text" class="form-control" name="usuario" id="usuario" 
                           placeholder="Ingresa tu usuario" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i> Contraseña
                    </label>
                    <input type="password" class="form-control" name="password" id="password" 
                           placeholder="Ingresa tu contraseña" required>
                </div>
                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Entrar
                </button>
            </form>
            
            <div class="footer-login">
                <p>Sistema de Gestión de Turnos v2.0</p>
            </div>
        </div>
    </div>
</div>

<!-- Script para mantener la sesión activa -->
<script>
    // Si el usuario presiona Enter, se envía el formulario automáticamente
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            document.querySelector('form').submit();
        }
    });
</script>

</body>
</html>