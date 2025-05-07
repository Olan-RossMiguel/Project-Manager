<?php
header('Content-Type: application/json');
require_once __DIR__.'/../conexion.php';

session_start();

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['actividad_id'])) {
        throw new Exception('ID de actividad no proporcionado');
    }

    // Primero eliminar la asignación
    $queryAsignacion = "DELETE FROM asignacion_actividades WHERE actividad_id = :actividad_id";
    $stmtAsignacion = $conn->prepare($queryAsignacion);
    $stmtAsignacion->bindParam(':actividad_id', $data['actividad_id'], PDO::PARAM_INT);
    $stmtAsignacion->execute();

    // Luego eliminar la actividad
    $query = "DELETE FROM actividades WHERE id = :actividad_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':actividad_id', $data['actividad_id'], PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Actividad eliminada correctamente'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}


