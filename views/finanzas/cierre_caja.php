<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/CierreCaja.php';

// Verificar que el usuario sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header('Location: ' . BASE_PATH . '/views/finanzas/resumen.php?error=acceso_denegado');
    exit;
}

$page_title = 'Cierre de Caja';
$mensaje = '';
$tipo_mensaje = '';

$cierreModel = new CierreCaja();

// Procesar cierre de caja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar_caja'])) {
    try {
        $observaciones = !empty($_POST['observaciones']) ? clean_input($_POST['observaciones']) : null;
        
        $cierre_id = $cierreModel->registrarCierre($observaciones);
        
        if ($cierre_id) {
            $mensaje = 'Caja cerrada exitosamente. Rendición de cuentas registrada. ID del cierre: ' . $cierre_id;
            $tipo_mensaje = 'success';
        } else {
            throw new Exception('Error al cerrar la caja');
        }
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
        error_log("Error al cerrar caja: " . $e->getMessage());
    }
}

// Obtener resumen actual
$resumen_actual = $cierreModel->calcularResumenActual();

// Obtener historial
$historial = $cierreModel->obtenerHistorial(50);

include __DIR__ . '/../../includes/header.php';
?>

<style>
.glass-card {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.8);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dark .glass-card {
    background: rgba(23, 23, 23, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.stat-card {
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
}

.modal-content {
    background-color: #fefefe;
    margin: 3% auto;
    padding: 20px;
    border: 1px solid #888;
    border-radius: 12px;
    width: 95%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}
</style>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-4xl font-bold text-noir dark:text-white mb-2">
                <i class="fas fa-cash-register text-green-600"></i> Cierre de Caja
            </h1>
            <p class="text-sm md:text-base text-gray-500 dark:text-gray-400">
                Gestiona tus rendiciones de cuentas - Cierra cuando quieras
            </p>
        </div>
        <a href="<?php echo BASE_PATH; ?>/index.php" class="px-3 md:px-6 py-2 md:py-3 border border-gray-300 dark:border-gray-700 rounded-lg md:rounded-xl text-gray-700 dark:text-gray-300 text-sm md:text-base font-medium hover:bg-mist dark:hover:bg-gray-800 transition-all duration-200 text-center">
            ← Volver
        </a>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($mensaje): ?>
    <div class="mb-8 animate-fade-in">
        <div class="bg-<?php echo $tipo_mensaje === 'success' ? 'green' : 'red'; ?>-50 dark:bg-<?php echo $tipo_mensaje === 'success' ? 'green' : 'red'; ?>-900/20 border border-<?php echo $tipo_mensaje === 'success' ? 'green' : 'red'; ?>-200 dark:border-<?php echo $tipo_mensaje === 'success' ? 'green' : 'red'; ?>-800 rounded-xl p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <?php if ($tipo_mensaje === 'success'): ?>
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-<?php echo $tipo_mensaje === 'success' ? 'green' : 'red'; ?>-800 dark:text-<?php echo $tipo_mensaje === 'success' ? 'green' : 'red'; ?>-200">
                        <?php echo $mensaje; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Resumen Actual de Caja -->
<div class="glass-card rounded-xl p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-noir dark:text-white mb-2">
                <i class="fas fa-box-open text-blue-600"></i> Caja Actual (Abierta)
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Desde: <span class="font-semibold"><?php echo date('d/m/Y H:i', strtotime($resumen_actual['fecha_apertura'])); ?></span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold animate-pulse">
                <i class="fas fa-circle text-xs"></i> Abierta
            </span>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Efectivo -->
        <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-4 border-2 border-green-300 dark:border-green-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-green-700 dark:text-green-300">Efectivo</span>
                <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
            </div>
            <p class="text-2xl font-bold text-green-800 dark:text-green-200">
                Bs. <?php echo number_format($resumen_actual['total_efectivo'], 2); ?>
            </p>
        </div>

        <!-- QR -->
        <div class="stat-card bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl p-4 border-2 border-purple-300 dark:border-purple-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-purple-700 dark:text-purple-300">QR</span>
                <i class="fas fa-qrcode text-2xl text-purple-600"></i>
            </div>
            <p class="text-2xl font-bold text-purple-800 dark:text-purple-200">
                Bs. <?php echo number_format($resumen_actual['total_qr'], 2); ?>
            </p>
        </div>

        <!-- Egresos -->
        <div class="stat-card bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-xl p-4 border-2 border-red-300 dark:border-red-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-red-700 dark:text-red-300">Egresos</span>
                <i class="fas fa-arrow-down text-2xl text-red-600"></i>
            </div>
            <p class="text-2xl font-bold text-red-800 dark:text-red-200">
                Bs. <?php echo number_format($resumen_actual['total_egresos'], 2); ?>
            </p>
        </div>

        <!-- Balance -->
        <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-4 border-2 border-blue-300 dark:border-blue-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">Balance Efectivo</span>
                <i class="fas fa-wallet text-2xl text-blue-600"></i>
            </div>
            <p class="text-2xl font-bold text-blue-800 dark:text-blue-200">
                Bs. <?php echo number_format($resumen_actual['balance_efectivo'], 2); ?>
            </p>
        </div>
    </div>

    <!-- Total General -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 dark:from-gray-900 dark:to-black rounded-xl p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-300 mb-1">Balance Total</p>
                <p class="text-xs text-gray-400">(Efectivo + QR - Egresos)</p>
            </div>
            <p class="text-4xl font-bold text-white">
                Bs. <?php echo number_format($resumen_actual['balance_total'], 2); ?>
            </p>
        </div>
    </div>

    <!-- Botón de Cerrar Caja -->
    <div class="flex gap-4">
        <button 
            onclick="abrirModalCerrarCaja()"
            class="flex-1 px-6 py-4 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-xl font-bold text-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
        >
            <i class="fas fa-lock mr-2"></i> Cerrar Caja y Generar Rendición
        </button>
        <button 
            onclick="verDetalles()"
            class="px-6 py-4 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-xl font-semibold transition-all duration-200"
        >
            <i class="fas fa-list mr-2"></i> Ver Detalles
        </button>
    </div>
</div>

<!-- Detalles (Ocultos por defecto) -->
<div id="detallesSection" style="display: none;" class="glass-card rounded-xl p-6 mb-8">
    <h3 class="text-xl font-bold text-noir dark:text-white mb-4">
        <i class="fas fa-info-circle text-blue-600"></i> Detalles de Movimientos
    </h3>
    
    <!-- Ingresos -->
    <div class="mb-6">
        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">
            <i class="fas fa-arrow-up text-green-600"></i> Ingresos (<?php echo count($resumen_actual['detalles_ingresos']); ?>)
        </h4>
        <?php if (!empty($resumen_actual['detalles_ingresos'])): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-left">Concepto</th>
                            <th class="px-3 py-2 text-left">Método</th>
                            <th class="px-3 py-2 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($resumen_actual['detalles_ingresos'] as $ing): ?>
                            <tr>
                                <td class="px-3 py-2"><?php echo date('d/m/Y', strtotime($ing['fecha'])); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($ing['concepto']); ?></td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-1 rounded text-xs font-semibold <?php 
                                        echo $ing['metodo_pago'] === 'efectivo' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800';
                                    ?>">
                                        <?php echo strtoupper($ing['metodo_pago']); ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-green-600">
                                    Bs. <?php echo number_format($ing['monto'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4">No hay ingresos en este período</p>
        <?php endif; ?>
    </div>

    <!-- Egresos -->
    <div>
        <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">
            <i class="fas fa-arrow-down text-red-600"></i> Egresos (<?php echo count($resumen_actual['detalles_egresos']); ?>)
        </h4>
        <?php if (!empty($resumen_actual['detalles_egresos'])): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-left">Concepto</th>
                            <th class="px-3 py-2 text-left">Categoría</th>
                            <th class="px-3 py-2 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php foreach ($resumen_actual['detalles_egresos'] as $egr): ?>
                            <tr>
                                <td class="px-3 py-2"><?php echo date('d/m/Y', strtotime($egr['fecha'])); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($egr['concepto']); ?></td>
                                <td class="px-3 py-2"><?php echo htmlspecialchars($egr['categoria'] ?? '-'); ?></td>
                                <td class="px-3 py-2 text-right font-semibold text-red-600">
                                    Bs. <?php echo number_format($egr['monto'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500 text-center py-4">No hay egresos en este período</p>
        <?php endif; ?>
    </div>
</div>

<!-- Historial de Cierres -->
<div class="glass-card rounded-xl p-6">
    <h2 class="text-2xl font-bold text-noir dark:text-white mb-6">
        <i class="fas fa-history text-gray-600"></i> Historial de Cierres
    </h2>

    <?php if (empty($historial)): ?>
        <div class="text-center py-12">
            <i class="fas fa-inbox text-6xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">
                No hay cierres registrados aún
            </h3>
            <p class="text-gray-500 dark:text-gray-400">
                Cuando realices tu primer cierre de caja, aparecerá aquí
            </p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Período</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Usuario</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Efectivo</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">QR</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Balance</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($historial as $cierre): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                #<?php echo $cierre['id']; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500">Desde:</span>
                                    <span class="font-medium"><?php echo date('d/m/Y H:i', strtotime($cierre['fecha_apertura'])); ?></span>
                                    <span class="text-xs text-gray-500 mt-1">Hasta:</span>
                                    <span class="font-medium"><?php echo date('d/m/Y H:i', strtotime($cierre['fecha_cierre'])); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                <?php echo htmlspecialchars($cierre['usuario_nombre']); ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">
                                Bs. <?php echo number_format($cierre['total_efectivo'], 2); ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-purple-600">
                                Bs. <?php echo number_format($cierre['total_qr'], 2); ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-blue-600">
                                Bs. <?php echo number_format($cierre['balance_total'], 2); ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a 
                                    href="<?php echo BASE_PATH; ?>/views/finanzas/generar_pdf_cierre.php?id=<?php echo $cierre['id']; ?>"
                                    target="_blank"
                                    class="inline-block px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs font-medium transition-colors"
                                    title="Descargar PDF"
                                >
                                    <i class="fas fa-file-pdf"></i> PDF
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de Cerrar Caja -->
<div id="modalCerrarCaja" class="modal">
    <div class="modal-content">
        <h2 class="text-2xl font-bold text-noir mb-4">
            <i class="fas fa-exclamation-triangle text-orange-600"></i> Confirmar Cierre de Caja
        </h2>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
            <p class="text-sm text-yellow-800">
                <strong>⚠️ Importante:</strong> Al cerrar la caja, se registrará una rendición de cuentas con los datos actuales. 
                Después del cierre, se abrirá automáticamente una nueva caja que empezará a acumular los nuevos movimientos.
            </p>
        </div>

        <form method="POST" id="formCerrarCaja">
            <input type="hidden" name="cerrar_caja" value="1">
            
            <!-- Resumen -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-4">
                <h3 class="font-semibold text-gray-800 dark:text-gray-200 mb-3">Resumen a cerrar:</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Efectivo:</span>
                        <span class="font-semibold text-green-600">Bs. <?php echo number_format($resumen_actual['total_efectivo'], 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">QR:</span>
                        <span class="font-semibold text-purple-600">Bs. <?php echo number_format($resumen_actual['total_qr'], 2); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Egresos:</span>
                        <span class="font-semibold text-red-600">Bs. <?php echo number_format($resumen_actual['total_egresos'], 2); ?></span>
                    </div>
                    <div class="border-t border-gray-300 dark:border-gray-600 my-2"></div>
                    <div class="flex justify-between">
                        <span class="font-semibold text-gray-800 dark:text-gray-200">Balance Total:</span>
                        <span class="font-bold text-blue-600 text-lg">Bs. <?php echo number_format($resumen_actual['balance_total'], 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Observaciones (Opcional):
                </label>
                <textarea 
                    name="observaciones" 
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200"
                    placeholder="Ej: Rendición semanal, efectivo entregado al dueño, etc."
                ></textarea>
            </div>
            
            <div class="flex gap-3">
                <button 
                    type="submit"
                    class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-colors"
                >
                    <i class="fas fa-lock"></i> Confirmar y Cerrar Caja
                </button>
                <button 
                    type="button"
                    onclick="cerrarModal()"
                    class="px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg font-semibold transition-colors"
                >
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalCerrarCaja() {
    document.getElementById('modalCerrarCaja').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalCerrarCaja').style.display = 'none';
}

function verDetalles() {
    const detallesSection = document.getElementById('detallesSection');
    if (detallesSection.style.display === 'none') {
        detallesSection.style.display = 'block';
    } else {
        detallesSection.style.display = 'none';
    }
}

// Cerrar modal al hacer clic fuera de él
window.onclick = function(event) {
    const modal = document.getElementById('modalCerrarCaja');
    if (event.target == modal) {
        cerrarModal();
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
