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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recetas as $receta): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($receta['fecha_emision'])); ?></td>
                            <td><?php echo htmlspecialchars($receta['medico_nombre'] . ' ' . $receta['medico_apellido']); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($receta['tratamiento'])); ?></td>
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

<?php require_once __DIR__ . '/Templates/footer.php'; ?>