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
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Citas del Médico</h4>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <h5 class="card-header">Filtrar Citas</h5>
                <div class="card-body">
                    <form id="formFiltrarCitas" method="GET" action="./verCitasMedico">
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" required>
                        </div>
                        <div class="mb-3">
                            <label for="horario" class="form-label">Horario</label>
                            <select class="form-select" id="horario" name="horario" required>
                                <option value="">Seleccione un horario</option>
                                <!-- Los horarios se cargarán dinámicamente -->
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Filtrar Citas</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card mb-4">
                <h5 class="card-header">Citas del Día</h5>
                <div class="card-body">
                    <?php if (isset($citas) && count($citas) > 0): ?>
                        <ul class="list-group">
                            <?php foreach ($citas as $cita): ?>
                                <li class="list-group-item">
                                    <p><strong>Paciente:</strong>
                                        <?php echo htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']); ?>
                                    </p>
                                    <p><strong>Fecha:</strong> <?php echo htmlspecialchars($cita['fecha_cita']); ?></p>
                                    <p><strong>Horario:</strong> <?php echo htmlspecialchars($cita['horario']); ?></p>
                                    <p><strong>Razón:</strong> <?php echo htmlspecialchars($cita['razon']); ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No hay citas para el día y horario seleccionados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($informacionPaciente)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <h5 class="card-header">Información del Paciente</h5>
                    <div class="card-body">
                        <!-- Formulario de Información del Paciente -->
                        <form id="formInformacionPaciente" method="POST" action="./procesarHistorialMedico">
                            <input type="hidden" name="paciente_id"
                                value="<?php echo htmlspecialchars($informacionPaciente['paciente']['id']); ?>">
                            <input type="hidden" name="fecha_cita" value="<?php echo htmlspecialchars($_GET['fecha']); ?>">
                            <input type="hidden" name="horario" value="<?php echo htmlspecialchars($_GET['horario']); ?>">
                            <h6>Datos del Usuario</h6>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($informacionPaciente['usuario']['email']); ?>"
                                    readonly>
                            </div>
                            <h6>Datos del Paciente</h6>
                            <div class="mb-3">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre"
                                    value="<?php echo htmlspecialchars($informacionPaciente['paciente']['nombre']); ?>"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label for="apellido" class="form-label">Apellido</label>
                                <input type="text" class="form-control" id="apellido" name="apellido"
                                    value="<?php echo htmlspecialchars($informacionPaciente['paciente']['apellido']); ?>"
                                    readonly>
                            </div>
                            <div class="mb-3">
                                <label for="cedula" class="form-label">Cédula</label>
                                <input type="text" class="form-control" id="cedula" name="cedula"
                                    value="<?php echo htmlspecialchars($informacionPaciente['informacion_paciente']['cedula'] ?? ''); ?>"
                                    required>
                            </div>

                            <div class="mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                    value="<?php echo htmlspecialchars($informacionPaciente['informacion_paciente']['fecha_nacimiento'] ?? ''); ?>"
                                    required>
                            </div>
                            <h6>Información del Paciente</h6>
                            <div class="mb-3">
                                <label for="edad" class="form-label">Edad</label>
                                <input type="number" class="form-control" id="edad" name="edad"
                                    value="<?php echo htmlspecialchars($informacionPaciente['informacion_paciente']['edad'] ?? ''); ?>"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="sexo" class="form-label">Sexo</label>
                                <select class="form-select" id="sexo" name="sexo" required>
                                    <option value="">Seleccione</option>
                                    <option value="masculino" <?php echo isset($informacionPaciente['informacion_paciente']['sexo']) && $informacionPaciente['informacion_paciente']['sexo'] == 'masculino' ? 'selected' : ''; ?>>Masculino</option>
                                    <option value="femenino" <?php echo isset($informacionPaciente['informacion_paciente']['sexo']) && $informacionPaciente['informacion_paciente']['sexo'] == 'femenino' ? 'selected' : ''; ?>>Femenino</option>
                                    <option value="otro" <?php echo isset($informacionPaciente['informacion_paciente']['sexo']) && $informacionPaciente['informacion_paciente']['sexo'] == 'otro' ? 'selected' : ''; ?>>
                                        Otro</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono"
                                    value="<?php echo htmlspecialchars($informacionPaciente['informacion_paciente']['telefono'] ?? ''); ?>"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="direccion" name="direccion"
                                    value="<?php echo htmlspecialchars($informacionPaciente['informacion_paciente']['direccion'] ?? ''); ?>"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="tipo_sangre" class="form-label">Tipo de Sangre</label>
                                <select class="form-select" id="tipo_sangre" name="tipo_sangre" required>
                                    <option value="">Seleccione</option>
                                    <option value="A+" <?php echo isset($informacionPaciente['informacion_paciente']['tipo_sangre']) && $informacionPaciente['informacion_paciente']['tipo_sangre'] == 'A+' ? 'selected' : ''; ?>>A+</option>
                                    <option value="A-" <?php echo isset($informacionPaciente['informacion_paciente']['tipo_sangre']) && $informacionPaciente['informacion_paciente']['tipo_sangre'] == 'A-' ? 'selected' : ''; ?>>A-</option>
                                    <option value="B+" <?php echo isset($informacionPaciente['informacion_paciente']['tipo_sangre']) && $informacionPaciente['informacion_paciente']['tipo_sangre'] == 'B+' ? 'selected' : ''; ?>>B+</option>
                                    <option value="B-" <?php echo isset($informacionPaciente['informacion_paciente']['tipo_sangre']) && $informacionPaciente['informacion_paciente']['tipo_sangre'] == 'B-' ? 'selected' : ''; ?>>B-</option>
                                    <option value="AB+" <?php echo isset($informacionPaciente['informacion_paciente']['tipo_sangre']) && $informacionPaciente['informacion_paciente']['tipo_sangre'] == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                                    <option value="AB-" <?php echo isset($informacionPaciente['informacion_paciente']['tipo_sangre']) && $informacionPaciente['informacion_paciente']['tipo_sangre'] == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                                    <option value="O+" <?php echo isset($informacionPaciente['informacion_paciente']['tipo_sangre']) && $informacionPaciente['informacion_paciente']['tipo_sangre'] == 'O+' ? 'selected' : ''; ?>>O+</option>
                                    <option value="O-" <?php echo isset($informacionPaciente['informacion_paciente']['tipo_sangre']) && $informacionPaciente['informacion_paciente']['tipo_sangre'] == 'O-' ? 'selected' : ''; ?>>O-</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="provincia" class="form-label">Provincia</label>
                                <select class="form-select" id="provincia" name="provincia" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($informacionPaciente['provincias'] as $provincia): ?>
                                        <option value="<?php echo $provincia['id']; ?>" <?php echo isset($informacionPaciente['informacion_paciente']['provincia_id']) && $informacionPaciente['informacion_paciente']['provincia_id'] == $provincia['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($provincia['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nacionalidad" class="form-label">Nacionalidad</label>
                                <select class="form-select" id="nacionalidad" name="nacionalidad" required>
                                    <option value="">Seleccione</option>
                                    <?php foreach ($informacionPaciente['nacionalidades'] as $nacionalidad): ?>
                                        <option value="<?php echo $nacionalidad['id']; ?>" <?php echo isset($informacionPaciente['informacion_paciente']['nacionalidad_id']) && $informacionPaciente['informacion_paciente']['nacionalidad_id'] == $nacionalidad['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($nacionalidad['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <h6>Historial Médico Base</h6>
                            <div id="historial-medico">
                                <!-- Campos editables -->
                                <div class="mb-3">
                                    <label for="peso" class="form-label">Peso (kg)</label>
                                    <input type="number" step="0.01" class="form-control" id="peso" name="peso"
                                        value="<?php echo htmlspecialchars($informacionPaciente['historial_medico']['peso'] ?? ''); ?>"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="altura" class="form-label">Altura (cm)</label>
                                    <input type="number" step="0.01" class="form-control" id="altura" name="altura"
                                        value="<?php echo htmlspecialchars($informacionPaciente['historial_medico']['altura'] ?? ''); ?>"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="presion_arterial" class="form-label">Presión Arterial</label>
                                    <input type="text" class="form-control" id="presion_arterial" name="presion_arterial"
                                        value="<?php echo htmlspecialchars($informacionPaciente['historial_medico']['presion_arterial'] ?? ''); ?>"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="frecuencia_cardiaca" class="form-label">Frecuencia Cardiaca</label>
                                    <input type="number" class="form-control" id="frecuencia_cardiaca"
                                        name="frecuencia_cardiaca"
                                        value="<?php echo htmlspecialchars($informacionPaciente['historial_medico']['frecuencia_cardiaca'] ?? ''); ?>"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="temperatura" class="form-label">Temperatura (°C)</label>
                                    <input type="number" step="0.1" class="form-control" id="temperatura" name="temperatura"
                                        value="<?php echo htmlspecialchars($informacionPaciente['historial_medico']['temperatura'] ?? ''); ?>"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="alergias" class="form-label">Alergias</label>
                                    <textarea class="form-control" id="alergias" name="alergias" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['alergias'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="medicamentos" class="form-label">Medicamentos</label>
                                    <textarea class="form-control" id="medicamentos" name="medicamentos" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['medicamentos'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="cirugias" class="form-label">Cirugías</label>
                                    <textarea class="form-control" id="cirugias" name="cirugias" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['cirugias'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="habitos" class="form-label">Hábitos</label>
                                    <textarea class="form-control" id="habitos" name="habitos" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['habitos'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="antecedentes_familiares" class="form-label">Antecedentes Familiares</label>
                                    <textarea class="form-control" id="antecedentes_familiares"
                                        name="antecedentes_familiares" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['antecedentes_familiares'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <h6>Nueva Consulta</h6>
                            <div id="nueva-consulta">
                                <div class="mb-3">
                                    <label for="motivo_consulta" class="form-label">Motivo de Consulta</label>
                                    <textarea class="form-control" id="motivo_consulta" name="motivo_consulta" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['motivo_consulta'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="diagnostico" class="form-label">Diagnóstico</label>
                                    <textarea class="form-control" id="diagnostico" name="diagnostico" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['diagnostico'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="tratamiento" class="form-label">Tratamiento</label>
                                    <textarea class="form-control" id="tratamiento" name="tratamiento" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['tratamiento'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="enfermedades_preexistentes" class="form-label">Enfermedades
                                        Preexistentes</label>
                                    <textarea class="form-control" id="enfermedades_preexistentes"
                                        name="enfermedades_preexistentes" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['enfermedades_preexistentes'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    document.getElementById('fecha').addEventListener('change', function () {
        const fecha = this.value;
        const horarioSelect = document.getElementById('horario');

        if (fecha) {
            // Obtener el ID del médico de la sesión o de donde corresponda
            fetch(`./obtenerHorariosCitas?fecha=${fecha}`)
                .then(response => response.json())
                .then(data => {
                    horarioSelect.innerHTML = '<option value="">Seleccione un horario</option>';
                    data.forEach(horario => {
                        const option = document.createElement('option');
                        option.value = horario;
                        option.textContent = horario;
                        horarioSelect.appendChild(option);
                    });
                    horarioSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    horarioSelect.disabled = true;
                });
        } else {
            horarioSelect.innerHTML = '<option value="">Seleccione un horario</option>';
            horarioSelect.disabled = true;
        }
    });
    document.getElementById('fecha_nacimiento').addEventListener('change', function() {
    const fechaNacimiento = new Date(this.value);
    const hoy = new Date();
    let edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
    const mes = hoy.getMonth() - fechaNacimiento.getMonth();
    
    if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNacimiento.getDate())) {
        edad--;
    }
    
    document.getElementById('edad').value = edad;
});

// Calcular edad inicial si hay una fecha de nacimiento
window.addEventListener('load', function() {
    const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
    if (fechaNacimientoInput.value) {
        const evento = new Event('change');
        fechaNacimientoInput.dispatchEvent(evento);
    }
});
</script>



<?php
// Verificar rutas
$footerPath = __DIR__ . '/Templates/footer.php';
if (!file_exists($footerPath)) {
    die('Error: No se encontró el archivo footer.php en la ruta especificada.');
}
require $footerPath;
?>