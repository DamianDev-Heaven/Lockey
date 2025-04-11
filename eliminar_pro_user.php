<?php
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_proyecto = $_POST['id_proyecto'];
    $id_empleado = $_POST['id_empleado'];

    try {
        $sql = "DELETE FROM asignaciones WHERE proyecto_id = :id_proyecto AND empleado_id = :id_empleado";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_proyecto', $id_proyecto, PDO::PARAM_INT);
        $stmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "<script>
                    alert('Registro eliminado correctamente');
                    window.location.href='admin_dashboard.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al eliminar el registro');
                    window.history.back();
                  </script>";
        }
    } catch (PDOException $e) {
        echo "<script>
                alert('Error: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
    }
}
?>
