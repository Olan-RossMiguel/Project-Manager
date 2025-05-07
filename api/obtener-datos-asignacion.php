<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_SESSION['proyecto_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

try {
    // Obtener actividades que no están asignadas a ningún usuario
    $checkActividadesDisponibles = "SELECT a.id, a.nombre, a.descripcion 
                                    FROM actividades a
                                    LEFT JOIN asignacion_actividades aa ON aa.actividad_id = a.id
                                    WHERE a.proyecto_id = :proyecto_id
                                    AND aa.actividad_id IS NULL";  // Solo las actividades sin asignar

    $stmt = $conn->prepare($checkActividadesDisponibles);
    $stmt->execute([':proyecto_id' => $_SESSION['proyecto_id']]);
    $actividadesDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener usuarios asignados al proyecto
    $checkUsuariosProyecto = "SELECT u.id, u.nombre FROM usuarios u
                              JOIN usuarios_proyectos up ON u.id = up.usuario_id
                              WHERE up.proyecto_id = :proyecto_id";
    $stmt = $conn->prepare($checkUsuariosProyecto);
    $stmt->execute([':proyecto_id' => $_SESSION['proyecto_id']]);
    $usuariosProyecto = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'actividades' => $actividadesDisponibles,
            'usuarios' => $usuariosProyecto,
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error en base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
