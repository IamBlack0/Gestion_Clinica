<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!file_exists($headerPath)) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Historial de Citas</h4>

    <div class="card">
        <h5 class="card-header">Citas</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre del Médico</th>
                        <th>Fecha de la Cita</th>
                        <th>Hora</th> 
                        <th>Estado de Pago</th>
                        <th>Estado de la Cita</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellido']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($cita['fecha_cita']); ?></td>
                            <td><?php echo htmlspecialchars(date('h:i A', strtotime($cita['horario']))); ?></td>
                            <!-- Nueva celda -->
                            <td>
                                <?php if ($cita['estado_pago'] === 'pagado'): ?>
                                    <span class="badge bg-label-primary">Pagado</span>
                                <?php elseif ($cita['estado_pago'] === 'pendiente'): ?>
                                    <span class="badge bg-label-warning">Pendiente</span>
                                <?php else: ?>
                                    <span class="badge bg-label-danger">Desconocido</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cita['estado_cita'] === 'completada'): ?>
                                    <span class="badge bg-label-primary me-1">Completada</span>
                                <?php elseif ($cita['estado_cita'] === 'aceptada'): ?>
                                    <span class="badge bg-label-success">Aceptada</span>
                                <?php elseif ($cita['estado_cita'] === 'pendiente'): ?>
                                    <span class="badge bg-label-warning">Pendiente</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/Templates/footer.php';
?>