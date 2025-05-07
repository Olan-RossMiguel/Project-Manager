<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    // Consulta que excluye el rol de Líder
    $query = "SELECT id, nombre FROM roles_proyecto 
              WHERE nombre NOT LIKE '%Líder%' 
              ORDER BY nombre";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage(),
        'data' => []
    ]);
}
