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

// Obtener roles de la base de datos
$queryRoles = "SELECT id, nombre FROM roles";
$stmtRoles = $conn->prepare($queryRoles);
$stmtRoles->execute();
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

// Obtener el número total de usuarios
$queryTotalUsuarios = "SELECT COUNT(*) as total FROM usuarios";
$stmtTotalUsuarios = $conn->prepare($queryTotalUsuarios);
$stmtTotalUsuarios->execute();
$totalUsuarios = $stmtTotalUsuarios->fetch(PDO::FETCH_ASSOC)['total'];

// Configuración de la paginación
$limit = 10;
$totalPaginas = ceil($totalUsuarios / $limit);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Obtener usuarios con paginación
$queryUsuarios = "SELECT u.id, u.email, r.nombre AS rol, p.nombre, p.apellido
                  FROM usuarios u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  LEFT JOIN pacientes p ON u.id = p.usuario_id
                  LIMIT :limit OFFSET :offset";
$stmtUsuarios = $conn->prepare($queryUsuarios);
$stmtUsuarios->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmtUsuarios->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Usuarios /</span> Lista de Usuarios</h4>
        <!-- Button trigger modal -->
        <div class="d-flex justify-content-end mb-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#agregarUsuarioModal">
                <i class="bx bx-plus me-2"></i> Agregar Usuario
            </button>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="agregarUsuarioModal" tabindex="-1" aria-labelledby="agregarUsuarioModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="agregarUsuarioModalLabel">Agregar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="agregarUsuarioForm" action="./agregarUsuario" method="POST">
                            <div class="mb-3">
                                <label for="tipoUsuario" class="form-label">Tipo de Usuario</label>
                                <select class="form-select" id="tipoUsuario" name="tipoUsuario" required
                                    onchange="mostrarCamposAdicionales()">
                                    <option value="" selected disabled>Seleccione el tipo de usuario</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['id']; ?>"><?php echo $rol['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="camposAdicionales" style="display: none;">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary"
                            onclick="submitAgregarUsuarioForm()">Guardar</button>
                    </div>
                </div>

            </div>

        </div>


        <script>
            function mostrarCamposAdicionales() {
                const tipoUsuario = document.getElementById('tipoUsuario').value;
                const camposAdicionales = document.getElementById('camposAdicionales');
                if (tipoUsuario == 1 || tipoUsuario == 2) { // Asumiendo que los IDs de los roles son 1 y 2
                    camposAdicionales.style.display = 'block';
                } else {
                    camposAdicionales.style.display = 'none';
                }
            }

            function submitAgregarUsuarioForm() {
                const form = $('#agregarUsuarioForm');
                if (form[0].checkValidity()) {
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method'),
                        data: form.serialize(),
                        success: function (response) {
                            const res = JSON.parse(response);
                            if (res.success) {
                                alert(res.message);
                                $('#agregarUsuarioModal').modal('hide');
                                actualizarTablaUsuarios();
                            } else {
                                alert(res.message);
                            }
                        }
                    });
                } else {
                    form[0].reportValidity();
                }
            }

            function actualizarTablaUsuarios() {
                $.ajax({
                    url: './obtenerUsuariosPaginados',
                    type: 'GET',
                    success: function (response) {
                        const usuarios = JSON.parse(response);
                        const tbody = $('.table tbody');
                        tbody.empty();
                        usuarios.forEach(usuario => {
                            tbody.append(`
                        <tr>
                            <td>${usuario.id}</td>
                            <td>${usuario.nombre || 'Nombre no disponible'}</td>
                            <td>${usuario.apellido || 'Apellido no disponible'}</td>
                            <td>${usuario.email}</td>
                            <td>${usuario.rol || 'Rol no disponible'}</td>
                            <td>
                                <div class="dropdown">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="editarUsuario(${usuario.id})">
                                            <i class="bx bx-edit-alt me-2"></i> Editar
                                        </a>
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarUsuario(${usuario.id})">
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
            <h5 class="card-header">Usuarios</h5>
            <div class="table-responsive text-nowrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
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
                                <td><?php echo $usuario['id']; ?></td>
                                <td><?php echo isset($usuario['nombre']) ? $usuario['nombre'] : 'Nombre no disponible'; ?>
                                </td>
                                <td><?php echo isset($usuario['apellido']) ? $usuario['apellido'] : 'Apellido no disponible'; ?>
                                </td>
                                <td><?php echo $usuario['email']; ?></td>
                                <td><?php echo isset($usuario['rol']) ? $usuario['rol'] : 'Rol no disponible'; ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                                                <i class="bx bx-edit-alt me-2"></i> Editar
                                            </a>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)">
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
        <div class="card-body">
            <div class="row justify-content-center">
                <div class="col-auto">
                    <div class="demo-inline-spacing">
                        <!-- Basic Pagination -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination">
                                <li class="page-item first <?php if ($page <= 1) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=1">
                                        <i class="tf-icon bx bx-chevrons-left"></i>
                                    </a>
                                </li>
                                <li class="page-item prev <?php if ($page <= 1) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="tf-icon bx bx-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item next <?php if ($page >= $totalPaginas) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        <i class="tf-icon bx bx-chevron-right"></i>
                                    </a>
                                </li>
                                <li class="page-item last <?php if ($page >= $totalPaginas) echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $totalPaginas; ?>">
                                        <i class="tf-icon bx bx-chevrons-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <!--/ Basic Pagination -->
                    </div>
                </div>
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