<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/navBarTables.css">
    <link rel="stylesheet" href="../css/main.css">

    
    <title>Gestión de Proyectos</title>
    <style>
      
    </style>
</head>
<body>
<nav class="navbar">
        <div class="logo"> <img src="../img/logo.png" alt="">
        <button class="dropbtn"><a href="../menuUsers.html">Inicio</a></button>
    </div>
        <div class="dropdowns">
     
            <div class="dropdown">
                <button class="dropbtn">Reportes</button>
                <div class="dropdown-content">
                    <a href="./reportesLeader/reportes_proyectos.php">Proyectos</a>
                    <a href="reportes_actividades.php">Actividades</a>
                    
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
echo "<tr><th>Objetivo</th><th>Descripción</th><th>Estado</th><th>Inicio</th><th>Fin</th></tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row["objetivo"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["descripcion"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["estado"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["inicio"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["fin"]) . "</td>";
       
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6'>No hay proyectos registrados</td></tr>";
}

echo "</table>";

$conn->close();
?>





</body>
</html>