<?php
session_start(); // Iniciar la sesión

// Incluir la conexión a la base de datos
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $email = $_POST['usuario']; // Cambia 'usuario' a 'email' si el campo en el formulario es 'usuario'
    $password = $_POST['password'];

    // Validar que los campos no estén vacíos
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=campos_vacios");
        exit;
    }

    // Consultar la base de datos para verificar el usuario
    try {
        $sql = "SELECT id, nombre, contrasena, rol FROM usuarios WHERE correo = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        // Verificar si el usuario existe
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar la contraseña (comparación en texto plano)
            if ($password === $user['contrasena']) {
                // Iniciar sesión y guardar datos del usuario
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nombre'] = $user['nombre'];
                $_SESSION['usuario_rol'] = $user['rol'];
                $_SESSION['imagen_perfil'] = $user['imagen_perfil']; // Guardar la ruta de la imagen de perf

                // Redirigir según el rol global
                if ($user['rol'] === 'Administrador') {
                    header("Location: menuAdmin.php");
                } else {
                    header("Location: login_usuario.php");
                }
                exit;
            } else {
                header("Location: login.php?error=contrasena_incorrecta");
                exit;
            }
        } else {
            header("Location: login.php?error=usuario_no_encontrado");
            exit;
        }
    } catch (PDOException $e) {
        header("Location: login.php?error=error_servidor");
        exit;
    }
}
?>