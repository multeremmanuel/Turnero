<?php
session_start();
require '../conexion.php';

// Solo admin puede acceder
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Atención en Tiempo Real</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    body { background-color: #f5f5f5; }
    .container { margin-top: 30px; }
    table { font-size: 18px; }
    th { background-color: #007bff; color: white; }
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

<div class="container">
    <h3>Atención en Tiempo Real</h3>
    <div id="tablaAtencion">
        <!-- Tabla se actualizará vía AJAX -->
    </div>
</div>

<script>
function actualizarAtencion() {
    $.ajax({
        url: 'get_turnos_por_usuario.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            let html = '<table class="table table-striped table-bordered"><thead><tr><th>Usuario</th><th>Caja</th><th>Turnos Atendidos</th></tr></thead><tbody>';
            data.forEach(u => {
                html += `<tr>
                    <td>${u.usuario}</td>
                    <td>${u.caja ? u.caja : '--'}</td>
                    <td>${u.turnos_atendidos}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            $('#tablaAtencion').html(html);
        },
        error: function(xhr, status, error) {
            $('#tablaAtencion').html('<div class="alert alert-danger">Error al cargar los datos.</div>');
            console.error(error);
        }
    });
}

// Inicializar y actualizar cada 3 segundos
actualizarAtencion();
setInterval(actualizarAtencion, 3000);
</script>

</body>
</html>
