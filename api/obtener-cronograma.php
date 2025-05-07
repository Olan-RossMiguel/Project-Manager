<?php
header('Content-Type: application/json');
session_start();
require_once '../conexion.php';

// Verificar si se proporcionó el ID del proyecto
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'ID de proyecto no válido']);
    exit();
}

$proyectoId = $_GET['id'];

try {
    // Consulta para obtener el cronograma del proyecto
    $query = "SELECT cronograma FROM proyectos WHERE id = :proyecto_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':proyecto_id', $proyectoId, PDO::PARAM_INT);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado && $resultado['cronograma']) {
        // El cronograma está almacenado como JSON en la base de datos
        $cronograma = json_decode($resultado['cronograma'], true);

        if ($cronograma) {
            echo json_encode($cronograma);
        } else {
            // Si no se puede decodificar el JSON (puede ser null o un formato incorrecto)
            echo json_encode([]); // Devolver un array vacío
        }
    } else {
        // Si no se encuentra el proyecto o no tiene cronograma
        echo json_encode([]); // Devolver un array vacío
    }

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>