<?php
session_start(); // Iniciar la sesión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Verificar si se ha seleccionado un proyecto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['proyecto'])) {
        header("Location: login_usuario.php?error=proyecto_no_seleccionado");
        exit;
    }

    // Obtener el proyecto seleccionado y el rol del usuario en ese proyecto
    $proyecto_id = $_POST['proyecto'];
    $usuario_id = $_SESSION['usuario_id'];

    // Incluir la conexión a la base de datos
    require 'conexion.php';

    try {
        $sql = "
            SELECT r.nombre AS rol_nombre
            FROM usuarios_proyectos up
            JOIN roles_proyecto r ON up.rol_id = r.id
            WHERE up.usuario_id = :usuario_id AND up.proyecto_id = :proyecto_id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':proyecto_id', $proyecto_id);
        $stmt->execute();
        $rol = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rol) {
            // Guardar el proyecto seleccionado y el rol en la sesión
            $_SESSION['proyecto_id'] = $proyecto_id;
            $_SESSION['rol'] = $rol['rol_nombre'];

            // Redirigir al menú correspondiente
            if ($rol['rol_nombre'] === 'Líder de Proyecto') { // Comparación corregida
                header("Location: leader.php");
            } else {
                header("Location: menuUsers.php");
            }
            exit;
        } else {
            header("Location: login_usuario.php?error=proyecto_no_encontrado");
            exit;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}
?>