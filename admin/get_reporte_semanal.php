<?php
header('Content-Type: application/json');

require '../conexion.php'; // Asegúrate de que la ruta sea correcta

try {
    // Obtenemos los últimos 7 días (incluyendo hoy)
    $stmt = $pdo->prepare("
        SELECT 
            DATE(fechaRegistro) AS fecha,
            tipo,
            COUNT(*) AS total
        FROM turnos
        WHERE fechaRegistro >= CURDATE() - INTERVAL 6 DAY
        GROUP BY fecha, tipo
        ORDER BY fecha ASC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicializamos un array con los últimos 7 días
    $dias = [];
    for ($i = 6; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        $dias[$fecha] = ['normal'=>0,'especial'=>0,'prioritario'=>0];
    }

    // Rellenamos los datos
    foreach ($result as $row) {
        $dias[$row['fecha']][$row['tipo']] = (int)$row['total'];
    }

    // Convertimos a un array de fechas con día de la semana
    $data = [];
    foreach ($dias as $fecha => $tipos) {
        $data[] = [
            'fecha' => $fecha,
            'dia' => date('D', strtotime($fecha)),
            'normal' => $tipos['normal'],
            'especial' => $tipos['especial'],
            'prioritario' => $tipos['prioritario']
        ];
    }

    echo json_encode($data);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
