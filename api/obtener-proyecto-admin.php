<?php
header('Content-Type: application/json');
require_once '../conexion.php';

if (!isset($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de proyecto no proporcionado'
    ]);
    exit;
}

$proyecto_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

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
            p.*,
            CONCAT(u.nombre, ' ', u.apellido_paterno) AS lider_nombre
        FROM proyectos p
        JOIN usuarios u ON p.lider_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$proyecto_id]);
    $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$proyecto) {
        echo json_encode([
            'success' => false,
            'message' => 'Proyecto no encontrado'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $proyecto
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener proyecto: ' . $e->getMessage()
    ]);
}
?>