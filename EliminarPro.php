<?php
include('config\conexion.php'); // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proyecto_id'])) {
    $proyecto_id = $_POST['proyecto_id'];

    try {
        // Verificar si el proyecto está asignado a algún usuario
        $verificarStmt = $pdo->prepare("SELECT COUNT(*) FROM empleados_proyectos WHERE proyecto_id = ?");
        $verificarStmt->execute([$proyecto_id]);
        $proyectoAsignado = $verificarStmt->fetchColumn();

        if ($proyectoAsignado > 0) {
            // Proyecto asignado, no se puede eliminar
            echo "<script>
                    alert('El proyecto está asignado a uno o más usuarios. No se puede eliminar.');
                    window.history.back();
                  </script>";
            exit();
        }

        // Eliminar el proyecto si no está asignado
        $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = ?");
        $stmt->execute([$proyecto_id]);

        // Redirigir con mensaje de éxito
        echo "<script>
                alert('Proyecto eliminado correctamente.');
                window.location.href='admin_dashboard.php';
              </script>";
        exit();
    } catch (PDOException $e) {
        echo "Error al eliminar el proyecto: " . $e->getMessage();
    }
} else {
    echo "ID de proyecto no válido.";
}
?>
