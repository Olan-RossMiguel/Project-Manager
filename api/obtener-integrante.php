<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['proyecto_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if (empty($_GET['asignacion_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de asignación no proporcionado']);
    exit();
}

try {
    $asignacionId = $_GET['asignacion_id'];
    
    $query = "SELECT 
                up.id,
                up.usuario_id,
                up.rol_id,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_completo,
                rp.nombre as rol_nombre
              FROM usuarios_proyectos up
              JOIN usuarios u ON up.usuario_id = u.id
              JOIN roles_proyecto rp ON up.rol_id = rp.id
              WHERE up.id = :asignacion_id
              AND up.proyecto_id = :proyecto_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':asignacion_id', $asignacionId, PDO::PARAM_INT);
    $stmt->bindParam(':proyecto_id', $_SESSION['proyecto_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $integrante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$integrante) {
        throw new Exception('Integrante no encontrado');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $integrante
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}