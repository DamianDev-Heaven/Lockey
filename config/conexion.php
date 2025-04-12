<?php
// Configuraci�n de la base de datos
$host = 'localhost';      // Direcci�n del servidor de base de datos
$usuario = 'root';        // Nombre de usuario de MySQL
$contrasena = '';         // Contrase�a de MySQL
$baseDeDatos = 'lockey'; // Nombre de la base de datos

try {
    $pdo = new PDO("mysql:host=$host;dbname=$baseDeDatos", $usuario, $contrasena);
    // Habilitar el modo de excepciones
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexi�n: " . $e->getMessage());
}
?>
