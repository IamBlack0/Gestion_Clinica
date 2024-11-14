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
                        <div class="col-sm-10">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Bienvenido</h5>
                                Aqui va la info
                            </div>
                        </div>
                        <div class="col-sm-5 text-center text-sm-left">
                            <div class="card-body pb-0 px-0 px-md-4">

                            </div>
                        </div>
                    </div>
                </div>
            </div>

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