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
<title>Registrar Turno de Cita</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    body { background-color: #f8f9fa; }
    .container { max-width: 600px; margin-top: 50px; }
    .form-label { font-size: 1.8rem; }
    .form-control { font-size: 2rem; padding: 1rem; }
    .btn-submit { font-size: 2rem; padding: 1rem; margin-top: 15px; }
    .card { font-size: 2rem; }
    .card-header { font-size: 2rem; padding: 1.5rem; }
    .card-body { font-size: 2rem; padding: 2rem; }
    #btn-nuevo-turno { font-size: 2rem; padding: 1rem; margin-top: 20px; width: 100%; }
</style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">Registrar Turno de Cita</h2>

    <form id="cita-form">
        <div class="mb-3">
            <label for="idCita" class="form-label">Ingrese ID de la Cita:</label>
            <input type="number" class="form-control" id="idCita" name="id" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-submit">Asignar Turno</button>
    </form>

    <div id="resultado" class="mt-4"></div>
</div>

<script>
$(document).ready(function(){
    $('#cita-form').submit(function(e){
        e.preventDefault();
        var idCita = $('#idCita').val();

        if(idCita === '') {
            $('#resultado').html('<div class="card shadow text-center text-danger"><div class="card-body">❌ Debes ingresar el ID de la cita.</div></div>');
            return;
        }

        $.ajax({
            url: 'procesar_cita.php',
            type: 'POST',
            data: { id: idCita },
            dataType: 'json',
            success: function(respuesta){
                if(respuesta.error){
                    $('#resultado').html('<div class="card shadow text-center text-danger"><div class="card-body">❌ ' + respuesta.error + '</div></div>');
                } else if(respuesta.success){
                    let tipoTexto = respuesta.tipo === 'prioritario' ? 'Prioritario' : (respuesta.tipo === 'especial' ? 'Especial' : 'Normal');
                    let colorHeader = '#28a745'; // Normal
                    if(respuesta.tipo === 'prioritario') colorHeader = '#ffc107';
                    if(respuesta.tipo === 'especial') colorHeader = '#007bff';

                    $('#resultado').html(
                        '<div class="card shadow text-center">' +
                        '<div class="card-header text-white" style="background-color: '+colorHeader+';">✅ Turno Asignado</div>' +
                        '<div class="card-body">' +
                        'Turno: <strong>' + respuesta.turno + '</strong><br>' +
                        'Tipo: <strong>' + tipoTexto + '</strong><br>' +
                        '<a href="registrar_turno.php" class="btn btn-success mt-3" id="btn-nuevo-turno">Registrar Nuevo Turno</a>' +
                        '</div></div>'
                    );

                    // ocultar formulario
                    $('#cita-form').hide();
                }
            },
            error: function(){
                $('#resultado').html('<div class="card shadow text-center text-danger"><div class="card-body">❌ Error al procesar la cita.</div></div>');
            }
        });
    });
});
</script>
</body>
</html>
