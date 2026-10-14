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
    :root {
        --cafe-piedra:   #B09585;
        --verde:         #00A759;
        --azul-agua:     #009CDE;
        --azul-claro:    #5BCAE9;
        --azul-instit:   #004376;
        --gris-detalle:  #C1C0C1;
        --gris-fondo:    #F4F6F8;
        --gris-texto:    #4A5560;
        --borde-suave:   #E3E7EB;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html, body {
        width: 100%;
        height: 100%;
        overflow: hidden;
        background: var(--gris-fondo);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
                     "Helvetica Neue", Arial, sans-serif;
        color: #1b1b1b;
        -webkit-font-smoothing: antialiased;
    }

    .pantalla {
        width: 100vw;
        height: 100vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* ---------- Encabezado ---------- */
    .encabezado {
        height: 8vh;
        min-height: 40px;
        max-height: 90px;
        background: #ffffff;
        border-bottom: 1px solid var(--borde-suave);
        display: flex;
        align-items: center;
        padding: 0 2vw;
        position: relative;
        flex: 0 0 auto;
    }
    .encabezado::after {
        content: "";
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg,
                    var(--azul-instit) 0%,
                    var(--azul-agua)   50%,
                    var(--verde)       100%);
    }
    .encabezado img {
        height: 60%;
        width: auto;
        margin-right: 1.5vw;
    }
    .encabezado .titulo {
        font-size: 2.4vh;
        font-weight: 600;
        color: var(--azul-instit);
        letter-spacing: 0.3px;
    }
    .encabezado .subtitulo {
        margin-left: auto;
        font-size: 1.8vh;
        color: var(--gris-texto);
        font-weight: 400;
    }

    /* ---------- Cuerpo: dos columnas ---------- */
    .cuerpo {
        flex: 1 1 auto;
        display: flex;
        flex-direction: row;
        overflow: hidden;
        height: 92vh;
    }

    .columna {
        width: 50%;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* ========== COLUMNA IZQUIERDA ========== */
    .columna-izq {
        padding: 1vh 0.8vw 1vh 1.2vw;
    }

    .video-section {
        flex: 1 1 auto;
        height: 0;
        min-height: 0;
        background: var(--azul-instit);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        margin-bottom: 1vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .video-section video {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: var(--azul-instit);
        display: block;
    }

    /* ========== LISTA DE TURNOS EN CURSO ========== */
    .turnos-en-curso-section {
        flex: 0 0 28vh;
        background: #ffffff;
        border-radius: 12px;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        align-content: center;
        overflow: hidden;
        padding: 0.5vh 0.5vw;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    }

    #turnos-en-curso {
        width: 100%;
        height: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        align-content: center;
        gap: 0.4vh 0.4vw;
    }

    .turno-card {
        min-width: 4.8vw;
        text-align: center;
        font-weight: 700;
        margin: 0;
        padding: 0.8vh 0.6vw;
        border-radius: 10px;
        background: var(--gris-fondo);
        border: 2px solid var(--borde-suave);
        font-size: 2.6vh;
        line-height: 1.15;
        color: var(--azul-instit);
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .turno-card .caja {
        font-size: 1.9vh;
        color: var(--gris-texto);
        font-weight: 500;
        margin-top: 0.3vh;
    }

    /* ========== COLUMNA DERECHA ========== */
    .columna-der {
        padding: 1vh 1.2vw 1vh 0.8vw;
    }

    .bloque-turno-actual {
        flex: 1 1 auto;
        height: 0;
        min-height: 0;
        background: #ffffff;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 1vh 1vw;
        box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        margin-bottom: 1vh;
        overflow: hidden;
    }
    /* Título "Turno Actual" — más grande y con más presencia */
    .bloque-turno-actual h2 {
        font-size: 3.2vh;
        margin: 0 0 1.2vh 0;
        color: var(--gris-texto);
        letter-spacing: 4px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .bloque-siguiente {
        flex: 0 0 20vh;
        background: var(--azul-instit);
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 1vh 1vw;
        color: #ffffff;
        box-shadow: 0 6px 18px rgba(0,67,118,0.18);
        position: relative;
        overflow: hidden;
    }
    .bloque-siguiente::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--azul-agua), var(--azul-claro));
    }
    /* Título "Siguiente" — más grande y con más presencia */
    .bloque-siguiente h3 {
        font-size: 3vh;
        margin: 0 0 1vh 0;
        color: rgba(255,255,255,0.85);
        letter-spacing: 4px;
        text-transform: uppercase;
        font-weight: 600;
    }

    /* ---------- Tamaños de texto ---------- */
    .turno-grande  {
        font-size: 17vh;
        line-height: 0.95;
        font-weight: 700;
        letter-spacing: -2px;
    }
    .turno-mediano {
        font-size: 7.5vh;
        line-height: 1;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -1px;
    }
    .modulo {
        font-size: 3.5vh;
        line-height: 1.1;
        font-weight: 500;
        margin-top: 0.5vh;
        color: var(--gris-texto);
        letter-spacing: 1px;
    }

    .prioritario { color: #C62828; }
    .normal      { color: var(--azul-instit); }
    .especial    { color: var(--verde); }

    .turno-mediano.prioritario,
    .turno-mediano.normal,
    .turno-mediano.especial {
        color: #ffffff;
    }
</style>
</head>
<body>

<audio id="audioTurno" src="tono.mp3" preload="auto"></audio>

<div class="pantalla">

    <div class="encabezado">
        <img src="logo.png" alt="Turnero">
        <div class="titulo">Sistema de Turnos</div>
        <div class="subtitulo">Nombre del Negocio o Dependencia </div>
    </div>

    <div class="cuerpo">

        <!-- ================= IZQUIERDA ================= -->
        <div class="columna columna-izq">
            <div class="video-section">
                <video autoplay muted loop>
                    <source src="video.mp4" type="video/mp4">
                    Tu navegador no soporta video HTML5.
                </video>
            </div>

            <div class="turnos-en-curso-section">
                <div id="turnos-en-curso">
                    <!-- Se actualiza vía AJAX -->
                </div>
            </div>
        </div>

        <!-- ================= DERECHA ================= -->
        <div class="columna columna-der">
            <div class="bloque-turno-actual">
                <h2>Turno Actual</h2>
                <div id="ultimo-turno">
                    <!-- Se actualiza vía AJAX -->
                </div>
            </div>

            <div class="bloque-siguiente">
                <h3>Siguiente</h3>
                <div id="siguiente-turno">
                    <!-- Se actualiza vía AJAX -->
                </div>
            </div>
        </div>

    </div>
</div>

<script>
/* =====================================================================
   CONFIGURACIÓN DE VOZ
   ===================================================================== */
var VOZ_ACTIVA    = true;
var VOZ_IDIOMA    = 'es-MX';
var VOZ_VELOCIDAD = 0.85;
var VOZ_TONO      = 1;
var VOZ_VOLUMEN   = 1;

/* Cantidad de turnos en curso a mostrar */
var TURNOS_EN_CURSO_MAX = 13;

var vozSoportada = ('speechSynthesis' in window) &&
                   (typeof SpeechSynthesisUtterance !== 'undefined') &&
                   VOZ_ACTIVA;

var vocesEspanol = [];

function cargarVoces() {
    if (!vozSoportada) return;
    try {
        var voces = window.speechSynthesis.getVoices() || [];
        vocesEspanol = voces.filter(function(v) {
            return v.lang && v.lang.toLowerCase().indexOf('es') === 0;
        });
    } catch (e) { vocesEspanol = []; }
}
if (vozSoportada) {
    cargarVoces();
    if (typeof window.speechSynthesis.onvoiceschanged !== 'undefined') {
        window.speechSynthesis.onvoiceschanged = cargarVoces;
    }
}

/* Deletreo: "S042" -> "ese cero cuatro dos" */
function deletrearTurno(turno) {
    if (!turno) return '';
    var texto = String(turno).toUpperCase();
    var mapaLetras = {
        'A':'a','B':'be','C':'ce','D':'de','E':'e','F':'efe','G':'ge',
        'H':'hache','I':'i','J':'jota','K':'ka','L':'ele','M':'eme',
        'N':'ene','O':'o','P':'pe','Q':'cu','R':'erre','S':'ese',
        'T':'te','U':'u','V':'uve','W':'doble u','X':'equis',
        'Y':'ye','Z':'zeta','Ñ':'eñe'
    };
    var mapaDigitos = {
        '0':'cero','1':'uno','2':'dos','3':'tres','4':'cuatro',
        '5':'cinco','6':'seis','7':'siete','8':'ocho','9':'nueve'
    };
    var partes = [];
    for (var i = 0; i < texto.length; i++) {
        var c = texto.charAt(i);
        if (mapaLetras[c]) partes.push(mapaLetras[c]);
        else if (mapaDigitos[c]) partes.push(mapaDigitos[c]);
        else if (c === ' ') { /* ignorar */ }
        else partes.push(c);
    }
    return partes.join(' ');
}

/* ---------------------------------------------------------------------
   Dictar el turno con voz (sin tono, ya sonó antes)
   --------------------------------------------------------------------- */
function dictarConVoz(turno, caja) {
    if (!vozSoportada) return;

    var cajaTexto = caja ? String(caja).replace(/CAJA\s*/i, 'caja ') : 'caja';
    var texto = 'Turno ' + deletrearTurno(turno) +
                '. Favor de pasar a ' + cajaTexto + '.';

    var vozElegida = vocesEspanol.length > 0 ? vocesEspanol[0] : null;

    try {
        window.speechSynthesis.cancel();
        var msg = new SpeechSynthesisUtterance(texto);
        msg.lang   = VOZ_IDIOMA;
        msg.rate   = VOZ_VELOCIDAD;
        msg.pitch  = VOZ_TONO;
        msg.volume = VOZ_VOLUMEN;
        if (vozElegida) msg.voice = vozElegida;
        window.speechSynthesis.speak(msg);
    } catch (e) {
        // Si la voz falla, no rompe nada: el tono ya sonó
    }
}

/* ---------------------------------------------------------------------
   Anunciar: 1) tono siempre → 2) al terminar el tono, voz
   --------------------------------------------------------------------- */
function anunciarTurno(turno, caja) {
    var audio = document.getElementById('audioTurno');

    if (!audio) {
        dictarConVoz(turno, caja);
        return;
    }

    try {
        audio.onended = null;
        audio.pause();
        audio.currentTime = 0;

        audio.onended = function() {
            dictarConVoz(turno, caja);
            audio.onended = null;
        };

        var p = audio.play();

        if (p && typeof p.then === 'function') {
            p.then(function() {
                // Arrancó bien; esperamos onended para la voz
            }).catch(function() {
                dictarConVoz(turno, caja);
            });
        }
    } catch (e) {
        dictarConVoz(turno, caja);
    }
}

/* =====================================================================
   AJAX
   ===================================================================== */
var ultimoTurnoId = null;
var primerCarga   = true;

function actualizarTurnos() {
    $.ajax({
        url: 'get_turnos_index2.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {

            var nuevoTurnoId = data.ultimo ? data.ultimo.turno : null;

            if (!primerCarga && nuevoTurnoId && nuevoTurnoId !== ultimoTurnoId) {
                var cajaActual = (data.ultimo && data.ultimo.caja) ? data.ultimo.caja : '';
                anunciarTurno(nuevoTurnoId, cajaActual);
            }
            primerCarga   = false;
            ultimoTurnoId = nuevoTurnoId;

            // === Turno Actual ===
            if (data.ultimo) {
                var claseUltimo = 'normal';
                switch (parseInt(data.ultimo.prioridad)) {
                    case 2: claseUltimo = 'prioritario'; break;
                    case 3: claseUltimo = 'especial';    break;
                }
                $('#ultimo-turno').html(
                    '<div class="' + claseUltimo + ' turno-grande">' + data.ultimo.turno + '</div>' +
                    '<div class="modulo">' + (data.ultimo.caja || '') + '</div>'
                );
            } else {
                $('#ultimo-turno').html(
                    '<div class="turno-grande normal">--</div>' +
                    '<div class="modulo">Caja: --</div>'
                );
            }

            // === Siguiente Turno ===
            if (data.siguiente) {
                $('#siguiente-turno').html(
                    '<div class="turno-mediano">' + data.siguiente.turno + '</div>'
                );
            } else {
                $('#siguiente-turno').html('<div class="turno-mediano">--</div>');
            }

            // === Turnos en curso — mostramos los ÚLTIMOS 13 ===
            var html = '';
            if (data.enCurso && data.enCurso.length > 0) {
                var ultimos = data.enCurso.slice(-TURNOS_EN_CURSO_MAX);
                ultimos.forEach(function(t) {
                    var clase = 'normal';
                    switch (parseInt(t.prioridad)) {
                        case 2: clase = 'prioritario'; break;
                        case 3: clase = 'especial';    break;
                    }
                    html +=
                        '<div class="turno-card ' + clase + '">' +
                            '<div>' + t.turno + '</div>' +
                            '<div class="caja">' + (t.caja || '') + '</div>' +
                        '</div>';
                });
                $('#turnos-en-curso').html(html);
            } else {
                $('#turnos-en-curso').html(
                    '<p style="font-size:2.2vh;color:#889;margin:0;">Sin turnos en curso</p>'
                );
            }
        }
    });
}

/* =====================================================================
   Desbloqueo de audio + voz al primer click/tecla
   ===================================================================== */
function desbloquearAudioYVoz() {
    var a = document.getElementById('audioTurno');
    if (a) {
        try {
            a.volume = 0;
            var p = a.play();
            if (p && typeof p.then === 'function') {
                p.then(function() {
                    a.pause();
                    a.currentTime = 0;
                    a.volume = 1;
                }).catch(function() { a.volume = 1; });
            }
        } catch (e) {}
    }
    if (vozSoportada) {
        try {
            var m = new SpeechSynthesisUtterance('');
            m.volume = 0;
            window.speechSynthesis.speak(m);
        } catch (e) {}
    }
    document.removeEventListener('click', desbloquearAudioYVoz);
    document.removeEventListener('keydown', desbloquearAudioYVoz);
}
document.addEventListener('click',   desbloquearAudioYVoz);
document.addEventListener('keydown', desbloquearAudioYVoz);

/* Inicio + refresco cada 3 segundos */
actualizarTurnos();
setInterval(actualizarTurnos, 3000);
</script>

</body>
</html>