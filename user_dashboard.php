<?php
    session_start();
    
    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['username'])) {
        header("Location: index.php");
        exit();
    }
    
    $username = $_SESSION['username'];
    $rol = $_SESSION['rol'];
    $empleado_id = $_SESSION['empleado_id'];
    
    if ($rol == 'administrador') {
        header('Location: admin_dashboard.php'); 
        exit();
    }
    
    // Conectar a la base de datos
    include('config/conexion.php');
    
    try {
        // Ejecutamos el procedimiento almacenado
        $stmt = $pdo->prepare("CALL vista_usuario(:empleado_id)");
        $stmt->bindParam(':empleado_id', $empleado_id, PDO::PARAM_INT);
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

    } catch (PDOException $e) {
        $error = "Error al obtener datos: " . $e->getMessage();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="stylesheet" href="assets/css/pages/user_dashboard.css">
    <style>
        /* Estilos adicionales para botones deshabilitados */
        .btn-action:disabled {
            cursor: not-allowed !important;
            opacity: 0.7;
        }

        .btn-action:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        /* Asegurar que los proyectos finalizados están claramente marcados */
        .status-inactive {
            font-weight: bold !important;
            color: #dc3545 !important; 
        }
    </style>
</head>
<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>
    
    <!-- Barra de navegación -->
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
                        <i class="fas fa-key me-1"></i> Cambiar Contraseña
                    </button>
                    <form action="logout.php" method="post" onsubmit="return confirmarCerrarSesion();">
                        <button type="submit" class="btn btn-outline-light btn-sm ms-2" name="cerrar_sesion">
                            <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>
    <script>
        function confirmarCerrarSesion() {
            return confirm("¿Estás seguro de que deseas cerrar sesión?");
        }
    </script>

<!-- Contenido principal -->
<div class="container table-container">
    <h2 class="text-center mb-4 text-primary">
        <i class="fas fa-user-circle me-2"></i>Mis Proyectos Asignados
    </h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif (!is_array($usuarios) || count($usuarios) === 0): ?>
        <!-- Mensaje cuando no hay proyectos -->
        <div class="text-center text-muted my-5">
            <i class="fas fa-folder-open fa-3x d-block mb-3"></i>
            <h4>Sin proyectos asignados</h4>
            <p>Actualmente no tienes ningún proyecto asignado.</p>
        </div>
    <?php else: ?>
        <!-- Tabla de proyectos cuando sí hay datos -->
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
                                        data-id-empleado="<?= $row['id'] ?>"
                                        data-proyecto="<?= htmlspecialchars($row['proyecto']) ?>">
                                    <i class="fas fa-check-circle me-1"></i>Finalizar
                                </button>
                                <button class="btn btn-danger btn-action eliminar-btn"
                                        data-id-proyecto="<?= $row['proyecto_id'] ?>"
                                        data-id-empleado="<?= $row['id'] ?>"
                                        data-proyecto="<?= htmlspecialchars($row['proyecto']) ?>">
                                    <i class="fas fa-trash-alt me-1"></i>Eliminar
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>




    <!-- Modal Cambiar Contraseña -->
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
                    <form id="passwordForm" action="contrasena.php" method="POST">
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
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
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
            
            // Función mejorada para deshabilitar botones de proyectos finalizados
            function disableFinishedProjectButtons() {
                // Comprobamos todas las celdas de estado
                $("td.status-inactive").each(function() {
                    // Caso insensible para "finalizado"
                    if ($(this).text().trim().toLowerCase() === "finalizado") {
                        $(this).closest('tr').find('.finalizar-btn')
                            .prop('disabled', true)
                            .addClass('btn-secondary')
                            .removeClass('btn-success')
                            .html('<i class="fas fa-check-circle me-1"></i>Completado');
                    }
                });
                
                // También comprobar desde el atributo data-estado
                $('.finalizar-btn').each(function() {
                    if ($(this).data('estado') && $(this).data('estado').toLowerCase() === 'finalizado') {
                        $(this).prop('disabled', true)
                            .addClass('btn-secondary')
                            .removeClass('btn-success')
                            .html('<i class="fas fa-check-circle me-1"></i>Completado');
                    }
                });
            }
            
            // Ejecutar al cargar la página
            disableFinishedProjectButtons();
            
            // Función para mostrar notificaciones
            function showNotification(message, type) {
                const bgColor = type === 'success' ? '#28a745' : 
                               type === 'error' ? '#dc3545' : '#17a2b8';
                
                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: bgColor,
                    }
                }).showToast();
            }
            
            // Mostrar/ocultar overlay de carga
            function toggleLoading(show) {
                $('#loadingOverlay').css('visibility', show ? 'visible' : 'hidden');
            }
            
            // Finalizar proyecto
            $(".finalizar-btn").click(function() {
                let button = $(this);
                let idProyecto = button.data('id-proyecto');
                let idEmpleado = button.data('id-empleado');
                let nombreProyecto = button.data('proyecto');

                if (confirm(`¿Estás seguro de finalizar el proyecto "${nombreProyecto}"?`)) {
                    toggleLoading(true);
                    
                    $.ajax({
                        url: "cambiar_estado.php",
                        type: "POST",
                        data: { 
                            id_proyecto: idProyecto, 
                            id_empleado: idEmpleado, 
                            accion: "finalizar" 
                        },
                        success: function(response) {
                            toggleLoading(false);
                            
                            if (response === "success") {
                                // Deshabilitar el botón después de finalizar correctamente
                                button.prop('disabled', true)
                                      .addClass('btn-secondary')
                                      .removeClass('btn-success')
                                      .html('<i class="fas fa-check-circle me-1"></i>Completado');
                                
                                // También actualiza el estado en la tabla
                                button.closest('tr').find('td:nth-child(7)')
                                      .text('Finalizado')
                                      .removeClass('status-active')
                                      .addClass('status-inactive');
                                      
                                // Actualizar el atributo data-estado
                                button.data('estado', 'Finalizado');
                                      
                                showNotification("Proyecto finalizado correctamente", "success");
                            } else if (response === "already_finished") {
                                button.prop('disabled', true)
                                      .addClass('btn-secondary')
                                      .removeClass('btn-success')
                                      .html('<i class="fas fa-check-circle me-1"></i>Completado');
                                      
                                showNotification("Este proyecto ya está marcado como finalizado", "info");
                            } else {
                                showNotification("Error al finalizar el proyecto: " + response, "error");
                            }
                        },
                        error: function(xhr, status, error) {
                            toggleLoading(false);
                            showNotification("Error de conexión: " + error, "error");
                        }
                    });
                }
            });
            
            // Eliminar Proyecto
            $(".eliminar-btn").click(function() {
                let idProyecto = $(this).data('id-proyecto');
                let idEmpleado = $(this).data('id-empleado');
                let nombreProyecto = $(this).data('proyecto');
                
                if (confirm(`¿Estás seguro de eliminar el proyecto "${nombreProyecto}"?`)) {
                    toggleLoading(true);
                    
                    $.ajax({
                        url: "cambiar_estado.php",
                        type: "POST",
                        data: { 
                            id_proyecto: idProyecto, 
                            id_empleado: idEmpleado, 
                            accion: "eliminar" 
                        },
                        success: function(response) {
                            toggleLoading(false);
                            
                            if (response === "success") {
                                showNotification("Proyecto eliminado correctamente", "success");
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showNotification("Error al eliminar el proyecto: " + response, "error");
                            }
                        },
                        error: function(xhr, status, error) {
                            toggleLoading(false);
                            showNotification("Error de conexión: " + error, "error");
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>