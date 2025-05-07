<?php
header('Content-Type: application/json');
require_once realpath(dirname(__FILE__) . '/../conexion.php');

try {
    // Obtener todos los usuarios excepto el administrador (ID 1)
    $stmt = $conn->prepare("SELECT id, nombre, apellido_paterno FROM usuarios WHERE id != :admin_id");
    $stmt->execute(['admin_id' => 1]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($usuarios);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener usuarios: ' . $e->getMessage()]);
}
