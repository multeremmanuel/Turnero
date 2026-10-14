<?php
require '../conexion.php';

// Últimos 7 días
$fechas = [];
for($i=6; $i>=0; $i--){
    $fechas[] = date('Y-m-d', strtotime("-$i days"));
}

// Colores predefinidos para las cajas
$colores = ['#1abc9c','#3498db','#e74c3c','#f39c12','#9b59b6','#2ecc71','#e67e22','#34495e','#16a085','#2980b9','#c0392b','#d35400'];

$cajas = [];
$stmt = $pdo->query("SELECT id, nombre FROM cajas");
$idx = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $turnosPorDia = [];
    foreach($fechas as $fecha) {
        $sql = "SELECT COUNT(*) as total FROM turnos WHERE idCaja=:idCaja AND DATE(fechaRegistro)=:fecha AND atendido=1";
        $s = $pdo->prepare($sql);
        $s->execute(['idCaja'=>$row['id'], 'fecha'=>$fecha]);
        $total = $s->fetch(PDO::FETCH_ASSOC)['total'];
        $turnosPorDia[] = (int)$total;
    }

    $cajas[] = [
        'nombre' => $row['nombre'],
        'turnos' => $turnosPorDia,
        'color' => $colores[$idx % count($colores)]
    ];
    $idx++;
}

echo json_encode([
    'fechas' => $fechas,
    'cajas' => $cajas
]);
