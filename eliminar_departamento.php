<?php
// filepath: c:\xampp\htdocs\Prototipo\eliminar_departamento.php
session_start();
require_once('config/conexion.php');

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validar que se haya seleccionado un departamento
    if (empty($_POST['departamento_id'])) {
        $_SESSION['error'] = "Debe seleccionar un departamento para eliminar.";
        header("Location: admin_dashboard.php");
        exit();
    }
    
    // Verificar confirmación
    if (!isset($_POST['confirmar']) || $_POST['confirmar'] != '1') {
        $_SESSION['error'] = "Debe confirmar la eliminación del departamento.";
        header("Location: admin_dashboard.php");
        exit();
    }
    
    $departamento_id = $_POST['departamento_id'];
    
    try {
        // Verificar si hay empleados asociados al departamento
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados WHERE departamento_id = ?");
        $stmt->execute([$departamento_id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = "No se puede eliminar el departamento porque tiene empleados asignados.";
            header("Location: admin_dashboard.php");
            exit();
        }
        
        // Eliminar el departamento
        $stmt = $pdo->prepare("DELETE FROM departamentos WHERE id = ?");
        $stmt->execute([$departamento_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['mensaje'] = "Departamento eliminado exitosamente.";
        } else {
            $_SESSION['error'] = "El departamento no existe o no pudo ser eliminado.";
        }
        
        header("Location: admin_dashboard.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al eliminar el departamento: " . $e->getMessage();
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Si no es POST, redirigir
header("Location: admin_dashboard.php");
exit();
?>