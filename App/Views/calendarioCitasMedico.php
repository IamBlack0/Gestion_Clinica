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
$queryCitas = "SELECT DISTINCT c.id, p.nombre AS paciente_nombre, p.apellido AS paciente_apellido, 
               c.fecha_cita, 
               DATE_FORMAT(c.horario, '%H:%i:00') as horario_24,
               DATE_FORMAT(c.horario, '%h:%i %p') as horario_display,
               c.razon
               FROM citas c
               JOIN pacientes p ON c.paciente_id = p.id
               JOIN historial_citas hc ON c.id = hc.cita_id
               WHERE c.medico_id = :medico_id
               AND hc.estado_cita = 'aceptada'
               ORDER BY c.fecha_cita ASC, c.horario ASC";

$stmtCitas = $db->prepare($queryCitas);
$stmtCitas->bindParam(':medico_id', $medico['id']);
$stmtCitas->execute();
$citas = $stmtCitas->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Calendario de Citas</h4>

    <div class="row">
        <div class="col-md-12">
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
            slotMinTime: '08:00:00',
            slotMaxTime: '17:00:00',
            displayEventTime: true,
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            events: [
                <?php foreach ($citas as $index => $cita): ?> {
                        title: '<?php echo htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']); ?>',
                        start: '<?php echo htmlspecialchars($cita['fecha_cita'] . 'T' . $cita['horario_24']); ?>',
                        description: '<?php echo htmlspecialchars($cita['razon']); ?>',
                        horario: '<?php echo htmlspecialchars($cita['horario_display']); ?>'
                    }
                    <?php
                    if ($index < count($citas) - 1)
                        echo ',';
                    ?>
                <?php endforeach; ?>
            ],
            eventClick: function(info) {
                alert('Paciente: ' + info.event.title + '\nHorario: ' + info.event.extendedProps.horario + '\nRazón: ' + info.event.extendedProps.description);
            }
        });

        calendar.render();
    });
</script>