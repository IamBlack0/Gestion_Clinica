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
$queryFecha = "SELECT fecha_expiracion FROM productos"; // Asegúrate de que esta tabla existe
$stmtFecha = $conn->prepare($queryFecha);
$stmtFecha->execute();
$fecha = $stmtFecha->fetchAll(PDO::FETCH_ASSOC);
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
                                <td>
                                    <span class="producto_id"><?php echo $producto['producto_id']; ?></span>
                                </td>
                                <td>
                                    <span class="nombre"><?php echo $producto['nombre'] ?? 'Nombre no disponible'; ?></span>
                                </td>
                                <td>
                                    <span
                                        class="codigo_sku"><?php echo $producto['codigo_sku'] ?? 'Código no disponible'; ?></span>
                                </td>
                                <td>
                                    <span
                                        class="categoria_nombre"><?php echo $producto['categoria_nombre'] ?? 'Categoría no disponible'; ?></span>
                                </td>
                                <td>
                                    <span
                                        class="cantidad"><?php echo $producto['cantidad'] ?? 'Cantidad no disponible'; ?></span>
                                </td>
                                <td>
                                    <span
                                        class="precio"><?php echo '$' . number_format($producto['precio'], 2) ?? 'Precio no disponible'; ?></span>
                                </td>
                                <td>
                                    <span
                                        class="unidad_medida"><?php echo $producto['unidad_medida'] ?? 'Unidad/Medida no disponible'; ?></span>
                                </td>
                                <td>
                                    <!-- Botón de editar -->
                                    <button class="edit_btn btn btn-primary" type="button"
                                        data-producto_id="<?php echo $producto['producto_id']; ?>">Editar</button>
                                    <!-- Formulario oculto que aparece luego de hacer clic en editar -->
                                    <button class="close_btn btn btn-primary" type="button"
                                        style="display:none;">Cerrar</button>
                                    <form class="edit_form" id="edit_form" method="POST" style="display:none;">
                                        <input type="hidden" name="producto_id"
                                            value="<?php echo $producto['producto_id']; ?>" />
                                        <label for="nombre" class="form-label">Nombre del Producto:</label>
                                        <input type="text" class="form-control" name="nombre"
                                            value="<?php echo $producto['nombre']; ?>" required /><br>

                                        <label for="codigo" class="form-label">Código SKU:</label>
                                        <input type="text" class="form-control" name="codigo"
                                            value="<?php echo $producto['codigo_sku']; ?>" required /><br>

                                        <label for="categoria" class="form-label">Categoría:</label>
                                        <select name="tipoProducto" class="form-control">
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?php echo $categoria['categoria_id']; ?>">
                                                    <?php echo $categoria['nombre']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select><br>

                                        <label for="cantidad" class="form-label">Cantidad:</label>
                                        <input type="number" class="form-control" name="cantidad"
                                            value="<?php echo $producto['cantidad']; ?>" /><br>

                                        <label for="precio" class="form-label">Precio:</label>
                                        <input type="number" step="0.01" class="form-control" name="precio"
                                            value="<?php echo $producto['precio']; ?>" /><br>

                                        <label for="precio" class="form-label">Medida:</label>
                                        <select class="form-select" id="forma" name="forma" required>
                                            <option value="" selected disabled>Seleccione una forma</option>
                                            <option value="Tableta">Tableta</option>
                                            <option value="Capsula">Cápsula</option>
                                            <option value="Jarabe">Jarabe</option>
                                        </select>

                                        <input type="submit" class="btn btn-primary mt-3" value="Actualizar Datos">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            function refrescarPag() {
                location.reload();
            }

            document.getElementById('edit_form').addEventListener('submit', function (e) {
                e.preventDefault(); // Evita que el formulario recargue la página

                const formData = new FormData(this); // Captura los datos del formulario

                fetch('./editarProducto', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json()) // Procesa la respuesta como JSON
                    .then(data => {
                        // Maneja la respuesta del servidor
                        const mensajeDiv = document.getElementById('mensaje');
                        if (data.success) {
                            // Mostrar modal de éxito
                            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                            successModal.show();
                        } else {
                            // Mostrar mensaje de error
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });

            document.addEventListener('DOMContentLoaded', function () {
                const editBtns = document.querySelectorAll('.edit_btn');
                const closeBtns = document.querySelectorAll('.close_btn');
                const editForms = document.querySelectorAll('.edit_form');

                editBtns.forEach((btn, index) => {
                    btn.addEventListener('click', function () {
                        // Ocultar todos los formularios antes de mostrar el correspondiente
                        editForms.forEach(form => form.style.display = 'none');
                        closeBtns.forEach(btn => btn.style.display = 'none');
                        // Mostrar el formulario para el producto correspondiente
                        editForms[index].style.display = 'block';
                        closeBtns[index].style.display = 'inline-block';
                    });
                });

                closeBtns.forEach((btn, index) => {
                    btn.addEventListener('click', function () {
                        // Ocultar el formulario y el botón de cerrar cuando se haga clic en cerrar
                        editForms[index].style.display = 'none';
                        closeBtns[index].style.display = 'none';
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