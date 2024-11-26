<?php
// App/Views/procesarPagoCaja.php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!file_exists($headerPath)) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;

// Conexión a la base de datos
require_once './Config/DataBase.php';
$database = new Database();
$db = $database->getConnection();

// Obtener todas las citas pendientes de pago
$query = "SELECT DISTINCT 
    c.id, 
    p.nombre as paciente_nombre,
    p.apellido as paciente_apellido,
    col.nombre as medico_nombre, 
    col.apellido as medico_apellido,
    c.fecha_cita,
    c.horario,
    hc.estado_pago,
    COALESCE(SUM(50 + COALESCE(pag.monto_insumos, 0)), 50) as monto_total
FROM citas c
INNER JOIN historial_citas hc ON c.id = hc.cita_id
INNER JOIN colaboradores col ON c.medico_id = col.id
INNER JOIN pacientes p ON c.paciente_id = p.id
LEFT JOIN pagos pag ON hc.id = pag.historial_cita_id
WHERE hc.estado_cita = 'completada'
AND hc.estado_pago = 'pendiente'
GROUP BY c.id, p.nombre, p.apellido, col.nombre, col.apellido, c.fecha_cita, c.horario, hc.estado_pago
ORDER BY c.fecha_cita ASC, c.horario ASC";

$stmt = $db->prepare($query);
$stmt->execute();
$citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Pagos Pendientes - Caja</h4>

    <div class="card">
        <h5 class="card-header">Citas Pendientes de Pago</h5>
        <div class="table-responsive text-nowrap">
            <?php if (!empty($citas)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Médico</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($citas as $cita): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cita['paciente_nombre'] . ' ' . $cita['paciente_apellido']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellido']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($cita['fecha_cita']); ?></td>
                                <td><?php echo htmlspecialchars(date('h:i A', strtotime($cita['horario']))); ?></td>
                                <td>$<?php echo number_format($cita['monto_total'], 2); ?></td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm"
                                        onclick="procesarPago(<?php echo $cita['id']; ?>, <?php echo $cita['monto_total']; ?>)">
                                        Registrar Pago
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-4 text-center">
                    <p>No hay citas pendientes de pago.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function procesarPago(citaId, montoTotal) {
    if (confirm(`¿Confirmar el pago de $${montoTotal.toFixed(2)}?`)) {
        const formData = new FormData();
        formData.append('cita_id', citaId);
        formData.append('monto_total', montoTotal);

        fetch('./actualizarEstadoPago', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pago registrado correctamente');
                location.reload();
            } else {
                alert('Error al registrar el pago: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar el pago');
        });
    }
}
</script>

<?php require_once __DIR__ . '/Templates/footer.php'; ?>