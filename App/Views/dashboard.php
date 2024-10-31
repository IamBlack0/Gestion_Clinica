<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!$headerPath) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;
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

        <?php
        // Verificar si hay un mensaje de éxito en la URL
        $mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';

        if ($mensaje === 'suceso') {
            echo '<div class="alert alert-success" role="alert">Su cita ha sido agendada con éxito. Puede ver el estado en "Ver Citas".</div>';
        } elseif ($mensaje === 'cita_terminada') {
            echo '<div class="alert alert-info" role="alert">La cita ha terminado. Puede ver los detalles en "Ver Citas".</div>';
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