<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!$headerPath) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;

// Conexión a la base de datos
require_once './Config/DataBase.php';
$db = new DataBase();
$conn = $db->getConnection();

// Obtener insumos de la base de datos
$queryInsumos = "SELECT * FROM insumos";
$stmtInsumos = $conn->prepare($queryInsumos);
$stmtInsumos->execute();
$insumos = $stmtInsumos->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Inventario /</span> Agregar Insumo</h4>
        <!-- Button trigger modal -->
        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarInsumoModal">
                <i class="bx bx-plus me-2"></i> Agregar Insumo
            </button>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="agregarInsumoModal" tabindex="-1" aria-labelledby="agregarInsumoModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="agregarInsumoModalLabel">Agregar Insumo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="agregarInsumoForm" action="./agregarInsumo" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre del Insumo</label>
                                <input type="text" class="form-control" id="nombre_insumo" name="nombre_insumo" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion_insu" name="descripcion_insu"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad_insumo" name="cantidad_insumo" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio</label>
                                <input type="number" class="form-control" id="precio_insumo" name="precio_insumo" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label for="fecha_registro" class="form-label">Fecha de Registro</label>
                                <input type="date" class="form-control" id="fechaRegistro" name="fechaRegistro">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="submitAgregarInsumoForm()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación -->
        <div class="modal fade" id="confirmacionModal" tabindex="-1" aria-labelledby="confirmacionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmacionModalLabel">Registro de Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="mensajeConfirmacion">
                        <!-- Aquí se mostrará el mensaje de éxito o error -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Basic Bootstrap Table -->
        <div class="card">
            <h5 class="card-header">Insumos</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Fecha de Registro</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <?php foreach ($insumos as $insumo): ?>
                            <tr>
                                <td><?php echo $insumo['id_insumo']; ?></td>
                                <td><?php echo $insumo['nombre_insumo']; ?></td>
                                <td><?php echo $insumo['descripcion_insu'] ?? 'Sin descripción'; ?></td>
                                <td><?php echo $insumo['cantidad_insumo']; ?></td>
                                <td><?php echo '$' . number_format($insumo['precio_insumo'], 2); ?></td>
                                <td><?php echo $insumo['fechaRegistro']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!--/ Basic Bootstrap Table -->
    </div>
</div>

<script>
    function submitAgregarInsumoForm() {
        const form = $('#agregarInsumoForm');
        if (form[0].checkValidity()) {
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        console.log("Registro Exitoso");
                        // Ocultar el modal de agregar producto
                        $('#agregarInsumoModal').modal('hide');

                        // Mostrar el mensaje en el modal de confirmación
                        const mensajeConfirmacion = document.getElementById('mensajeConfirmacion');
                        mensajeConfirmacion.textContent = res.message;

                        // Mostrar el modal de confirmación
                        $('#confirmacionModal').modal('show');

                    } else {
                        alert(res.message);
                    }
                }
            });
        } else {
            form[0].reportValidity();
        }
    }
</script>

<?php
$footerPath = __DIR__ . '/Templates/footer.php';
if (!$footerPath) {
    die('Error: No se encontró el archivo footer.php en la ruta especificada.');
}
require $footerPath;
?>
