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
    // Consulta corregida
    $query = "SELECT u.id, CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre 
              FROM usuarios u
              WHERE u.rol IN ('líder', 'usuario') -- Solo líderes y usuarios
              AND u.rol != 'Administrador' -- Aunque no debería hacer falta si el rol ya se filtra, lo dejamos por seguridad
              AND u.id != (SELECT lider_id FROM proyectos WHERE id = :proyecto_id) -- Excluir líder actual
              ORDER BY u.nombre";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([':proyecto_id' => $_SESSION['proyecto_id']]);
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}


