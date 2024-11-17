<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!$headerPath) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;

// Incluir archivo de base de datos y crear conexión
require_once __DIR__ . '/../../Config/DataBase.php';
$database = new Database();
$db = $database->getConnection();

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ./login');
    exit();
}

$queryMedicos = "SELECT COUNT(*) as total FROM colaboradores WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'medico')";
$stmtMedicos = $db->query($queryMedicos);
$totalMedicos = $stmtMedicos->fetch(PDO::FETCH_ASSOC)['total'];

// Obtener total de pacientes
$queryPacientes = "SELECT COUNT(*) as total FROM pacientes";
$stmtPacientes = $db->query($queryPacientes);
$totalPacientes = $stmtPacientes->fetch(PDO::FETCH_ASSOC)['total'];

// Obtener total de citas del día
$queryHoy = "SELECT COUNT(*) as total FROM citas WHERE fecha_cita = CURRENT_DATE";
$stmtHoy = $db->query($queryHoy);
$citasHoy = $stmtHoy->fetch(PDO::FETCH_ASSOC)['total'];



// Verificar si es un paciente
$queryRol = "SELECT r.nombre as rol FROM usuarios u 
             JOIN roles r ON u.rol_id = r.id 
             WHERE u.id = :user_id";
$stmtRol = $db->prepare($queryRol);
$stmtRol->bindParam(':user_id', $_SESSION['user_id']);
$stmtRol->execute();
$rolUsuario = $stmtRol->fetch(PDO::FETCH_ASSOC);

if ($rolUsuario['rol'] === 'paciente') {
    // Verificar si tiene información completa
    $queryInfo = "SELECT ip.* FROM informacion_paciente ip 
                  JOIN pacientes p ON ip.paciente_id = p.id 
                  WHERE p.usuario_id = :user_id";
    $stmtInfo = $db->prepare($queryInfo);
    $stmtInfo->bindParam(':user_id', $_SESSION['user_id']);
    $stmtInfo->execute();
    $infoCompleta = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    // Verificar campos requeridos
    $camposIncompletos = !$infoCompleta ||
        empty($infoCompleta['cedula']) ||
        empty($infoCompleta['fecha_nacimiento']) ||
        empty($infoCompleta['sexo']);

    if ($camposIncompletos) {
        echo '
        <!-- Modal -->
        <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="infoModalLabel">Información Requerida</h5>
                    </div>
                    <div class="modal-body">
                        <p>Para brindarle un mejor servicio, necesitamos que complete su información personal.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="window.location.href=\'./configuracionCuenta\'">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var modal = new bootstrap.Modal(document.getElementById("infoModal"));
                modal.show();
            });
        </script>';
    }
}
?>


<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <?php
        // Verificar si hay un mensaje de éxito en la URL
        $mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';

        if ($mensaje === 'suceso') {
            echo '<div class="alert alert-success" role="alert">Su cita ha sido agendada con éxito. Puede ver el estado en "Ver Citas".</div>';
        }
        ?>
        <div class="row">
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Bienvenido <?php echo htmlspecialchars($_SESSION['nombre']); ?>! 🎉</h5>
                                <p class="mb-4">Sistema de Gestión Clínica</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
    <!-- Tarjeta de Médicos -->
    <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="card-info">
                        <p class="card-text">Médicos Activos</p>
                        <div class="d-flex align-items-end mb-2">
                            <h4 class="card-title mb-0"><?php echo $totalMedicos; ?></h4>
                                </div>
                            </div>
                            <div class="card-icon">
                                <span class="badge bg-label-primary p-3">
                                    <i class="bx bx-user-circle"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
            <?php if ($rolUsuario['rol'] !== 'paciente'): ?>
                <!-- Tarjeta de Pacientes (solo visible para colaboradores) -->
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div class="card-info">
                                    <p class="card-text">Pacientes Registrados</p>
                                    <div class="d-flex align-items-end mb-2">
                                        <h4 class="card-title mb-0"><?php echo $totalPacientes; ?></h4>
                                    </div>
                                </div>
                                <div class="card-icon">
                                    <span class="badge bg-label-success p-3">
                                        <i class="bx bx-group"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        
            <?php if ($rolUsuario['rol'] !== 'paciente'): ?>
            <!-- Tarjeta de Citas Hoy -->
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text">Citas Hoy</p>
                                <div class="d-flex align-items-end mb-2">
                                    <h4 class="card-title mb-0"><?php echo $citasHoy; ?></h4>
                                </div>
                            </div>
                            <div class="card-icon">
                                <span class="badge bg-label-warning p-3">
                                    <i class="bx bx-calendar"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- / Content -->




    <?php
    // Verificar rutas
    $footerPath = __DIR__ . '/Templates/footer.php';
    if (!$footerPath) {
        die('Error: No se encontró el archivo footer.php en la ruta especificada.');
    }
    require $footerPath;
    ?>