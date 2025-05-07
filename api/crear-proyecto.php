<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once realpath(dirname(__FILE__) . '/../conexion.php');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Datos JSON inválidos']));
}

try {
    // Validación de campos requeridos
    $required = ['nombre', 'area', 'lider_id'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

   // Insertar proyecto básico
$stmt = $conn->prepare("INSERT INTO proyectos 
(nombre, area, lider_id, estado) 
VALUES (?, ?, ?, 'Por iniciar')");

$success = $stmt->execute([
$data['nombre'],
$data['area'],
$data['lider_id']
]);

if ($success) {
$proyectoId = $conn->lastInsertId();

// Buscar el ID del rol "Líder de Proyecto"
$stmtRol = $conn->prepare("SELECT id FROM roles_proyecto WHERE nombre = 'Líder de Proyecto'");
$stmtRol->execute();
$rol = $stmtRol->fetch(PDO::FETCH_ASSOC);

if (!$rol) {
    throw new Exception("No se encontró el rol 'Líder de Proyecto'");
}

// Insertar al líder en la tabla usuarios_proyectos
$stmtUsuarioProyecto = $conn->prepare("INSERT INTO usuarios_proyectos (usuario_id, proyecto_id, rol_id) VALUES (?, ?, ?)");
$stmtUsuarioProyecto->execute([$data['lider_id'], $proyectoId, $rol['id']]);

echo json_encode([
    'success' => true, 
    'message' => 'Proyecto creado exitosamente',
    'proyectoId' => $proyectoId
]);
}

    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
