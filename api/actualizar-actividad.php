<?php
header('Content-Type: application/json');
require_once __DIR__.'/../conexion.php';

session_start();

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['actividad_id']) || !isset($data['nombre'])) {
        throw new Exception('Datos incompletos para la actividad');
    }

    // Actualizar la actividad
    $query = "UPDATE actividades SET
                        nombre = :nombre,
                        descripcion = :descripcion,
                        fecha_inicio = :fecha_inicio,
                        fecha_fin = :fecha_fin,
                        horas_estimadas = :horas_estimadas
                      WHERE id = :actividad_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nombre', $data['nombre'], PDO::PARAM_STR);
    $stmt->bindParam(':descripcion', $data['descripcion'], PDO::PARAM_STR);
    $stmt->bindParam(':fecha_inicio', $data['fecha_inicio'], PDO::PARAM_STR);
    $stmt->bindParam(':fecha_fin', $data['fecha_fin'], PDO::PARAM_STR);
    $stmt->bindParam(':horas_estimadas', $data['horas_estimadas'], PDO::PARAM_INT);
    $stmt->bindParam(':actividad_id', $data['actividad_id'], PDO::PARAM_INT);
    $stmt->execute();

    // Manejar la asignación del responsable
    if (isset($data['responsable_id']) && $data['responsable_id'] !== null) {
        // Verificar si ya existe una asignación para esta actividad
        $checkQuery = "SELECT COUNT(*) FROM asignacion_actividades WHERE actividad_id = :actividad_id";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':actividad_id', $data['actividad_id'], PDO::PARAM_INT);
        $checkStmt->execute();

        if ($checkStmt->fetchColumn() > 0) {
            // Si existe, actualizar la asignación
            $updateAsignacionQuery = "UPDATE asignacion_actividades SET usuario_id = :usuario_id WHERE actividad_id = :actividad_id";
            $updateAsignacionStmt = $conn->prepare($updateAsignacionQuery);
            $updateAsignacionStmt->bindParam(':actividad_id', $data['actividad_id'], PDO::PARAM_INT);
            $updateAsignacionStmt->bindParam(':usuario_id', $data['responsable_id'], PDO::PARAM_INT);
            $updateAsignacionStmt->execute();
        } else {
            // Si no existe, insertar una nueva asignación
            $insertAsignacionQuery = "INSERT INTO asignacion_actividades (actividad_id, usuario_id) VALUES (:actividad_id, :usuario_id)";
            $insertAsignacionStmt = $conn->prepare($insertAsignacionQuery);
            $insertAsignacionStmt->bindParam(':actividad_id', $data['actividad_id'], PDO::PARAM_INT);
            $insertAsignacionStmt->bindParam(':usuario_id', $data['responsable_id'], PDO::PARAM_INT);
            $insertAsignacionStmt->execute();
        }
    } else {
        // Si el responsable es null, eliminar la asignación existente (si la hay)
        $deleteAsignacionQuery = "DELETE FROM asignacion_actividades WHERE actividad_id = :actividad_id";
        $deleteAsignacionStmt = $conn->prepare($deleteAsignacionQuery);
        $deleteAsignacionStmt->bindParam(':actividad_id', $data['actividad_id'], PDO::PARAM_INT);
        $deleteAsignacionStmt->execute();
    }

    echo json_encode([
        'success' => true,
        'message' => 'Actividad actualizada correctamente'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>