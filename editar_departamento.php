<?php
// filepath: c:\xampp\htdocs\Prototipo\editar_departamento.php
session_start();
require_once('config/conexion.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar datos
    if (empty($_POST['departamento_id']) || empty($_POST['nuevo_nombre'])) {
        $_SESSION['error'] = "Todos los campos obligatorios deben ser completados.";
        header("Location: admin_dashboard.php");
        exit();
    }
    
    $departamento_id = $_POST['departamento_id'];
    $nuevo_nombre = trim($_POST['nuevo_nombre']);
    
    try {
        // Verificar si el departamento existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM departamentos WHERE id = ?");
        $stmt->execute([$departamento_id]);
        if ($stmt->fetchColumn() == 0) {
            $_SESSION['error'] = "El departamento seleccionado no existe.";
            header("Location: admin_dashboard.php");
            exit();
        }
        
        // Verificar si ya existe otro departamento con el mismo nombre
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM departamentos WHERE nombre = ? AND id != ?");
        $stmt->execute([$nuevo_nombre, $departamento_id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "Ya existe otro departamento con ese nombre.";
            header("Location: admin_dashboard.php");
            exit();
        }
        
        // Actualizar el departamento (solo nombre)
        $stmt = $pdo->prepare("UPDATE departamentos SET nombre = ? WHERE id = ?");
        $stmt->execute([$nuevo_nombre, $departamento_id]);
        
        $_SESSION['mensaje'] = "Departamento actualizado exitosamente.";
        header("Location: admin_dashboard.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al actualizar el departamento: " . $e->getMessage();
        header("Location: admin_dashboard.php");
        exit();
    }
}

header("Location: admin_dashboard.php");
exit();
?>