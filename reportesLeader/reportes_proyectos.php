<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="../css/navBarTables.css">
    <title>Gestión de Proyectos</title>
    <style>
      
    </style>
</head>
<body>
<nav class="navbar">
        <div class="logo"> <img src="../img/logo.png" alt="">
        <button class="dropbtn"><a href="../leader.php">Inicio</a></button>
    </div>
        <div class="dropdowns">
     
            <div class="dropdown">
                <button class="dropbtn">Reportes</button>
                <div class="dropdown-content">
                    <a href="./reportesLeader/reportes_proyectos.php">Proyectos</a>
                    <a href="reportes_actividades.php">Actividades</a>
                    <a href="reportes_equipos.php">Equipos</a>
                </div>
            </div>
            <div class="dropdown">
                <button class="dropbtn"> Usuario</button>
                <div class="dropdown-content">
                    <a href="#">Ver Información</a>
                    <a href="#">Salir</a>
                </div>
            </div>
        </div>
    </nav>
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_proyectos";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$sql = "SELECT * FROM proyectos";
$result = $conn->query($sql);



echo "<center><h2>Proyectos</h2></center>";
echo "</br>";

echo "<table>";
echo "<tr><th>Objetivo</th><th>Descripción</th><th>Estado</th><th>Inicio</th><th>Fin</th><th>Acciones</th></tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["objetivo"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["descripcion"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["estado"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["inicio"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["fin"]) . "</td>";
        echo "<td><button onclick='abrirModal(" . $row["id"] . ")'>Editar</button></td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No hay proyectos registrados</td></tr>";
}

echo "</table>";

$conn->close();
?>

<!-- Modal -->
<div id="modal">
    <div class="modal-contenedor">
        <button onclick="cerrarModal()" class="modal-cerrar">✖</button>
        <center><h2>Editar Proyecto</h2></center>
        <br>
        <form id="form-editar-proyecto" class="formulario" method="POST">
            <input type="hidden" id="id-proyecto" name="id-proyecto">

            <label for="objetivo">Objetivo</label>
            <input type="text" id="objetivo" name="objetivo" class="formulario__input" required>

            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3" class="formulario__textarea" required></textarea>

            <label for="estado">Estado del Proyecto</label>
            <select id="estado" name="estado" class="formulario__select">
                <option value="pendiente">Pendiente</option>
                <option value="en-progreso">En Progreso</option>
                <option value="finalizado">Finalizado</option>
            </select>

            <label for="inicio">Fecha de Inicio</label>
            <input type="date" id="inicio" name="inicio" class="formulario__input" required>

            <label for="fin">Fecha de Finalización</label>
            <input type="date" id="fin" name="fin" class="formulario__input">

            <button type="submit" name="actualizar-proyecto" class="formulario__button">Actualizar Proyecto</button>
        </form>
    </div>
</div>

<script>
function abrirModal(id) {
    fetch(`obtener_proyecto.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error("Error al obtener datos del proyecto");
            }
            return response.json();
        })
        .then(data => {
            // Rellenar el formulario con los datos del proyecto
            document.getElementById("id-proyecto").value = data.id;
            document.getElementById("objetivo").value = data.objetivo;
            document.getElementById("descripcion").value = data.descripcion;
            document.getElementById("estado").value = data.estado;
            document.getElementById("inicio").value = data.inicio;
            document.getElementById("fin").value = data.fin;

            // Mostrar el modal
            document.getElementById("modal").style.display = "block";
        })
        .catch(error => {
            alert("Error al cargar el proyecto: " + error.message);
        });
}

function cerrarModal() {
    document.getElementById("modal").style.display = "none";
}
</script>

</body>
</html>
