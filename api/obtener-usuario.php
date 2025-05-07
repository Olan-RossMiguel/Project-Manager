<?php
header('Content-Type: application/json');
require_once '../conexion.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

$id = $_GET['id'];

try {
    $stmt = $conn->prepare("
        SELECT 
            id,
            nombre,
            apellido_paterno,
            apellido_materno,
            edad,
            correo,
            rol,
            imagen_perfil
        FROM usuarios
        WHERE id = :id AND visible = 1
    ");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado o no visible'
        ]);
        exit;
    }

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $usuario
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener usuario: ' . $e->getMessage()
    ]);
}