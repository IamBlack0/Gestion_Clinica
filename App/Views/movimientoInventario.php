<?php
// Verificar rutas
$headerPath = __DIR__ . '/Templates/header.php';
if (!file_exists($headerPath)) {
    die('Error: No se encontró el archivo header.php en la ruta especificada.');
}
require $headerPath;
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Inventario /</span> Movimientos</h4>

    <div class="card">
        <h5 class="card-header">Movimientos de Inventario</h5>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Cantidad</th>
                        <th>Realizado por</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($movimientos)): ?>
                        <?php foreach ($movimientos as $movimiento): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($movimiento['producto_nombre']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($movimiento['fecha_movimiento'])); ?></td>
                                <td>
                                    <?php if ($movimiento['tipo_movimiento'] === 'entrada'): ?>
                                        <span class="badge bg-success">Entrada</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Salida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($movimiento['cantidad']); ?></td>
                                <td><?php echo htmlspecialchars($movimiento['usuario'] ?? 'Administrador'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No se encontraron movimientos en la base de datos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php
require_once __DIR__ . '/Templates/footer.php';
?>