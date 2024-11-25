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

// Obtener productos con toda su información
$queryInsumos = "SELECT *
                  FROM insumos";
$stmtInsumos = $conn->prepare($queryInsumos);
$stmtInsumos->execute();
$insumos = $stmtInsumos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Inventario /</span> Editar insumos</h4>
        <!-- MODAL -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">Éxito</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Insumo actualizado correctamente.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            onclick="refrescarPag()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- FIN MODAL -->
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
                                <td><?php echo htmlspecialchars($insumo['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($insumo['descripcion']); ?></td>
                                <td><?php echo htmlspecialchars($insumo['cantidad']); ?></td>
                                <td><?php echo htmlspecialchars($insumo['precio']); ?></td>
                                <td><?php echo htmlspecialchars($insumo['fecha_registro']); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-edit" data-bs-toggle="modal"
                                        data-bs-target="#editModal" data-id="<?php echo $insumo['id_insumo']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($insumo['nombre']); ?>"
                                        data-codigo="<?php echo htmlspecialchars($insumo['descripcion']); ?>"
                                        data-categoria="<?php echo htmlspecialchars($insumo['cantidad']); ?>"
                                        data-cantidad="<?php echo htmlspecialchars($insumo['precio']); ?>"
                                        data-precio="<?php echo htmlspecialchars($insumo['fecha_registro']); ?>">
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="editModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Insumos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editForm" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="id_insumo" id="id_insumo">

                            <div class="mb-3">
                                <label class="form-label">Nombre del Insumo</label>
                                <input type="text" class="form-control" name="nombre_insumo" id="nombre_insumo" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Descripcion</label>
                                <input type="text" class="form-control" name="descripcion_insu" id="descripcion_insu" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="text" class="form-control" name="cantidad_insumo" id="cantidad_insumo" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio</label>
                                <input type="number" class="form-control" name="precio_insumo" id="precio_insumo" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Fecha Registro</label>
                                <input type="number" step="0.01" class="form-control" name="fechaRegistro" id="fechaRegistro"required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function refrescarPag() {
                location.reload();
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Manejar el clic en el botón editar
                const btnsEdit = document.querySelectorAll('.btn-edit');
                btnsEdit.forEach(btn => {
                    btn.addEventListener('click', function () {
                        // Obtener datos del botón
                        const id_insumo = this.dataset.id_insumo;
                        const nombre_insumo = this.dataset.nombre_insumo;
                        const descripcion_insu = this.dataset.descripcion_insu;
                        const cantidad_insumo = this.dataset.cantidad_insumo;
                        const precio_insumo = this.dataset.precio_insumo;
                        const fechaRegistro = this.dataset.fechaRegistro;

                        // Llenar el formulario del modal
                        document.getElementById('id_insumo').value = id_insumo;
                        document.getElementById('nombre_insumo').value = nombre_insumo;
                        document.getElementById('descripcion_insu').value = descripcion_insu;
                        document.getElementById('cantidad_insumo').value = cantidad_insumo;
                        document.getElementById('precio_insumo').value = precio_insumo;
                        document.getElementById('FechaRegistro').value = fechaRegistro;
                    });
                });

                // Manejar el envío del formulario
                document.getElementById('editForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    fetch('./editarInsumos', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Cerrar modal de edición
                                bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
                                // Mostrar modal de éxito
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
    </div>
</div>
<?php
// Verificar rutas
$footerPath = __DIR__ . '/Templates/footer.php';
if (!$footerPath) {
    die('Error: No se encontró el archivo footer.php en la ruta especificada.');
}
require $footerPath;
?>