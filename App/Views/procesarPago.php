<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!file_exists($headerPath)) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;
?>

<!-- Views/procesarPago.php -->
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Procesar Pago de Citas</h4>

    <div class="card">
        <h5 class="card-header">Citas Pendientes de Pago</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Estado</th>
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
                            <td><span class="badge bg-label-warning">Pendiente</span></td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#pagoModal" data-cita-id="<?php echo $cita['id']; ?>">
                                    Procesar Pago
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Pago -->
<div class="modal fade" id="pagoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Procesar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="./procesarPago" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="cita_id" id="citaIdInput">
                    <div class="mb-3">
                        <label class="form-label">Monto</label>
                        <input type="number" class="form-control" name="monto" required step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Método de Pago</label>
                        <select class="form-select" name="metodo_pago" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Procesar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>



<?php
require_once __DIR__ . '/Templates/footer.php';
?>