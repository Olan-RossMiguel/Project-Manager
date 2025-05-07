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
    $query = "SELECT id, nombre 
              FROM actividades 
              WHERE proyecto_id = :proyecto_id
              ORDER BY fecha_inicio DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([':proyecto_id' => $_SESSION['proyecto_id']]);
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
