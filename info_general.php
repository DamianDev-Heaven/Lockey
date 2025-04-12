<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$rol = $_SESSION['rol'];

include('config/conexion.php');

// Consulta para contar departamentos
$deptos_stmt = $pdo->prepare("SELECT COUNT(*) FROM departamentos");
$deptos_stmt->execute();
$total_departamentos = $deptos_stmt->fetchColumn();


// Consulta para contar empleados
$empleados_stmt = $pdo->prepare("SELECT COUNT(*) FROM empleados");
$empleados_stmt->execute();
$total_empleados_real = $empleados_stmt->fetchColumn();


//Consulta para contar proyectos activos
$activos_stmt = $pdo->prepare("
    SELECT COUNT(*) FROM empleados_proyectos 
    JOIN estado e ON empleados_proyectos.estado_id = e.id
    WHERE e.estado = 'En proceso';
");
$activos_stmt->execute();
$total_proyectos_activos = $activos_stmt->fetchColumn();




// Consulta para salarios promedio por departamento
$sql = "SELECT d.nombre AS Departamento, 
               AVG(ep.salario) AS salario_promedio 
        FROM empleados e
        INNER JOIN empleados_proyectos ep ON e.id = ep.empleado_id
        INNER JOIN departamentos d ON e.departamento_id = d.id
        GROUP BY d.nombre";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$salarios_dep = $stmt->fetchAll(PDO::FETCH_ASSOC);



// Consulta para empleados y proyectos por departamento
$query = "SELECT d.nombre AS Departamento, 
                 COUNT(DISTINCT e.id) AS cantidad_empleados, 
                 COUNT(DISTINCT ep.proyecto_id) AS cantidad_proyectos 
          FROM empleados e
          INNER JOIN empleados_proyectos ep ON e.id = ep.empleado_id
          INNER JOIN departamentos d ON e.departamento_id = d.id
          GROUP BY d.nombre";
$cmd = $pdo->prepare($query);
$cmd->execute();
$proyectos = $cmd->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales
$total_empleados = array_sum(array_column($proyectos, 'cantidad_empleados'));
$total_proyectos = array_sum(array_column($proyectos, 'cantidad_proyectos')); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control Administrativo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../pages/.css">
    <link rel="stylesheet" href="assets/css/pages/info_general.css">
</head>
<body>
    <!-- menú -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-chart-line me-2"></i>Panel Administrativo
            </a>
            <!-- Reemplazar el span existente en línea 75-78 -->
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
        <form action="logout.php" method="post">
            <button type="submit" class="btn btn-outline-light btn-sm ms-2" name="cerrar_sesion">
                <i class="fas fa-sign-out-alt me-1"></i> Cerrar Sesión
            </button>
        </form>
    </div>
</div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="dashboard-container">
        <!-- Tarjeta de bienvenida -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-chart-pie me-2"></i> Resumen Ejecutivo</h2>
                    <p class="mb-0">Visualiza y gestiona los datos clave de tu organización</p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-light rounded-pill" onclick="redirectToOtherView()">
                        <i class="fas fa-home me-1"></i> Volver al Inicio
                    </button>
                </div>
            </div>
        </div>

        <!-- Métricas resumidas -->
        <div class="metrics-container">
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <i class="fas fa-building fa-2x text-primary me-3"></i>
                    <div>
                        <h5 class="mb-0">Departamentos</h5>
                        <p class="fs-4 fw-bold mb-0"><?= $total_departamentos ?></p>
                    </div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <i class="fas fa-users fa-2x text-primary me-3"></i>
                    <div>
                        <h5 class="mb-0">Empleados</h5>
                        <p class="fs-4 fw-bold mb-0"><?= $total_empleados_real ?></p>
                    </div>
                </div>
            </div>
            
            <div class="metric-card">
                <div class="d-flex align-items-center">
                    <i class="fas fa-project-diagram fa-2x text-primary me-3"></i>
                    <div>
                        <h5 class="mb-0">Proyectos Activos</h5>
                        <p class="fs-4 fw-bold mb-0"><?= $total_proyectos_activos ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de acción -->
        <h3 class="section-title">
            <i class="fas fa-tasks me-2"></i> Reportes Disponibles
        </h3>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card card-action h-100" data-bs-toggle="modal" data-bs-target="#modalSalarios">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-money-bill-wave text-primary fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Salarios Promedio</h5>
                                <p class="card-text text-muted">Por departamento</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card card-action h-100" data-bs-toggle="modal" data-bs-target="#modalProyectos">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                                <i class="fas fa-chart-bar text-info fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="card-title mb-1">Distribución de Personal</h5>
                                <p class="card-text text-muted">Empleados y proyectos por departamento</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Salarios Promedio -->
    <div class="modal fade" id="modalSalarios" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-money-bill-wave me-2"></i> Salarios Promedio por Departamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover">
                            <thead>
                                <tr>
                                    <th>Departamento</th>
                                    <th class="text-end">Salario Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($salarios_dep as $salario) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($salario['Departamento']) ?></td>
                                    <td class="text-end">$<?= number_format($salario['salario_promedio'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer no-print">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button onclick="exportarModalComoPDF()" type="button" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> Exportar
                    </button>
                </div>

            </div>
        </div>
    </div>
    
    <!-- Modal Proyectos y Empleados -->
    <div class="modal fade" id="modalProyectos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-project-diagram me-2"></i> Distribución de Personal y Proyectos
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover">
                            <thead>
                                <tr>
                                    <th>Departamento</th>
                                    <th class="text-center">Empleados</th>
                                    <th class="text-center">Proyectos</th>
                                    <th class="text-center">Empleados por Proyecto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proyectos as $proyecto): ?>
                                <tr>
                                    <td><?= htmlspecialchars($proyecto['Departamento']) ?></td>
                                    <td class="text-center"><?= $proyecto['cantidad_empleados'] ?></td>
                                    <td class="text-center"><?= $proyecto['cantidad_proyectos'] ?></td>
                                    <td class="text-center">
                                        <?= $proyecto['cantidad_proyectos'] > 0 ? 
                                            number_format($proyecto['cantidad_empleados'] / $proyecto['cantidad_proyectos'], 2) : 'N/A' ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-active">
                                    <td><strong>Totales</strong></td>
                                    <td class="text-center"><strong><?= $total_empleados ?></strong></td>
                                    <td class="text-center"><strong><?= $total_proyectos ?></strong></td>
                                    <td class="text-center">
                                        <strong><?= $total_proyectos > 0 ? number_format($total_empleados / $total_proyectos, 2) : 'N/A' ?></strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer no-print1">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button onclick="exportarModalComoPDFProyectos()" type="button" class="btn btn-info text-white">
                        <i class="fas fa-download me-1"></i> Exportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="cambiarcontra" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i> Cambiar Contraseña
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="contrasena.php" method="POST">
                        <div class="mb-3">
                            <label for="contrasena_actual" class="form-label">Contraseña Actual</label>
                            <input type="password" class="form-control" id="contrasena_actual" name="contrasena_actual" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nueva_contrasena" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmar_contrasena" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" name="cambiar_contrasena">
                                <i class="fas fa-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón flotante -->
    <a href="#" class="floating-btn" onclick="redirectToOtherView()">
        <i class="fas fa-home"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function redirectToOtherView() {
            window.location.href = 'admin_dashboard.php';
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
    function exportarModalComoPDF() {
        const element = document.querySelector('#modalSalarios .modal-content');
        const noPrintElements = element.querySelectorAll('.no-print');

        // Oculta los botones
        noPrintElements.forEach(el => el.style.display = 'none');

        const opt = {
            margin:       0.5,
            filename:     'salarios_promedio.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            // Vuelve a mostrar los botones después de exportar
            noPrintElements.forEach(el => el.style.display = '');
        });
    }
    </script>
    <script>
    function exportarModalComoPDFProyectos() {
        const element = document.querySelector('#modalProyectos .modal-content');
        const noPrintElements = element.querySelectorAll('.no-print1');

        // Oculta los botones
        noPrintElements.forEach(el => el.style.display = 'none');

        const opt = {
            margin:       0.5,
            filename:     'distribucion_proyectos.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            // Vuelve a mostrar los botones después de exportar
            noPrintElements.forEach(el => el.style.display = '');
        });
    }
    </script>
</body>
</html>