<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

try {
    // Obtener el proyecto actual del usuario
    $stmt = $conn->prepare("
    SELECT p.id, p.nombre, p.descripcion, p.fecha_inicio, p.fecha_fin, p.estado
    FROM proyectos p
    JOIN usuarios_proyectos up ON p.id = up.proyecto_id
    WHERE up.usuario_id = :usuario_id
    AND p.estado = 'En progreso' -- Solo proyectos activos
    ORDER BY p.fecha_inicio DESC
    LIMIT 1
");

    $stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt->execute();

    $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proyecto) {
        echo json_encode([
            'success' => true,
            'message' => 'No estás asignado a ningún proyecto activo',
            'data' => null
        ]);
        exit();
    }

    echo json_encode([
        'success' => true,
        'data' => $proyecto
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}