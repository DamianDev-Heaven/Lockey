<?php
session_start();

// Verificar autenticación
if (!isset($_SESSION['username']) || !isset($_SESSION['empleado_id'])) {
    echo "no_auth";
    exit();
}

// Validar datos recibidos
if (!isset($_POST['id_proyecto']) || !isset($_POST['id_empleado']) || !isset($_POST['accion'])) {
    echo "invalid_params";
    exit();
}

$id_proyecto = filter_var($_POST['id_proyecto'], FILTER_VALIDATE_INT);
$id_empleado = filter_var($_POST['id_empleado'], FILTER_VALIDATE_INT);
$accion = $_POST['accion'];

// Validar los IDs
if ($id_proyecto === false || $id_empleado === false) {
    echo "invalid_data";
    exit();
}

// Conectar a la base de datos
include('config/conexion.php');

try {
    if ($accion === 'finalizar') {
        // Primero verificamos si el proyecto ya está finalizado
        $checkStmt = $pdo->prepare("SELECT estado_id FROM empleados_proyectos 
                                  WHERE proyecto_id = :id_proyecto AND empleado_id = :id_empleado");
        $checkStmt->bindParam(':id_proyecto', $id_proyecto, PDO::PARAM_INT);
        $checkStmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $checkStmt->execute();
        $currentStatus = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        // Si ya está finalizado (estado_id = 2), informamos que ya está completado
        if ($currentStatus && $currentStatus['estado_id'] == 2) {
            echo "already_finished";
            exit();
        }
        
        // Actualizar el estado del proyecto a "finalizado" (estado_id = 2)
        $stmt = $pdo->prepare("UPDATE empleados_proyectos 
                              SET estado_id = 2 
                              WHERE proyecto_id = :id_proyecto AND empleado_id = :id_empleado");
        $stmt->bindParam(':id_proyecto', $id_proyecto, PDO::PARAM_INT);
        $stmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();
        
    } elseif ($accion === 'eliminar') {
        // Eliminar la asignación del proyecto al empleado
        $stmt = $pdo->prepare("DELETE FROM empleados_proyectos 
                              WHERE proyecto_id = :id_proyecto AND empleado_id = :id_empleado");
        $stmt->bindParam(':id_proyecto', $id_proyecto, PDO::PARAM_INT);
        $stmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        echo "invalid_action";
        exit();
    }
    
    echo "success";
    
} catch (PDOException $e) {
    echo "error: " . $e->getMessage();
}
?>