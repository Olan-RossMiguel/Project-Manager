<?php
// Verificar si hay salida antes de los headers
if (ob_get_length()) ob_clean();

// Establecer el tipo de contenido primero
header('Content-Type: application/json; charset=utf-8');

// Desactivar visualización de errores para el cliente
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../conexion.php';

// Verificar conexión
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

try {
    // Consulta modificada (solo incluye campos existentes)
    $stmt = $conn->prepare("
        SELECT 
            id,
            nombre,
            apellido_paterno,
            apellido_materno,
            edad,
            correo
        FROM usuarios
        WHERE visible = 1 AND rol != 'Administrador'
        ORDER BY apellido_paterno, nombre
    ");
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar la consulta');
    }
    
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar si hay resultados
    if (empty($usuarios)) {
        echo json_encode([
            'success' => true,
            'data' => [],
            'total' => 0,
            'message' => 'No se encontraron usuarios'
        ]);
        exit;
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'data' => $usuarios,
        'total' => count($usuarios)
    ]);
    
} catch (PDOException $e) {
    // Error específico de PDO
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    // Otros errores
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}