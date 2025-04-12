<?php
header('Content-Type: text/html; charset=UTF-8');
include('config\conexion.php');

// Obtener departamentos para el selector
$departamentos = [];
try {
    $stmt = $pdo->query("SELECT id, nombre FROM departamentos");
    $departamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar departamentos: " . $e->getMessage();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    $departamento_id = $_POST['departamento_id'];
    
    $errores = [];
    
    // Validaciones básicas
    if (empty($nombre)) $errores[] = "El nombre es obligatorio";
    if (empty($username)) $errores[] = "El nombre de usuario es obligatorio";
    if (empty($password)) $errores[] = "La contraseña es obligatoria";
    if ($password != $confirmar_password) $errores[] = "Las contraseñas no coinciden";
    if (strlen($password) < 8) $errores[] = "La contraseña debe tener al menos 8 caracteres";
    
    // Verificar si el usuario ya existe
    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                $errores[] = "Este nombre de usuario ya está registrado";
            }
        } catch (PDOException $e) {
            $errores[] = "Error de verificación: " . $e->getMessage();
        }
    }
    
    // Si no hay errores, registrar el usuario
    if (empty($errores)) {
        try {
            // Valores predeterminados
            $salario = 0;
            $proyecto_id = null;
            $rol_id = 2; // Asumiendo 2 para usuario regular (1 para admin)
            $estado_id = 1; // Activo
            $fecha_asignacion = date("Y-m-d H:i:s");
            
            // Hash de la contraseña
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // Llamar al procedimiento almacenado para agregar usuario
            $stmt = $pdo->prepare("CALL agregar_usuario(?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $nombre,
                $salario,
                $proyecto_id,
                $departamento_id,
                $username,
                $hashed_password,
                $rol_id,
                $estado_id,
                $fecha_asignacion
            ]);
            
            // Redirigir a la página de inicio de sesión
            header("Location: index.php?registro=exitoso");
            exit;
            
        } catch (PDOException $e) {
            $errores[] = "Error al registrar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse</title>
    <link rel="stylesheet" href="assets/css/buttons">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/pages/register.css">
</head>
<body>
    <div class="register-container">
        <svg class="logo" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path fill="#4361ee" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
        </svg>
        
        <h2>Crear Cuenta</h2>
        
        <?php if (!empty($errores)): ?>
            <div class="error-container">
                <strong>Por favor corrige los siguientes errores:</strong>
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="registro.php" method="POST" class="form">
            <div class="form-group">
                <label for="nombre">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" class="form-control" value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>" placeholder="Ingresa tu nombre completo" required>
            </div>
            
            <div class="form-group">
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="username" name="username" class="form-control" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" placeholder="Crea un nombre de usuario" required>
            </div>
            
            <div class="form-group">
                <label for="departamento_id">Departamento</label>
                <select id="departamento_id" name="departamento_id" class="form-control" required>
                    <option value="">-- Selecciona un departamento --</option>
                    <?php foreach ($departamentos as $departamento): ?>
                        <option value="<?php echo $departamento['id']; ?>" <?php echo (isset($departamento_id) && $departamento_id == $departamento['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($departamento['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Crea una contraseña (mínimo 8 caracteres)" minlength="8" required>
            </div>
            
            <div class="form-group">
                <label for="confirmar_password">Confirmar Contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" class="form-control" placeholder="Confirma tu contraseña" minlength="8" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
        </form>
        
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="index.php">Iniciar Sesión</a>
        </div>
    </div>
</body>
</html>