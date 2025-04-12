<?php
include('config\conexion.php'); // Incluye la conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_proyecto = $_POST['proyecto'];  // Nombre del proyecto
    $descripcion = $_POST['descripcion'];   // Descripción del proyecto

    try {
        $stmt = $pdo->prepare("INSERT INTO proyectos (nombre, descripcion) VALUES (?, ?)");
        $stmt->execute([$nombre_proyecto, $descripcion]);

        
        header("Location: admin_dashboard.php?success=proyecto_creado");
        exit();
    } catch (PDOException $e) {
        echo "Error al crear el proyecto: " . $e->getMessage();
    }
}
?>
