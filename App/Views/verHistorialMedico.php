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
    <h4 class="fw-bold py-3 mb-4">Historial Médico</h4>

    <div class="card mb-4">
    <h5 class="card-header">Información del Paciente</h5>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($paciente['nombre']); ?></p>
                    <p><strong>Apellido:</strong> <?php echo htmlspecialchars($paciente['apellido']); ?></p>
                    <p><strong>Cédula:</strong> <?php echo htmlspecialchars($informacion_paciente['cedula']); ?></p>
                    <p><strong>Fecha de Nacimiento:</strong>
                        <?php echo date('d-m-Y', strtotime($informacion_paciente['fecha_nacimiento'])); ?></p>
                    <p><strong>Edad:</strong> <?php echo htmlspecialchars($informacion_paciente['edad']); ?> años</p>
                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($informacion_paciente['telefono']); ?></p>
                    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($informacion_paciente['direccion']); ?></p>
                    <p><strong>Sexo:</strong> <?php echo htmlspecialchars($informacion_paciente['sexo']); ?></p>
                    <p><strong>Tipo de Sangre:</strong>
                        <?php echo htmlspecialchars($informacion_paciente['tipo_sangre']); ?></p>
                    <p><strong>Provincia:</strong>
                        <?php echo htmlspecialchars($informacion_paciente['provincia_nombre']); ?></p>
                    <p><strong>Nacionalidad:</strong>
                        <?php echo htmlspecialchars($informacion_paciente['nacionalidad_nombre']); ?></p>
                </div>
            </div>
        </div>
    </div>

   <div class="card mb-4">
    <h5 class="card-header">Historial Médico</h5>
    <div class="card-body">
        <?php if (count($historial_medico) > 0): ?>
            <div class="accordion" id="accordionHistorialMedico">
                <?php foreach ($historial_medico as $index => $historial): ?>
                    <div class="card accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                            <button type="button" class="accordion-button <?php echo $index !== 0 ? 'collapsed' : ''; ?>"
                                data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>"
                                aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                aria-controls="collapse<?php echo $index; ?>">
                                Historial Médico | <?php echo date('d-m-Y', strtotime($historial['fecha_registro'])); ?>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $index; ?>"
                            class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                            data-bs-parent="#accordionHistorialMedico">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Peso:</strong> <?php echo htmlspecialchars($historial['peso']); ?> kg</p>
                                        <p><strong>Altura:</strong> <?php echo htmlspecialchars($historial['altura']); ?> cm</p>
                                        <p><strong>Presión Arterial:</strong>
                                            <?php echo htmlspecialchars($historial['presion_arterial']); ?></p>
                                        <p><strong>Frecuencia Cardiaca:</strong>
                                            <?php echo htmlspecialchars($historial['frecuencia_cardiaca']); ?> bpm</p>
                                        <p><strong>Temperatura:</strong>
                                            <?php echo htmlspecialchars($historial['temperatura']); ?> °C</p>
                                        <p><strong>Alergias:</strong> <?php echo htmlspecialchars($historial['alergias']); ?>
                                        </p>
                                        <p><strong>Medicamentos:</strong>
                                            <?php echo htmlspecialchars($historial['medicamentos']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Cirugías:</strong> <?php echo htmlspecialchars($historial['cirugias']); ?>
                                        </p>
                                        <p><strong>Hábitos:</strong> <?php echo htmlspecialchars($historial['habitos']); ?></p>
                                        <p><strong>Antecedentes Familiares:</strong>
                                            <?php echo htmlspecialchars($historial['antecedentes_familiares']); ?></p>
                                        <p><strong>Motivo de Consulta:</strong>
                                            <?php echo htmlspecialchars($historial['motivo_consulta']); ?></p>
                                        <p><strong>Diagnóstico:</strong>
                                            <?php echo htmlspecialchars($historial['diagnostico']); ?></p>
                                        <p><strong>Tratamiento:</strong>
                                            <?php echo htmlspecialchars($historial['tratamiento']); ?></p>
                                        <p><strong>Enfermedades Preexistentes:</strong>
                                            <?php echo htmlspecialchars($historial['enfermedades_preexistentes']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No hay historial médico registrado.</p>
        <?php endif; ?>
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