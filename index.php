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
/* =====================================================================
   CONFIGURACIÓN DE VOZ
   Ajusta estos valores si quieres que hable más rápido, más lento,
   más fuerte, más agudo, etc.
   ===================================================================== */
var VOZ_ACTIVA      = true;    // false para desactivar la voz sin tocar código
var VOZ_IDIOMA      = 'es-MX'; // idioma preferido
var VOZ_VELOCIDAD   = 0.75;    // 0.5 = muy lento, 1 = normal, 1.5 = rápido
var VOZ_TONO        = 1;       // 0.5 = grave, 1 = normal, 1.5 = agudo
var VOZ_VOLUMEN     = 1;       // 0 a 1
var VOZ_REPETIR     = 1;       // cuántas veces repetir la frase (1 = una vez)
var VOZ_PAUSA_MS    = 400;     // pausa entre repeticiones (si VOZ_REPETIR > 1)

// ---------------------------------------------------------------
// Detectar si el navegador soporta voz
// ---------------------------------------------------------------
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
    } catch (e) {
        vocesEspanol = [];
    }
}
if (vozSoportada) {
    cargarVoces();
    // Algunos navegadores cargan las voces de forma asíncrona
    if (typeof window.speechSynthesis.onvoiceschanged !== 'undefined') {
        window.speechSynthesis.onvoiceschanged = cargarVoces;
    }
}

// ---------------------------------------------------------------
// Deletreo: convierte "S042" en "ese cero cuatro dos"
// ---------------------------------------------------------------
function deletrearTurno(turno) {
    if (!turno) return '';

    var texto = String(turno).toUpperCase();

    // Mapa de letras a su pronunciación en español
    var mapaLetras = {
        'A':'a','B':'be','C':'ce','D':'de','E':'e','F':'efe','G':'ge',
        'H':'hache','I':'i','J':'jota','K':'ka','L':'ele','M':'eme',
        'N':'ene','O':'o','P':'pe','Q':'cu','R':'erre','S':'ese',
        'T':'te','U':'u','V':'uve','W':'doble u','X':'equis',
        'Y':'ye','Z':'zeta','Ñ':'eñe'
    };

    // Mapa de dígitos a su pronunciación
    var mapaDigitos = {
        '0':'cero','1':'uno','2':'dos','3':'tres','4':'cuatro',
        '5':'cinco','6':'seis','7':'siete','8':'ocho','9':'nueve'
    };

    var partes = [];
    for (var i = 0; i < texto.length; i++) {
        var c = texto.charAt(i);
        if (mapaLetras[c]) {
            partes.push(mapaLetras[c]);
        } else if (mapaDigitos[c]) {
            partes.push(mapaDigitos[c]);
        } else if (c === ' ') {
            // ignorar espacios
        } else {
            partes.push(c);
        }
    }
    return partes.join(' ');
}

// ---------------------------------------------------------------
// Dicer el turno + caja en voz alta
// ---------------------------------------------------------------
var vozEnCurso = false; // evita empalmes si entra otro turno mientras habla

function decirTurno(turno, caja) {
    if (!vozSoportada) return;

    try {
        // Cancelar cualquier locución previa para no empalmar
        window.speechSynthesis.cancel();

        var cajaTexto = caja ? String(caja).replace(/CAJA\s*/i, 'caja ') : 'caja';
        var texto = 'Turno ' + deletrearTurno(turno) +
                    '. Favor de pasar a ' + cajaTexto + '.';

        // Si hay voces en español, úsala; si no, deja la que traiga por defecto
        var vozElegida = null;
        if (vocesEspanol.length > 0) {
            vozElegida = vocesEspanol[0];
        }

        for (var r = 0; r < VOZ_REPETIR; r++) {
            var msg = new SpeechSynthesisUtterance(texto);
            msg.lang   = VOZ_IDIOMA;
            msg.rate   = VOZ_VELOCIDAD;
            msg.pitch  = VOZ_TONO;
            msg.volume = VOZ_VOLUMEN;
            if (vozElegida) msg.voice = vozElegida;

            // Pequeño retardo entre repeticiones
            if (r > 0 && VOZ_PAUSA_MS > 0) {
                (function(m, delay){
                    setTimeout(function(){
                        try { window.speechSynthesis.speak(m); } catch(e){}
                    }, delay);
                })(msg, VOZ_PAUSA_MS * r);
            } else {
                window.speechSynthesis.speak(msg);
            }
        }
    } catch (e) {
        // Si algo falla en la voz, no rompemos la pantalla
        if (window.console) console.warn('Error en voz:', e);
    }
}

