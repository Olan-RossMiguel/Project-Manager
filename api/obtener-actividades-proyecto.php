<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de proyecto no válido']);
    exit();
}

$proyectoId = $_GET['id'];

try {
    $query = "SELECT nombre, fecha_inicio, fecha_fin, horas_estimadas 
              FROM actividades 
              WHERE proyecto_id = :proyecto_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
    $stmt->execute();
    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($actividades);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener las actividades: ' . $e->getMessage()]);
}
?>
