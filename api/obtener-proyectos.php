<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    $stmt = $conn->prepare("
        SELECT 
            p.id,
            p.nombre,
            p.area,
            p.descripcion,
            p.objetivos,
            p.fecha_inicio,
            p.fecha_fin,
            p.estado,  -- Este campo es necesario para la interfaz
            p.url_repositorio,
            p.plataforma_repositorio,
            CONCAT(u.nombre, ' ', u.apellido_paterno) AS lider_nombre
        FROM proyectos p
        LEFT JOIN usuarios u ON p.lider_id = u.id
        ORDER BY p.fecha_inicio DESC
    ");
    $stmt->execute();
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $proyectos
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener proyectos: ' . $e->getMessage()
    ]);
}

