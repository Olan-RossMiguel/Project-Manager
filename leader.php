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

    // Consulta PDO para obtener los datos del usuario
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

    // Obtener el nombre del proyecto
    $proyectoNombre = 'Proyecto'; // Valor predeterminado

    if (isset($_SESSION['proyecto_id'])) {
        $proyectoId = $_SESSION['proyecto_id'];

        // Consulta PDO para obtener el nombre del proyecto
        $stmtProyecto = $conn->prepare("SELECT nombre FROM proyectos WHERE id = :id");
        $stmtProyecto->bindParam(':id', $proyectoId, PDO::PARAM_INT);
        $stmtProyecto->execute();

        $proyecto = $stmtProyecto->fetch(PDO::FETCH_ASSOC);

        if ($proyecto && !empty($proyecto['nombre'])) {
            $proyectoNombre = $proyecto['nombre'];
        }
    }
} catch (PDOException $e) {
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
    <link rel="stylesheet" href="./css/mesagge.css">
    <link rel="stylesheet" href="./css/forms.css">
    <link rel="stylesheet" href="./css/reports.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.5.0/dist/frappe-gantt.min.css">
    <title>AdminSite</title>
</head>

<body>
    <section id="sidebar">
        <a href="#" class="brand">Project Manager</a>
        <ul class="side-menu">
            <li><a href="#" class="active"><i class='bx bxs-dashboard icon'></i> Dashboard</a></li>
            <li class="divider" data-text="main">Main</li>
            <li><a href="#"><i class='bx bxs-calendar icon'></i> Calendario</a></li>
            <li class="divider" data-text="Tablas y Formularios">Tablas y formularios</li>
            <li>
                <a href="#"><i class='bx bx-table icon'></i> Reportes<i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <li><a href="#" data-view="reporte-equipo">Equipo</a></li>
                    <li><a href="#" data-view="reporte-actividades">Actividades</a></li>
                    <li><a href="#" data-view="reporte-lider-proyecto">Proyecto</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><i class='bx bxs-folder-plus icon'></i> Agregar <i class='bx bx-chevron-right icon-right'></i></a>
                <ul class="side-dropdown">
                    <li><a href="#" data-view="detallar-proyecto" class="nav-link detalle-proyecto" data-proyecto-id="<?= htmlspecialchars($_SESSION['proyecto_id']) ?>">Detalles</a></li>
                    <li><a href="#" data-view="agregar-integrantes">Integrantes</a></li>
                    <li><a href="#" data-view="agregar-actividades">Actividades</a></li>
                    <li><a href="#" data-view="asignar-actividades">Asignar Actividades</a></li>
                </ul>
            </li>
        </ul>
    </section>
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

        <main>
            <div class="welcome-message">
                <p>Bienvenido a: <?php echo htmlspecialchars($proyectoNombre); ?></p>
            </div>

            <div id="dashboard" class="content-view"></div>
            <div id="calendario" class="content-view" style="display:none;"></div>
            <div id="reporte-equipo" class="content-view" style="display:none;"></div>
            <div id="reporte-actividades" class="content-view" style="display:none;"></div>
            <div id="reporte-lider-proyecto" class="content-view" style="display:none;">
                <?php include('./views/reporte-lider-proyecto.html'); ?>
            </div>
            <div id="detallar-proyecto" class="content-view" style="display:none;"></div>
            <div id="agregar-actividades" class="content-view" style="display:none;"></div>
            <div id="agregar-integrantes" class="content-view" style="display:none;"></div>
            <div id="asignar-actividades" class="content-view" style="display:none;"></div>
        </main>
    </section>
    <div id="modal-confirmacion" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Notificación</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div id="modal-mensaje" style="padding: 20px 0;"></div>
            <div class="modal-footer" style="text-align: right;">
                <button id="modal-aceptar" class="btn-primary" style="display: none;">Aceptar</button>
            </div>
        </div>
    </div>
    <script src="./js/reporteLiderProyecto.js"></script>
<!--     <script>
      
        
        // Abrir modal (se llama desde alguna acción en tu script principal)
        function abrirModalCronograma() {
            document.getElementById("modal-cronograma").classList.add("active");
        }

        // Cerrar modal al hacer clic en la X
        document.getElementById("cerrar-modal").addEventListener("click", function () {
            document.getElementById("modal-cronograma").classList.remove("active");
        });

        // Cerrar modal al hacer clic fuera del contenido
        window.addEventListener("click", function (event) {
            const modal = document.getElementById("modal-cronograma");
            if (event.target === modal) {
                modal.classList.remove("active");
            }
        });
    </script> -->

   <!-- Elimina el script de manejo del modal que está al final -->
<!-- Reemplázalo con esto: -->
<script>
// Configuración global
const APP_CONFIG = {
    userId: <?= $userId ?>,
    baseUrl: '<?= rtrim($_SERVER['REQUEST_URI'], '/') ?>',
    apiBase: '/Project-Manager/api',
    proyectoId: <?= $_SESSION['proyecto_id'] ?? 'null' ?>
};
</script>

<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.0/dist/frappe-gantt.min.js"></script>
<script src="./js/main.js" defer></script>
<script src="./js/js/script.js" defer></script>
</body>

</html>