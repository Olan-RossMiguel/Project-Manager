<?php
// perfil.php

session_start(); // Iniciar la sesión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    // Si no hay sesión, redirigir al login
    header("Location: login.php");
    exit();
}

// Incluir la conexión a la base de datos
require 'conexion.php'; // Asegúrate de que la ruta sea correcta

// Verificar si $conn está definida (la conexión PDO)
if (!isset($conn)) {
    die("Error: La conexión a la base de datos no está definida.");
}

// Inicializar variables para evitar errores de "Undefined variable"
$nombre = "";
$apellido_paterno = "";
$apellido_materno = "";
$edad = "";
$correo = "";
$imagen_perfil = "/img/default_profile.png"; // Imagen de perfil por defecto

// Obtener el ID del usuario de la sesión
$usuario_id = $_SESSION['usuario_id'];

// Obtener los datos actuales del usuario
try {
    $query = "SELECT nombre, apellido_paterno, apellido_materno, edad, correo, imagen_perfil FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($query); // Usar $conn (la conexión PDO)
    $stmt->execute([$usuario_id]); // Pasar el ID del usuario como parámetro
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC); // Obtener los datos como un array asociativo

    if ($usuario) {
        // Asignar los valores obtenidos de la base de datos
        $nombre = $usuario['nombre'];
        $apellido_paterno = $usuario['apellido_paterno'];
        $apellido_materno = $usuario['apellido_materno'];
        $edad = $usuario['edad'];
        $correo = $usuario['correo'];
        $imagen_perfil = $usuario['imagen_perfil'] ? $usuario['imagen_perfil'] : "/img/default_profile.png"; // Usar imagen por defecto si no hay una
    } else {
        $error = "Usuario no encontrado.";
    }
} catch (PDOException $e) {
    die("Error al obtener los datos del usuario: " . $e->getMessage());
}

// Procesar el formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nombre = $_POST['nombre'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $edad = $_POST['edad'];
    $correo = $_POST['correo'];

    // Procesar la subida de la imagen de perfil
    if ($_FILES['imagen_perfil']['error'] === UPLOAD_ERR_OK) {
        $imagen_nombre = $_FILES['imagen_perfil']['name'];
        $imagen_tmp = $_FILES['imagen_perfil']['tmp_name'];
        $imagen_ruta = "./img/perfiles/" . basename($imagen_nombre);

        // Mover la imagen al directorio de perfiles
        if (move_uploaded_file($imagen_tmp, $imagen_ruta)) {
            $imagen_perfil = $imagen_ruta;
        } else {
            $error = "Error al subir la imagen.";
        }
    }

    // Si se presionó el botón "Quitar imagen"
    if (isset($_POST['quitar_imagen'])) {
        $imagen_perfil = "/img/default_profile.png"; // Restablecer a la imagen por defecto
    }

    // Actualizar los datos del usuario en la base de datos
    try {
        $query = "UPDATE usuarios SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, edad = ?, correo = ?, imagen_perfil = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$nombre, $apellido_paterno, $apellido_materno, $edad, $correo, $imagen_perfil, $usuario_id]);

        $mensaje = "Perfil actualizado correctamente.";
    } catch (PDOException $e) {
        $error = "Error al actualizar el perfil: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .profile-container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 3px solid #007bff;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .profile-header img:hover {
            transform: scale(1.05);
            border-color: #0056b3;
        }
        .profile-info {
            margin-top: 20px;
        }
        .profile-info h2 {
            margin-bottom: 10px;
            font-size: 24px;
            color: #333;
            text-align: center;
        }
        .profile-info p {
            font-size: 16px;
            color: #555;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input[type="file"] {
            border: none;
        }
        .btn {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 14px;
            text-align: center;
        }
        .success {
            color: green;
            font-size: 14px;
            text-align: center;
        }

        /* Estilos para la ventana modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 300px;
            text-align: center;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        .modal-content h3 {
            margin-bottom: 15px;
            font-size: 18px;
            color: #333;
        }
        .modal-content input[type="file"] {
            margin: 20px 0;
            width: 100%;
        }
        .modal-content button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease;
            margin: 5px;
        }
        .modal-content button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include './navBars/navBarAdmin.php'; ?> <!-- Incluir la barra de navegación -->

    <div class="profile-container">
        <!-- Título "Editar Perfil" -->
        <div class="profile-info">
            <h2>Editar Perfil</h2>
        </div>

        <!-- Círculo de la foto de perfil -->
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($imagen_perfil); ?>" alt="Foto de perfil" id="profileImage" onclick="openModal()">
        </div>

        <!-- Formulario de edición de perfil -->
        <div class="profile-info">
            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <?php if (isset($mensaje)): ?>
                <p class="success"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <form method="POST" action="perfil.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>
                </div>
                <div class="form-group">
                    <label for="apellido_paterno">Apellido Paterno:</label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" value="<?php echo htmlspecialchars($apellido_paterno); ?>" required>
                </div>
                <div class="form-group">
                    <label for="apellido_materno">Apellido Materno:</label>
                    <input type="text" id="apellido_materno" name="apellido_materno" value="<?php echo htmlspecialchars($apellido_materno); ?>" required>
                </div>
                <div class="form-group">
                    <label for="edad">Edad:</label>
                    <input type="number" id="edad" name="edad" value="<?php echo htmlspecialchars($edad); ?>" required>
                </div>
                <div class="form-group">
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($correo); ?>" required>
                </div>
                <!-- Input oculto para la imagen de perfil -->
                <input type="file" id="imagen_perfil" name="imagen_perfil" accept="image/png, image/jpeg" style="display: none;">
                <button type="submit" class="btn">Actualizar Perfil</button>
            </form>
        </div>
    </div>

    <!-- Ventana modal para seleccionar la imagen -->
    <div id="imageModal" class="modal">
        <div class="modal-content">
            <h3>Seleccionar imagen de perfil</h3>
            <input type="file" id="modalFileInput" accept="image/png, image/jpeg">
            <button onclick="quitarImagen()">Quitar imagen</button>
            <button onclick="closeModal()">Cerrar</button>
        </div>
    </div>

    <script>
        // Función para abrir la ventana modal
        function openModal() {
            document.getElementById('imageModal').style.display = 'flex';
        }

        // Función para cerrar la ventana modal
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Función para quitar la imagen
        function quitarImagen() {
            document.getElementById('profileImage').src = "/img/default_profile.png"; // Restablecer a la imagen por defecto
            document.getElementById('imagen_perfil').value = ""; // Limpiar el input de archivo
            closeModal();
        }

        // Manejar la selección de archivo en la ventana modal
        document.getElementById('modalFileInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                // Validar el tipo de archivo
                if (file.type === 'image/png' || file.type === 'image/jpeg') {
                    // Actualizar el input oculto del formulario
                    document.getElementById('imagen_perfil').files = event.target.files;

                    // Mostrar la imagen seleccionada en el círculo de perfil
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('profileImage').src = e.target.result;
                    };
                    reader.readAsDataURL(file);

                    // Cerrar la ventana modal
                    closeModal();
                } else {
                    alert('Solo se permiten archivos PNG o JPG.');
                }
            }
        });
    </script>
</body>
</html>