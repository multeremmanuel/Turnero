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
<title>Histórico de Turnos - Tiempo Real</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background-color: #f4f6f8;
}

/* Ajuste para el contenido desplazado por el sidebar */
.main-content {
    margin-left: 240px;
    padding: 22px;
}

.header {
    background: #ffffff;
    padding: 12px 16px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.header h3 { margin: 0; font-size: 1.15rem; color: #333; }

/* ===== Tarjetas de turnos ===== */
.grid {
    margin-top: 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 14px;
}

.card-turno {
    background: #ffffff;
    border-radius: 10px;
    padding: 14px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    border-top: 4px solid #e6e9ec;
    transition: transform .18s ease, box-shadow .18s ease;
}

.card-turno:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 26px rgba(0,0,0,0.10);
}

.card-turno h5 { font-size: 1.4rem; margin: 8px 0; color: #222; }
.card-turno p { margin: 0; font-size: 0.92rem; color: #444; }
.card-turno small { display: block; margin-top: 8px; font-size: 0.8rem; color: #6b7280; }

/* Tarjeta del último turno (más grande) */
.card-ultimo {
    grid-column: span 2;
    background: linear-gradient(135deg, #005BAC, #0078d4);
    color: #fff;
    border-top: 4px solid #003f6b;
    padding: 26px;
    text-align: center;
    box-shadow: 0 12px 28px rgba(0,91,172,0.18);
}

.card-ultimo h1 { font-size: 3.6rem; margin: 0; font-weight: 700; letter-spacing: 1px; }
.card-ultimo p { font-size: 1.1rem; margin-top: 8px; }

/* Adaptabilidad */
@media (max-width: 700px) {
    .card-ultimo { grid-column: span 1; }
}
</style>
</head>
<body>

<?php include 'menu_admin.php'; ?>

<div class="main-content">
    <div class="header">
        <h3>Histórico de Turnos Atendidos (Tiempo Real)</h3>
        <div>Usuario: <strong><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></div>
    </div>

    <div class="grid" id="turnos-historico">
        <!-- tarjetas via AJAX -->
    </div>
</div>

<script>
function actualizarHistorico() {
    $.ajax({
        url: 'get_turnos_historico.php',
        method: 'GET',
        dataType: 'json',
        success: function(turnos) {
            let html = '';

            if (turnos && turnos.length > 0) {
                const ultimo = turnos[0];
                const ultimoTiempo = ultimo.tiempo_entre_turnos;
                html += `
                    <div class="card-turno card-ultimo">
                        <h1>${ultimo.turno}</h1>
                        <p><strong>Caja:</strong> ${ultimo.caja ? ultimo.caja : '--'}</p>
                        ${ultimoTiempo 
                            ? `<small>⏳ Tiempo entre este y el anterior: ${ultimoTiempo}</small>`
                            : `<small>⏳ Tiempo aprox. en atención: N/D</small>`
                        }
                    </div>
                `;

                const otros = turnos.slice(1, 13);
                otros.forEach(t => {
                    const tiempo = t.tiempo_entre_turnos;
                    html += `
                        <div class="card-turno">
                            <h5>${t.turno}</h5>
                            <p><strong>Caja:</strong> ${t.caja ? t.caja : '--'}</p>
                            ${tiempo 
                                ? `<small>⏳ Tiempo de Atención: ${tiempo}</small>`
                                : `<small>⏳ Tiempo aprox. en atención: N/D</small>`
                            }
                        </div>
                    `;
                });
            } else {
                html = `<div style="grid-column: 1 / -1; text-align:center; padding:20px;"><p>No hay turnos atendidos aún.</p></div>`;
            }

            $('#turnos-historico').html(html);
        },
        error: function(xhr, status, err) {
            console.error('Error al cargar turnos:', err);
        }
    });
}

// Inicializar y refrescar cada 3 segundos
actualizarHistorico();
setInterval(actualizarHistorico, 3000);
</script>
</body>
</html>
