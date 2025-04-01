<?php
session_start(); // Iniciar la sesión

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Incluir la conexión a la base de datos
require 'conexion.php';

// Obtener la lista de proyectos y roles del usuario desde la base de datos
try {
    $usuario_id = $_SESSION['usuario_id'];
    $sql = "
        SELECT p.id AS proyecto_id, p.nombre AS proyecto_nombre, r.nombre AS rol_nombre
        FROM usuarios_proyectos up
        JOIN proyectos p ON up.proyecto_id = p.id
        JOIN roles_proyecto r ON up.rol_id = r.id
        WHERE up.usuario_id = :usuario_id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al obtener proyectos: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="./css/style.css">
    <title>Seleccionar Proyecto</title>
</head>
<body>
    <img class="wave" src="img/wave.png">
    <div class="container">
        <div class="img">
            <img src="./img/gestion.png">
        </div>
        <div class="login-content">
            <form method="post" action="login_usuario_process.php">
                <img class="avatar" src="./img/avataruser.png">
                <h2 class="title">BIENVENIDO, <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></h2>
                

                <!-- Mostrar mensajes de error -->
                <?php
                if (isset($_GET['error'])) {
                    $error = $_GET['error'];
                    $mensaje = "";

                    switch ($error) {
                        case 'proyecto_no_seleccionado':
                            $mensaje = "Por favor, seleccione un proyecto";
                            break;
                        default:
                            $mensaje = "Error desconocido.";
                            break;
                    }

                    echo "<div style='color: red; text-align: center; margin-bottom: 15px;'>$mensaje</div>";
                }
                ?>

                <div class="input-div one">
                    <div class="i">
                        <i class="fas fa-folder"></i>
                    </div>
                    <div class="div">
                      
                        <select id="proyecto" name="proyecto" class="input" required>
                            <option value="">Seleccione un proyecto</option>
                            <?php foreach ($proyectos as $proyecto): ?>
                                <option value="<?php echo $proyecto['proyecto_id']; ?>">
                                    <?php echo htmlspecialchars($proyecto['proyecto_nombre']); ?> (<?php echo htmlspecialchars($proyecto['rol_nombre']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <input name="btnseleccionar" class="btn" type="submit" value="INGRESAR">
            </form>
        </div>
    </div>
</body>
</html>