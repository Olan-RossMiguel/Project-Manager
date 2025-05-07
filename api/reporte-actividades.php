<?php
header('Content-Type: application/json');
require_once __DIR__.'/../conexion.php';

session_start();

try {
    // Verificar si hay un proyecto seleccionado
    if (!isset($_SESSION['proyecto_id'])) {
        throw new Exception('No se ha seleccionado un proyecto');
    }

    $proyectoId = $_SESSION['proyecto_id'];

    // Consulta para obtener las actividades con sus responsables
    $query = "SELECT 
                a.id AS actividad_id,
                a.nombre AS actividad_nombre,
                a.descripcion,
                a.fecha_inicio,
                a.fecha_fin,
                a.horas_estimadas,
                CONCAT(u.nombre, ' ', u.apellido_paterno) AS responsable_nombre,
                u.id AS responsable_id
              FROM actividades a
              LEFT JOIN asignacion_actividades aa ON a.id = aa.actividad_id
              LEFT JOIN usuarios u ON aa.usuario_id = u.id
              WHERE a.proyecto_id = :proyecto_id
              ORDER BY a.fecha_inicio";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
    $stmt->execute();

    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $actividades
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
