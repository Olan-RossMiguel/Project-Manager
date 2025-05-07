<?php
// Configuración inicial
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

// Incluir conexión (sin cambios)
require_once __DIR__ . '/../conexion.php';

try {
    // 1. Validar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Método no permitido', 405);
    }

    // 2. Validar parámetro proyecto_id
    if (!isset($_GET['proyecto_id']) || !is_numeric($_GET['proyecto_id'])) {
        throw new Exception('ID de proyecto no válido', 400);
    }

    $proyecto_id = (int)$_GET['proyecto_id'];

    // 3. Obtener información del proyecto
    $stmt = $conn->prepare("
        SELECT p.*, CONCAT(u.nombre, ' ', u.apellido_paterno) AS lider_nombre
        FROM proyectos p
        JOIN usuarios u ON p.lider_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$proyecto_id]);
    $proyecto = $stmt->fetch();

    if (!$proyecto) {
        throw new Exception('Proyecto no encontrado', 404);
    }

    // 4. Obtener actividades del proyecto
    $stmt = $conn->prepare("
        SELECT a.*, COUNT(aa.usuario_id) AS asignados
        FROM actividades a
        LEFT JOIN asignacion_actividades aa ON a.id = aa.actividad_id
        WHERE a.proyecto_id = ?
        GROUP BY a.id
        ORDER BY a.fecha_inicio ASC
    ");
    $stmt->execute([$proyecto_id]);
    $actividades = $stmt->fetchAll();

    // 5. Calcular estadísticas
    $total_actividades = count($actividades);
    $completadas = 0;
    $hoy = new DateTime();

    foreach ($actividades as $actividad) {
        if (new DateTime($actividad['fecha_fin']) < $hoy) {
            $completadas++;
        }
    }

    // 6. Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => [
            'proyecto' => $proyecto,
            'actividades' => $actividades,
            'estadisticas' => [
                'total' => $total_actividades,
                'completadas' => $completadas,
                'porcentaje' => $total_actividades > 0 ? round(($completadas / $total_actividades) * 100) : 0
            ]
        ]
    ]);

} catch (PDOException $e) {
    // Error de base de datos
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos',
        'error_code' => $e->getCode()
    ]);
    error_log("PDO Error: " . $e->getMessage());
} catch (Exception $e) {
    // Otros errores
    http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
?>