// ---------------------------------------------------------------
// Actualización de turnos vía AJAX
// ---------------------------------------------------------------
var ultimoTurnoId = null;

function actualizarTurnos() {
    $.ajax({
        url: 'get_turnos_index.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            // === Reproducir tono y voz si cambió el turno actual ===
            var nuevoTurnoId = data.ultimo ? data.ultimo.turno : null;
            if (ultimoTurnoId !== null && nuevoTurnoId !== ultimoTurnoId) {
                var audio = document.getElementById('audioTurno');
                var caja = (data.ultimo && data.ultimo.caja) ? data.ultimo.caja : '';

                if (audio) {
                    // Reproducir el tono y, al terminar, decir el turno
                    audio.onended = function() {
                        decirTurno(nuevoTurnoId, caja);
                    };
                    try {
                        audio.currentTime = 0;
                        var p = audio.play();
                        // Si play() devuelve promesa (navegadores modernos)
                        if (p && typeof p.catch === 'function') {
                            p.catch(function() {
                                // Si el navegador bloquea el audio, al menos intentamos la voz
                                decirTurno(nuevoTurnoId, caja);
                            });
                        }
                    } catch (e) {
                        // Si play() truena, intentamos la voz directo
                        decirTurno(nuevoTurnoId, caja);
                    }
                } else {
                    // Sin elemento audio, al menos decimos la voz
                    decirTurno(nuevoTurnoId, caja);
                }
            }
            ultimoTurnoId = nuevoTurnoId;

            // === Turno Actual ===
            if (data.ultimo) {
                var claseUltimo = '';
                switch (parseInt(data.ultimo.prioridad)) {
                    case 0: claseUltimo = 'normal'; break;
                    case 2: claseUltimo = 'prioritario'; break;
                    case 3: claseUltimo = 'especial'; break;
                    default: claseUltimo = 'normal';
                }
                $('#ultimo-turno').html(
                    '<div class="' + claseUltimo + ' turno-grande">' + data.ultimo.turno + '</div>' +
                    '<div class="modulo">' + (data.ultimo.caja || '') + '</div>'
                );
            } else {
                $('#ultimo-turno').html('<div class="turno-grande">--</div><div class="caja">Caja: --</div>');
            }

            // === Siguiente Turno ===
            if (data.siguiente) {
                var claseSiguiente = '';
                switch (parseInt(data.siguiente.prioridad)) {
                    case 0: claseSiguiente = 'normal'; break;
                    case 2: claseSiguiente = 'prioritario'; break;
                    case 3: claseSiguiente = 'especial'; break;
                    default: claseSiguiente = 'normal';
                }
                $('#siguiente-turno').html(
                    '<div class="' + claseSiguiente + ' turno-mediano">' + data.siguiente.turno + '</div>'
                );
            } else {
                $('#siguiente-turno').html('<div class="turno-mediano">--</div>');
            }

            // === Turnos en curso ===
            var html = '';
            if (data.enCurso && data.enCurso.length > 0) {
                data.enCurso.slice(0, 12).forEach(function(t) {
                    var clase = '';
                    switch (parseInt(t.prioridad)) {
                        case 0: clase = 'normal'; break;
                        case 2: clase = 'prioritario'; break;
                        case 3: clase = 'especial'; break;
                        default: clase = 'normal';
                    }
                    html +=
                        '<div class="turno-card ' + clase + '">' +
                            '<div>' + t.turno + '</div>' +
                            '<div class="caja">' + (t.caja || '') + '</div>' +
                        '</div>';
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