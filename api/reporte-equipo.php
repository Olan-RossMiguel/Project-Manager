<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

if (!isset($_SESSION['proyecto_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Proyecto no seleccionado']);
    exit();
}

try {
    $proyectoId = $_SESSION['proyecto_id'];
    
    $query = "SELECT 
                up.id as asignacion_id,
                u.id as usuario_id,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_completo,
                rp.nombre as rol_proyecto,
                rp.id as rol_id
              FROM usuarios_proyectos up
              JOIN usuarios u ON up.usuario_id = u.id
              JOIN roles_proyecto rp ON up.rol_id = rp.id
              WHERE up.proyecto_id = :proyecto_id
              ORDER BY u.nombre";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
    $stmt->execute();
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $resultados
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}
