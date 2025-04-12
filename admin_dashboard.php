<?php
session_start();

// Verificar si el usuario ha iniciado sesion
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php'); 
    exit();
}


$username = $_SESSION['username'];
$rol = $_SESSION['rol'];
//$id_usuario = $_SESSION['user_id'];

// valida si el usuario se paso de inteligente
if ($rol  === 'usuario' ) {
    header('Location: user_dashboard.php'); 
    exit();
}
    

// Conectar a la base de datos
include('config\conexion.php');

//llena un select con informacion de la base de datos para proyectos
$sqlProyectos = "SELECT id, nombre FROM proyectos";
$stmtProyectos = $pdo->prepare($sqlProyectos);
$stmtProyectos->execute();
$proyectos = $stmtProyectos->fetchAll(PDO::FETCH_ASSOC);

//llena un select con informacion de la base de datos para departamentos
$sqlDepartamentos = "SELECT id, nombre FROM departamentos";
$stmtDepartamentos = $pdo->prepare($sqlDepartamentos);
$stmtDepartamentos->execute();
$departamentos = $stmtDepartamentos->fetchAll(PDO::FETCH_ASSOC);


//llena un select con informacion de la base de datos para roles
$sqlRoles = "select id, nombre from roles";
$stmtRoles = $pdo->prepare($sqlRoles);
$stmtRoles->execute();
$role = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

