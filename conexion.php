<?php
// conexion.php

$host = "localhost"; // Servidor de la base de datos
$dbname = "mproject"; // Nombre de la base de datos
$username = "root"; // Usuario de la base de datos
$password = ""; // Contraseña de la base de datos

try {
    // Crear una conexión PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Configurar el manejo de errores
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Conexión exitosa"; // Opcional: para verificar la conexión
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>