<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Finanzas.php';
require_once __DIR__ . '/../../models/RegistroOcupacion.php';

$page_title = 'Pagos QR';
$mensaje = '';

// Procesar registro de pago QR
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pago_qr'])) {
    $finanzasModel = new Finanzas();
    
    $datos = [
        'ocupacion_id' => !empty($_POST['ocupacion_id']) ? $_POST['ocupacion_id'] : null,
        'monto' => floatval($_POST['monto']),
        'fecha' => $_POST['fecha'],
        'numero_transaccion' => clean_input($_POST['numero_transaccion']),
        'observaciones' => clean_input($_POST['observaciones'])
    ];
    
    if ($finanzasModel->registrarPagoQR($datos)) {
        $mensaje = '<div class="alert alert-success">Pago QR registrado correctamente.</div>';
    } else {
        $mensaje = '<div class="alert alert-danger">Error al registrar pago QR.</div>';
    }
}

// Obtener pagos QR
$finanzasModel = new Finanzas();
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$pagos_qr = $finanzasModel->obtenerPagosQR($fecha_inicio, $fecha_fin);

// Obtener ocupaciones activas
$registroModel = new RegistroOcupacion();
$ocupaciones_activas = $registroModel->obtenerActivos();

include __DIR__ . '/../../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Registrar Pago QR</h5>
            </div>
            <div class="card-body">
                <?php echo $mensaje; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Ocupación (Opcional)</label>
                        <select class="form-select" name="ocupacion_id">
                            <option value="">No asociar</option>
                            <?php foreach ($ocupaciones_activas as $ocu): ?>
                                <option value="<?php echo $ocu['id']; ?>">
                                    <?php echo $ocu['nro_pieza'] . ' - ' . $ocu['nombres_apellidos']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Monto (Bs.)</label>
                        <input type="number" step="0.01" class="form-control" name="monto" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Número de Transacción</label>
                        <input type="text" class="form-control" name="numero_transaccion" 
                               placeholder="Opcional">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Fecha</label>
                        <input type="date" class="form-control" name="fecha" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" name="registrar_pago_qr" class="btn btn-primary w-100">
                        Registrar Pago QR
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Lista de Pagos QR</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" 
                               value="<?php echo $fecha_inicio; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" name="fecha_fin" 
                               value="<?php echo $fecha_fin; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-dark w-100">Filtrar</button>
                    </div>
                </form>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Huésped/Pieza</th>
                                <th>Nro Transacción</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total = 0;
                            foreach ($pagos_qr as $pqr): 
                                $total += $pqr['monto'];
                            ?>
                                <tr>
                                    <td><?php echo formatDate($pqr['fecha']); ?></td>
                                    <td>
                                        <?php 
                                        if ($pqr['nombres_apellidos']) {
                                            echo htmlspecialchars($pqr['nombres_apellidos']) . ' (' . $pqr['nro_pieza'] . ')';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($pqr['numero_transaccion'] ?? '-'); ?>
                                    </td>
                                    <td class="text-end"><strong>Bs. <?php echo formatMoney($pqr['monto']); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($pagos_qr)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No hay pagos QR registrados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <th colspan="3" class="text-end">TOTAL:</th>
                                <th class="text-end">Bs. <?php echo formatMoney($total); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
