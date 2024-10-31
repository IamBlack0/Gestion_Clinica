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
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($paciente['nombre']); ?></p>
            <p><strong>Apellido:</strong> <?php echo htmlspecialchars($paciente['apellido']); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($informacion_paciente['telefono']); ?></p>
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($informacion_paciente['direccion']); ?></p>
            <p><strong>Edad:</strong> <?php echo htmlspecialchars($informacion_paciente['edad']); ?></p>
            <p><strong>Sexo:</strong> <?php echo htmlspecialchars($informacion_paciente['sexo']); ?></p>
            <p><strong>Tipo de Sangre:</strong> <?php echo htmlspecialchars($informacion_paciente['tipo_sangre']); ?></p>
            <p><strong>Provincia:</strong> <?php echo htmlspecialchars($informacion_paciente['provincia_nombre']); ?></p>
            <p><strong>Nacionalidad:</strong> <?php echo htmlspecialchars($informacion_paciente['nacionalidad_nombre']); ?></p>
        </div>
    </div>

    <div class="card mb-4">
        <h5 class="card-header">Historial Médico</h5>
        <div class="card-body">
            <?php if (count($historial_medico) > 0): ?>
                <ul class="list-group">
                    <?php foreach ($historial_medico as $historial): ?>
                        <li class="list-group-item">
                            <p><strong>Fecha:</strong> <?php echo htmlspecialchars($historial['fecha_registro']); ?></p>
                            <p><strong>Peso:</strong> <?php echo htmlspecialchars($historial['peso']); ?> kg</p>
                            <p><strong>Altura:</strong> <?php echo htmlspecialchars($historial['altura']); ?> cm</p>
                            <p><strong>Presión Arterial:</strong> <?php echo htmlspecialchars($historial['presion_arterial']); ?></p>
                            <p><strong>Frecuencia Cardiaca:</strong> <?php echo htmlspecialchars($historial['frecuencia_cardiaca']); ?> bpm</p>
                            <p><strong>Temperatura:</strong> <?php echo htmlspecialchars($historial['temperatura']); ?> °C</p>
                            <p><strong>Alergias:</strong> <?php echo htmlspecialchars($historial['alergias']); ?></p>
                            <p><strong>Medicamentos:</strong> <?php echo htmlspecialchars($historial['medicamentos']); ?></p>
                            <p><strong>Cirugías:</strong> <?php echo htmlspecialchars($historial['cirugias']); ?></p>
                            <p><strong>Hábitos:</strong> <?php echo htmlspecialchars($historial['habitos']); ?></p>
                            <p><strong>Antecedentes Familiares:</strong> <?php echo htmlspecialchars($historial['antecedentes_familiares']); ?></p>
                            <p><strong>Motivo de Consulta:</strong> <?php echo htmlspecialchars($historial['motivo_consulta']); ?></p>
                            <p><strong>Diagnóstico:</strong> <?php echo htmlspecialchars($historial['diagnostico']); ?></p>
                            <p><strong>Tratamiento:</strong> <?php echo htmlspecialchars($historial['tratamiento']); ?></p>
                            <p><strong>Enfermedades Preexistentes:</strong> <?php echo htmlspecialchars($historial['enfermedades_preexistentes']); ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
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