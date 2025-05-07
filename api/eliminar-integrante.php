<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

// Verificación de seguridad mejorada
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Verificar sesión y proyecto
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['proyecto_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validación de entrada
if (empty($input['asignacion_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de asignación no proporcionado']);
    exit();
}

try {
    // Verificar que el usuario que elimina es el líder del proyecto
    $stmtVerificar = $conn->prepare("
        SELECT p.lider_id 
        FROM proyectos p
        JOIN usuarios_proyectos up ON p.id = up.proyecto_id
        WHERE up.id = :asignacion_id
    ");
    $stmtVerificar->bindParam(':asignacion_id', $input['asignacion_id'], PDO::PARAM_INT);
    $stmtVerificar->execute();
    
    $proyecto = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if (!$proyecto) {
        throw new Exception('Asignación no encontrada');
    }
    
    if ($proyecto['lider_id'] != $_SESSION['usuario_id']) {
        throw new Exception('Solo el líder del proyecto puede eliminar integrantes');
    }

    // Eliminar la asignación
    $stmtEliminar = $conn->prepare("
        DELETE FROM usuarios_proyectos 
        WHERE id = :asignacion_id
        AND proyecto_id = :proyecto_id
    ");
    
    $stmtEliminar->bindParam(':asignacion_id', $input['asignacion_id'], PDO::PARAM_INT);
    $stmtEliminar->bindParam(':proyecto_id', $_SESSION['proyecto_id'], PDO::PARAM_INT);
    $stmtEliminar->execute();
    
    // Verificar si se eliminó correctamente
    if ($stmtEliminar->rowCount() === 0) {
        throw new Exception('No se pudo eliminar la asignación');
    }
    
    
    
    echo json_encode([
        'success' => true,
        'message' => 'Integrante eliminado correctamente'
    ]);
    
} catch (PDOException $e) {
    error_log('Error en eliminar-integrante.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

