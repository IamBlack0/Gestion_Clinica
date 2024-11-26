<?php
require_once __DIR__ . '/Templates/header.php';
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Mis Recetas Médicas</h4>

    <div class="card">
        <h5 class="card-header">Historial de Recetas</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Médico</th>
                        <th>Tratamiento/Receta</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recetas as $receta): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($receta['fecha_emision'])); ?></td>
                            <td><?php echo htmlspecialchars($receta['medico_nombre'] . ' ' . $receta['medico_apellido']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($receta['tratamiento'])); ?></td>
                            <td>
                                <button type="button" class="btn" style="color: white; background-color: #696cff;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#verRecetaModal"
                                    data-receta='<?php echo json_encode($receta); ?>'>
                                    Ver
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($recetas)): ?>
                <div class="text-center p-3">
                    <p>No hay recetas disponibles.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal para ver la información de la receta -->
<div class="modal fade" id="verRecetaModal" tabindex="-1" aria-labelledby="verRecetaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verRecetaModalLabel">Información de la Receta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>Fecha:</strong> <span id="recetaFecha"></span></p>
                <p><strong>Médico:</strong> <span id="recetaMedico"></span></p>
                <p><strong>Tratamiento/Receta:</strong></p>
                <p id="recetaTratamiento"></p>
                <p><strong>Firma del Médico:</strong></p>
                <img id="recetaFirma" alt="Firma del médico" class="img-fluid w-px-100 h-px-100">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        var verRecetaModal = document.getElementById('verRecetaModal');
        verRecetaModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var receta = JSON.parse(button.getAttribute('data-receta'));

            document.getElementById('recetaFecha').textContent = new Date(receta.fecha_emision).toLocaleDateString();
            document.getElementById('recetaMedico').textContent = receta.medico_nombre + ' ' + receta.medico_apellido;
            document.getElementById('recetaTratamiento').textContent = receta.tratamiento;
            document.getElementById('recetaFirma').textContent = receta.medico_firma;

            // Verificar si existe la firma antes de asignarla
            if (receta.medico_firma) {
                document.getElementById('recetaFirma').src = receta.medico_firma;
                document.getElementById('recetaFirma').style.display = 'block';
            } else {
                document.getElementById('recetaFirma').style.display = 'none';
            }
        });
    });
</script>

<?php require_once __DIR__ . '/Templates/footer.php'; ?>