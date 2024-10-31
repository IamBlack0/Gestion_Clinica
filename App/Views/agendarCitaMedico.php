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

// Obtener la información del médico
$queryMedico = "SELECT * FROM colaboradores WHERE usuario_id = :user_id";
$stmtMedico = $db->prepare($queryMedico);
$stmtMedico->bindParam(':user_id', $_SESSION['user_id']);
$stmtMedico->execute();
$medico = $stmtMedico->fetch(PDO::FETCH_ASSOC);

if (!$medico) {
    die('Error: No se encontró el médico con el ID especificado.');
}

// Obtener los pacientes que el médico ha atendido
$queryPacientes = "SELECT DISTINCT p.id, p.nombre, p.apellido 
                   FROM pacientes p
                   JOIN citas c ON p.id = c.paciente_id
                   WHERE c.medico_id = :medico_id";
$stmtPacientes = $db->prepare($queryPacientes);
$stmtPacientes->bindParam(':medico_id', $medico['id']);
$stmtPacientes->execute();
$pacientes = $stmtPacientes->fetchAll(PDO::FETCH_ASSOC);

// Obtener las especialidades
$queryEspecialidades = "SELECT id, nombre FROM especialidades";
$stmtEspecialidades = $db->prepare($queryEspecialidades);
$stmtEspecialidades->execute();
$especialidades = $stmtEspecialidades->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Agendar Cita</h4>

    <div class="card mb-4">
        <h5 class="card-header">Formulario de Agendamiento</h5>
        <div class="card-body">
            <form action="./procesarAgendarCitaMedico" method="POST">
                <div class="mb-3">
                    <label for="paciente_id" class="form-label">Paciente</label>
                    <select class="form-select" id="paciente_id" name="paciente_id" required>
                        <option value="">Seleccione un paciente</option>
                        <?php foreach ($pacientes as $paciente): ?>
                            <option value="<?php echo htmlspecialchars($paciente['id']); ?>">
                                <?php echo htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="fecha_cita" class="form-label">Fecha de la Cita</label>
                    <input type="date" class="form-control" id="fecha_cita" name="fecha_cita" required disabled>
                </div>
                <div class="mb-3">
                    <label for="especialidad_id" class="form-label">Especialidad</label>
                    <select class="form-select" id="especialidad_id" name="especialidad_id" required disabled>
                        <option value="">Seleccione una especialidad</option>
                        <?php foreach ($especialidades as $especialidad): ?>
                            <option value="<?php echo htmlspecialchars($especialidad['id']); ?>">
                                <?php echo htmlspecialchars($especialidad['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="medico_id" class="form-label">Médico</label>
                    <select class="form-select" id="medico_id" name="medico_id" required disabled>
                        <option value="">Seleccione un médico</option>
                        <!-- Opciones de médicos se cargarán dinámicamente -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="horario" class="form-label">Horario</label>
                    <select class="form-select" id="horario" name="horario" required disabled>
                        <option value="">Seleccione un horario</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="razon" class="form-label">Razón</label>
                    <textarea class="form-control" id="razon" name="razon" rows="5" maxlength="300" required disabled></textarea>
                </div>
                <button type="submit" class="btn btn-primary" disabled>Agendar</button>
            </form>
        </div>
    </div>
</div>

<?php
// Verificar rutas
$footerPath = __DIR__ . '/Templates/footer.php';
if (!file_exists($footerPath)) {
    die('Error: No se encontró el archivo footer.php en la ruta especificada.');
}
require $footerPath;
?>

<script>
document.getElementById('paciente_id').addEventListener('change', function() {
    const pacienteId = this.value;
    const fechaCitaInput = document.getElementById('fecha_cita');
    if (pacienteId) {
        fechaCitaInput.disabled = false;
    } else {
        fechaCitaInput.disabled = true;
    }
});

document.getElementById('fecha_cita').addEventListener('change', function() {
    const fecha = this.value;
    const especialidadSelect = document.getElementById('especialidad_id');
    const today = new Date().toISOString().split('T')[0];
    if (fecha && fecha >= today) {
        especialidadSelect.disabled = false;
    } else {
        especialidadSelect.disabled = true;
        alert('La fecha de la cita no puede ser menor a la fecha actual.');
    }
});

document.getElementById('especialidad_id').addEventListener('change', function() {
    const especialidadId = this.value;
    const medicoSelect = document.getElementById('medico_id');
    if (especialidadId) {
        fetch(`./obtenerMedicosPorEspecialidad?especialidad_id=${especialidadId}`)
            .then(response => response.json())
            .then(data => {
                medicoSelect.innerHTML = '<option value="">Seleccione un médico</option>';
                data.medicos.forEach(medico => {
                    const option = document.createElement('option');
                    option.value = medico.id;
                    option.textContent = `${medico.nombre} ${medico.apellido}`;
                    medicoSelect.appendChild(option);
                });
                medicoSelect.disabled = false;
            });
    } else {
        medicoSelect.disabled = true;
    }
});

document.getElementById('medico_id').addEventListener('change', function() {
    const medicoId = this.value;
    const fecha = document.getElementById('fecha_cita').value;
    const especialidadId = document.getElementById('especialidad_id').value;
    const horarioSelect = document.getElementById('horario');
    if (medicoId) {
        fetch(`./obtenerHorariosDisponibles?medico_id=${medicoId}&fecha=${fecha}&especialidad_id=${especialidadId}`)
            .then(response => response.json())
            .then(data => {
                horarioSelect.innerHTML = '<option value="">Seleccione un horario</option>';
                data.horarios.forEach(horario => {
                    const option = document.createElement('option');
                    option.value = horario;
                    option.textContent = horario.charAt(0).toUpperCase() + horario.slice(1);
                    horarioSelect.appendChild(option);
                });
                horarioSelect.disabled = false;
            });
    } else {
        horarioSelect.disabled = true;
    }
});

document.getElementById('horario').addEventListener('change', function() {
    const horario = this.value;
    const razonTextarea = document.getElementById('razon');
    const submitButton = document.querySelector('button[type="submit"]');
    if (horario) {
        razonTextarea.disabled = false;
        submitButton.disabled = false;
    } else {
        razonTextarea.disabled = true;
        submitButton.disabled = true;
    }
});
</script>