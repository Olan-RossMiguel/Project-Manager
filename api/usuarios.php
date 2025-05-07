<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Ruta absoluta garantizada
require_once realpath(dirname(__FILE__) . '/../conexion.php');

// Obtener el contenido POST como JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Datos JSON inválidos']));
}

try {
    // Validación de campos requeridos
    $required = ['nombre', 'apellido_paterno', 'edad', 'correo', 'contrasena', 'rol'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    // Validar edad
    $edad = filter_var($data['edad'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 18]]);
    if (!$edad) throw new Exception("La edad debe ser mayor o igual a 18");

    // Validar correo
    $correo = filter_var($data['correo'], FILTER_VALIDATE_EMAIL);
    if (!$correo) throw new Exception("Correo electrónico no válido");

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
    $stmt->execute([$correo]);
    if ($stmt->fetch()) throw new Exception("El correo ya está registrado");

    // MODIFICACIÓN: Almacenar contraseña en texto plano (NO SEGURO)
    $contrasena = $data['contrasena']; // <- Cambio aquí (elimina password_hash)

    // Insertar usuario
    $stmt = $conn->prepare("INSERT INTO usuarios 
        (nombre, apellido_paterno, apellido_materno, edad, correo, contrasena, rol, imagen_perfil) 
        VALUES (?, ?, ?, ?, ?, ?, ?, '/img/default_profile.png')");
    
    $success = $stmt->execute([
        $data['nombre'],
        $data['apellido_paterno'],
        $data['apellido_materno'] ?? '',
        $edad,
        $correo,
        $contrasena, // <- Ahora va la contraseña en texto plano
        in_array($data['rol'], ['Administrador', 'Líder']) ? $data['rol'] : 'Usuario'
    ]);

    echo json_encode([
        'success' => true, 
        'message' => 'Usuario creado',
        'userId' => $conn->lastInsertId()
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}