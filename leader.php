<?php
// menuAdmin.php
session_start();

// Verificación más estricta de la sesión
$requiredSessionVars = ['usuario_id', 'user_id', 'usuario_nombre'];
$hasValidSession = false;

foreach ($requiredSessionVars as $var) {
    if (isset($_SESSION[$var])) {
        $hasValidSession = true;
        break;
    }
}

// Redirigir SI NO hay sesión válida
if (!$hasValidSession) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';

try {
    // Obtener ID del usuario (compatible con ambos nombres de variable)
    $userId = $_SESSION['usuario_id'] ?? $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        header("Location: login.php");
        exit();
    }

    // Consulta PDO
    $query = "SELECT nombre, apellido_paterno, imagen_perfil FROM usuarios WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Usuario no encontrado en DB - destruir sesión
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
    
    $profilePic = !empty($user['imagen_perfil']) ? $user['imagen_perfil'] : '/img/default_profile.png';
    $userName = $_SESSION['usuario_nombre'] ?? $user['nombre'] ?? 'Usuario';
    
} catch(PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    header("Location: login.php");
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
   
    <link rel="stylesheet" href="./css/styles.css">
    <title>AdminSite</title>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">Project Manager</a>
        <ul class="side-menu">
            <li><a href="#" class="active"><i class='bx bxs-dashboard icon'></i> Dashboard</a></li>
            <li class="divider" data-text="main">Main</li>
            <li><a href="#"><i class='bx bxs-calendar icon'></i> Calendario</a></li>
            <li><a href="#"><i class='bx bxs-chart icon'></i> Cronogramas</a></li>

            <li class="divider" data-text="Tablas y Formularios">Tablas y formularios</li>
            
            <li>
                <a href="#"><i class='bx bx-table icon'></i> Reportes<i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <li><a href="#">Usuarios</a></li>
                    <li><a href="#">Detalles de Proyectos</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><i class='bx bxs-folder-plus icon'></i> Crear <i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <li><a href="../crearUsuario.php">Crear Usuario</a></li>
                    <li><a href="#">Crear Proyecto</a></li>
                </ul>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- NAVBAR -->
    <section id="content">
        <nav>
            <i class='bx bx-menu toggle-sidebar'></i>
            <form action="#">
                <div class="form-group">
                    <input type="text" placeholder="Search...">
                    <i class='bx bx-search icon'></i>
                </div>
            </form>
            <a href="#" class="nav-link">
                <i class='bx bxs-bell icon'></i>
                <span class="badge">5</span>
            </a>
            <a href="#" class="nav-link">
                <i class='bx bxs-message-square-dots icon'></i>
                <span class="badge">8</span>
            </a>
            <span class="divider"></span>
            <div class="profile">
                <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Foto de perfil" class="profile-img">
                <ul class="profile-link">
                    <li><a href="#"><i class='bx bxs-user-circle icon'></i> <?php echo htmlspecialchars($userName); ?></a></li>
                    <li><a href="perfil.php"><i class='bx bxs-cog'></i> Configuración</a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </nav>

        <!-- MAIN CONTENT -->
        <main>
            <h1 class="title">Dashboard</h1>
            <ul class="breadcrumbs">
                <li><a href="#">Home</a></li>
                <li class="divider">/</li>
                <li><a href="#" class="active">Dashboard</a></li>
            </ul>

           
        </main>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="./js/script.js"></script>
</body>
</html>