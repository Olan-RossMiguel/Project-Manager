<?php
header('Content-Type: application/json');
session_start();

require_once realpath(dirname(__FILE__) . '/../conexion.php');

// Verificar que el método sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener y decodificar el JSON recibido
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

// Verificar que el JSON se haya decodificado correctamente
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit();
}

try {
    // Validación de campos requeridos
    $requiredFields = ['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin', 'horas_estimadas'];
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo '$field' es requerido");
        }
    }

    // Verificar que el proyecto_id exista en la sesión
    if (!isset($_SESSION['proyecto_id'])) {
        throw new Exception("No se ha seleccionado un proyecto");
    }

    // Preparar la consulta SQL
    $query = "INSERT INTO actividades (
                proyecto_id, 
                nombre, 
                descripcion, 
                fecha_inicio, 
                fecha_fin, 
                horas_estimadas
              ) VALUES (
                :proyecto_id, 
                :nombre, 
                :descripcion, 
                :fecha_inicio, 
                :fecha_fin, 
                :horas_estimadas
              )";

    $stmt = $conn->prepare($query);

    // Convertir horas estimadas a entero
    $horas = (int)$data['horas_estimadas'];

    // Enlazar parámetros
    $stmt->bindParam(':proyecto_id', $_SESSION['proyecto_id'], PDO::PARAM_INT);
    $stmt->bindParam(':nombre', $data['nombre']);
    $stmt->bindParam(':descripcion', $data['descripcion']);
    $stmt->bindParam(':fecha_inicio', $data['fecha_inicio']);
    $stmt->bindParam(':fecha_fin', $data['fecha_fin']);
    $stmt->bindParam(':horas_estimadas', $horas, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Actividad creada exitosamente',
            'id' => $conn->lastInsertId()
        ]);
    } else {
        throw new Exception('Error al ejecutar la consulta');
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
