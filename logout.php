<?php
session_start();

// Ajusta la ruta si tu conexion.php está en otra carpeta
require 'conexion.php'; // debe dejar disponible $pdo (PDO)

if (isset($_SESSION['usuario_id'])) {
    $usuario_id = (int) $_SESSION['usuario_id'];

    try {
        // Marcar al usuario como desconectado
        $stmt = $pdo->prepare("UPDATE usuarios SET login = 0 WHERE id = :id");
        $stmt->execute([':id' => $usuario_id]);
    } catch (Exception $e) {
        // Registrar error para depuración (no mostrar al usuario)
        error_log("Logout DB error (usuario_id={$usuario_id}): " . $e->getMessage());
        // opcional: puedes mostrar un mensaje en desarrollo
        // echo "Error al actualizar login: " . $e->getMessage();
    }
}

// Limpiar variables de sesión
$_SESSION = [];

// Eliminar cookie de sesión si aplica
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al login
header('Location: login.php');
exit;
