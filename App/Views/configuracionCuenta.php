<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
$configPath = __DIR__ . '/../../config.php';
if (!file_exists($headerPath)) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
if (!file_exists($configPath)) {
    die('Error: No se encontró el archivo config.php en la ruta especificada.');
}
require $headerPath;
require $configPath;

// Conectar a la base de datos para obtener la información del usuario
$database = new Database();
$db = $database->getConnection();

// Obtener la información del usuario
$queryUsuario = "SELECT email, rol_id FROM usuarios WHERE id = :user_id";
$stmtUsuario = $db->prepare($queryUsuario);
$stmtUsuario->bindParam(':user_id', $_SESSION['user_id']);
$stmtUsuario->execute();
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

// Obtener la información del paciente desde el controlador
$queryPaciente = "SELECT * FROM informacion_paciente WHERE paciente_id = (SELECT id FROM pacientes WHERE usuario_id = :user_id)";
$stmtPaciente = $db->prepare($queryPaciente);
$stmtPaciente->bindParam(':user_id', $_SESSION['user_id']);
$stmtPaciente->execute();
$informacionPaciente = $stmtPaciente->fetch(PDO::FETCH_ASSOC) ?? [];

// Obtener las nacionalidades
$queryNacionalidades = "SELECT id, nombre FROM nacionalidades";
$stmtNacionalidades = $db->prepare($queryNacionalidades);
$stmtNacionalidades->execute();
$nacionalidades = $stmtNacionalidades->fetchAll(PDO::FETCH_ASSOC);

// Obtener las provincias
$queryProvincias = "SELECT id, nombre FROM provincias";
$stmtProvincias = $db->prepare($queryProvincias);
$stmtProvincias->execute();
$provincias = $stmtProvincias->fetchAll(PDO::FETCH_ASSOC);

// Verificar si el usuario es administrador
$queryAdmin = "SELECT id FROM roles WHERE nombre = 'administrador'";
$stmtAdmin = $db->prepare($queryAdmin);
$stmtAdmin->execute();
$adminRole = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

$isAdmin = $usuario['rol_id'] == $adminRole['id'];

// Verificar si el usuario es médico
$queryMedico = "SELECT id FROM roles WHERE nombre = 'medico'";
$stmtMedico = $db->prepare($queryMedico);
$stmtMedico->execute();
$medicoRole = $stmtMedico->fetch(PDO::FETCH_ASSOC);

$isMedico = $usuario['rol_id'] == $medicoRole['id'];
?>

