<?php
// filepath: c:\xampp\htdocs\Prototipo\agregar_departamento.php
session_start();
require_once('config/conexion.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar datos
    if (empty($_POST['nombre'])) {
        $_SESSION['error'] = "El nombre del departamento es obligatorio.";
        header("Location: admin_dashboard.php");
        exit();
    }
    
    $nombre = trim($_POST['nombre']);
    
    try {
        // Verificar si ya existe un departamento con el mismo nombre
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM departamentos WHERE nombre = ?");
        $stmt->execute([$nombre]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Ya existe un departamento con ese nombre.";
            header("Location: admin_dashboard.php");
            exit();
        }
        
        // Insertar el nuevo departamento (solo nombre)
        $stmt = $pdo->prepare("INSERT INTO departamentos (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        
        $_SESSION['mensaje'] = "Departamento creado exitosamente.";
        header("Location: admin_dashboard.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al crear el departamento: " . $e->getMessage();
        header("Location: admin_dashboard.php");
        exit();
    }
}

header("Location: admin_dashboard.php");
exit();
?>