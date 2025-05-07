<?php
header('Content-Type: application/json');
require_once __DIR__.'/../conexion.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID de actividad no proporcionado');
    }

    $actividadId = $_GET['id'];

    // Consulta para obtener la actividad y su responsable
    $query = "SELECT 
                a.*,
                aa.usuario_id AS responsable_id
              FROM actividades a
              LEFT JOIN asignacion_actividades aa ON a.id = aa.actividad_id
              WHERE a.id = :id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $actividadId, PDO::PARAM_INT);
    $stmt->execute();

    $actividad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$actividad) {
        throw new Exception('Actividad no encontrada');
    }

    echo json_encode([
        'success' => true,
        'data' => $actividad
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}