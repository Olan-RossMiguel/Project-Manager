<?php
header('Content-Type: application/json');
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['proyecto_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de proyecto no proporcionado'
    ]);
    exit;
}

$proyecto_id = filter_var($data['proyecto_id'], FILTER_VALIDATE_INT);

if (!$proyecto_id) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de proyecto inválido'
    ]);
    exit;
}

try {
    $conn->beginTransaction();
    
    // Eliminar asignaciones de actividades
    $stmt = $conn->prepare("
        DELETE aa FROM asignacion_actividades aa
        JOIN actividades a ON aa.actividad_id = a.id
        WHERE a.proyecto_id = ?
    ");
    $stmt->execute([$proyecto_id]);
    
    // Eliminar actividades
    $stmt = $conn->prepare("DELETE FROM actividades WHERE proyecto_id = ?");
    $stmt->execute([$proyecto_id]);
    
    // Eliminar usuarios_proyectos
    $stmt = $conn->prepare("DELETE FROM usuarios_proyectos WHERE proyecto_id = ?");
    $stmt->execute([$proyecto_id]);
    
    // Eliminar proyecto
    $stmt = $conn->prepare("DELETE FROM proyectos WHERE id = ?");
    $stmt->execute([$proyecto_id]);
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Proyecto eliminado correctamente'
    ]);
    
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar proyecto: ' . $e->getMessage()
    ]);
}
?>
