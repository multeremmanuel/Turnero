<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require '../conexion.php';
?>

<?php include 'menu_admin.php'; ?>

<!-- === Librerías === -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="main-content">
    <!-- Header -->
    <div class="header">
        <h3>Reportes</h3>
        <div>Usuario conectado: <strong><?= htmlspecialchars($_SESSION['usuario_nombre']); ?></strong></div>
    </div>
<center>
<br>    <!-- Botones de reportes -->
    <button class="btn btn-primary m-2" id="btn-reporte-semanal">Reporte Semanal</button>
    <button class="btn btn-primary m-2" id="btn-reporte-caja">Reporte por Caja</button>
    <button class="btn btn-primary m-2" id="btn-reporte-usuario">Reporte por Usuario</button>
    <button class="btn btn-primary m-2" id="btn-reporte-pdf">Reporte PDF</button>

    <!-- Contenedor de gráficas adaptativo -->
    <div class="mt-4" style="width: 100%; height: 70vh;">
        <canvas id="grafica-reportes" style="width: 100%; height: 100%;"></canvas>
    </div>
</div>

<!-- Modal para Reporte PDF -->
<div class="modal fade" id="modalReportePDF" tabindex="-1" aria-labelledby="modalReportePDFLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalReportePDFLabel">Generar Reporte PDF</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formReportePDF">
          <div class="mb-3">
            <label for="tipo-reporte" class="form-label">Selecciona el tipo de reporte:</label>
            <select id="tipo-reporte" name="tipo" class="form-select" required>
              <option value="">-- Seleccionar --</option>
              <option value="usuario">Por Usuario</option>
              <option value="caja">Por Caja</option>
              <option value="dia">Por Día</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="fecha-inicio" class="form-label">Fecha Inicio:</label>
            <input type="date" id="fecha-inicio" name="fecha_inicio" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="fecha-fin" class="form-label">Fecha Fin:</label>
            <input type="date" id="fecha-fin" name="fecha_fin" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Generar PDF</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
$(document).ready(function(){
    let chartInstance = null;

    function mostrarGrafica(labels, title, datasets) {
        if(chartInstance) chartInstance.destroy();
        const ctx = document.getElementById('grafica-reportes').getContext('2d');
        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false, // permite ocupar todo el contenedor
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: title }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // === REPORTE SEMANAL ===
    $('#btn-reporte-semanal').click(function() {
        $.ajax({
            url: 'get_reporte_semanal.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const etiquetas = data.map(d => d.dia + ' ' + d.fecha);
                const datasets = [
                    { label: 'Normal', data: data.map(d => d.normal), backgroundColor: 'green' },
                    { label: 'Especial', data: data.map(d => d.especial), backgroundColor: 'blue' },
                    { label: 'Prioritario/Cita', data: data.map(d => d.prioritario), backgroundColor: 'red' }
                ];
                mostrarGrafica(etiquetas, 'Turnos últimos 7 días', datasets);
            },
            error: function() {
                alert('Error al cargar los datos del reporte semanal.');
            }
        });
    });

    // === REPORTE POR CAJA ===
    $('#btn-reporte-caja').click(function() {
        $.ajax({
            url: 'get_reporte_por_caja.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                const datasets = data.cajas.map(caja => ({
                    label: caja.nombre,
                    data: caja.turnos,
                    backgroundColor: caja.color
                }));
                mostrarGrafica(data.fechas, 'Turnos atendidos por caja (últimos 7 días)', datasets);
            },
            error: function() {
                alert('Error al cargar los datos del reporte por caja.');
            }
        });
    });

    // === REPORTE POR USUARIO ===
    $('#btn-reporte-usuario').click(function() {
        $.ajax({
            url: 'get_reporte_por_usuario.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                if(!data.usuarios || data.usuarios.length === 0){
                    alert('No hay usuarios o datos disponibles.');
                    return;
                }

                const datasets = data.usuarios.map(usuario => ({
                    label: usuario.usuario,
                    data: usuario.turnos,
                    backgroundColor: usuario.color
                }));

                mostrarGrafica(data.fechas, 'Turnos atendidos por usuario (últimos 7 días)', datasets);
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert('Error al cargar los datos del reporte por usuario.');
            }
        });
    });

    // === BOTÓN REPORTE PDF ===
    $('#btn-reporte-pdf').click(function() {
        $('#modalReportePDF').modal('show');
    });

    // === GENERAR PDF ===
    $('#formReportePDF').submit(function(e){
        e.preventDefault();
        const tipo = $('#tipo-reporte').val();
        const inicio = $('#fecha-inicio').val();
        const fin = $('#fecha-fin').val();

        if(tipo && inicio && fin){
            window.open(
                'get_reporte_pdf.php?tipo=' + tipo + '&fecha_inicio=' + inicio + '&fecha_fin=' + fin,
                '_blank'
            );
            $('#modalReportePDF').modal('hide');
        } else {
            alert('Por favor completa todos los campos.');
        }
    });
});
</script>
