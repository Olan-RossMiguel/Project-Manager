<?php
header('Content-Type: application/json');
require_once '../conexion.php';

// Permitir campos opcionales
$data = json_decode(file_get_contents('php://input'), true);

// Validaciones básicas
if (!isset($data['id'], $data['nombre'], $data['apellido_paterno'], $data['correo'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Campos requeridos faltantes']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE usuarios SET
            nombre = :nombre,
            apellido_paterno = :apellido_paterno,
            apellido_materno = :apellido_materno,
            edad = :edad,
            correo = :correo
        WHERE id = :id
    ");

    $stmt->bindValue(':id', $data['id'], PDO::PARAM_INT);
    $stmt->bindValue(':nombre', trim($data['nombre']), PDO::PARAM_STR);
    $stmt->bindValue(':apellido_paterno', trim($data['apellido_paterno']), PDO::PARAM_STR);
    $stmt->bindValue(':apellido_materno', !empty($data['apellido_materno']) ? trim($data['apellido_materno']) : null, PDO::PARAM_STR);
    $stmt->bindValue(':edad', isset($data['edad']) ? (int)$data['edad'] : null, PDO::PARAM_INT);
    $stmt->bindValue(':correo', trim($data['correo']), PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado correctamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se realizaron cambios'
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}