<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->

    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Configuracion de cuenta /</span> cuenta</h4>

        <div class="row">
            <div class="col-md-12">
                <ul class="nav nav-pills flex-column flex-md-row mb-3">
                    <li class="nav-item">
                        <a class="nav-link active" href="javascript:void(0);"><i class="bx bx-user me-1"></i> Cuenta</a>
                    </li>
                </ul>
                <div class="card mb-4">
                    <h5 class="card-header">Detalles del Perfil</h5>
                    <!-- Account -->
                    <!-- Sección de foto de perfil -->
                    <div class="card-body">
                        <div class="d-flex align-items-start align-items-sm-center gap-4">
                            <img src="<?php echo !empty($informacionPaciente['foto_perfil']) ? $informacionPaciente['foto_perfil'] : 'Public/img/avatars/default.png'; ?>"
                                alt="user-avatar" class="d-block rounded" height="100" width="100"
                                id="uploadedAvatar" />
                            <div class="button-wrapper">
                                <form id="formFotoPerfil" enctype="multipart/form-data">
                                    <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                                        <span class="d-none d-sm-block">Subir nueva foto</span>
                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                        <input type="file" id="upload" name="foto_perfil" class="account-file-input"
                                            hidden accept="image/png, image/jpeg" onchange="handleFileSelect(this)" />
                                    </label>
                                </form>
                                <p class="text-muted mb-0">Permitido JPG o PNG. Tamaño máximo 800K</p>
                            </div>
                        </div>
                    </div>
                    <hr class="my-0" />
                    <div class="card-body">
                        <form id="formAccountSettings" method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="firstName" class="form-label">Nombre</label>
                                    <input class="form-control" type="text" id="firstName" name="firstName"
                                        value="<?php echo $_SESSION['nombre'] ?? ''; ?>" readonly />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="lastName" class="form-label">Apellido</label>
                                    <input class="form-control" type="text" name="lastName" id="lastName"
                                        value="<?php echo $_SESSION['apellido'] ?? ''; ?>" readonly />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="cedula" class="form-label">Cédula</label>
                                    <input class="form-control" type="text" id="cedula" name="cedula"
                                        value="<?php echo $informacionPaciente['cedula'] ?? ''; ?>" required />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento"
                                        name="fecha_nacimiento"
                                        value="<?php echo $informacionPaciente['fecha_nacimiento'] ?? ''; ?>"
                                        required />
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="email" class="form-label">Correo</label>
                                    <input class="form-control" type="text" id="email" name="email"
                                        value="<?php echo $usuario['email'] ?? ''; ?>" readonly />
                                </div>
                                <?php if (!$isAdmin && !$isMedico): ?>
                                    <div class="mb-3 col-md-6">
                                        <label for="edad" class="form-label">Edad</label>
                                        <input type="number" class="form-control" id="edad" name="edad"
                                            value="<?php echo $informacionPaciente['edad'] ?? ''; ?>" readonly />
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="sexo" class="form-label">Sexo</label>
                                        <select id="sexo" name="sexo" class="form-select" required>
                                            <option value="">Selecciona</option>
                                            <option value="masculino" <?php echo ($informacionPaciente['sexo'] ?? '') == 'masculino' ? 'selected' : ''; ?>>Masculino</option>
                                            <option value="femenino" <?php echo ($informacionPaciente['sexo'] ?? '') == 'femenino' ? 'selected' : ''; ?>>Femenino</option>
                                            <option value="otro" <?php echo ($informacionPaciente['sexo'] ?? '') == 'otro' ? 'selected' : ''; ?>>Otro</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="tipo_sangre" class="form-label">Tipo de sangre</label>
                                        <select id="tipo_sangre" name="tipo_sangre" class="form-select">
                                            <option value="">Selecciona</option>
                                            <option value="A+" <?php echo ($informacionPaciente['tipo_sangre'] ?? '') == 'A+' ? 'selected' : ''; ?>>A+</option>
                                            <option value="A-" <?php echo ($informacionPaciente['tipo_sangre'] ?? '') == 'A-' ? 'selected' : ''; ?>>A-</option>
                                            <option value="B+" <?php echo ($informacionPaciente['tipo_sangre'] ?? '') == 'B+' ? 'selected' : ''; ?>>B+</option>
                                            <option value="B-" <?php echo ($informacionPaciente['tipo_sangre'] ?? '') == 'B-' ? 'selected' : ''; ?>>B-</option>
                                            <option value="AB+" <?php echo ($informacionPaciente['tipo_sangre'] ?? '') == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                            <option value="AB-" <?php echo ($informacionPaciente['tipo_sangre'] ?? '') == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                            <option value="O+" <?php echo ($informacionPaciente['tipo_sangre'] ?? '') == 'O+' ? 'selected' : ''; ?>>O+</option>
                                            <option value="O-" <?php echo ($informacionPaciente['tipo_sangre'] ?? '') == 'O-' ? 'selected' : ''; ?>>O-</option>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="telefono">Numero de telefono</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text">PA (+507)</span>
                                            <input type="text" id="telefono" name="telefono" class="form-control"
                                                placeholder="0000-0000"
                                                value="<?php echo $informacionPaciente['telefono'] ?? ''; ?>" required />
                                        </div>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="nacionalidad_id">Nacionalidad</label>
                                        <select id="nacionalidad_id" name="nacionalidad_id" class="select2 form-select"
                                            required>
                                            <option value="">Selecciona</option>
                                            <?php foreach ($nacionalidades as $nacionalidad): ?>
                                                <option value="<?php echo $nacionalidad['id']; ?>" <?php echo ($informacionPaciente['nacionalidad_id'] ?? '') == $nacionalidad['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $nacionalidad['nombre']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion"
                                            placeholder="Address"
                                            value="<?php echo $informacionPaciente['direccion'] ?? ''; ?>" required />
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="provincia_id">Provincia</label>
                                        <select id="provincia_id" name="provincia_id" class="select2 form-select" required>
                                            <option value="">Selecciona</option>
                                            <?php foreach ($provincias as $provincia): ?>
                                                <option value="<?php echo $provincia['id']; ?>" <?php echo ($informacionPaciente['provincia_id'] ?? '') == $provincia['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $provincia['nombre']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-2">Guardar cambios</button>
                                <button type="reset" class="btn btn-outline-secondary">Cancelar</button>
                            </div>
                        </form>
                    </div>
                    <!-- /Account -->
                </div>
            </div>
        </div>
    </div>
    <!-- / Content -->

    <?php
    // Verificar rutas
    $footerPath = __DIR__ . '/Templates/footer.php';
    if (!file_exists($footerPath)) {
        die('Error: No se encontró el archivo footer.php en la ruta especificada.');
    }
    require $footerPath;
    ?>

    <script>
        document.getElementById('formAccountSettings').addEventListener('submit', function (event) {
            event.preventDefault(); // Evitar el envío del formulario

            var formData = new FormData(this);

            fetch('./actualizarInformacionPaciente', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Información del paciente actualizada correctamente.');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar la información del paciente.');
                });
        });

        // Validaciones en tiempo real
        document.getElementById('edad').addEventListener('input', function () {
            if (this.value < 0) {
                this.value = 0;
                alert('La edad no puede ser negativa.');
            }
        });

        function calcularEdad(fechaNacimiento) {
            const hoy = new Date();
            const fechaNac = new Date(fechaNacimiento);
            let edad = hoy.getFullYear() - fechaNac.getFullYear();
            const mes = hoy.getMonth() - fechaNac.getMonth();

            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
                edad--;
            }

            return edad;
        }

        // Actualizar la edad cuando cambie la fecha de nacimiento
        document.getElementById('fecha_nacimiento').addEventListener('change', function () {
            const fechaNacimiento = this.value;
            if (fechaNacimiento) {
                const edad = calcularEdad(fechaNacimiento);
                document.getElementById('edad').value = edad;
            } else {
                document.getElementById('edad').value = '';
            }
        });

        // Calcular la edad inicial si hay una fecha de nacimiento
        window.addEventListener('load', function () {
            const fechaNacimiento = document.getElementById('fecha_nacimiento').value;
            if (fechaNacimiento) {
                const edad = calcularEdad(fechaNacimiento);
                document.getElementById('edad').value = edad;
            }
        });

        // Validación de cédula única
        document.getElementById('cedula').addEventListener('blur', function () {
            const cedula = this.value;
            const currentCedula = '<?php echo $informacionPaciente['cedula'] ?? ''; ?>';

            // Solo verificar si la cédula ha cambiado
            if (cedula && cedula !== currentCedula) {
                fetch(`./verificarCedula?cedula=${cedula}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.exists) {
                            alert('Esta cédula ya está registrada');
                            this.value = currentCedula;
                            this.focus();
                        }
                    });
            }
        });

        // Validación de fecha de nacimiento
        document.getElementById('fecha_nacimiento').addEventListener('change', function () {
            const selectedDate = new Date(this.value);
            const today = new Date();
            const minDate = new Date('1960-01-01');
            const maxDate = new Date('2020-12-31');

            if (selectedDate > today) {
                alert('La fecha de nacimiento no puede ser mayor al día actual');
                this.value = '';
                return;
            }

            if (selectedDate < minDate) {
                alert('La fecha de nacimiento no puede ser anterior a 1960');
                this.value = '';
                return;
            }

            if (selectedDate > maxDate) {
                alert('La fecha de nacimiento no puede ser posterior a 2020');
                this.value = '';
                return;
            }

            // Actualizar la edad después de validar la fecha
            const edad = calcularEdad(this.value);
            document.getElementById('edad').value = edad;
        });

        // Modificar el input de fecha para establecer los límites
        document.addEventListener('DOMContentLoaded', function () {
            const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
            fechaNacimientoInput.setAttribute('min', '1960-01-01');
            fechaNacimientoInput.setAttribute('max', '2020-12-31');
        });

        // Validación del teléfono (formato panameño)
        document.getElementById('telefono').addEventListener('input', function () {
            let value = this.value.replace(/\D/g, ''); // Eliminar no dígitos
            if (value.length > 8) value = value.slice(0, 8); // Máximo 8 dígitos

            // Formatear como XXXX-XXXX
            if (value.length > 4) {
                value = value.slice(0, 4) + '-' + value.slice(4);
            }

            this.value = value;
        });

    </script>


    <script>
        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                const formData = new FormData();
                formData.append('foto_perfil', input.files[0]);

                fetch('./subirFotoPerfil', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('uploadedAvatar').src = data.path;
                            alert('Foto de perfil actualizada correctamente');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al subir la imagen');
                    });
            }
        }

        function resetImage() {
            document.getElementById('uploadedAvatar').src = 'Public/img/avatars/default.png';
            document.getElementById('upload').value = '';
            // Aquí podrías agregar una llamada al servidor para eliminar la foto actual
        }
    </script>