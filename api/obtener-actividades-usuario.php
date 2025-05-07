<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

// Verificar sesión y parámetros
if (!isset($_SESSION['usuario_id']) || !isset($_GET['proyecto_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit();
}

$proyectoId = $_GET['proyecto_id'];
$usuarioId = $_SESSION['usuario_id'];

try {
    // Verificar que el usuario pertenece al proyecto
    $stmtVerificar = $conn->prepare("
        SELECT 1 FROM usuarios_proyectos 
        WHERE usuario_id = :usuario_id AND proyecto_id = :proyecto_id
    ");
    $stmtVerificar->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmtVerificar->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
    $stmtVerificar->execute();
    
    if ($stmtVerificar->rowCount() === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a este proyecto']);
        exit();
    }
    
    // Obtener actividades del usuario en el proyecto
    $stmtActividades = $conn->prepare("
        SELECT 
            a.id, 
            a.nombre, 
            a.descripcion, 
            a.fecha_inicio, 
            a.fecha_fin, 
            a.estado,
            a.prioridad
        FROM actividades a
        JOIN asignaciones act_asig ON a.id = act_asig.actividad_id
        WHERE act_asig.usuario_id = :usuario_id
        AND a.proyecto_id = :proyecto_id
        ORDER BY a.fecha_inicio ASC
    ");
    $stmtActividades->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
    $stmtActividades->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
    $stmtActividades->execute();
    
    $actividades = $stmtActividades->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'actividades' => $actividades
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}