<?php
// menuAdmin.php
session_start();

// Verificación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';

// Obtener datos del usuario
try {
    $stmt = $conn->prepare("SELECT nombre, apellido_paterno, imagen_perfil FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['usuario_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }
    
    $profilePic = !empty($user['imagen_perfil']) ? $user['imagen_perfil'] : '/img/default_profile.png';
    $userName = htmlspecialchars($user['nombre'] . ' ' . $user['apellido_paterno']);

} catch(PDOException $e) {
    error_log("Error de base de datos: " . $e->getMessage());
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Manager</title>
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="./css/styles.css"> <!-- Tus estilos actuales -->
    <link rel="stylesheet" href="./css/forms.css">

    <link rel="stylesheet" href="./css/reports.css">

</head>
<body>
    <!-- SIDEBAR (Manteniendo tu estructura actual) -->
    <section id="sidebar" class="sidebar">
        <a href="#" class="brand" data-view="dashboard">Project Manager</a>
        <ul class="side-menu">
            
            <li class="divider" data-text="main">Main</li>
            <li><a href="#" data-view="calendario"><i class='bx bxs-calendar icon'></i> Calendario</a></li>
          
            <li class="divider" data-text="Tablas y Formularios">Tablas y formularios</li>
            
            <li>
                <a href="#"><i class='bx bx-table icon'></i> Reportes<i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <li><a href="#" data-view="reporte-proyecto-usuario">Proyecto</a></li>
                    
                </ul>
            </li>
            <li>
                <a href="#"><i class='bx bxs-folder-plus icon'></i>Actualizar<i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <li><a href="#" data-view="descripcion-actualizacion-actividades">Tus actividades</a></li>
                    
                </ul>
            </li>
        </ul>
    </section>

    <!-- CONTENIDO PRINCIPAL (Manteniendo tu estructura) -->
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
                <img src="<?= $profilePic ?>" alt="Profile" class="profile-img">
                <ul class="profile-link">
                    <li><a href="#"><i class='bx bxs-user-circle icon'></i> <?= $userName ?></a></li>
                    <li><a href="perfil.php"><i class='bx bxs-cog'></i> Configuración</a></li>
                    <li><a href="logout.php"><i class='bx bxs-log-out-circle'></i> Cerrar sesión</a></li>
                </ul>
            </div>
        </nav>

        <!-- MAIN CONTENT - Área dinámica -->
        <main>
            <!-- Todas las vistas se cargarán aquí -->
           
            <div id="calendario" class="content-view" style="display:none;"></div>
          
            <div id="reporte-proyecto-usuario" class="content-view" style="display:none;"></div>
            <div id="descripcion-actualizacion-actividades" class="content-view" style="display:none;"></div>
           
        </main>
    </section>
<!-- Modal de Confirmación -->
<div id="modal-confirmacion" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modal-title">Notificación</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div id="modal-mensaje" style="padding: 20px 0;"></div>
        <div class="modal-footer" style="text-align: right;">
            <button id="modal-aceptar" style="padding: 8px 16px; background: #4c2252; color: white; border: none; border-radius: 4px; cursor: pointer;">Aceptar</button>
        </div>
    </div>
</div>

    <!-- JavaScript (Mínimo necesario) -->
    <script>
    // Configuración global
    const APP_CONFIG = {
        userId: <?= $_SESSION['usuario_id'] ?>,
        baseUrl: '<?= rtrim($_SERVER['REQUEST_URI'], '/') ?>',
        apiBase: '/Project-Manager/api' // Añade esta línea
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.0/dist/frappe-gantt.min.js"></script>
    <script src="./js/main.js"></script>


<!--     <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> -->
    <script src="./js/js/script.js"></script>

<!--     <script src="./js/app.js"></script> -->
</body>
</html>