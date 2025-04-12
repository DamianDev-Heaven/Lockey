<?php
include('config/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empleado_id = $_POST['usuario_id'];  
    $proyecto_id = $_POST['proyecto_id']; 
    $salario = $_POST['salario'];
    $fecha_asignacion = $_POST['fecha_asignacion'];  

    try {
        // Verificar si el proyecto ya está asignado al empleado
        $verificarStmt = $pdo->prepare("SELECT COUNT(*) FROM empleados_proyectos WHERE empleado_id = ? AND proyecto_id = ?");
        $verificarStmt->execute([$empleado_id, $proyecto_id]);
        $proyectoExistente = $verificarStmt->fetchColumn();

        if ($proyectoExistente > 0) {
            echo "<script>
                    alert('Este proyecto ya está asignado a este usuario. Elija otro proyecto.');
                    window.history.back();
                  </script>";
            exit();
        }

        // Preparar la llamada al procedimiento almacenado
        $stmt = $pdo->prepare("CALL asignar_proyecto_a_empleado(?, ?, ?, ?)");

        // Vincular los parámetros
        $stmt->bindParam(1, $empleado_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $proyecto_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $salario, PDO::PARAM_STR); 
        $stmt->bindParam(4, $fecha_asignacion, PDO::PARAM_STR);   

        if ($stmt->execute()) {
            echo "<script>
                    alert('Proyecto asignado correctamente.');
                    window.location.href='admin_dashboard.php';
                  </script>";
            exit();
        } else {
            echo "<script>
                    alert('Error al asignar el proyecto.');
                    window.history.back();
                  </script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "Error al asignar el proyecto y salario: " . $e->getMessage();
    }
}
?>

