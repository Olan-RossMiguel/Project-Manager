<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['proyecto_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Validación
$required = ['actividad_id', 'usuario_id'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Campo $field requerido"]);
        exit();
    }
}

try {
    // Verificar que la actividad pertenece al proyecto
    $checkActividad = "SELECT 1 FROM actividades 
                       WHERE id = :actividad_id AND proyecto_id = :proyecto_id";
    $stmt = $conn->prepare($checkActividad);
    $stmt->execute([
        ':actividad_id' => $data['actividad_id'],
        ':proyecto_id' => $_SESSION['proyecto_id']
    ]);
    
    if (!$stmt->fetch()) {
        throw new Exception('La actividad no pertenece a este proyecto');
    }

    // Verificar que el usuario está en el proyecto
    $checkUsuario = "SELECT 1 FROM usuarios_proyectos 
                     WHERE usuario_id = :usuario_id AND proyecto_id = :proyecto_id";
    $stmt = $conn->prepare($checkUsuario);
    $stmt->execute([
        ':usuario_id' => $data['usuario_id'],
        ':proyecto_id' => $_SESSION['proyecto_id']
    ]);
    
    if (!$stmt->fetch()) {
        throw new Exception('El usuario no está asignado a este proyecto');
    }

    // Insertar asignación
    $query = "INSERT INTO asignacion_actividades 
    (actividad_id, usuario_id) 
    VALUES (:actividad_id, :usuario_id)";

    
    $stmt = $conn->prepare($query);
    $success = $stmt->execute([
        ':actividad_id' => $data['actividad_id'],
        ':usuario_id' => $data['usuario_id']
    ]);

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Actividad asignada correctamente' : 'Error al asignar'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en base de datos: '.$e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
