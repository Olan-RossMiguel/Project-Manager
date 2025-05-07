<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

// Validar método POST y sesión
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['proyecto_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

// Obtener datos del cuerpo de la petición
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit();
}

// Validar campos requeridos
if (empty($data['usuario_id']) || empty($data['rol_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

try {
    // 1. Verificar que el usuario no sea administrador
    $stmt = $conn->prepare("SELECT rol FROM usuarios WHERE id = ?");
    $stmt->execute([$data['usuario_id']]);
    $usuarioRol = $stmt->fetchColumn();

    if ($usuarioRol === 'Administrador') {
        throw new Exception('No se puede asignar un administrador como integrante');
    }

    // 2. Verificar que no sea el líder del proyecto actual
    $stmt = $conn->prepare("SELECT 1 FROM proyectos WHERE id = ? AND lider_id = ?");
    $stmt->execute([$_SESSION['proyecto_id'], $data['usuario_id']]);
    
    if ($stmt->fetch()) {
        throw new Exception('El líder del proyecto ya está asignado por defecto');
    }

    // 3. Verificar si el usuario ya está asignado para actualizar su rol
    $stmt = $conn->prepare("SELECT id FROM usuarios_proyectos WHERE usuario_id = ? AND proyecto_id = ?");
    $stmt->execute([$data['usuario_id'], $_SESSION['proyecto_id']]);
    $asignacionExistente = $stmt->fetch();

    if ($asignacionExistente) {
        // Actualizar rol existente
        $stmt = $conn->prepare("UPDATE usuarios_proyectos SET rol_id = ? WHERE id = ?");
        $success = $stmt->execute([$data['rol_id'], $asignacionExistente['id']]);
        $message = 'Rol actualizado correctamente';
    } else {
        // Insertar nueva asignación
        $stmt = $conn->prepare("INSERT INTO usuarios_proyectos (usuario_id, proyecto_id, rol_id) VALUES (?, ?, ?)");
        $success = $stmt->execute([$data['usuario_id'], $_SESSION['proyecto_id'], $data['rol_id']]);
        $message = 'Integrante agregado correctamente';
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => $success,
        'message' => $success ? $message : 'Error al procesar la solicitud'
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