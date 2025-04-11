<?php
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener parámetros de la solicitud
    $id_proyecto = $_POST['id_proyecto'];
    $id_empleado = $_POST['id_empleado'];
    $accion = $_POST['accion'];

    try {
        // Validar que los valores no sean nulos o vacíos
        if (empty($id_proyecto) || empty($id_empleado) || empty($accion)) {
            echo "error: faltan parámetros";
            exit();
        }

        // Ejecutar la acción según el tipo de solicitud (finalizar o eliminar)
        if ($accion == "finalizar") {
            // Finalizar proceso (cambiar estado a 'finalizado')
            $sql = "UPDATE empleados_proyectos SET  estado_id = 2 WHERE proyecto_id = :id_proyecto AND empleado_id = :id_empleado";
        } elseif ($accion == "eliminar") {
            // Eliminar la asignación
            $sql = "DELETE FROM empleados_proyectos WHERE proyecto_id = :id_proyecto AND empleado_id = :id_empleado";
        } else {
            echo "error: acción no válida";
            exit();
        }

        // Preparar y ejecutar la consulta
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_proyecto', $id_proyecto, PDO::PARAM_INT);
        $stmt->bindParam(':id_empleado', $id_empleado, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: no se pudo ejecutar la consulta";
        }
    } catch (PDOException $e) {
        echo "error: " . $e->getMessage();
    }
}
?>


