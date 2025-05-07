<?php
header('Content-Type: application/json');
require_once realpath(dirname(__FILE__) . '/../conexion.php');

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['id'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'ID de proyecto requerido']));
}

try {
    // 1. Verificar que el usuario es el líder asignado
    $stmt = $conn->prepare("SELECT * FROM proyectos WHERE id = ? AND lider_id = ?");
    $stmt->execute([$data['id'], $_SESSION['usuario_id']]);
    $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proyecto) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'No tienes permisos para este proyecto']));
    }

    // 2. Determinar qué campos están vacíos y pueden ser editados
    $camposEditables = [
        'descripcion', 'objetivos', 'fecha_inicio', 
        'fecha_fin', 'estado', 'url_repositorio', 'plataforma_repositorio'
    ];
    
    $camposParaActualizar = [];
    $valores = [];
    
    foreach ($camposEditables as $campo) {
        // Solo permitir actualizar si el campo está vacío en la BD o si es el estado
        if (empty($proyecto[$campo]) || $campo === 'estado') {
            if (isset($data[$campo])) {
                $camposParaActualizar[] = "$campo = ?";
                $valores[] = $data[$campo];
            }
        }
    }

    // 3. Validar que hay campos para actualizar
    if (empty($camposParaActualizar)) {
        http_response_code(400);
        die(json_encode(['success' => false, 'message' => 'No hay campos editables para actualizar']));
    }

    // 4. Preparar y ejecutar la consulta
    $valores[] = $data['id']; // Para el WHERE
    $sql = "UPDATE proyectos SET " . implode(', ', $camposParaActualizar) . " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute($valores);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Proyecto actualizado correctamente',
            'updatedFields' => array_map(function($v) { return str_replace(' = ?', '', $v); }, $camposParaActualizar)
        ]);
    } else {
        throw new Exception("Error al ejecutar la actualización");
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}