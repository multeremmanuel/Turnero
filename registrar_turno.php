<?php
session_start();
require 'conexion.php';

// Verificar que el usuario sea despachador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'despachador') {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar Turno - Despachador</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background-color: #f4f6f8;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

.navbar-custom {
    background-color: #005BAC; /* Azul PAN */
}

.container-turnos {
    max-width: 500px;
    margin: 50px auto;
    text-align: center;
}

.btn-turno {
    font-size: 1.5rem;
    padding: 1.5rem;
    margin-bottom: 20px;
    width: 100%;
    border-radius: 10px;
    font-weight: 600;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    cursor: pointer;
    border: none;
    color: #fff;
}

.btn-turno:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

.turno-especial { background-color: #0d6efd; } /* Azul PAN */
.turno-normal { background-color: #198754; }   /* Verde */
.turno-cita { background-color: #fd7e14; }     /* Naranja */
.turno-preferente { background-color: #dc3545; } /* Rojo Prioritario */

h3 {
    margin-bottom: 30px;
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom navbar-dark">
<div class="container-fluid">
    <a class="navbar-brand" href="#">Panel de Control</a>
    <div class="d-flex">
        <span class="navbar-text me-3">
            <?= htmlspecialchars($_SESSION['usuario_nombre']); ?>
        </span>
        <a href="logout.php" class="btn btn-outline-light">Cerrar Sesión</a>
    </div>
</div>
</nav>

<div class="container-turnos">
    <h3>Registrar Turno</h3>

    <button class="btn-turno turno-especial" onclick="window.location.href='turno.php?tipo=especial'">
        Turno Especial
    </button>

    <button class="btn-turno turno-normal" onclick="window.location.href='turno.php?tipo=normal'">
        Turno Normal
    </button>

    <button class="btn-turno turno-cita" onclick="window.location.href='registrar_turno_cita.php'">
        Turno de Cita
    </button>

    <button class="btn-turno turno-preferente" onclick="window.location.href='turno.php?tipo=prioritario'">
        Turno Preferente
    </button>
</div>
</body>
</html>
