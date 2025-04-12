<?php
// filepath: c:\xampp\htdocs\Prototipo\eliminar_usuario.php
session_start();
include('config/conexion.php');

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $empleado_id = $_GET['id'];

    try {
        // Primero verificamos si el usuario tiene proyectos asignados
        $checkProyecto = "SELECT COUNT(*) as proyectos_count 
                          FROM empleados_proyectos 
                          WHERE empleado_id = :empleado_id";
        $stmtCheck = $pdo->prepare($checkProyecto);
        $stmtCheck->bindParam(':empleado_id', $empleado_id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        // Si tiene proyectos asignados, no permitimos la eliminación
        if ($result['proyectos_count'] > 0) {
            $_SESSION['error'] = "No se puede eliminar este usuario porque tiene proyectos asignados. Desasígnele los proyectos primero.";
            header("Location: admin_dashboard.php");
            exit();
        }

        // Iniciar una transacción para garantizar la integridad de los datos
        $pdo->beginTransaction();

        // Con ON DELETE CASCADE, ya no es necesario eliminar manualmente los registros de bitácora
        // La base de datos lo hará automáticamente

        // Eliminar el usuario usando el procedimiento almacenado
        $sql = "CALL eliminar_empleado(:empleado_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':empleado_id', $empleado_id, PDO::PARAM_INT);
        $stmt->execute();

        // Confirmar transacción
        $pdo->commit();
        
        $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
    } catch (PDOException $e) {
        // Revertir cambios en caso de error
        $pdo->rollBack();
        $_SESSION['error'] = "Error al eliminar el usuario: " . $e->getMessage();
    }

    header("Location: admin_dashboard.php");
    exit();
} else {
    $_SESSION['error'] = "Solicitud inválida.";
    header("Location: admin_dashboard.php");
    exit();
}
?>