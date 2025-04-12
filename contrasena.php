<?php
include('config/conexion.php');
session_start();

$rol = $_SESSION['rol'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cambiar_contrasena'])) {
    $usuario_id = $_SESSION['user_id'];
    $contrasena_actual = $_POST['contrasena_actual'];
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    // Validar que la nueva contraseña y la confirmación coincidan
    if ($nueva_contrasena != $confirmar_contrasena) {
        echo "<script>alert('La nueva contraseña no coincide.'); window.history.back();</script>";
        exit();
    }

    try {
        // Obtener la contraseña actual de la base de datos
        $stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id = :usuario_id");
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar la contraseña actual
        if (!$resultado || !password_verify($contrasena_actual, $resultado['password_hash'])) {
            echo "<script>alert('La contraseña actual es incorrecta.'); window.history.back();</script>";
            exit();
        }

        // Validar que la nueva contraseña no sea igual a la actual
        if (password_verify($nueva_contrasena, $resultado['password_hash'])) {
            echo "<script>alert('La nueva contraseña no puede ser igual a la contraseña actual.'); window.history.back();</script>";
            exit();
        }

        // Generar el hash de la nueva contraseña
        $nueva_contrasena_hash = password_hash($nueva_contrasena, PASSWORD_BCRYPT);

        // Iniciar transacción
        $pdo->beginTransaction();

        // Actualizar la contraseña
        $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = :nueva_contrasena WHERE id = :usuario_id");
        $stmt->bindParam(':nueva_contrasena', $nueva_contrasena_hash, PDO::PARAM_STR);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();

        // Datos para la bitácora
        $direccion_ip = $_SERVER['REMOTE_ADDR'];
        $navegador = $_SERVER['HTTP_USER_AGENT'];
        $fecha_cambio = date("Y-m-d H:i:s");

        // Registrar en la bitácora
        $stmtBitacora = $pdo->prepare("INSERT INTO bitacora_cambios_contrasena (usuario_id, fecha_cambio, direccion_ip, navegador) 
                                       VALUES (:usuario_id, :fecha_cambio, :direccion_ip, :navegador)");
        $stmtBitacora->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmtBitacora->bindParam(':fecha_cambio', $fecha_cambio, PDO::PARAM_STR);
        $stmtBitacora->bindParam(':direccion_ip', $direccion_ip, PDO::PARAM_STR);
        $stmtBitacora->bindParam(':navegador', $navegador, PDO::PARAM_STR);
        $stmtBitacora->execute();

        // Confirmar transacción
        $pdo->commit();

        // Redirección según el rol
        if ($rol == 'usuario') {
            echo "<script>alert('Contraseña actualizada correctamente.'); window.location.href='user_dashboard.php';</script>";
        } else {
            echo "<script>alert('Contraseña actualizada correctamente.'); window.location.href='admin_dashboard.php';</script>";
        }
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
}
?>
