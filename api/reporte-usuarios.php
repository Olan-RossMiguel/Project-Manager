<?php
header('Content-Type: application/json');
require_once '../conexion.php';

try {
    // Consulta para obtener todos los usuarios activos con información básica
    $stmt = $conn->prepare("
        SELECT 
            id,
            nombre,
            apellido_paterno,
            apellido_materno,
            fecha_nacimiento,
            correo,
          
        FROM usuarios
        WHERE estado = 1
        ORDER BY apellido_paterno, nombre
    ");
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear datos para la vista
    $formattedData = array_map(function($usuario) {
        return [
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'apellido_paterno' => $usuario['apellido_paterno'],
            'apellido_materno' => $usuario['apellido_materno'] ?? '',
            'edad' => $usuario['edad'],
            'correo' => $usuario['correo'],
           
        ];
    }, $usuarios);

    echo json_encode([
        'success' => true,
        'data' => $formattedData,
        'total' => count($formattedData)
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
}

