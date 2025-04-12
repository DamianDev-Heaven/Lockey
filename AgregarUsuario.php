<?php
session_start();
include('config/conexion.php'); // Corregido el separador de directorios

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar campos requeridos
    $campos_requeridos = [
        'nombre' => 'Nombre completo',
        'salario' => 'Salario',
        'departamento_id' => 'Departamento',
        'nombreUsuario' => 'Nombre de usuario',
        'contrasena' => 'Contraseña',
        'Roles_id' => 'Rol'
    ];
    
    $errores = [];
    
    // Verificar campos vacíos
    foreach ($campos_requeridos as $campo => $etiqueta) {
        if (empty($_POST[$campo])) {
            $errores[] = "El campo {$etiqueta} es obligatorio.";
        }
    }
    
    // Validación específica del salario
    if (isset($_POST['salario']) && (!is_numeric($_POST['salario']) || $_POST['salario'] < 0 || $_POST['salario'] === '-0')) {
        $errores[] = "El salario debe ser un número positivo.";
    }
    
    // Validación de nombre de usuario (al menos 4 caracteres)
    if (isset($_POST['nombreUsuario']) && strlen($_POST['nombreUsuario']) < 4) {
        $errores[] = "El nombre de usuario debe tener al menos 4 caracteres.";
    }
    
    // Validación de contraseña (al menos 6 caracteres)
    if (isset($_POST['contrasena']) && strlen($_POST['contrasena']) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres.";
    }
    
    // Si hay errores, mostrar mensajes y regresar
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: admin_dashboard.php");
        exit();
    }
    
    // Si todo está validado, proceder con el registro
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
            $_SESSION['mensaje'] = "Usuario agregado correctamente";
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Error al agregar usuario";
            header("Location: admin_dashboard.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: admin_dashboard.php");
        exit();
    }
}
// Si se intenta acceder directamente sin POST
else {
    $_SESSION['error'] = "Acceso inválido";
    header("Location: admin_dashboard.php");
    exit();
}
?>