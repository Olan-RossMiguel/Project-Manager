<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

if (!isset($_SESSION['proyecto_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Sesión inválida']);
    exit();
}

try {
    $query = "SELECT u.id, CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre, r.nombre as rol
              FROM usuarios u
              JOIN usuarios_proyectos up ON u.id = up.usuario_id
              JOIN roles_proyecto r ON up.rol_id = r.id
              WHERE up.proyecto_id = :proyecto_id
              ORDER BY u.nombre";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([':proyecto_id' => $_SESSION['proyecto_id']]);
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}