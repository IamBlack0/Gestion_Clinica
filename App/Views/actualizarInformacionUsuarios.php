<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!$headerPath) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;

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
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Obtener usuarios con paginación
$queryUsuarios = "SELECT u.id, u.email, r.nombre AS rol, COALESCE(p.nombre, c.nombre) AS nombre, COALESCE(p.apellido, c.apellido) AS apellido
                  FROM usuarios u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  LEFT JOIN pacientes p ON u.id = p.usuario_id
                  LEFT JOIN colaboradores c ON u.id = c.usuario_id
                  LIMIT :limit OFFSET :offset";
$stmtUsuarios = $conn->prepare($queryUsuarios);
$stmtUsuarios->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmtUsuarios->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmtUsuarios->execute();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// Obtener las nacionalidades
$queryNacionalidades = "SELECT id, nombre FROM nacionalidades";
$stmtNacionalidades = $conn->prepare($queryNacionalidades);
$stmtNacionalidades->execute();
$nacionalidades = $stmtNacionalidades->fetchAll(PDO::FETCH_ASSOC);

// Obtener las provincias
$queryProvincias = "SELECT id, nombre FROM provincias";
$stmtProvincias = $conn->prepare($queryProvincias);
$stmtProvincias->execute();
$provincias = $stmtProvincias->fetchAll(PDO::FETCH_ASSOC);


// Obtener las especialidades
$queryEspecialidades = "SELECT id, nombre FROM especialidades";
$stmtEspecialidades = $conn->prepare($queryEspecialidades);
$stmtEspecialidades->execute();
$especialidades = $stmtEspecialidades->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Usuarios /</span> Lista de Usuarios</h4>
        <!-- Button trigger modal -->
        <div class="d-flex justify-content-end mb-4">
            <div class="btn-group">
                <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="bx bx-plus me-2"></i> Agregar Usuario
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                            data-bs-target="#agregarPacienteModal">Agregar Paciente</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal"
                            data-bs-target="#agregarColaboradorModal">Agregar Colaborador</a></li>
                </ul>
            </div>
        </div>

        <!-- Modal para agregar Paciente -->
        <div class="modal fade" id="agregarPacienteModal" tabindex="-1" aria-labelledby="agregarPacienteModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="agregarPacienteModalLabel">Agregar Paciente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="agregarPacienteForm" action="./agregarUsuario" method="POST">
                            <div class="mb-3">
                                <label for="nombrePaciente" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombrePaciente" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="apellidoPaciente" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellidoPaciente" name="apellido" required>
                            </div>
                            <div class="mb-3">
                                <label for="emailPaciente" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="emailPaciente" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="passwordPaciente" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="passwordPaciente" name="password"
                                    required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary"
                            onclick="submitAgregarPacienteForm()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para agregar Colaborador -->
        <div class="modal fade" id="agregarColaboradorModal" tabindex="-1"
            aria-labelledby="agregarColaboradorModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="agregarColaboradorModalLabel">Agregar Colaborador</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="agregarColaboradorForm" action="./agregarColaborador" method="POST">
                            <div class="mb-3">
                                <label for="nombreColaborador" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombreColaborador" name="nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="apellidoColaborador" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellidoColaborador" name="apellido"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="emailColaborador" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="emailColaborador" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="passwordColaborador" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="passwordColaborador" name="password"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="rolColaborador" class="form-label">Rol</label>
                                <select class="form-select" id="rolColaborador" name="rol_id"
                                    onchange="toggleEspecialidad()">
                                    <option value="" selected disabled>Seleccione el rol</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <?php if ($rol['nombre'] != 'paciente'): ?>
                                            <option value="<?php echo $rol['id']; ?>"><?php echo $rol['nombre']; ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3" id="especialidadColaboradorDiv" style="display: none;">
                                <label for="especialidadColaborador" class="form-label">Especialidad</label>
                                <select class="form-select" id="especialidadColaborador" name="especialidad">
                                    <option value="" selected disabled>Seleccione la especialidad</option>
                                    <?php foreach ($especialidades as $especialidad): ?>
                                        <option value="<?php echo $especialidad['id']; ?>">
                                            <?php echo $especialidad['nombre']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary"
                            onclick="submitAgregarColaboradorForm()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

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
                                <td><?php echo $usuario['nombre']; ?></td>
                                <td><?php echo $usuario['apellido']; ?></td>
                                <td><?php echo $usuario['email']; ?></td>
                                <td><?php echo $usuario['rol']; ?></td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:void(0);">
                                                <i class="bx bx-edit-alt me-2"></i> Editar
                                            </a>
                                            <a class="dropdown-item" href="">
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
                                <li class="page-item first <?php if ($page <= 1)
                                    echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=1">
                                        <i class="tf-icon bx bx-chevrons-left"></i>
                                    </a>
                                </li>
                                <li class="page-item prev <?php if ($page <= 1)
                                    echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="tf-icon bx bx-chevron-left"></i>
                                    </a>
                                </li>
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?php if ($i == $page)
                                        echo 'active'; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item next <?php if ($page >= $totalPaginas)
                                    echo 'disabled'; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        <i class="tf-icon bx bx-chevron-right"></i>
                                    </a>
                                </li>
                                <li class="page-item last <?php if ($page >= $totalPaginas)
                                    echo 'disabled'; ?>">
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




<script src="Public/js/scripts.js"></script>

<?php
// Verificar rutas
$footerPath = __DIR__ . '/Templates/footer.php';
if (!$footerPath) {
    die('Error: No se encontró el archivo footer.php en la ruta especificada.');
}
require $footerPath;
?>