<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del cuerpo de la petición
$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

// Validar ID
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // 1. Primero verificamos si el usuario existe y está visible
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = :id AND visible = 1");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        throw new Exception('El usuario no existe o ya fue eliminado');
    }

    // 2. Actualizamos visible a 0 (eliminación lógica)
    $stmt = $conn->prepare("UPDATE usuarios SET visible = 0 WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Confirmar transacción
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente']);
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar usuario: ' . $e->getMessage()]);
}
