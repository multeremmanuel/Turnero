<?php
header('Content-Type: application/json; charset=utf-8');
require '../conexion.php';

// Obtener últimos 24 turnos atendidos con idCaja y fecha
$stmt = $pdo->prepare("
    SELECT 
        t.id,
        t.turno,
        t.tipo,
        t.idCaja,
        c.nombre AS caja,
        t.fechaAtencion
    FROM turnos t
    LEFT JOIN cajas c ON t.idCaja = c.id
    WHERE t.atendido = 1
    ORDER BY t.fechaAtencion DESC
    LIMIT 24
");
$stmt->execute();
$turnosDesc = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si no hay turnos, devolver arreglo vacío
if (!$turnosDesc) {
    echo json_encode([]);
    exit;
}

// Formateador de segundos a texto legible
function format_seconds_readable(int $seconds): string {
    if ($seconds <= 0) return '0s';
    $h = intdiv($seconds, 3600);
    $seconds -= $h * 3600;
    $m = intdiv($seconds, 60);
    $s = $seconds - $m * 60;

    $parts = [];
    if ($h > 0) $parts[] = "{$h}h";
    if ($m > 0) $parts[] = "{$m}m";
    if ($s > 0 || empty($parts)) $parts[] = "{$s}s";

    return implode(' ', $parts);
}

// Para calcular la diferencia entre cada turno y su inmediato anterior de la misma caja
// es más sencillo iterar en orden cronológico ascendente y mantener "lastSeen" por caja.
$turnosAsc = array_reverse($turnosDesc);
$lastSeen = []; // idCaja => fechaAtencion de la última vez (más reciente ya procesada)
$resultAsc = [];

foreach ($turnosAsc as $row) {
    $cajaId = $row['idCaja'] !== null ? (string)$row['idCaja'] : 'no_box_'.$row['id']; // clave segura
    $fechaActual = $row['fechaAtencion'];
    $tiempo_text = null;

    if (isset($lastSeen[$cajaId])) {
        // Diferencia entre el turno actual (más nuevo) y el anterior (más viejo)
        $prevFecha = $lastSeen[$cajaId]; // fecha de la instancia anterior (más vieja)
        $diffSeconds = strtotime($fechaActual) - strtotime($prevFecha);
        if ($diffSeconds < 0) $diffSeconds = abs($diffSeconds); // seguridad por si el orden fuera raro
        $tiempo_text = format_seconds_readable($diffSeconds);
    } else {
        // No hay previo para esta caja (primer registro en el rango)
        $tiempo_text = null; // lo manejamos en el frontend (mostrar "Tiempo aprox... / N/D")
    }

    $row['tiempo_entre_turnos'] = $tiempo_text;
    $resultAsc[] = $row;

    // Actualizamos lastSeen para la caja con la fecha del registro actual
    // (avanzamos cronológicamente: la próxima vez que encontremos la misma caja
    // tendremos su anterior más cercano)
    $lastSeen[$cajaId] = $fechaActual;
}

// Devolvemos en orden descendente (más reciente primero), que es lo que espera el frontend
$resultDesc = array_reverse($resultAsc);
echo json_encode($resultDesc, JSON_UNESCAPED_UNICODE);
