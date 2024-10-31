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

// Obtener las citas del médico
$queryCitas = "SELECT c.id, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, c.fecha_cita, c.horario, c.razon, hc.estado_cita
               FROM citas c
               JOIN pacientes p ON c.paciente_id = p.id
               JOIN historial_citas hc ON c.id = hc.id
               WHERE c.medico_id = :medico_id";
$stmtCitas = $db->prepare($queryCitas);
$stmtCitas->bindParam(':medico_id', $medico['id']);
$stmtCitas->execute();
$citas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Calendario de Citas</h4>

    <div class="row">
        <div class="col-md-4">
            <h5 class="card-header">Lista de Pacientes</h5>
            <div class="list-group" style="max-height: 600px; overflow-y: auto;">
                <?php foreach ($citas as $cita): ?>
                    <?php if ($cita['estado_cita'] === 'pendiente'): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']); ?></h5>
                                <p class="card-text"><strong>Fecha:</strong>  <?php echo htmlspecialchars($cita['fecha_cita']); ?></p>
                                <p class="card-text"><strong>Horario:</strong>  <?php echo htmlspecialchars($cita['horario']); ?></p>
                                <p class="card-text"><strong>Razón:</strong>  <?php echo htmlspecialchars($cita['razon']); ?></p>
                                <button class="btn btn-success aceptar-cita" data-cita-id="<?php echo $cita['id']; ?>">Aceptar</button>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-8">
            <div id="calendar"></div>
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

<!-- Incluir FullCalendar -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: [
            <?php foreach ($citas as $index => $cita): ?>
            <?php if ($cita['estado_cita'] === 'aceptada'): ?>
            {
                title: '<?php echo htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']); ?>',
                start: '<?php echo htmlspecialchars($cita['fecha_cita'] . 'T' . ($cita['horario'] === 'mañana' ? '08:00:00' : ($cita['horario'] === 'tarde' ? '13:00:00' : '18:00:00'))); ?>',
                description: '<?php echo htmlspecialchars($cita['razon']); ?>',
                horario: '<?php echo htmlspecialchars($cita['horario']); ?>'
            }<?php if ($index < count($citas) - 1) echo ','; ?>
            <?php endif; ?>
            <?php endforeach; ?>
        ],
        eventClick: function(info) {
            alert('Paciente: ' + info.event.title + '\nHorario: ' + info.event.extendedProps.horario + '\nRazón: ' + info.event.extendedProps.description);
        }
    });

    calendar.render();

    // Manejar la aceptación de citas
    document.querySelectorAll('.aceptar-cita').forEach(function(button) {
        button.addEventListener('click', function() {
            var citaId = this.getAttribute('data-cita-id');
            fetch(`./aceptarCita?cita_id=${citaId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cita aceptada correctamente.');
                    location.reload();
                } else {
                    alert('Error al aceptar la cita.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al aceptar la cita.');
            });
        });
    });
});
</script>