<?php
session_start();
require '../conexion.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Lista de Turnos - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    .prioritario { color: red; font-weight: bold; }
    .normal { color: black; font-weight: bold; }
    table { margin-top: 20px; }
    .badge-estado { font-size: 0.9rem; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Panel de Control</a>
        <div class="d-flex">
            <span class="navbar-text text-white me-3"><?= $_SESSION['usuario_nombre']; ?></span>
            <a href="../logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <h2 class="text-center mb-4">Turnos en Atención - Tiempo Real</h2>
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>Turno</th>
                <th>Tipo</th>
                <th>Caja</th>
                <th>Hora de Llamado</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody id="turnos-en-curso">
            <!-- Se actualizará con AJAX -->
        </tbody>
    </table>

    <nav>
        <ul class="pagination justify-content-center" id="paginacion"></ul>
    </nav>
</div>

<script>
let paginaActual = 1;

function actualizarTurnos(page = 1) {
    $.ajax({
        url: 'get_turnos_admin.php',
        method: 'GET',
        dataType: 'json',
        data: { page: page },
        success: function(turnos) {
            let html = '';
            if (turnos.length > 0) {
                turnos.forEach(turno => {
                    let clase = turno.tipo === 'prioritario' ? 'prioritario' : 'normal';
                    let estadoBadge = '';
                    if(turno.estado === 'cola') estadoBadge = '<span class="badge bg-warning text-dark badge-estado">En Cola</span>';
                    if(turno.estado === 'atendiendo') estadoBadge = '<span class="badge bg-info badge-estado">Atendiendo</span>';
                    if(turno.estado === 'atendido') estadoBadge = '<span class="badge bg-success badge-estado">Atendido</span>';

                    html += `
                        <tr class="${clase}">
                            <td>${turno.turno}</td>
                            <td>${turno.tipo}</td>
                            <td>${turno.cajaNombre ?? '—'}</td>
                            <td>${turno.fechaAtencion ?? turno.fechaRegistro}</td>
                            <td>${estadoBadge}</td>
                        </tr>
                    `;
                });
            } else {
                html = `<tr><td colspan="5" class="text-center">No hay turnos registrados</td></tr>`;
            }
            $('#turnos-en-curso').html(html);
        }
    });
}

// Inicializar
actualizarTurnos();
setInterval(() => actualizarTurnos(paginaActual), 3000);
</script>
</body>
</html>
