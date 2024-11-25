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

// Obtener insumos
$queryInsumos = "SELECT * FROM insumos";
$stmtInsumos = $conn->prepare($queryInsumos);
$stmtInsumos->execute();
$insumos = $stmtInsumos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Inventario /</span> Editar Insumos</h4>
        </div>

        <!-- Basic Bootstrap Table -->
        <div class="card">
            <h5 class="card-header">Lista de Insumos</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <?php foreach ($insumos as $insumo): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($insumo['id_insumo']); ?></td>
                                <td><?php echo htmlspecialchars($insumo['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($insumo['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($insumo['cantidad']); ?></td>
                                <td>$<?php echo number_format($insumo['precio'], 2); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($insumo['fecha_registro'])); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm btn-edit" data-bs-toggle="modal"
                                        data-bs-target="#editModal" data-id="<?php echo $insumo['id_insumo']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($insumo['nombre']); ?>"
                                        data-descripcion="<?php echo htmlspecialchars($insumo['descripcion']); ?>"
                                        data-cantidad="<?php echo htmlspecialchars($insumo['cantidad']); ?>"
                                        data-precio="<?php echo htmlspecialchars($insumo['precio']); ?>"
                                        data-fecha="<?php echo htmlspecialchars($insumo['fecha_registro']); ?>">
                                        <i class="bx bx-edit-alt me-1"></i> Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal de Éxito -->
        <div class="modal fade" id="successModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">¡Éxito!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Insumo actualizado correctamente.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" onclick="location.reload()">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Edición -->
        <div class="modal fade" id="editModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Insumo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editForm">
                        <div class="modal-body">
                            <input type="hidden" name="id_insumo" id="edit_id">

                            <div class="mb-3">
                                <label class="form-label">Nombre del Insumo</label>
                                <input type="text" class="form-control" name="nombre_insumo" id="edit_nombre" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control" name="descripcion_insu" id="edit_descripcion" rows="3"
                                    required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control" name="cantidad_insumo" id="edit_cantidad"
                                    required min="0">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio</label>
                                <input type="number" class="form-control" name="precio_insumo" id="edit_precio" required
                                    min="0" step="0.01">
                            </div>

                            <input type="hidden" name="FechaRegistro" id="edit_fecha">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnsEdit = document.querySelectorAll('.btn-edit');

        btnsEdit.forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('edit_id').value = this.dataset.id;
                document.getElementById('edit_nombre').value = this.dataset.nombre;
                document.getElementById('edit_descripcion').value = this.dataset.descripcion;
                document.getElementById('edit_cantidad').value = this.dataset.cantidad;
                document.getElementById('edit_precio').value = this.dataset.precio;
                document.getElementById('edit_fecha').value = this.dataset.fecha;
            });
        });

        document.getElementById('editForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('./editarInsumo', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const editModal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                        editModal.hide();
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                    } else {
                        alert('Error al actualizar: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la actualización');
                });
        });
    });
</script>

<?php require_once __DIR__ . '/Templates/footer.php'; ?>