<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

// Verificar que el proyecto esté seleccionado en la sesión
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['proyecto_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado o proyecto no seleccionado']);
    exit();
}

try {
    $id_lider = $_SESSION['usuario_id'];
    $id_proyecto = $_SESSION['proyecto_id'];

    // Consulta para obtener los detalles del proyecto donde el usuario es líder
    $query = "SELECT
                        p.nombre,
                        p.area,
                        p.descripcion,
                        p.objetivos,
                        p.fecha_inicio,
                        p.fecha_fin,
                        p.estado,
                        p.cronograma,
                        p.url_repositorio,
                        p.plataforma_repositorio,
                        p.id  
                    FROM proyectos p
                    WHERE p.id = :proyecto_id AND p.lider_id = :lider_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':proyecto_id', $id_proyecto, PDO::PARAM_INT);
    $stmt->bindParam(':lider_id', $id_lider, PDO::PARAM_INT);
    $stmt->execute();

    $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($proyecto) {
        echo json_encode([
            'success' => true,
            'data' => $proyecto
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el proyecto o no es líder del mismo'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
}
?>