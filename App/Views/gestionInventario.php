<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!$headerPath) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;

// Conexión a la base de datos
require_once __DIR__ . '/../../Config/DataBase.php'; // Corregir la ruta aquí
$db = new DataBase();
$conn = $db->getConnection();

// Obtener productos de la base de datos
$queryProductos = "SELECT * FROM productos";
$stmtProductos = $conn->prepare($queryProductos);
$stmtProductos->execute();
$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

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
                        <form id="agregarProductoForm" action="./agregarUsuario" method="POST">
                            <div class="mb-3">
                                <label for="tipoProducto" class="form-label">Tipo de Producto</label>
                                <select class="form-select" id="tipoProducto" name="tipoProducto" required
                                    onchange="mostrarCamposAdicionales()">
                                    <option value="" selected disabled>Seleccione el tipo de Producto</option>
                                    <!-- <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nombre']; ?></option>
                                    <?php endforeach; ?> -->
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
                                    <label for="cantidad" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="precio" class="form-label">Precio</label>
                                    <input type="number" class="form-control" id="precio" name="precio" min="0.01" required>
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
                        <button type="button" class="btn btn-primary" onclick="submitAgregarProductoForm()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
    function mostrarCamposAdicionales() {
        const tipoProducto = document.getElementById('tipoProducto').value;
        const camposAdicionales = document.getElementById('camposAdicionales');
        if (tipoProducto == 1 || tipoProducto == 2) { // Asumiendo que los IDs de los roles son 1 y 2
            camposAdicionales.style.display = 'block';
        } else {
            camposAdicionales.style.display = 'none';
        }
    }

    function submitAgregarProductoForm() {
        const form = $('#agregarProductoForm');
        if (form[0].checkValidity()) {
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                success: function(response) {
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
            success: function(response) {
                console.log(response);
                const productos = JSON.parse(response);
                const tbody = $('.table tbody');
                tbody.empty();
                productos.forEach(producto => {
                    tbody.append(`
                        <tr>
                            <td>${producto.producto_id}</td>
                            <td>${producto.nombre || 'Nombre no disponible'}</td>
                            <td>${producto.codigo_sku || 'Codigo no disponible'}</td>
                            <td>${producto.categoria_nombre}</td>
                            <td>${producto.descripcion || 'Descripcion no disponible'}</td>
                            <td>${producto.unidad_medida || 'Unidad/Medida no disponible'}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="editarUsuario(${producto.id})">
                                            <i class="bx bx-edit-alt me-2"></i> Editar
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarUsuario(${producto.id})">
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
                            <th>Descripcion</th>
                            <th>Medida</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo $producto['producto_id']; ?></td>
                                <td><?php echo isset($producto['nombre']) ? $producto['nombre'] : 'Nombre no disponible'; ?></td>
                                <td><?php echo isset($producto['codigo_sku']) ? $producto['codigo_sku'] : 'Código no disponible'; ?></td>
                                <td><?php echo isset($producto['categoria_nombre']) ? $producto['categoria_nombre'] : 'Categoría no disponible'; ?></td>
                                <td><?php echo isset($producto['descripcion']) ? $producto['descripcion'] : 'Descripción no disponible'; ?></td>
                                <td><?php echo isset($producto['unidad_medida']) ? $producto['unidad_medida'] : 'Unidad/Medida no disponible'; ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <!-- <a class="dropdown-item" href="javascript:void(0);"
                                                onclick="editarProducto(<?php echo $producto['id']; ?>)">
                                                <i class="bx bx-edit-alt me-2"></i> Editar
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                onclick="eliminarProducto(<?php echo $producto['id']; ?>)">
                                                <i class="bx bx-trash me-2"></i> Eliminar
                                            </a> -->
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

<script>
    function editarProducto(id) {
        // Redirigir a la página de edición con el ID del producto
        window.location.href = './editarProducto?id=' + id;
    }

    function eliminarProducto(id) {
        // Confirmar la eliminación del usuario
        if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
            // Redirigir a la página de eliminación con el ID del producto
            window.location.href = './eliminarProducto?id=' + id;
        }
    }
</script>

<?php
// Verificar rutas
$footerPath = __DIR__ . '/Templates/footer.php';
if (!$footerPath) {
    die('Error: No se encontró el archivo footer.php en la ruta especificada.');
}
require $footerPath;
?>