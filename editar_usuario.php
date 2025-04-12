<?php
// filepath: c:\xampp\htdocs\Prototipo\editar_usuario.php
session_start();
include('config/conexion.php');

// Verificar si hay una sesión válida
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Verificar si es una solicitud POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtener datos del formulario
    $empleado_id = $_POST['empleado_id'];
    $proyecto_id = $_POST['proyecto_id'];
    $salario = $_POST['salario'];

    // Validar salario
    if ($salario < 0 || $salario === '-0') {
        $_SESSION['error'] = "El salario no puede ser negativo o igual a '-0'";
        header("Location: admin_dashboard.php");
        exit();
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Verificar si se está desasignando el proyecto
        if ($proyecto_id === "desasignar") {
            // Primero verificamos si existe registro en empleados_proyectos
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM empleados_proyectos WHERE empleado_id = ?");
            $checkStmt->execute([$empleado_id]);
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                // Eliminar la asignación de proyecto
                $deleteStmt = $pdo->prepare("DELETE FROM empleados_proyectos WHERE empleado_id = ?");
                $deleteStmt->execute([$empleado_id]);
                $_SESSION['mensaje'] = "Usuario actualizado y proyecto desasignado correctamente.";
            } else {
                $_SESSION['mensaje'] = "El usuario no tenía proyectos asignados.";
            }
        } else {
            // Verificar si el empleado ya tiene un proyecto asignado
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM empleados_proyectos WHERE empleado_id = ?");
            $checkStmt->execute([$empleado_id]);
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                // Actualizar el registro existente
                $updateStmt = $pdo->prepare("UPDATE empleados_proyectos SET proyecto_id = ?, salario = ? WHERE empleado_id = ?");
                $updateStmt->execute([$proyecto_id, $salario, $empleado_id]);
            } else {
                // Insertar un nuevo registro
                $insertStmt = $pdo->prepare("INSERT INTO empleados_proyectos (empleado_id, proyecto_id, salario) VALUES (?, ?, ?)");
                $insertStmt->execute([$empleado_id, $proyecto_id, $salario]);
            }
            $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
        }

        // Confirmar la transacción
        $pdo->commit();
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        $_SESSION['error'] = "Error al actualizar el usuario: " . $e->getMessage();
    }

    // Redirigir de vuelta a la página de administración
    header("Location: admin_dashboard.php");
    exit();
} else {
    // Si no es POST, redirigir
    header("Location: admin_dashboard.php");
    exit();
}
?>