// Llamar al procedimiento almacenado con el ID del empleado logueado
$sql = "CALL vista_administrador()";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$empleado = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
</head>
<body>

    <!-- HEADER -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <i class="fas fa-chart-line me-2"></i>Panel Administrativo
                </a>
                <div class="d-flex align-items-center">
                    <span class="text-white me-3">
                        <i class="fas fa-user-circle me-2"></i>
                        <?= htmlspecialchars($username) ?> (<?= htmlspecialchars($rol) ?>)
                    </span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#cambiarcontra">
                            <i class="fas fa-key me-1"></i> Cambiar Contraseña
                        </button>
                        <form action="logout.php" method="post">
                            <button type="submit" class="btn btn-outline-light btn-sm ms-2" name="cerrar_sesion">
                                <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

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
    
        <!-- Sección de botones de gestión -->
        <div class="mt-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-tools text-success me-2"></i>Herramientas de Gestión
                    </h5>
                    
                    <div class="d-flex flex-wrap gap-3">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#adduser">
                            <i class="bi bi-person-plus me-1"></i>Agregar Usuario
                        </button>
                        
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignProjectModal">
                            <i class="bi bi-kanban me-1"></i>Asignar Proyecto
                        </button>
                        
                        <button class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#addproyect">
                            <i class="bi bi-plus-circle me-1"></i>Agregar Proyecto
                        </button>
                        
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteProjectModal">
                            <i class="bi bi-trash me-1"></i>Eliminar Proyecto
                        </button>
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
                    <h5 class="modal-title">Confirmar Eliminacion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Estas seguro de que desea eliminar al usuario <strong id="deleteNombre"></strong>?</p>
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
                            </div>
                            <div class="mb-3">
                                <label>Salario:</label>
                                <input type="number" name="salario" class="form-control" min="0" step="0.01" oninput="validarSalario(this)" required>
                                <div class="invalid-feedback">El salario no puede ser negativo</div>
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
                            </div>
                            <div class="mb-3">
                                <label>Nombre de Usuario:</label>
                                <input type="text" name="nombreUsuario" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Contraseña:</label>
                                <input type="password" class="form-control py-2" id="contrasena" name="contrasena" require minlength="8" placeholder="Mínimo 8 caracteres">
                            </div>
                            <div class="mb-3">
                                <label>Rol:</label>
                                <select name="Roles_id" class="form-control">
                                    <option value="">-- Seleccione el rol de usuario --</option>
                                    <?php foreach ($role as $roles) : ?>
                                        <option value="<?= htmlspecialchars($roles['id']) ?>">
                                            <?= htmlspecialchars($roles['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
            <div class="modal-dialog modal-dialog-scrollable"> <!-- Agregar "modal-lg" si es necesario -->
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Agregar Proyecto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form action="AgregarProyecto.php" method="post">
                            
                            <div class="mb-3">
                                <label>Proyecto:</label>
                                <input type="text" name="proyecto" class="form-control" required >
                            </div>
                        
                            <div class="mb-3">
                                <label>Descripción:</label>
                                <input type="text" name="descripcion" class="form-control" required >
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
                            <?php
                            include('config/conexion.php');
                            $stmt = $pdo->query("SELECT id, nombre FROM proyectos");
                            while ($proyecto = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<option value='{$proyecto['id']}'>{$proyecto['nombre']}</option>";
                            }
                            ?>
                        </select>
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
                <form action="editar_usuario.php" method="post">
                    <input type="hidden" name="empleado_id" id="editId">

                    <!-- Selección de Proyecto -->
                    <label>Proyecto:</label>
                    <select name="proyecto_id" id="editProyecto" class="form-control" required>
                        <option value="">Seleccione un Proyecto</option>
                        <?php
                        include('config/conexion.php');
                        $stmt = $pdo->query("SELECT id, nombre FROM proyectos");
                        while ($proyecto = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$proyecto['id']}'>{$proyecto['nombre']}</option>";
                        }
                        ?>
                    </select>

                    <!-- Salario -->
                    <label>Salario:</label>
                    <input type="number" name="salario" id="editSalario" class="form-control" min="0" step="0.01" oninput="validarSalario(this)" required>
                    <div class="invalid-feedback">El salario no puede ser negativo</div>

                    <button type="submit" class="btn btn-warning mt-3">Modificar</button>
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
                      <form action="asignar_proyecto.php" method="POST" class="needs-validation" novalidate>
                          <!-- Selección de Usuario -->
                          <div class="mb-4">
                              <label for="usuario_id" class="form-label fw-semibold">
                                  <i class="fas fa-user me-2 text-info"></i>Seleccionar Usuario
                              </label>
                              <select name="usuario_id" id="usuario_id" class="form-select py-2" required>
                                  <option value="" selected disabled>-- Seleccione un usuario --</option>
                                  <?php
                                  $query = "SELECT id, nombre FROM empleados";
                                  $result = $pdo->query($query);
                                  while ($empleado = $result->fetch(PDO::FETCH_ASSOC)) {
                                      echo "<option value='{$empleado['id']}'>{$empleado['nombre']}</option>";
                                  }
                                  ?>
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
                                  <?php
                                  $query = "SELECT id, nombre FROM proyectos";
                                  $result = $pdo->query($query);
                                  while ($proyecto = $result->fetch(PDO::FETCH_ASSOC)) {
                                      echo "<option value='{$proyecto['id']}'>{$proyecto['nombre']}</option>";
                                  }
                                  ?>
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
                                         min="1" 
                                         required
                                         placeholder="0.00">
                              </div>
                              <div class="invalid-feedback">
                                  Por favor ingrese un salario válido.
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
                                     required>
                              <div class="invalid-feedback">
                                  Por favor seleccione una fecha de asignación.
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

       <div class="modal fade" id="cambiarcontra" tabindex="-1" aria-hidden="true">
           <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content border-0 shadow-lg">
                   <!-- Encabezado del modal -->
                   <div class="modal-header bg-gradient-primary text-white">
                       <div class="d-flex align-items-center">
                           <i class="bi bi-shield-lock fs-4 me-2"></i>
                           <h5 class="modal-title mb-0">Cambiar Contraseña</h5>
                       </div>
                       <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                   
                   <!-- Cuerpo del modal -->
                   <div class="modal-body p-4">
                       <form action="contrasena.php" method="POST" class="needs-validation" novalidate>
                           <!-- Contraseña actual -->
                           <div class="mb-4">
                               <label for="contrasena_actual" class="form-label fw-semibold">
                                   <i class="bi bi-key-fill me-2 text-primary"></i>Contraseña Actual
                               </label>
                               <div class="input-group">
                                   <input type="password" 
                                          class="form-control py-2" 
                                          id="contrasena_actual" 
                                          name="contrasena_actual" 
                                          required
                                          placeholder="Ingresa tu contraseña actual">
                                   <span class="input-group-text bg-white">
                                       <i class="bi bi-eye-slash toggle-password" style="cursor: pointer;"></i>
                                   </span>
                               </div>
                               <div class="invalid-feedback">
                                   Por favor ingresa tu contraseña actual.
                               </div>
                           </div>
                           
                           <!-- Nueva contraseña -->
                           <div class="mb-4">
                               <label for="nueva_contrasena" class="form-label fw-semibold">
                                   <i class="bi bi-key me-2 text-primary"></i>Nueva Contraseña
                               </label>
                               <div class="input-group">
                                   <input type="password" 
                                          class="form-control py-2" 
                                          id="nueva_contrasena" 
                                          name="nueva_contrasena" 
                                          required
                                          minlength="8"
                                          placeholder="Mínimo 8 caracteres">
                                   <span class="input-group-text bg-white">
                                       <i class="bi bi-eye-slash toggle-password" style="cursor: pointer;"></i>
                                   </span>
                               </div>
                               <div class="invalid-feedback">
                                   La contraseña debe tener al menos 8 caracteres.
                               </div>
                               <small class="text-muted">Incluye mayúsculas, números y caracteres especiales para mayor seguridad.</small>
                           </div>
                           
                           <!-- Confirmar nueva contraseña -->
                           <div class="mb-4">
                               <label for="confirmar_contrasena" class="form-label fw-semibold">
                                   <i class="bi bi-key me-2 text-primary"></i>Confirmar Nueva Contraseña
                               </label>
                               <div class="input-group">
                                   <input type="password" 
                                          class="form-control py-2" 
                                          id="confirmar_contrasena" 
                                          name="confirmar_contrasena" 
                                          required
                                          placeholder="Repite tu nueva contraseña">
                                   <span class="input-group-text bg-white">
                                       <i class="bi bi-eye-slash toggle-password" style="cursor: pointer;"></i>
                                   </span>
                               </div>
                               <div class="invalid-feedback">
                                   Las contraseñas deben coincidir.
                               </div>
                           </div>
                           
                           <!-- Botón de enviar -->
                           <div class="d-grid mt-4">
                               <button type="submit" 
                                       name="cambiar_contrasena" 
                                       class="btn btn-primary btn-lg py-2 fw-semibold">
                                   <i class="bi bi-check-circle me-2"></i>Actualizar Contraseña
                               </button>
                           </div>
                       </form>
                   </div>
               </div>
           </div>
       </div>

<script>
    // Función para mostrar/ocultar contraseña
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.closest('.input-group').querySelector('input');
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('bi-eye-slash');
                this.classList.toggle('bi-eye');
            });
        });
        
        // Validación de formulario
        const form = document.querySelector('.needs-validation');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Validar que las contraseñas coincidan
            const nueva = document.getElementById('nueva_contrasena');
            const confirmar = document.getElementById('confirmar_contrasena');
            
            if (nueva.value !== confirmar.value) {
                confirmar.setCustomValidity("Las contraseñas no coinciden");
                confirmar.classList.add('is-invalid');
            } else {
                confirmar.setCustomValidity("");
                confirmar.classList.remove('is-invalid');
            }
            
            form.classList.add('was-validated');
        }, false);
    });
