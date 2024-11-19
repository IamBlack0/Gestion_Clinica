<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!file_exists($headerPath)) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Mis Pagos Pendientes</h4>

    <div class="card">
        <h5 class="card-header">Pagos de Consultas</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Médico</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Total a Pagar</th>
                        <th>Método de Pago</th>
                        <th>N° Comprobante</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($citas as $cita): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellido']); ?>
                            </td>
                            <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($cita['fecha_cita']))); ?></td>
                            <td><?php echo htmlspecialchars(date('h:i A', strtotime($cita['horario']))); ?></td>
                            <td>$50.00</td>
                            <td><?php echo ucfirst(htmlspecialchars($cita['metodo_pago'] ?? 'Pendiente')); ?></td>
                            <td><?php echo htmlspecialchars($cita['numero_comprobante'] ?? '-'); ?></td>
                            <td>
                                <?php if ($cita['estado_pago'] === 'pendiente'): ?>
                                    <span class="badge bg-label-warning">Pendiente</span>
                                <?php else: ?>
                                    <span class="badge bg-label-success">Pagado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cita['estado_pago'] === 'pendiente'): ?>
                                    <?php if ($cita['metodo_pago'] === 'efectivo'): ?>
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="confirmarPagoEfectivo(<?php echo $cita['id']; ?>)">
                                            Confirmar Pago en Efectivo
                                        </button>
                                    <?php elseif ($cita['metodo_pago'] === 'tarjeta'): ?>
                                        <button type="button" class="btn btn-primary btn-sm"
                                            onclick="pagarConTarjeta(<?php echo $cita['id']; ?>)">
                                            Pagar con Tarjeta
                                        </button>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-success">Pagado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Pago con Tarjeta -->
<div class="modal fade" id="pagoTarjetaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pago con Tarjeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPagoTarjeta">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Número de Tarjeta</label>
                        <input type="text" class="form-control" id="numeroTarjeta" required maxlength="16"
                            pattern="\d{16}">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Expiración</label>
                            <input type="text" class="form-control" id="fechaExpiracion" placeholder="MM/YY" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" required maxlength="3" pattern="\d{3}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre en la Tarjeta</label>
                        <input type="text" class="form-control" id="nombreTarjeta" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Procesar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmacionPagoModal" tabindex="-1" aria-labelledby="confirmacionPagoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmacionPagoModalLabel">¡Pago Confirmado!</h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bx bx-check-circle text-success" style="font-size: 64px;"></i>
                </div>
                <p class="text-center">Su pago ha sido confirmado exitosamente.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="location.reload()">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmarPagoEfectivo(citaId) {
    if (confirm('¿Confirma que realizará el pago en efectivo en la clínica?')) {
        fetch('./confirmarPagoEfectivo', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cita_id: citaId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = new bootstrap.Modal(document.getElementById('confirmacionPagoModal'));
                modal.show();
            } else {
                alert('Error al confirmar el pago: ' + data.message);
            }
        });
    }
}

    function pagarConTarjeta(citaId) {
        const modal = new bootstrap.Modal(document.getElementById('pagoTarjetaModal'));
        modal.show();

        document.getElementById('formPagoTarjeta').onsubmit = function(e) {
        e.preventDefault();
        fetch('./procesarPagoTarjeta', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cita_id: citaId,
                numero_tarjeta: document.getElementById('numeroTarjeta').value,
                fecha_expiracion: document.getElementById('fechaExpiracion').value,
                cvv: document.getElementById('cvv').value,
                nombre_tarjeta: document.getElementById('nombreTarjeta').value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pagoModal = bootstrap.Modal.getInstance(document.getElementById('pagoTarjetaModal'));
                pagoModal.hide();
                const confirmacionModal = new bootstrap.Modal(document.getElementById('confirmacionPagoModal'));
                confirmacionModal.show();
            } else {
                alert('Error al procesar el pago: ' + data.message);
            }
        });
    };
}

    // Formateo de campos de tarjeta
    document.getElementById('numeroTarjeta').addEventListener('input', function (e) {
        this.value = this.value.replace(/\D/g, '');
    });

    document.getElementById('fechaExpiracion').addEventListener('input', function (e) {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 2) {
            value = value.substr(0, 2) + '/' + value.substr(2);
        }
        this.value = value;
    });

    document.getElementById('cvv').addEventListener('input', function (e) {
        this.value = this.value.replace(/\D/g, '');
    });
</script>

<?php
require_once __DIR__ . '/Templates/footer.php';
?>