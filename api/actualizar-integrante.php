<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

// Validación de entrada
if (empty($input['asignacion_id']) || empty($input['usuario_id']) || empty($input['rol_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

try {
    // Verificar permisos (solo líder puede modificar)
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
        throw new Exception('No tienes permisos para editar este integrante');
    }

    // Actualizar tanto usuario como rol
    $stmtActualizar = $conn->prepare("
        UPDATE usuarios_proyectos 
        SET usuario_id = :usuario_id, 
            rol_id = :rol_id
        WHERE id = :asignacion_id
    ");
    
    $stmtActualizar->bindParam(':usuario_id', $input['usuario_id'], PDO::PARAM_INT);
    $stmtActualizar->bindParam(':rol_id', $input['rol_id'], PDO::PARAM_INT);
    $stmtActualizar->bindParam(':asignacion_id', $input['asignacion_id'], PDO::PARAM_INT);
    $stmtActualizar->execute();
    
    if ($stmtActualizar->rowCount() === 0) {
        throw new Exception('No se realizaron cambios');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Integrante actualizado correctamente'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}