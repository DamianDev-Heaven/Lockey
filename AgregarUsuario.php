<?php
include('conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $salario = $_POST['salario'];
    $proyecto_id = !empty($_POST['proyecto_id']) ? $_POST['proyecto_id'] : null;
    $departamento_id = $_POST['departamento_id'];
    $u_username = $_POST['nombreUsuario'];
    $u_contrasenha = $_POST['contrasena'];
    $u_rol = $_POST['Roles_id'];
    
    // Encriptar la contraseña utilizando password_hash
    $hashed_contrasenha = password_hash($u_contrasenha, PASSWORD_BCRYPT);
    
    $estado_id = 1;
    $fecha_asignacion = date("Y-m-d H:i:s");

    try {
        // Llamar a la función almacenada con la contraseña encriptada
        $sql = "CALL agregar_usuario(:nombre, :salario, :proyecto_id, :departamento_id, 
                                    :nombreUsuario, :contrasena, :Roles_id, :estado_id, :fecha_asignacion)";
        $stmt = $pdo->prepare($sql);
        
        // Asignar los parámetros
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':salario', $salario);
        $stmt->bindParam(':proyecto_id', $proyecto_id, PDO::PARAM_INT);
        $stmt->bindParam(':departamento_id', $departamento_id, PDO::PARAM_INT);
        $stmt->bindParam(':nombreUsuario', $u_username);
        $stmt->bindParam(':contrasena', $hashed_contrasenha);  // Se pasa la contraseña encriptada
        $stmt->bindParam(':Roles_id', $u_rol, PDO::PARAM_INT);
        $stmt->bindParam(':estado_id', $estado_id, PDO::PARAM_INT);
        $stmt->bindParam(':fecha_asignacion', $fecha_asignacion);
        
        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "<script>
                    alert('Usuario agregado correctamente');
                    window.location.href='admin_dashboard.php';
                  </script>";
            exit();
        } else {
            echo "<script>
                    alert('Error al agregar usuario');
                    window.history.back();
                  </script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>
                alert('Error: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
        exit();
    }
}
?>
