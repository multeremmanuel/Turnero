<?php
session_start();
require 'conexion.php';

// Validar que sea operador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'operador') {
    header('Location: login.php');
    exit;
}

// Validar que el operador tenga asignada una caja
if (!isset($_SESSION['idCaja'])) {
    header('Location: seleccionar_caja.php');
    exit;
}

$idCaja = $_SESSION['idCaja'];
$idUsuario = $_SESSION['usuario_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Llamar Turno - Operador</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
body {
    background-color: #f4f6f8;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

.navbar-custom {
    background-color: #005BAC; /* Azul PAN */
}

.card-turno, .card-ultimo-turno {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    padding: 20px;
    margin-bottom: 20px;
}

.especial { color: #0d6efd; font-weight: bold; } /* Azul PAN */
.prioritario { color: #fd7e14; font-weight: bold; } /* Naranja */
.normal { color: #198754; font-weight: bold; } /* Verde */

.btn-llamar {
    background-color: #005BAC;
    color: #fff;
    font-weight: 600;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    transition: background-color 0.2s ease, transform 0.15s ease;
    cursor: pointer;
}

.btn-llamar:hover {
    background-color: #004a91;
    transform: translateY(-1px);
}

h3 {
    margin-bottom: 25px;
}

#ultimo-turno {
    font-size: 1.1rem;
    font-weight: 500;
}

@media (max-width: 880px) {
    .card-turno, .card-ultimo-turno {
        padding: 15px;
    }
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom navbar-dark">
<div class="container-fluid">
    <a class="navbar-brand" href="#">Panel de Control</a>
    <div class="d-flex">
        <span class="navbar-text me-3">
            <?= $_SESSION['usuario_nombre']; ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
    </div>
</div>
</nav>

<div class="container mt-5 text-center">
    <h3>Llamar Siguiente Turno</h3>

    <div id="turno-container" class="mt-4">
        <!-- Aquí se mostrará el turno disponible vía AJAX -->
    </div>

    <!-- Sección del último turno atendido -->
    <div id="ultimo-turno-container" class="mt-5">
        <!-- Se cargará vía AJAX -->
    </div>
</div>

<script>
function cargarTurno() {
    $.ajax({
        url: 'get_siguiente_turno.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if(data.turno) {
                let tipoClase = '';
                let textoTipo = '';

                switch(data.prioridad) {
                    case 3:
                        tipoClase = 'especial';
                        textoTipo = 'Especial';
                        break;
                    case 2:
                        tipoClase = 'prioritario';
                        textoTipo = 'Prioritario';
                        break;
                    default:
                        tipoClase = 'normal';
                        textoTipo = 'Normal';
                }

                let html = `
                    <div class="card-turno">
                        <h1 class="${tipoClase}">Turno: ${data.turno}</h1>
                        <p>Tipo: ${textoTipo}</p>
                        <button id="llamar-btn" class="btn-llamar mt-3">Llamar este turno</button>
                    </div>
                `;
                $('#turno-container').html(html);

                $('#llamar-btn').click(function() {
                    $.ajax({
                        url: 'llamar_turno_ajax.php',
                        method: 'POST',
                        data: { idTurno: data.id },
                        success: function(resp) {
                            alert(resp);
                            cargarTurno();       // Recargar siguiente turno
                            cargarUltimoTurno(); // Actualizar el último turno atendido
                        }
                    });
                });
            } else {
                $('#turno-container').html('<div class="alert alert-secondary">No hay turnos disponibles.</div>');
            }
        }
    });
}

function cargarUltimoTurno() {
    $.ajax({
        url: 'get_ultimo_turno.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if(data.turno) {
                $('#ultimo-turno-container').html(`
                    <div class="card-ultimo-turno">
                        Último turno atendido en tu caja: <span class="text-primary">${data.turno}</span>
                    </div>
                `);
            } else {
                $('#ultimo-turno-container').html(`
                    <div class="card-ultimo-turno">
                        Aún no has atendido ningún turno.
                    </div>
                `);
            }
        }
    });
}

// Cargar ambos al inicio
cargarTurno();
cargarUltimoTurno();

// Refrescar turno disponible cada 3s
setInterval(cargarTurno, 3000);
// Refrescar último turno atendido cada 5s
setInterval(cargarUltimoTurno, 5000);
</script>
</body>
</html>
