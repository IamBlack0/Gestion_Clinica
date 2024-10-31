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

?>
<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Inventario /</span> Agregar Medicamentos</h4>
        <!-- Button trigger modal -->
        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarProductoModal">
                <i class="bx bx-plus me-2"></i> Agregar Medicamento
            </button>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="agregarProductoModal" tabindex="-1" aria-labelledby="agregarProductoModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="agregarProductoModalLabel">Agregar Medicamento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="agregarProductoForm" action="./agregarProducto" method="POST">
                            <div class="mb-3">
                                <label for="tipoProducto" class="form-label">Tipo de Medicamento</label>
                                <select class="form-select" id="tipoProducto" name="tipoProducto" required
                                    onchange="mostrarCamposAdicionales()">
                                    <option value="" selected disabled>Seleccione el Tipo de Medicamento</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['categoria_id']; ?>">
                                            <?php echo $categoria['nombre']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="camposAdicionales" style="display: none;">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre del Medicamento</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label for="codigo" class="form-label">Codigo o SKU</label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" required>
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
                                    <label for="precio" class="form-label">Precio</label>
                                    <input type="number" class="form-control" id="precio" name="precio" step="0.01"
                                        required>
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

        <!-- Modal de confirmación -->
        <div class="modal fade" id="confirmacionModal" tabindex="-1" aria-labelledby="confirmacionModalLabel" aria-hidden="true">
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

        <!-- Modal de Editar -->
        <div class="modal fade" id="editarProducto" tabindex="-1" aria-labelledby="editarProductoLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarProductoLabel">Editar Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarProducto">
                            <input type="hidden" id="producto_id" name="producto_id">
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre">
                            </div>
                            <div class="mb-3">
                                <label for="codigo_sku" class="form-label">Código/SKU</label>
                                <input type="text" class="form-control" id="codigo_sku" name="codigo_sku">
                            </div>
                            <div class="mb-3">
                                <label for="categoria_nombre" class="form-label">Categoria</label>
                                <input type="text" class="form-control" id="categoria_nombre" name="categoria_nombre">
                            </div>
                            <div class="mb-3">
                                <label for="cantidad" class="form-label">Cantidad</label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad">
                            </div>
                            <div class="mb-3">
                                <label for="precio" class="form-label">Precio</label>
                                <input type="number" class="form-control" id="precio" name="precio">
                            </div>
                            <div class="mb-3">
                                <label for="unidad_medida" class="form-label">Medida</label>
                                <input type="text" class="form-control" id="unidad_medida" name="unidad_medida">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="guardarCambios">Guardar cambios</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Función para limpiar el formulario al cerrar el modal
            document.addEventListener('DOMContentLoaded', function () {
                var agregarProductoModal = document.getElementById('agregarProductoModal');
                agregarProductoModal.addEventListener('hidden.bs.modal', function () {
                    // Reiniciar el formulario
                    document.getElementById('agregarProductoForm').reset();
                    // Ocultar campos adicionales
                    document.getElementById('camposAdicionales').style.display = 'none';
                });
            });
            
            // Evento al hacer clic en el botón de editar
            $(document).ready(function() {
                // Evento al hacer clic en el botón de editar
                $('.btn-editar-producto').on('click', function() {
                    console.log('boton de editar clicked');
                    
                    // Obtiene el ID del producto
                    const productoId = $(this).data('id');

                    // Llama a la función para obtener los datos del producto
                    obtenerDatosProducto(productoId);
                });

                // Función para obtener los datos del producto por ID
                function obtenerDatosProducto(productoId) {
                    $.ajax({
                        url: './getProductoById', // Cambia esto por la ruta a tu script PHP
                        type: 'POST', // Cambia a POST
                        data: { producto_id: productoId }, // Envía el ID del producto al servidor
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                const producto = response.producto; // Obtiene el producto de la respuesta
                                // Rellena los campos del modal con los datos del producto
                                llenarCamposModal(producto);
                            } else {
                                console.error(response.message);
                                // Aquí puedes mostrar un mensaje al usuario si no se obtienen los datos
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error('Error al obtener los datos:', textStatus, errorThrown);
                            // Manejar errores aquí, como mostrar un mensaje al usuario
                        }
                    });
                }

                // Función para llenar los campos del modal
                function llenarCamposModal(producto) {
                    $('#producto_id').val(producto.producto_id);
                    $('#nombre').val(producto.nombre);
                    $('#codigo_sku').val(producto.codigo_sku);
                    $('#categoria_nombre').val(producto.categoria_nombre);
                    $('#cantidad').val(producto.cantidad);
                    $('#precio').val(producto.precio);
                    $('#unidad_medida').val(producto.unidad_medida);
                }
            });

            // Función para llenar los campos del modal
            function llenarCamposModal(producto) {
                $('#producto_id').val(producto.producto_id);
                $('#nombre').val(producto.nombre);
                $('#codigo_sku').val(producto.codigo_sku);
                $('#categoria_nombre').val(producto.categoria_nombre);
                $('#cantidad').val(producto.cantidad);
                $('#precio').val(producto.precio);
                $('#unidad_medida').val(producto.unidad_medida);
            }

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
                                console.log("Registro Exitoso");
                                // Ocultar el modal de agregar producto
                                $('#agregarProductoModal').modal('hide');

                                // Mostrar el mensaje en el modal de confirmación
                                const mensajeConfirmacion = document.getElementById('mensajeConfirmacion');
                                mensajeConfirmacion.textContent = res.message;

                                // Mostrar el modal de confirmación
                                $('#confirmacionModal').modal('show');

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
                        tbody.empty(); // Limpiar la tabla existente

                        // Llenar la tabla con los productos
                        productos.forEach(producto => {
                            tbody.append(`
                                <tr>
                                    <td>${producto.producto_id}</td>
                                    <td>${producto.nombre || 'Nombre no disponible'}</td>
                                    <td>${producto.codigo_sku || 'Código no disponible'}</td>
                                    <td>${producto.categoria_nombre || 'Categoría no disponible'}</td>
                                    <td>${producto.cantidad || 'Cantidad no disponible'}</td>
                                    <td>${producto.precio || 'Precio no disponible'}</td>
                                    <td>${producto.unidad_medida || 'Unidad/Medida no disponible'}</td>
                                    <td>
                                    <button type="button" class="btn p-0 btn-editar-producto text-decoration-underline" data-bs-toggle="modal" data-bs-target="#editarProducto">
                                        Editar
                                    </button>
                                </td>
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
                                <td><?php echo $producto['nombre'] ?? 'Nombre no disponible'; ?></td>
                                <td><?php echo $producto['codigo_sku'] ?? 'Código no disponible'; ?></td>
                                <td><?php echo $producto['categoria_nombre'] ?? 'Categoría no disponible'; ?></td>
                                <td><?php echo $producto['cantidad'] ?? 'Cantidad no disponible'; ?></td>
                                <td><?php echo '$' . number_format($producto['precio'], 2) ?? 'Precio no disponible'; ?></td>
                                <td><?php echo $producto['unidad_medida'] ?? 'Unidad/Medida no disponible'; ?></td>
                                <td>
                                    <button type="button" class="btn p-0 btn-editar-producto text-decoration-underline" data-bs-toggle="modal" data-bs-target="#editarProducto" data-id="<?php echo $producto['producto_id']; ?>">
                                        Editar
                                    </button>
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