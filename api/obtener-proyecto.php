<?php
header('Content-Type: application/json');
require_once realpath(dirname(__FILE__) . '/../conexion.php');

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['error' => 'No autorizado']));
}

$proyectoId = $_GET['id'] ?? null;

if (!$proyectoId) {
    http_response_code(400);
    die(json_encode(['error' => 'ID de proyecto requerido']));
}

try {
    // Verificar que el usuario es el líder asignado
    $stmt = $conn->prepare("
        SELECT p.* 
        FROM proyectos p
        WHERE p.id = ? AND p.lider_id = ?
    ");
    $stmt->execute([$proyectoId, $_SESSION['usuario_id']]);
    $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proyecto) {
        http_response_code(403);
        die(json_encode(['error' => 'No tienes permisos para este proyecto']));
    }

    echo json_encode($proyecto);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
}