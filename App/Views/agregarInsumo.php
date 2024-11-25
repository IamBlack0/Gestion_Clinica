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
                                <label for="nombre_insumo" class="form-label">Nombre del Insumo</label>
                                <input type="text" class="form-control" id="nombre_insumo" name="nombre_insumo" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion_insu" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion_insu" name="descripcion_insu"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="cantidad_insumo" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad_insumo" name="cantidad_insumo" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label for="precio_insumo" class="form-label">Precio</label>
                                <input type="number" class="form-control" id="precio_insumo" name="precio_insumo" step="0.01">
                            </div>
                            <div class="mb-3">
                                <label for="fechaRegistro" class="form-label">Fecha de Registro</label>
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
                        <h5 class="modal-title" id="confirmacionModalLabel">Registro de Insumos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="mensajeConfirmacion">
                        <!-- Aquí se mostrará el mensaje de éxito o error -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="refrescarPag()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        
    </div>
</div>

<script>
    function refrescarPag() {
        location.reload();
    }

    // Función para limpiar el formulario al cerrar el modal
    document.addEventListener('DOMContentLoaded', function () {
        var agregarInsumoModal = document.getElementById('agregarInsumoModal');
        agregarInsumoModal.addEventListener('hidden.bs.modal', function () {
            // Reiniciar el formulario
            document.getElementById('agregarInsumoForm').reset();
            // Ocultar campos adicionales
            document.getElementById('camposAdicionales').style.display = 'none';
        });
    });

    function submitAgregarInsumoForm() {

        const form = $('#agregarInsumoForm');
        if (form[0].checkValidity()) {
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                dataType: 'json',
                success: function (response) {
                    
                    const res = (response);
                    if (res.success) {
                        console.log("Registro Exitoso");
                        // Ocultar el modal de agregar producto
                        $('#agregarInsumoModal').modal('hide');

                        // Mostrar el mensaje en el modal de confirmación
                        const mensajeConfirmacion = document.getElementById('mensajeConfirmacion');
                        mensajeConfirmacion.textContent = res.message;

                        // Mostrar el modal de confirmación
                        $('#confirmacionModal').modal('show');

                        actualizarTablaProducto(); 
                    } else {
                        alert(res.message);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error en la solicitud AJAX:", status, error);
                    console.error("Respuesta del servidor:", xhr.responseText);
                    alert("Ocurrió un error. Por favor, intente más tarde.");
                }
            });
        } else {
            form[0].reportValidity();
        }
    }

    function actualizarTablaProducto() {
        $.ajax({
            url: './obtenerInsumos',
            type: 'GET',
            success: function (response) {
                const productos = JSON.parse(response);
                const tbody = $('.table tbody');
                tbody.empty(); // Limpiar la tabla existente

                // Llenar la tabla con los productos
                productos.forEach(insumos => {
                    tbody.append(`
                        <tr>
                            <td>${insumos.id_insumo}</td>
                            <td>${insumos.nombre || 'Nombre no disponible'}</td>
                            <td>${insumos.descripcion || 'Descripcion no disponible'}</td>
                            <td>${insumos.cantidad || 'Cantidad no disponible'}</td>
                            <td>${insumos.precio || 'Precio no disponible'}</td>
                            <td>${insumos.fecha_egistro || 'Fecha no disponible'}</td>
                        </tr>
                    `);
                });
            },
            error: function (xhr, status, error) {
                console.error("Error al actualizar la tabla:", error);
                alert("Ocurrió un error al obtener los productos.");
            }
        });
    }
</script>
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
                                <td><?php echo $insumo['nombre']; ?></td>
                                <td><?php echo $insumo['descripcion'] ?? 'Sin descripción'; ?></td>
                                <td><?php echo $insumo['cantidad']; ?></td>
                                <td><?php echo '$' . number_format($insumo['precio'], 2); ?></td>
                                <td><?php echo $insumo['fecha_registro']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!--/ Basic Bootstrap Table -->

<?php
$footerPath = __DIR__ . '/Templates/footer.php';
if (!$footerPath) {
    die('Error: No se encontró el archivo footer.php en la ruta especificada.');
}
require $footerPath;
?>
