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

// Obtener productos de la base de datos
$queryCategorias = "SELECT categoria_id, nombre FROM categorias";
$stmtCategorias = $conn->prepare($queryCategorias);
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

// Obtener proveedores de la base de datos
$queryProveedores = "SELECT proveedor_id, nombre FROM proveedores"; // Asegúrate de que esta tabla existe
$stmtProveedores = $conn->prepare($queryProveedores);
$stmtProveedores->execute();
$proveedores = $stmtProveedores->fetchAll(PDO::FETCH_ASSOC);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Inventario /</span> Gestionar Inventario</h4>
        <!-- Button trigger modal -->
        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                <i class="bx bx-plus me-2"></i> Agregar Producto
            </button>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="agregarProductoModal" tabindex="-1" aria-labelledby="agregarProductoModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="agregarProductoModalLabel">Agregar Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="agregarProductoForm" action="./agregarProducto" method="POST">
                            <div class="mb-3">
                                <label for="tipoProducto" class="form-label">Tipo de Producto</label>
                                <select class="form-select" id="tipoProducto" name="tipoProducto" required
                                    onchange="mostrarCamposAdicionales()">
                                    <option value="" selected disabled>Seleccione el tipo de Producto</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['categoria_id']; ?>">
                                            <?php echo $categoria['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="camposAdicionales" style="display: none;">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre del Producto</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Codigo o SKU</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" required>
                                </div>
                                <div class="mb-3">
                                    <label for="desc" class="form-label">Descripcion</label>
                                    <input type="text" class="form-control" id="desc" name="desc" required>
                                </div>
                                <div class="mb-3">
                                    <label for="forma" class="form-label">Forma</label>
                                    <select class="form-select" id="forma" name="forma" required>
                                        <option value="">Seleccione una forma</option>
                                        <option value="Tableta">Tableta</option>
                                        <option value="Capsula">Cápsula</option>
                                        <option value="Jarabe">Jarabe</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="0"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="proveedor" class="form-label">Proveedor</label>
                                    <select class="form-select" id="proveedor" name="proveedor_id" required
                                        onchange="updateProveedorName()">
                                        <option value="" selected disabled>Seleccione el proveedor</option>
                                        <?php foreach ($proveedores as $proveedor): ?>
                                            <option value="<?php echo $proveedor['proveedor_id']; ?>"
                                                data-nombre="<?php echo $proveedor['nombre']; ?>">
                                                <?php echo $proveedor['nombre']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="proveedor_nombre" id="proveedor_nombre">
                                </div>
                                <div class="mb-3">
                                    <label for="contacto" class="form-label">Contacto del Proveedor</label>
                                    <input type="text" class="form-control" id="contacto" name="contacto" required>
                                </div>
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Teléfono del Contacto</label>
                                    <input type="number" class="form-control" id="telefono" name="telefono" required>
                                </div>
                                <div class="mb-3">
                                    <label for="precio" class="form-label">Precio</label>
                                    <input type="number" class="form-control" id="precio" name="precio" step="0.01"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="almacen" class="form-label">¿En qué almacén?</label>
                                    <input type="text" class="form-control" id="almacen" name="almacen" required>
                                </div>
                                <div class="mb-3">
                                    <label for="fecha-registro" class="form-label">Fecha de Registro</label>
                                    <input type="date" class="form-control" id="fecha-registro" name="fecha-registro"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="movimiento" class="form-label">Movimiento</label>
                                    <input type="text" class="form-control" id="movimiento" name="movimiento"
                                        value="Entrada" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="fecha" class="form-label">Fecha de Expiracion</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary"
                            onclick="submitAgregarProductoForm()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function mostrarCamposAdicionales() {
                const tipoProducto = document.getElementById('tipoProducto').value;
                const camposAdicionales = document.getElementById('camposAdicionales');
                if (tipoProducto) {
                    camposAdicionales.style.display = 'block';
                } else {
                    camposAdicionales.style.display = 'none';
                }
            }

            function updateProveedorName() {
                const select = document.getElementById('proveedor');
                const selectedOption = select.options[select.selectedIndex];
                const proveedorNombreInput = document.getElementById('proveedor_nombre');

                // Asignar el nombre del proveedor al campo oculto
                proveedorNombreInput.value = selectedOption.getAttribute('data-nombre');
            }

            function submitAgregarProductoForm() {
                const form = $('#agregarProductoForm');
                if (form[0].checkValidity()) {
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        success: function (response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                alert(res.message);
                                $('#agregarProductoModal').modal('hide');
                                actualizarTablaProducto();
                            } else {
                                alert(res.message);
                            }
                        }
                    });
                } else {
                    form[0].reportValidity();
                }
            }

            function actualizarTablaProducto() {
                $.ajax({
                    url: './obtenerInventarios',
                    type: 'GET',
                    success: function (response) {
                        const productos = JSON.parse(response);
                        const tbody = $('.table tbody');
                        tbody.empty();
                        productos.forEach(producto => {
                            tbody.append(`
                        <tr>
                            <td>${producto.producto_id}</td>
                            <td>${producto.nombre || 'Nombre no disponible'}</td>
                            <td>${producto.codigo_sku || 'Codigo no disponible'}</td>
                            <td>${producto.categoria_nombre || 'Categoría no disponible'}</td>
                            <td>${producto.cantidad || 'Cantidad no disponible'}</td>
                            <td>${producto.precio || 'Precio no disponible'}</td>
                            <td>${producto.unidad_medida || 'Unidad/Medida no disponible'}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="editarProducto(${producto.id})">
                                            <i class="bx bx-edit-alt me-2"></i> Editar
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarProducto(${producto.id})">
                                            <i class="bx bx-trash me-2"></i> Eliminar
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    `);
                        });
                    }
                });
            }
        </script>

        <!-- Basic Bootstrap Table -->
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
                                <td><?php echo isset($producto['nombre']) ? $producto['nombre'] : 'Nombre no disponible'; ?>
                                </td>
                                <td><?php echo isset($producto['codigo_sku']) ? $producto['codigo_sku'] : 'Código no disponible'; ?>
                                </td>
                                <td><?php echo isset($producto['categoria_nombre']) ? $producto['categoria_nombre'] : 'Categoría no disponible'; ?>
                                </td>
                                <td><?php echo isset($producto['cantidad']) ? $producto['cantidad'] : 'Cantidad no disponible'; ?>
                                </td>
                                <td><?php echo isset($producto['precio']) ? '$' . number_format($producto['precio'], 2) : 'Precio no disponible'; ?>
                                </td>
                                <td><?php echo isset($producto['unidad_medida']) ? $producto['unidad_medida'] : 'Unidad/Medida no disponible'; ?>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item">
                                                <i class="bx bx-edit-alt me-2"></i> Editar
                                            </a>
                                            <a class="dropdown-item">
                                                <i class="bx bx-trash me-2"></i> Eliminar
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!--/ Basic Bootstrap Table -->
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