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

// Obtener productos con su stock actual
$queryProductos = "SELECT p.producto_id, p.nombre, p.codigo_sku, c.nombre AS categoria_nombre, 
                          ca.cantidad, pp.precio, p.unidad_medida
                   FROM productos p
                   LEFT JOIN categorias c ON p.categoria_id = c.categoria_id
                   LEFT JOIN cantidad ca ON p.producto_id = ca.producto_id
                   LEFT JOIN productos_proveedores pp ON p.producto_id = pp.producto_id
                   WHERE ca.cantidad > 0";
$stmtProductos = $conn->prepare($queryProductos);
$stmtProductos->execute();
$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Inventario /</span> Salida de Productos</h4>
        
        <!-- Botón para registrar salida -->
        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarSalidaModal">
                <i class="bx bx-minus me-2"></i> Registrar Salida
            </button>
        </div>

        <!-- Modal para registrar salida -->
        <div class="modal fade" id="agregarSalidaModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar Salida de Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="agregarSalidaForm" action="./salidaProducto" method="POST">
                            <div class="mb-3">
                                <label for="producto_id" class="form-label">Producto</label>
                                <select class="form-select" id="producto_id" name="producto_id" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                            <option value="<?php echo htmlspecialchars($producto['producto_id']); ?>">
                                                <?php echo htmlspecialchars($producto['nombre']) . ' - Stock: ' .
                                                    htmlspecialchars($producto['cantidad']); ?>
                                            </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="cantidadSalida" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidadSalida" name="cantidadSalida" 
                                       min="1" required>
                            </div>
                            <div class="mb-3">
                                <label for="fechaSalida" class="form-label">Fecha de Salida</label>
                                <input type="date" class="form-control" id="fechaSalida" name="fechaSalida" required>
                            </div>
                            <input type="hidden" name="movimiento" value="Salida">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" onclick="submitSalidaForm()">Registrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmación -->
        <div class="modal fade" id="confirmacionModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="mensajeConfirmacion"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de productos -->
        <div class="card">
            <h5 class="card-header">Inventario Actual</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock</th>
                            <th>Unidad de Medida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['unidad_medida']); ?></td>
                                </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function submitSalidaForm() {
    const form = $('#agregarSalidaForm');
    if (form[0].checkValidity()) {
        const formData = form.serialize();
        
        $.ajax({
            url: './salidaProducto',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#agregarSalidaModal').modal('hide');
                $('#mensajeConfirmacion').text(response.message);
                $('#confirmacionModal').modal('show');
                
                if (response.success) {
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                alert('Error al procesar la salida: ' + error);
            }
        });
    } else {
        form[0].reportValidity();
    }
}

// Validar stock disponible al cambiar cantidad
$('#cantidadSalida').on('input', function() {
    const productoId = $('#producto_id').val();
    const cantidadSolicitada = parseInt($(this).val());
    const option = $(`#producto_id option[value="${productoId}"]`);
    const stockDisponible = parseInt(option.text().split('Stock: ')[1]);
    
    if (cantidadSolicitada > stockDisponible) {
        alert('La cantidad solicitada excede el stock disponible');
        $(this).val(stockDisponible);
    }
});
</script>

<?php require __DIR__ . '/Templates/footer.php'; ?>