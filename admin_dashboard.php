<?php
session_start();

// Guard clauses para validación de sesión
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$rol = $_SESSION['rol'];

// Guard clause para validación de rol
if ($rol === 'usuario') {
    header('Location: user_dashboard.php'); 
    exit();
}

// Conectar a la base de datos
include('config/conexion.php');

// Obtener fecha actual para validaciones
$fechaActual = date('Y-m-d\TH:i');

// Consultas para obtener datos necesarios
function executeQuery($pdo, $sql) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener datos para selectores
$proyectos = executeQuery($pdo, "SELECT id, nombre FROM proyectos");
$departamentos = executeQuery($pdo, "SELECT id, nombre FROM departamentos");
$role = executeQuery($pdo, "SELECT id, nombre FROM roles");

// Obtener datos de empleados
$empleado = executeQuery($pdo, "CALL vista_administrador()");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/pages/dashboard.css">
    <style>
        /* Estilos para campos inválidos */
        .is-invalid {
            border-color: #dc3545 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right calc(0.375em + 0.1875rem) center !important;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
        }
        
        /* Estilos para campos válidos */
        .is-valid {
            border-color: #198754 !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e") !important;
            background-repeat: no-repeat !important;
            background-position: right calc(0.375em + 0.1875rem) center !important;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="fas fa-chart-line me-2"></i>Panel Administrativo
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

    <!-- Mensajes de alerta -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show container mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show container mt-3">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($_SESSION['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>
    
    <!-- Mensajes de alerta para errores múltiples -->
    <?php if (isset($_SESSION['errores']) && is_array($_SESSION['errores'])): ?>
        <div class="alert alert-danger alert-dismissible fade show container mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Error:</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($_SESSION['errores'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errores']); ?>
    <?php endif; ?>
    
    <!-- MAIN CONTENT - TABLA -->
    <main class="container mb-5">
        <!-- Encabezado con título y botones de acción -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4">
            <h2 class="mb-3 mb-md-0">
               <i class="fas fa-users fa-1x text-primary me-3"></i>Información de Empleados:
            </h2>
            
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-info" onclick="redirectTo()">
                    <i class="bi bi-graph-up me-1"></i>Información General
                </button>
            </div>
        </div>
    
        <!-- Tarjeta contenedora de la tabla -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <!-- Tabla responsive -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Salario</th>
                                <th class="text-center">Proyecto</th>
                                <th class="text-center">Departamento</th>
                                <th class="text-center" style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            <?php $numero = 1; ?>
                            <?php foreach ($empleado as $empleados) : ?>
                            <tr>
                                <td class="text-center"><?= $numero++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <span><?= htmlspecialchars($empleados['nombre']) ?></span>
                                    </div>
                                </td>
                                <td class="text-center fw-bold">$<?= number_format($empleados['salario'], 2) ?></td>
                                <td>
                                    <?php if (!empty($empleados['proyecto'])): ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <?= htmlspecialchars($empleados['proyecto']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                            Sin Proyecto
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <?= htmlspecialchars($empleados['departamento']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-outline-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editModal"
                                            data-id="<?= $empleados['id'] ?>"
                                            data-nombre="<?= htmlspecialchars($empleados['nombre']) ?>"
                                            data-salario="<?= $empleados['salario'] ?>"
                                            data-proyecto="<?= $empleados['proyecto'] ?>"
                                            data-departamento="<?= $empleados['departamento'] ?>">
                                            <i class="bi bi-pencil"></i> Editar
                                        </button>
                                        
                                        <button class="btn btn-sm btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal" 
                                            data-id="<?= $empleados['id'] ?>" 
                                            data-nombre="<?= $empleados['nombre'] ?>">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    
        <div class="mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h5 class="card-title mb-3">
                <i class="bi bi-tools text-success me-2"></i>Herramientas de Gestión
            </h5>
            
            <div class="d-flex flex-wrap align-items-center gap-3">
                <!-- Menú desplegable para Usuarios -->
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="usuariosDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-people me-1"></i>Usuarios
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="usuariosDropdown">
                        <li>
                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#adduser">
                                <i class="bi bi-person-plus me-1"></i>Agregar Usuario
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#assignProjectModal">
                                <i class="bi bi-kanban me-1"></i>Asignar Proyecto
                            </button>
                        </li>
                    </ul>
                </div>
                
                <!-- Menú desplegable para Proyectos -->
                <div class="dropdown">
                    <button class="btn btn-info text-white dropdown-toggle" type="button" id="proyectosDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-briefcase me-1"></i>Proyectos
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="proyectosDropdown">
                        <li>
                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addproyect">
                                <i class="bi bi-plus-circle me-1"></i>Agregar Proyecto
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">
                                <i class="bi bi-trash me-1"></i>Eliminar Proyecto
                            </button>
                        </li>
                    </ul>
                </div>
                
                <!-- Menú desplegable para Departamentos -->
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" id="departamentosDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-building me-1"></i>Departamentos
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="departamentosDropdown">
                        <li>
                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#addDepartment">
                                <i class="bi bi-building-add me-1"></i>Agregar Departamento
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editDepartmentModal">
                                <i class="bi bi-pencil-square me-1"></i>Editar Departamento
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#deleteDepartmentModal">
                                <i class="bi bi-trash me-1"></i>Eliminar Departamento
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
    </main>

    <!-- MODAL ELIMINAR -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar al usuario <strong id="deleteNombre"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a id="deleteConfirmBtn" class="btn btn-danger">Eliminar</a>
                </div>
                </div>
            </div>
        </div>

    <!-- MODAL AGREGAR -->
    <div class="modal fade" id="adduser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Agregar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="AgregarUsuario.php" method="post" id="userForm">
                        <div class="row">
                            <!-- Primera columna -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Nombre de Empleado:</label>
                                    <input type="text" name="nombre" class="form-control" required>
                                    <div class="invalid-feedback">Este campo es obligatorio.</div>
                                </div>
                                <div class="mb-3">
                                    <label>Salario:</label>
                                    <input type="number" name="salario" class="form-control" min="0.01" step="0.01" oninput="validarSalario(this)" required>
                                    <div class="invalid-feedback">El salario no puede ser negativo o igual a '-0'</div>
                                </div>
                                <div class="mb-3">
                                    <label>Proyecto:</label>
                                    <select name="proyecto_id" class="form-control" required>
                                        <option value="">-- Seleccionar Proyecto --</option>
                                        <?php foreach ($proyectos as $proyecto) : ?>
                                            <option value="<?= htmlspecialchars($proyecto['id']) ?>">
                                                <?= htmlspecialchars($proyecto['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione un proyecto.</div>
                                </div>
                            </div>

                            <!-- Segunda columna -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Departamento:</label>
                                    <select name="departamento_id" class="form-control" required>
                                        <option value="">-- Seleccionar Departamento --</option>
                                        <?php foreach ($departamentos as $departamento) : ?>
                                            <option value="<?= htmlspecialchars($departamento['id']) ?>">
                                                <?= htmlspecialchars($departamento['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione un departamento.</div>
                                </div>
                                <div class="mb-3">
                                    <label>Nombre de Usuario:</label>
                                    <input type="text" name="nombreUsuario" class="form-control" required minlength="4">
                                    <div class="invalid-feedback">El nombre de usuario debe tener al menos 4 caracteres.</div>
                                </div>
                                <div class="mb-3">
                                    <label>Contraseña:</label>
                                    <input type="password" class="form-control py-2" id="contrasena" name="contrasena" required minlength="8" placeholder="Mínimo 8 caracteres">
                                    <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
                                </div>
                                <div class="mb-3">
                                    <label>Rol:</label>
                                    <select name="Roles_id" class="form-control" required>
                                        <option value="">-- Seleccione el rol de usuario --</option>
                                        <?php foreach ($role as $roles) : ?>
                                            <option value="<?= htmlspecialchars($roles['id']) ?>">
                                                <?= htmlspecialchars($roles['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione un rol.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón de Agregar centrado -->
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-success">Agregar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Agregar Proyectos -->
    <div class="modal fade" id="addproyect" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Agregar Proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="AgregarProyecto.php" method="post" id="projectForm">
                        <div class="mb-3">
                            <label>Proyecto:</label>
                            <input type="text" name="proyecto" class="form-control" required>
                            <div class="invalid-feedback">Este campo es obligatorio.</div>
                        </div>
                    
                        <div class="mb-3">
                            <label>Descripción:</label>
                            <input type="text" name="descripcion" class="form-control" required>
                            <div class="invalid-feedback">Este campo es obligatorio.</div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3">Agregar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para eliminar proyecto -->
    <div class="modal fade" id="deleteProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Eliminar Proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="deleteProjectForm" action="EliminarPro.php" method="POST">
                        <label for="proyectoSelect" class="form-label">Seleccione un proyecto:</label>
                        <select name="proyecto_id" id="proyectoSelect" class="form-control" required>
                            <option value="">Seleccione un proyecto</option>
                            <?php foreach ($proyectos as $proyecto): ?>
                                <option value="<?= htmlspecialchars($proyecto['id']) ?>">
                                    <?= htmlspecialchars($proyecto['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione un proyecto.</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Modificar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="editar_usuario.php" method="post" id="editForm">
                        <input type="hidden" name="empleado_id" id="editId">

                        <!-- Selección de Proyecto -->
                        <div class="mb-3">
                            <label>Proyecto:</label>
                            <select name="proyecto_id" id="editProyecto" class="form-control">
                                <option value="desasignar">-- Sin Proyecto (Desasignar) --</option>
                                <?php foreach ($proyectos as $proyecto): ?>
                                    <option value="<?= htmlspecialchars($proyecto['id']) ?>">
                                        <?= htmlspecialchars($proyecto['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Salario -->
                        <div class="mb-3">
                            <label>Salario:</label>
                            <input type="number" name="salario" id="editSalario" class="form-control" min="0.01" step="0.01" oninput="validarSalario(this)" required>
                            <div class="invalid-feedback">El salario no puede ser negativo o igual a '-0'</div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>


    <!-- Modal Asignar proyecto -->
    <div class="modal fade" id="assignProjectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Encabezado del modal -->
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title text-dark">
                        <i class="fas fa-tasks me-2"></i>Asignar Proyecto
                    </h5>
                    <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <!-- Cuerpo del modal -->
                <div class="modal-body p-4">
                    <form action="asignar_proyecto.php" method="POST" class="needs-validation" id="assignProjectForm" novalidate>
                        <!-- Selección de Usuario -->
                        <div class="mb-4">
                            <label for="usuario_id" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-info"></i>Seleccionar Usuario
                            </label>
                            <select name="usuario_id" id="usuario_id" class="form-select py-2" required>
                                <option value="" selected disabled>-- Seleccione un usuario --</option>
                                <?php
                                $empleados = executeQuery($pdo, "SELECT id, nombre FROM empleados");
                                foreach ($empleados as $emp): ?>
                                    <option value="<?= htmlspecialchars($emp['id']) ?>">
                                        <?= htmlspecialchars($emp['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un usuario.
                            </div>
                        </div>
                        
                        <!-- Selección de Proyecto -->
                        <div class="mb-4">
                            <label for="proyecto_id" class="form-label fw-semibold">
                                <i class="fas fa-project-diagram me-2 text-info"></i>Seleccionar Proyecto
                            </label>
                            <select name="proyecto_id" id="proyecto_id" class="form-select py-2" required>
                                <option value="" selected disabled>-- Seleccione un proyecto --</option>
                                <?php foreach ($proyectos as $proyecto): ?>
                                    <option value="<?= htmlspecialchars($proyecto['id']) ?>">
                                        <?= htmlspecialchars($proyecto['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Por favor seleccione un proyecto.
                            </div>
                        </div>
                        
                        <!-- Salario -->
                        <div class="mb-4">
                            <label for="salario" class="form-label fw-semibold">
                                <i class="fas fa-dollar-sign me-2 text-info"></i>Salario para este Proyecto
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">$</span>
                                <input type="number" 
                                       class="form-control py-2" 
                                       id="salario" 
                                       name="salario" 
                                       step="0.01" 
                                       min="0.01" 
                                       required
                                       placeholder="0.00"
                                       oninput="validarSalario(this)">
                            </div>
                            <div class="invalid-feedback">
                                El salario debe ser un valor positivo mayor que cero.
                            </div>
                        </div>
                        
                        <!-- Fecha de Asignación -->
                        <div class="mb-4">
                            <label for="fecha_asignacion" class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-2 text-info"></i>Fecha de Asignación
                            </label>
                            <input type="datetime-local" 
                                   class="form-control py-2" 
                                   id="fecha_asignacion" 
                                   name="fecha_asignacion"
                                   min="<?= $fechaActual ?>"
                                   oninput="validarFecha(this)"
                                   required>
                            <div class="invalid-feedback">
                                La fecha debe ser igual o posterior a hoy (<?= date('d/m/Y H:i') ?>).
                            </div>
                        </div>
                        
                        <!-- Botón de enviar -->
                        <div class="d-grid mt-4">
                            <button type="submit" 
                                    class="btn btn-info btn-lg py-2 text-white fw-semibold">
                                <i class="fas fa-check-circle me-2"></i>Asignar Proyecto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
                                <input type="password" name="confirmar_contrasena" class="form-control" required minlength="8">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Utilidades comunes
const validateForm = (form, invalidHandler = null) => {
    if (!form) return true;
    
    let isValid = true;
    const requiredInputs = form.querySelectorAll('[required]');
    
    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!isValid && invalidHandler) {
        invalidHandler();
    }
    
    return isValid;
};

// Validar salario - Mejorado para prevenir valores negativos y '-0'
function validarSalario(input) {
    const valor = parseFloat(input.value);
    
    if (input.value === '-0' || valor <= 0 || isNaN(valor)) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        input.value = '';
        input.nextElementSibling.textContent = 'El salario debe ser un valor positivo';
        return false;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
}

// Validar que la fecha no sea pasada
function validarFecha(input) {
    const fechaSeleccionada = new Date(input.value);
    const ahora = new Date();
    
    // Resetear los segundos y milisegundos para una comparación más justa
    ahora.setSeconds(0);
    ahora.setMilliseconds(0);
    
    if (fechaSeleccionada < ahora) {
        input.classList.add('is-invalid');
        input.classList.remove('is-valid');
        return false;
    } else {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        return true;
    }
}

// Mostrar/ocultar contraseña
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            if (!input) return;
            
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.classList.toggle('bi-eye-slash');
            this.classList.toggle('bi-eye');
        });
    });
    const addDepartmentForm = document.getElementById('addDepartmentForm');
    if (addDepartmentForm) {
        addDepartmentForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    }
    
    // Validación para el formulario de editar departamento
    const editDepartmentForm = document.getElementById('editDepartmentForm');
    if (editDepartmentForm) {
        editDepartmentForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });
        
        // Cargar datos del departamento al cambiar la selección
        const editDepartamentoSelect = document.getElementById('editDepartamentoSelect');
        if (editDepartamentoSelect) {
            editDepartamentoSelect.addEventListener('change', function() {
                const departamentoId = this.value;
                if (departamentoId) {
                    // Petición AJAX para obtener datos del departamento
                    fetch('get_departamento.php?id=' + departamentoId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('editDepartamentoNombre').value = data.nombre;
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error al cargar los datos del departamento');
                        });
                } else {
                    document.getElementById('editDepartamentoNombre').value = '';
                }
            });
        }
    }
    
    // Limpiar formularios al cerrar modales
    const departmentModals = ['#addDepartment', '#editDepartmentModal', '#deleteDepartmentModal'];
    departmentModals.forEach(modalId => {
        const modal = document.querySelector(modalId);
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function() {
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated');
                }
            });
        }
    });
});

// Función para configurar el modal de edición
function configurarModalEdicion(button) {
    const id = button.getAttribute("data-id");
    const salario = button.getAttribute("data-salario");
    const proyecto = button.getAttribute("data-proyecto");
    
    // Asignar valores básicos
    document.getElementById("editId").value = id;
    document.getElementById("editSalario").value = salario;
    
    // Configurar el selector de proyectos
    const selectProyecto = document.getElementById("editProyecto");
    if (!selectProyecto) return;
    
    // Si el empleado no tiene proyecto asignado, seleccionar la opción "Sin Proyecto"
    if (!proyecto || proyecto === 'Sin Proyecto') {
        selectProyecto.value = "desasignar";
    } else {
        // Buscar y seleccionar el proyecto correspondiente
        for (let i = 0; i < selectProyecto.options.length; i++) {
            if (selectProyecto.options[i].textContent.trim() === proyecto) {
                selectProyecto.selectedIndex = i;
                break;
            }
        }
    }
}

function redirectTo() {
    window.location.href = 'info_general.php';
}

function resetForm(formElement) {
    if (formElement) {
        // Resetear el formulario a sus valores iniciales
        formElement.reset();
        
        // Eliminar todas las clases de validación
        formElement.querySelectorAll('.is-invalid, .is-valid').forEach(el => {
            el.classList.remove('is-invalid');
            el.classList.remove('is-valid');
        });
        
        // Eliminar mensajes de alerta
        const alertElement = formElement.querySelector('.alert');
        if (alertElement) {
            alertElement.remove();
        }
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const deleteDepartmentForm = document.getElementById('deleteDepartmentForm');
    if (deleteDepartmentForm) {
        deleteDepartmentForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('was-validated');
            }
        });
    }
    
    // Limpiar formulario al cerrar el modal
    const deleteDepartmentModal = document.getElementById('deleteDepartmentModal');
    if (deleteDepartmentModal) {
        deleteDepartmentModal.addEventListener('hidden.bs.modal', function() {
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                form.classList.remove('was-validated');
            }
        });
    }
});

