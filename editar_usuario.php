<?php
include('conexion.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $empleado_id = $_POST['empleado_id'];
    $proyecto_id = $_POST['proyecto_id']; 
    $nuevo_salario = !empty($_POST['salario']) ? $_POST['salario'] : null;

    try {
        $stmt = $pdo->prepare("CALL editar_usuario(?, ?, ?)");
        $stmt->bindParam(1, $empleado_id, PDO::PARAM_INT);
        $stmt->bindParam(2, $proyecto_id, PDO::PARAM_INT);
        $stmt->bindParam(3, $nuevo_salario, PDO::PARAM_STR);

        if ($stmt->execute()) {
            header("Location: admin_dashboard.php?success=usuario_actualizado");
            exit();
        } else {
            echo "Error al actualizar el usuario.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