</script>   

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Seleccionamos todos los botones de "Modificar"
    const editButtons = document.querySelectorAll("[data-bs-target='#editModal']");

    editButtons.forEach(button => {
        button.addEventListener("click", function () {
            // Obtener datos del botón presionado
            const id = this.getAttribute("data-id");
            const salario = this.getAttribute("data-salario");
            const proyecto = this.getAttribute("data-proyecto");
            
            // Llenar los campos del modal
            document.getElementById("editId").value = id;
            // Quitar esta línea ya que no existe editNombre
            // document.getElementById("editNombre").value = nombre;
            document.getElementById("editSalario").value = salario;
            
            // Seleccionar el proyecto en el dropdown
            const selectProyecto = document.getElementById("editProyecto");
            for (let i = 0; i < selectProyecto.options.length; i++) {
                if (selectProyecto.options[i].textContent.trim() === proyecto) {
                    selectProyecto.selectedIndex = i;
                    break;
                }
            }
        });
    });
});
</script>

<script>
    let deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        let button = event.relatedTarget;
        let empleadoId = button.getAttribute('data-id');
        let empleadoNombre = button.getAttribute('data-nombre');

        document.getElementById('deleteNombre').textContent = empleadoNombre;
        document.getElementById('deleteConfirmBtn').href = "eliminar_usuario.php?id=" + empleadoId;
    });
    
    function redirectTo() {
        // Cambia 'otra-vista.php' por la URL a la que quieres redirigir
        window.location.href = 'info_general.php';
    }
</script>

<script>
// Validación en tiempo real del salario
function validarSalario(input) {
    if (input.value < 0) {
        input.classList.add('is-invalid');
        input.value = '';
    } else {
        input.classList.remove('is-invalid');
    }
}

// Validación antes de enviar el formulario
document.getElementById('userForm').addEventListener('submit', function(e) {
    const salarioInput = document.querySelector('input[name="salario"]');
    if (salarioInput.value < 0) {
        e.preventDefault();
        salarioInput.classList.add('is-invalid');
        salarioInput.focus();
    }
});
</script>

</body>
</html>