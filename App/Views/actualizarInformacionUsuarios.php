<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!$headerPath) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;
?>

<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Usuarios /</span> Lista de Usuarios</h4>

        <!-- Basic Bootstrap Table -->
        <div class="card">
            <h5 class="card-header">Usuarios</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                    <?php foreach ($usuarios as $usuario): ?>
    <tr>
        <td><?php echo isset($usuario['nombre']) ? $usuario['nombre'] : 'Nombre no disponible'; ?></td>
        <td><?php echo isset($usuario['apellido']) ? $usuario['apellido'] : 'Apellido no disponible'; ?></td>
        <td><?php echo $usuario['email']; ?></td>
        <td><?php echo isset($usuario['rol']) ? $usuario['rol'] : 'Rol no disponible'; ?></td>
        <td>
            <div class="dropdown">
                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="javascript:void(0);" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                        <i class="bx bx-edit-alt me-2"></i> Editar
                    </a>
                    <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)">
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

<script>
function editarUsuario(id) {
    // Redirigir a la página de edición con el ID del usuario
    window.location.href = './editarUsuario?id=' + id;
}

function eliminarUsuario(id) {
    // Confirmar la eliminación del usuario
    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        // Redirigir a la página de eliminación con el ID del usuario
        window.location.href = './eliminarUsuario?id=' + id;
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