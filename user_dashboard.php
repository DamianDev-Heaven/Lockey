<?php
    session_start();
    
    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['username'])) {
        header("Location: index.php");
        exit();
    }
    

    $username = $_SESSION['username'];
    $rol = $_SESSION['rol'];
    $empleado_id = $_SESSION['empleado_id']; // ID del empleado logueado
    
    if ($rol  == 'administrador' ) {
        header('Location: admin_dashboard.php'); 
        exit();
    }
    
    // Conectar a la base de datos
    include('conexion.php');
    
    try {
        // Ejecutamos el procedimiento almacenado
        $stmt = $pdo->prepare("CALL vista_usuario(:empleado_id)");
        $stmt->bindParam(':empleado_id', $empleado_id, PDO::PARAM_INT);
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Cerramos el cursor para liberar los recursos del primer resultado
        $stmt->closeCursor();
    
        // Verificar si se obtuvieron usuarios
        if (!$usuarios || empty($usuarios)) {
            echo "No se encontraron usuarios.";
            exit();
        }
        
    } catch (PDOException $e) {
        echo "Error al obtener datos: " . $e->getMessage();
        exit();
    }

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
                    --primary-color: #1e90ff;
                    --secondary-color: #00bfff;
                    --light-color: #f0f8ff;
                    --dark-color: #007bff;
                    --success-color: #28a745;
                    --danger-color: #dc3545;
                }
                
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    background-color: #f8f9fa;
                }
                
                .navbar-custom {
                    background: linear-gradient(135deg, var(--dark-color), var(--primary-color));
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                }
                
                .table-container {
                    background-color: white;
                    border-radius: 10px;
                    box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
                    padding: 2rem;
                    margin-top: 2rem;
                }
                
                .table-custom {
                    border-collapse: separate;
                    border-spacing: 0;
                }
                
                .table-custom thead th {
                    background-color: var(--primary-color);
                    color: white;
                    position: sticky;
                    top: 0;
                    font-weight: 500;
                }
                
                .table-custom tbody tr:hover {
                    background-color: var(--light-color);
                }
                
                .status-active {
                    color: var(--success-color);
                    font-weight: 500;
                }
                
                .status-inactive {
                    color: var(--danger-color);
                    font-weight: 500;
                }
                
                .btn-action {
                    border-radius: 20px;
                    padding: 0.35rem 1rem;
                    font-size: 0.875rem;
                    transition: all 0.3s;
                }
                
                .btn-action:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                }
                
                .modal-header-custom {
                    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                    color: white;
                }
                
                .password-toggle {
                    cursor: pointer;
                    transition: all 0.3s;
                }
                
                .password-toggle:hover {
                    color: var(--primary-color);
                }
    </style>
</head>
<body>
    <!-- Barra de navegación mejorada -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container-fluid">
            <a class="navbar-brand text-white d-flex align-items-center" href="#">
                <i class="fas fa-user-tie me-2"></i>
                <span class="fw-medium">Panel de Usuario</span>
            </a>
            <div class="d-flex align-items-center">
                <div class="text-white me-4">
                    <i class="fas fa-user-circle me-2"></i>
                    <span class="me-2"><?= htmlspecialchars($username) ?></span>
                    <span class="badge bg-light text-dark"><?= htmlspecialchars($rol) ?></span>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#cambiarcontra">
                        <i class="fas fa-key me-1"></i> Contraseña
                    </button>
                    <form action="logout.php" method="post">
                        <button type="submit" class="btn btn-outline-light btn-sm ms-2" name="cerrar_sesion">
                            <i class="fas fa-sign-out-alt me-1"></i> Salir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container table-container">
        <h2 class="text-center mb-4 text-primary">
            <i class="fas fa-user-circle me-2"></i>Mis Proyectos Asignados
        </h2>
        
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Nombre</th>
                        <th class="text-end">Salario</th>
                        <th>Proyecto</th>
                        <th>Departamento</th>
                        <th class="text-center">Fecha Asignación</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $numero = 1; ?>
                    <?php foreach ($usuarios as $row): ?>
                    <tr>
                        <td class="text-center"><?= $numero++ ?></td>
                        <td><?= htmlspecialchars($row['nombre']) ?></td>
                        <td class="text-end fw-medium">$<?= number_format($row['salario'], 2) ?></td>
                        <td><?= htmlspecialchars($row['proyecto']) ?></td>
                        <td><?= htmlspecialchars($row['departamento']) ?></td>
                        <td class="text-center"><?= date('d/m/Y', strtotime($row['fecha_asignada'])) ?></td>
                        <td class="text-center <?= $row['Estado'] === 'Activo' ? 'status-active' : 'status-inactive' ?>">
                            <?= htmlspecialchars($row['Estado']) ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn btn-success btn-action finalizar-btn"
                                        data-id-proyecto="<?= $row['proyecto_id'] ?>"
                                        data-id-empleado="<?= $row['id'] ?>">
                                    <i class="fas fa-check-circle me-1"></i>Finalizar
                                </button>
                                <button class="btn btn-danger btn-action eliminar-btn"
                                        data-id-proyecto="<?= $row['proyecto_id'] ?>"
                                        data-id-empleado="<?= $row['id'] ?>">
                                    <i class="fas fa-trash-alt me-1"></i>Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña (Mejorado) -->
    <div class="modal fade" id="cambiarcontra" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>Cambiar Contraseña
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="contrasena.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Contraseña Actual</label>
                            <div class="input-group">
                                <input type="password" name="contrasena_actual" class="form-control" required>
                                <span class="input-group-text password-toggle">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-medium">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="nueva_contrasena" class="form-control" required minlength="8">
                                <span class="input-group-text password-toggle">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                            <small class="text-muted">Mínimo 8 caracteres</small>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-medium">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="confirmar_contrasena" class="form-control" required>
                                <span class="input-group-text password-toggle">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="cambiar_contrasena" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle para mostrar/ocultar contraseña
            $('.password-toggle').click(function() {
                const input = $(this).closest('.input-group').find('input');
                const icon = $(this).find('i');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // Finalizar Proyecto (misma funcionalidad)
            $(".finalizar-btn").click(function() {
                let idProyecto = $(this).data('id-proyecto');
                let idEmpleado = $(this).data('id-empleado'); 
        
                if (confirm("¿Estás seguro de finalizar este proyecto?")) {
                    $.post("cambiar_estado.php", 
                    { 
                        id_proyecto: idProyecto, 
                        id_empleado: idEmpleado, 
                        accion: "finalizar" 
                    }, 
                    function(response) {
                        if (response.trim() === "success") {
                            alert("Proceso finalizado correctamente");
                            location.reload();
                        } else {
                            alert("Error al finalizar el proyecto.");
                        }
                    });
                }
            });
            
            // Eliminar Proyecto (misma funcionalidad)
            $(".eliminar-btn").click(function() {
                let idProyecto = $(this).data('id-proyecto');
                let idEmpleado = $(this).data('id-empleado');
                
                if (confirm("¿Estás seguro de eliminar este proyecto?")) {
                    $.post("cambiar_estado.php", 
                    { 
                        id_proyecto: idProyecto, 
                        id_empleado: idEmpleado, 
                        accion: "eliminar" 
                    }, 
                    function(response) { 
                        if (response.trim() === "success") {
                            alert("Proceso eliminado correctamente");
                            location.reload();
                        } else {
                            alert("Error al eliminar el proyecto.");
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
