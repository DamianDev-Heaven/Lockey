<?php
session_start();
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['id'])) {
    $empleado_id = $_GET['id'];

    try {
        $sql = "CALL eliminar_empleado(:empleado_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':empleado_id', $empleado_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
    } catch (PDOException $e) {
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
