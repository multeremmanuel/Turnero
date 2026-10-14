<?php
require 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pantalla de Turnos - Tiempo Real</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
    html, body { height: 100%; margin: 0; padding: 0; background-color: #f5f5f5; }
    .container-fluid { height: 100%; }
    /* Video y turnos */
    .video-section { height: 90%; }
    .turnos-en-curso-section { height: 30%; display: flex; flex-wrap: wrap; justify-content: space-around; align-items: center; }
    .turno-card { width: 8%; min-width: 70px; text-align: center; font-weight: bold; margin: 2px; padding: 5px; border-radius: 5px; background-color: #fff; box-shadow: 0 0 3px rgba(0,0,0,0.2);}




.prioritario { color: red; }
    .normal { color: black; }
    .especial { color: blue; font-weight: bold; }
    .turno-grande { font-size: 150px; font-weight: bold; }
    .turno-mediano { font-size: 50px; font-weight: bold; }
    .caja { font-size: 25px; font-weight: bold; }
    .modulo { font-size: 50px; font-weight: bold; }
    .turno-right { height: 100%; display: flex; flex-direction: column; justify-content: flex-start; padding: 20px; }
    .siguiente-turno { margin-top: 50px; text-align: center; }
</style>
</head>
<body>

<audio id="audioTurno" src="tono.mp3" preload="auto"></audio>

<div class="container-fluid h-100">
    <div class="row h-100">
        <!-- Izquierda: Video y turnos en curso -->
        <div class="col-md-6 d-flex flex-column">
            <!-- Video -->
            <div class="video-section">
                <video width="100%" height="100%" autoplay muted loop>
                    <source src="video.mp4" type="video/mp4">
                    Tu navegador no soporta video HTML5.
                </video>
            </div>
            <!-- Turnos en curso -->
            <div class="turnos-en-curso-section bg-light mt-2">
                <div id="turnos-en-curso" class="d-flex flex-wrap justify-content-around align-items-center w-100">
                    <!-- Se actualiza vía AJAX -->
                </div>
            </div>
        </div>

        <!-- Derecha: Turno actual y siguiente -->
        <div class="col-md-6 turno-right">
            <div class="text-center">
                <h2>Turno Actual</h2>
                <div id="ultimo-turno">
                    <!-- Se actualiza vía AJAX -->
                </div>
            </div>

            <div class="siguiente-turno">
                <h3>Siguiente Turno</h3>
                <div id="siguiente-turno">
                    <!-- Se actualiza vía AJAX -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let ultimoTurnoId = null;

function actualizarTurnos() {
    $.ajax({
        url: 'get_turnos_index.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            // === Reproducir tono si cambió el turno actual ===
            let nuevoTurnoId = data.ultimo ? data.ultimo.turno : null;
            if (ultimoTurnoId !== null && nuevoTurnoId !== ultimoTurnoId) {
                document.getElementById('audioTurno').play();
            }
            ultimoTurnoId = nuevoTurnoId;

            // === Turno Actual ===
            if(data.ultimo) {
                let claseUltimo = '';
                switch(data.ultimo.prioridad) {
                    case 0: claseUltimo = 'normal'; break;
                    case 1: claseUltimo = 'prioritario'; break;
                    case 3: claseUltimo = 'especial'; break;
                }
                $('#ultimo-turno').html(`
                    <div class="${claseUltimo} turno-grande">${data.ultimo.turno}</div>
                    <div class="modulo">${data.ultimo.caja}</div>
                `);
            } else {
                $('#ultimo-turno').html('<div class="turno-grande">--</div><div class="caja">Caja: --</div>');
            }

            // === Siguiente Turno ===
            if(data.siguiente) {
                let claseSiguiente = '';
                switch(data.siguiente.prioridad) {
                    case 0: claseSiguiente = 'normal'; break;
                    case 1: claseSiguiente = 'prioritario'; break;
                    case 3: claseSiguiente = 'especial'; break;
                }
                $('#siguiente-turno').html(`<div class="${claseSiguiente} turno-mediano">${data.siguiente.turno}</div>`);
            } else {
                $('#siguiente-turno').html('<div class="turno-mediano">--</div>');
            }

            // === Turnos en curso ===
            let html = '';
            if(data.enCurso.length > 0) {
                data.enCurso.slice(0,12).forEach(t => {
                    let clase = '';
                    switch(t.prioridad) {
                        case 0: clase = 'normal'; break;
                        case 1: clase = 'prioritario'; break;
                        case 3: clase = 'especial'; break;
                    }
                    html += `
                        <div class="turno-card ${clase}">
                            <div>${t.turno}</div>
                            <div class="caja">${t.caja}</div>
                        </div>
                    `;
                });
                $('#turnos-en-curso').html(html);
            } else {
                $('#turnos-en-curso').html('<p>No hay turnos en curso.</p>');
            }
        }
    });
}

// Inicializar
actualizarTurnos();
setInterval(actualizarTurnos, 3000);
</script>

</body>
</html>
