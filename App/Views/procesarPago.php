<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!file_exists($headerPath)) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Procesar Pago de Citas</h4>

    <div class="card">
        <h5 class="card-header">Citas Pendientes de Pago</h5>
        <div class="table-responsive text-nowrap">
            <?php if (!empty($citas)): ?>
                <table class="table">
                    <thead>
                        <tr>
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
                                <td><?php echo htmlspecialchars($cita['medico_nombre'] . ' ' . $cita['medico_apellido']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($cita['fecha_cita']); ?></td>
                                <td><?php echo htmlspecialchars(date('h:i A', strtotime($cita['horario']))); ?></td>
                                <td>
                                    <div><strong>$<?php echo number_format($cita['monto_total'], 2); ?></strong></div>
                                </td>
                                <td>
                                    <?php if ($cita['metodo_pago'] === 'efectivo'): ?>
                                        <button type="button" class="btn btn-warning btn-sm">
                                            Pagar en Caja
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#pagoModal" data-cita-id="<?php echo $cita['id']; ?>"
                                            data-monto-total="<?php echo $cita['monto_total']; ?>">
                                            Pagar con Tarjeta
                                        </button>
                                    <?php endif; ?>
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

<!-- Modal de Pago con Tarjeta -->
<div class="modal fade" id="pagoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pago con Tarjeta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formPago" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="cita_id" id="citaIdInput">
                    <input type="hidden" name="metodo_pago" value="tarjeta">
                    <input type="hidden" name="forma_pago" value="débito">

                    <div class="mb-3">
                        <label class="form-label">Monto Total</label>
                        <input type="number" class="form-control" name="monto_total" value="50.00" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número de Tarjeta</label>
                        <input type="text" class="form-control" id="numero_tarjeta" required maxlength="16"
                            pattern="\d{16}" placeholder="1234 5678 9012 3456">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Expiración</label>
                            <input type="text" class="form-control" id="fecha_expiracion" required placeholder="MM/YY"
                                pattern="\d{2}/\d{2}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" required maxlength="3" pattern="\d{3}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número de Comprobante</label>
                        <input type="text" class="form-control" name="numero_comprobante" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('formPago').addEventListener('submit', function (e) {
        e.preventDefault();

        // Validar campos de tarjeta
        const numeroTarjeta = document.getElementById('numero_tarjeta').value;
        const fechaExpiracion = document.getElementById('fecha_expiracion').value;
        const cvv = document.getElementById('cvv').value;

        if (!numeroTarjeta || !fechaExpiracion || !cvv) {
            alert('Por favor complete todos los campos de la tarjeta');
            return;
        }

        const formData = new FormData(this);
        const citaId = document.getElementById('citaIdInput').value;
        formData.append('cita_id', citaId);

        fetch('./actualizarEstadoPago', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Pago procesado correctamente');
                    window.location.href = './pagarCita';
                } else {
                    alert('Error al procesar el pago: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar el pago');
            });
    });

    // Formateo de campos de tarjeta
    document.getElementById('numero_tarjeta').addEventListener('input', function (e) {
        this.value = this.value.replace(/\D/g, '');
    });

    document.getElementById('fecha_expiracion').addEventListener('input', function (e) {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 2) {
            this.value = this.value.substring(0, 2) + '/' + this.value.substring(2);
        }
    });

    document.getElementById('cvv').addEventListener('input', function (e) {
        this.value = this.value.replace(/\D/g, '');
    });
    document.getElementById('pagoModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const citaId = button.getAttribute('data-cita-id');
    const montoTotal = button.getAttribute('data-monto-total');
    
    document.getElementById('citaIdInput').value = citaId;
    document.querySelector('input[name="monto_total"]').value = montoTotal;

    fetch('./obtenerSiguienteComprobante')
        .then(response => response.json())
        .then(data => {
            if (data.comprobante) {
                document.querySelector('input[name="numero_comprobante"]').value = data.comprobante;
            }
        })
        .catch(error => console.error('Error:', error));
});
</script>

<?php require_once __DIR__ . '/Templates/footer.php'; ?>