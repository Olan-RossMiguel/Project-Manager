<?php
header('Content-Type: application/json');
require_once __DIR__.'/../conexion.php';

try {
    if (!isset($_GET['proyecto_id'])) {
        throw new Exception('ID de proyecto no proporcionado');
    }

    $proyectoId = $_GET['proyecto_id'];

    // Consulta para obtener los integrantes del proyecto
    $query = "SELECT
                u.id AS usuario_id,
                CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo
              FROM usuarios_proyectos up
              JOIN usuarios u ON up.usuario_id = u.id
              WHERE up.proyecto_id = :proyecto_id";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
    $stmt->execute();

    $integrantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $integrantes
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>