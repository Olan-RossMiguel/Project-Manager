<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_proyectos";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el ID del proyecto desde la solicitud GET
$id = $_GET['id'];

// Consulta para obtener los datos del proyecto
$sql = "SELECT * FROM proyectos WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Convertir el resultado a un array asociativo
    $row = $result->fetch_assoc();
    // Devolver los datos en formato JSON
    echo json_encode($row);
} else {
    echo json_encode(["error" => "No se encontró el proyecto"]);
}

$conn->close();
?>