// Limpiar todos los modales al cerrarse
document.addEventListener('DOMContentLoaded', function() {
    // Modales con formularios
    const modalIds = ['#adduser', '#addproyect', '#deleteProjectModal', '#editModal', 
                      '#assignProjectModal', '#cambiarcontra', '#deleteModal'];
    
    modalIds.forEach(modalId => {
        const modalElement = document.querySelector(modalId);
        if (modalElement) {
            modalElement.addEventListener('hidden.bs.modal', function() {
                // Para el modal de eliminación (no tiene form pero tiene otros elementos)
                if (modalId === '#deleteModal') {
                    const nombreElement = document.getElementById('deleteNombre');
                    if (nombreElement) nombreElement.textContent = '';
                    return;
                }
                
                // Para los demás modales que contienen formularios
                const formElement = this.querySelector('form');
                resetForm(formElement);
                
                // Limpiar mensajes de error específicos
                if (modalId === '#adduser') {
                    const userFormAlert = document.getElementById('userFormAlert');
                    if (userFormAlert) userFormAlert.remove();
                }
            });
        }
    });
    
    // Configuración especial para el modal de cambio de contraseña
    const passwordModal = document.querySelector('#cambiarcontra');
    if (passwordModal) {
        passwordModal.addEventListener('hidden.bs.modal', function() {
            // Restaurar los iconos de mostrar/ocultar contraseña
            this.querySelectorAll('.toggle-password').forEach(icon => {
                if (!icon.classList.contains('bi-eye-slash')) {
                    icon.classList.add('bi-eye-slash');
                    icon.classList.remove('bi-eye');
                }
            });
            
            // Restaurar los campos a tipo password
            this.querySelectorAll('input[type="text"]').forEach(input => {
                if (input.id.includes('contrasena')) {
                    input.type = 'password';
                }
            });
        });
    }
});
</script>
<<!-- Modal Agregar Departamento -->
<div class="modal fade" id="addDepartment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-building me-2"></i>Agregar Departamento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="agregar_departamento.php" method="post" id="addDepartmentForm">
                    <div class="mb-3">
                        <label for="nombre_departamento" class="form-label">Nombre del Departamento</label>
                        <input type="text" class="form-control" id="nombre_departamento" name="nombre" required>
                        <div class="invalid-feedback">
                            Por favor ingrese un nombre para el departamento.
                        </div>
                    </div>
                    
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Guardar Departamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Departamento -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Editar Departamento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="editar_departamento.php" method="post" id="editDepartmentForm">
                    <div class="mb-3">
                        <label for="editDepartamentoSelect" class="form-label">Seleccione el Departamento</label>
                        <select class="form-select" id="editDepartamentoSelect" name="departamento_id" required>
                            <option value="">-- Seleccione un departamento --</option>
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?= htmlspecialchars($departamento['id']) ?>">
                                    <?= htmlspecialchars($departamento['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Por favor seleccione un departamento.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="editDepartamentoNombre" class="form-label">Nuevo Nombre</label>
                        <input type="text" class="form-control" id="editDepartamentoNombre" name="nuevo_nombre" required>
                        <div class="invalid-feedback">
                            Por favor ingrese un nombre para el departamento.
                        </div>
                    </div>
                    
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Eliminar Departamento -->
<div class="modal fade" id="deleteDepartmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash me-2"></i>Eliminar Departamento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deleteDepartmentForm" action="eliminar_departamento.php" method="POST">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> Esta acción no se puede deshacer. Al eliminar un departamento, asegúrese de que no tenga empleados asignados.
                    </div>
                    
                    <div class="mb-3">
                        <label for="departamentoSelect" class="form-label">Seleccione el Departamento a Eliminar</label>
                        <select class="form-select" id="departamentoSelect" name="departamento_id" required>
                            <option value="">-- Seleccione un departamento --</option>
                            <?php foreach ($departamentos as $departamento): ?>
                                <option value="<?= htmlspecialchars($departamento['id']) ?>">
                                    <?= htmlspecialchars($departamento['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">
                            Por favor seleccione un departamento.
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmDelete" name="confirmar" value="1" required>
                        <label class="form-check-label" for="confirmDelete">
                            Confirmo que deseo eliminar este departamento
                        </label>
                        <div class="invalid-feedback">
                            Debe confirmar esta acción.
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>