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
                                    <textarea class="form-control" id="tratamientoPrincipal" name="tratamiento"
                                        rows="3"><?php echo htmlspecialchars($informacionPaciente['historial_medico']['tratamiento'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="enfermedades_preexistentes" class="form-label">Enfermedades
                                        Preexistentes</label>
                                    <textarea class="form-control" id="enfermedades_preexistentes"
                                        name="enfermedades_preexistentes" rows="3"
                                        required><?php echo htmlspecialchars($informacionPaciente['historial_medico']['enfermedades_preexistentes'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="d-flex mt-4 gap-2">
                                <button type="button" class="btn btn-primary" onclick="validarYProcesarPago()">
                                    Procesar Pago
                                </button>
                                <button type="button" class="btn btn-primary" onclick="crearReceta()">
                                    Crear receta
                                </button>
                                <button type="button" class="btn btn-primary me-2" onclick="mostrarModalInsumos()">
                                    Agregar Insumos
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Pago -->
<div class="modal fade" id="pagoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Procesar Pago de Consulta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="./procesarPago" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="historial_cita_id" id="historialCitaId">
                    <div class="mb-3">
                        <label class="form-label">Monto de Consulta</label>
                        <input type="number" class="form-control" name="monto_consulta" value="50.00" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto de Insumos</label>
                        <input type="number" class="form-control" name="monto_insumos" value="0.00" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto Total</label>
                        <input type="number" class="form-control" name="monto_total" value="50.00" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select class="form-select" name="metodo_pago" id="metodo_pago" required>
                            <option value="">Seleccione método de pago</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                        <input type="hidden" name="cita_id" value="<?php echo $cita['id']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Forma de Pago</label>
                        <select class="form-select" name="forma_pago" id="forma_pago" required disabled>
                            <option value="">Seleccione forma de pago</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Número de Comprobante</label>
                        <input type="text" class="form-control" name="numero_comprobante" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Receta -->
<div class="modal fade" id="recetaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Receta Médica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formReceta" action="./procesarReceta" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="paciente_id"
                        value="<?php echo htmlspecialchars($informacionPaciente['paciente']['id'] ?? ''); ?>">
                    <input type="hidden" name="medico_id"
                        value="<?php echo htmlspecialchars($_SESSION['user_id'] ?? ''); ?>">
                    <!--Nombre y apellido del medico-->
                    <div class="mb-3">
                        <label for="medico" class="form-label">Médico</label>
                        <input type="text" class="form-control" id="medico" name="medico"
                            value="<?php echo htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']); ?>"
                            readonly>
                    </div>
                    <div class="mb-3">
                        <label for="tratamientoModal" class="form-label">Tratamiento</label>
                        <textarea class="form-control" id="tratamientoModal" name="tratamiento" rows="3"
                            readonly></textarea>
                    </div>
                    <!--imagen de la firma segun el id del medico en la tabla firmas-->
                    <div class="mb-3">
                        <label for="firma" class="form-label">Firma</label><br>
                        <img id="firmaMedico" alt="Firma del médico" class="img-fluid w-px-100 h-px-100">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Receta</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal de Insumos -->
<div class="modal fade" id="insumosModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Insumos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col">
                        <select class="form-select" id="selectInsumo">
                            <option value="">Seleccionar insumo...</option>
                        </select>
                    </div>
                    <div class="col">
                        <input type="number" class="form-control" id="cantidadInsumo" placeholder="Cantidad" min="1">
                    </div>
                    <div class="col">
                        <button type="button" class="btn btn-primary" onclick="agregarInsumo()">Agregar</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table" id="tablaInsumos">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Total Insumos:</strong></td>
                                <td><span id="totalInsumos">$0.00</span></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="confirmarInsumos()">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmacionModal" tabindex="-1" aria-labelledby="confirmacionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmacionModalLabel">¡Operación Exitosa!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bx bx-check-circle text-success" style="font-size: 64px;"></i>
                </div>
                <p class="text-center">El historial médico y el pago han sido procesados correctamente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                    onclick="window.location.href='./verCitasMedico'">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function validarYProcesarPago() {
        const formPrincipal = document.getElementById('formInformacionPaciente');

        // Validar todos los campos requeridos
        if (!formPrincipal.checkValidity()) {
            // Mostrar validaciones del navegador
            formPrincipal.reportValidity();
            return;
        }

        // Si el formulario es válido, mostrar el modal
        const modal = new bootstrap.Modal(document.getElementById('pagoModal'));
        modal.show();
    }

    document.getElementById('pagoModal').addEventListener('show.bs.modal', function (event) {
        const formPago = document.querySelector('#pagoModal form');
        const formPrincipal = document.getElementById('formInformacionPaciente');
        const numeroComprobanteInput = document.querySelector('input[name="numero_comprobante"]');

        // Obtener el siguiente número de comprobante
        fetch('./obtenerSiguienteComprobante')
            .then(response => response.json())
            .then(data => {
                if (data.comprobante) {
                    numeroComprobanteInput.value = data.comprobante;
                    numeroComprobanteInput.readOnly = true;
                }
            })
            .catch(error => console.error('Error:', error));

        // Agregar el ID del historial_citas al formulario de pago
        const historialCitaId = '<?php echo $citas[0]['historial_cita_id'] ?? ''; ?>';
        document.getElementById('historialCitaId').value = historialCitaId;

        formPago.onsubmit = function (e) {
            e.preventDefault();

            // Primero guardar el historial médico
            const historialData = new FormData(formPrincipal);

            fetch('./procesarHistorialMedico', {
                method: 'POST',
                body: historialData
            })
                .then(response => response.json())
                .then(historialResponse => {
                    if (historialResponse.success) {
                        // Si el historial se guardó correctamente, proceder con el pago
                        const formData = new FormData(formPrincipal);
                        const formDataPago = new FormData(formPago);

                        formData.append('paciente_id', '<?php echo $informacionPaciente['paciente']['id'] ?? ''; ?>');
                        formData.append('fecha_cita', '<?php echo $_GET['fecha'] ?? ''; ?>');
                        formData.append('horario', '<?php echo $_GET['horario'] ?? ''; ?>');

                        for (let pair of formDataPago.entries()) {
                            formData.append(pair[0], pair[1]);
                        }

                        return fetch('./procesarPago', {
                            method: 'POST',
                            body: formData
                        });
                    } else {
                        throw new Error('Error al guardar el historial médico');
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cerrar el modal de pago
                        const pagoModal = bootstrap.Modal.getInstance(document.getElementById('pagoModal'));
                        pagoModal.hide();

                        // Mostrar el modal de confirmación
                        const confirmacionModal = new bootstrap.Modal(document.getElementById('confirmacionModal'));
                        confirmacionModal.show();

                        // Al cerrar el modal de confirmación, redirigir
                        document.querySelector('#confirmacionModal').addEventListener('hidden.bs.modal', function () {
                            window.location.href = './verCitasMedico';
                        });
                    } else {
                        alert('Error al procesar el pago: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ha ocurrido un error, pero los datos se han guardado correctamente.');
                    window.location.href = './verCitasMedico';
                });
        };
    });

    document.getElementById('metodo_pago').addEventListener('change', function () {
        const formaPagoSelect = document.getElementById('forma_pago');
        formaPagoSelect.innerHTML = '<option value="">Seleccione forma de pago</option>';
        if (this.value === 'efectivo') {
            formaPagoSelect.innerHTML += `
            <option value="contado">Contado</option>
        `;
            formaPagoSelect.disabled = false;
        } else if (this.value === 'tarjeta') {
            formaPagoSelect.innerHTML += `
            <option value="débito">Débito</option>
            <option value="crédito">Crédito</option>
        `;
            formaPagoSelect.disabled = false;
        } else {
            formaPagoSelect.disabled = true;
        }
    });
</script>


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
    document.getElementById('fecha_nacimiento').addEventListener('change', function () {
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
    window.addEventListener('load', function () {
        const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
        if (fechaNacimientoInput.value) {
            const evento = new Event('change');
            fechaNacimientoInput.dispatchEvent(evento);
        }
    });

    function crearReceta() {
        // Mostrar el modal de receta
        const modal = new bootstrap.Modal(document.getElementById('recetaModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const tratamientoPrincipal = document.getElementById('tratamientoPrincipal');
        const tratamientoModal = document.getElementById('tratamientoModal');
        const botonCrearReceta = document.querySelector('button[onclick="crearReceta()"]');
        const botonProcesarPago = document.querySelector('button[onclick="validarYProcesarPago()"]');
        // Manejar agregar medicamento
        document.getElementById('formReceta').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('./procesarReceta', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('recetaModal'));
                        modal.hide();
                        alert('Receta guardada correctamente');
                        botonProcesarPago.disabled = false;
                    } else {
                        alert('Error al guardar la receta: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la receta');
                });
        });

        document.getElementById('recetaModal').addEventListener('show.bs.modal', function () {
            tratamientoModal.value = tratamientoPrincipal.value;
        });

        tratamientoPrincipal.addEventListener('input', function () {
            if (tratamientoPrincipal.value.trim() !== '') {
                botonCrearReceta.disabled = false;
                botonProcesarPago.disabled = true;
            } else {
                botonCrearReceta.disabled = true;
                botonProcesarPago.disabled = false;
            }
        });

        if (tratamientoPrincipal.value.trim() === '') {
            botonCrearReceta.disabled = true;
        }

        const medicoId = <?php echo $_SESSION['user_id']; ?>;
        fetch(`./obtenerFirma?medico_id=${medicoId}`)
            .then(response => response.json())
            .then(data => {
                if (data.firma) {
                    document.getElementById('firmaMedico').src = data.firma;
                } else {
                    console.error('Firma no encontrada');
                }
            })
            .catch(error => console.error('Error:', error));
    });
</script>

<script>
    let insumosSeleccionados = [];
    let totalInsumos = 0;

    function mostrarModalInsumos() {
        // Cargar insumos disponibles
        fetch('./obtenerInsumosPagos')  // Actualizar la URL
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log("Insumos recibidos:", data); // Debug
                const select = document.getElementById('selectInsumo');
                select.innerHTML = '<option value="">Seleccionar insumo...</option>';

                if (Array.isArray(data)) {
                    data.forEach(insumo => {
                        if (insumo.cantidad > 0) {
                            select.innerHTML += `
                            <option value="${insumo.id_insumo}" 
                                    data-precio="${insumo.precio}"
                                    data-stock="${insumo.cantidad}">
                                ${insumo.nombre} (Stock: ${insumo.cantidad})
                            </option>`;
                        }
                    });

                    if (select.options.length <= 1) {
                        select.innerHTML += '<option disabled>No hay insumos disponibles</option>';
                    }
                } else {
                    throw new Error('Los datos recibidos no son un array');
                }

                // Mostrar el modal después de cargar los datos
                const modal = new bootstrap.Modal(document.getElementById('insumosModal'));
                modal.show();
            })
            .catch(error => {
                console.error('Error al cargar insumos:', error);
                alert('Error al cargar la lista de insumos: ' + error.message);
            });
    }

    function agregarInsumo() {
        const select = document.getElementById('selectInsumo');
        const cantidad = parseInt(document.getElementById('cantidadInsumo').value);
        const option = select.options[select.selectedIndex];

        if (!select.value || !cantidad) {
            alert('Por favor seleccione un insumo y especifique la cantidad');
            return;
        }

        const stock = parseInt(option.dataset.stock);
        if (cantidad > stock) {
            alert('La cantidad solicitada supera el stock disponible');
            return;
        }

        const insumo = {
            id: select.value,
            nombre: option.text.split(' - ')[0],
            cantidad: cantidad,
            precio: parseFloat(option.dataset.precio),
            subtotal: cantidad * parseFloat(option.dataset.precio)
        };

        insumosSeleccionados.push(insumo);
        actualizarTablaInsumos();
    }

    function actualizarTablaInsumos() {
        const tbody = document.querySelector('#tablaInsumos tbody');
        tbody.innerHTML = '';
        totalInsumos = 0;

        insumosSeleccionados.forEach((insumo, index) => {
            tbody.innerHTML += `
            <tr>
                <td>${insumo.nombre}</td>
                <td>${insumo.cantidad}</td>
                <td>$${insumo.precio.toFixed(2)}</td>
                <td>$${insumo.subtotal.toFixed(2)}</td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="eliminarInsumo(${index})">
                        Eliminar
                    </button>
                </td>
            </tr>`;
            totalInsumos += insumo.subtotal;
        });

        document.getElementById('totalInsumos').textContent = `$${totalInsumos.toFixed(2)}`;
    }

    function eliminarInsumo(index) {
        insumosSeleccionados.splice(index, 1);
        actualizarTablaInsumos();
    }

    function confirmarInsumos() {
    // Validar que haya insumos seleccionados
    if (insumosSeleccionados.length === 0) {
        alert('No hay insumos seleccionados');
        return;
    }

    // Solo actualizar los montos en el formulario sin actualizar el stock todavía
    const montoConsulta = parseFloat(document.querySelector('[name="monto_consulta"]').value);
    const nuevoTotal = montoConsulta + totalInsumos;
    document.querySelector('[name="monto_insumos"]').value = totalInsumos.toFixed(2);
    document.querySelector('[name="monto_total"]').value = nuevoTotal.toFixed(2);

    // Guardar los insumos seleccionados en un input hidden para usarlos después
    const inputInsumos = document.createElement('input');
    inputInsumos.type = 'hidden';
    inputInsumos.name = 'insumos_seleccionados';
    inputInsumos.value = JSON.stringify(insumosSeleccionados);
    document.querySelector('#pagoModal form').appendChild(inputInsumos);

    // Cerrar el modal de insumos
    bootstrap.Modal.getInstance(document.getElementById('insumosModal')).hide();
}
</script>

<?php
// Verificar rutas
$footerPath = __DIR__ . '/Templates/footer.php';
if (!file_exists($footerPath)) {
    die('Error: No se encontró el archivo footer.php en la ruta especificada.');
}
require $footerPath;
?>