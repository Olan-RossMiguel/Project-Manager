<?php
header('Content-Type: application/json');
require_once '../conexion.php';

if (!isset($_GET['proyecto_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de proyecto no proporcionado'
    ]);
    exit;
}

$proyecto_id = filter_var($_GET['proyecto_id'], FILTER_VALIDATE_INT);

if (!$proyecto_id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de proyecto inválido'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.nombre,
            a.descripcion,
            a.fecha_inicio,
            a.fecha_fin,
            a.horas_estimadas,
            COUNT(aa.id) as asignados
        FROM actividades a
        LEFT JOIN asignacion_actividades aa ON a.id = aa.actividad_id
        WHERE a.proyecto_id = ?
        GROUP BY a.id
        ORDER BY a.fecha_inicio ASC
    ");
    $stmt->execute([$proyecto_id]);
    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $actividades
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener actividades: ' . $e->getMessage()
    ]);
}
?>