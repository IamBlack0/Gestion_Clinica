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

// Obtener categorías
$queryCategorias = "SELECT categoria_id, nombre FROM categorias";
$stmtCategorias = $conn->prepare($queryCategorias);
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos con toda su información
$queryProductos = "SELECT p.producto_id, 
                         p.nombre, 
                         p.codigo_sku, 
                         p.categoria_id,
                         c.nombre AS categoria_nombre,
                         p.unidad_medida, 
                         ca.cantidad, 
                         pp.precio
                  FROM productos p
                  LEFT JOIN categorias c ON p.categoria_id = c.categoria_id
                  LEFT JOIN cantidad ca ON p.producto_id = ca.producto_id
                  LEFT JOIN productos_proveedores pp ON p.producto_id = pp.producto_id";
$stmtProductos = $conn->prepare($queryProductos);
$stmtProductos->execute();
$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Inventario /</span> Editar Medicamentos</h4>
        <!-- MODAL -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">Éxito</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Medicamento actualizado correctamente.
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
            <h5 class="card-header">Productos</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Codigo/SKU</th>
                            <th>Categoria</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Medida</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo $producto['producto_id']; ?></td>
                                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['codigo_sku']); ?></td>
                                <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                                <td><?php echo htmlspecialchars($producto['precio']); ?></td>
                                <td><?php echo htmlspecialchars($producto['unidad_medida']); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-edit" data-bs-toggle="modal"
                                        data-bs-target="#editModal" data-id="<?php echo $producto['producto_id']; ?>"
                                        data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                                        data-codigo="<?php echo htmlspecialchars($producto['codigo_sku']); ?>"
                                        data-categoria="<?php echo htmlspecialchars($producto['categoria_id']); ?>"
                                        data-cantidad="<?php echo htmlspecialchars($producto['cantidad']); ?>"
                                        data-precio="<?php echo htmlspecialchars($producto['precio']); ?>"
                                        data-medida="<?php echo htmlspecialchars($producto['unidad_medida']); ?>">
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
                        <h5 class="modal-title">Editar Medicamento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editForm" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="producto_id" id="edit_producto_id">

                            <div class="mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" class="form-control" name="nombre" id="edit_nombre" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Código SKU</label>
                                <input type="text" class="form-control" name="codigo" id="edit_codigo" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Categoría</label>
                                <select name="tipoProducto" class="form-select" id="edit_categoria" required>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['categoria_id']; ?>">
                                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" class="form-control" name="cantidad" id="edit_cantidad" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio</label>
                                <input type="number" step="0.01" class="form-control" name="precio" id="edit_precio"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Medida</label>
                                <select class="form-select" name="forma" id="edit_forma" required>
                                    <option value="Tableta">Tableta</option>
                                    <option value="Capsula">Cápsula</option>
                                    <option value="Jarabe">Jarabe</option>
                                </select>
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
                        const id = this.dataset.id;
                        const nombre = this.dataset.nombre;
                        const codigo = this.dataset.codigo;
                        const categoria = this.dataset.categoria;
                        const cantidad = this.dataset.cantidad;
                        const precio = this.dataset.precio;
                        const medida = this.dataset.medida;

                        // Llenar el formulario del modal
                        document.getElementById('edit_producto_id').value = id;
                        document.getElementById('edit_nombre').value = nombre;
                        document.getElementById('edit_codigo').value = codigo;
                        document.getElementById('edit_categoria').value = categoria;
                        document.getElementById('edit_cantidad').value = cantidad;
                        document.getElementById('edit_precio').value = precio;
                        document.getElementById('edit_forma').value = medida;
                    });
                });

                // Manejar el envío del formulario
                document.getElementById('editForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    fetch('./editarProducto', {
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