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

// Procesar apertura de caja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['abrir_caja'])) {
    try {
        $recepcionista = clean_input($_POST['recepcionista']);
        $_SESSION['recepcionista_actual'] = $recepcionista;
        $_SESSION['caja_abierta_fecha'] = date('Y-m-d H:i:s');
        
        $mensaje = 'Caja abierta para ' . htmlspecialchars($recepcionista) . '. Puedes empezar a trabajar.';
        $tipo_mensaje = 'success';
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}

// Procesar cierre de caja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar_caja'])) {
    try {
        $observaciones = !empty($_POST['observaciones']) ? clean_input($_POST['observaciones']) : null;
        $recepcionista = $_SESSION['recepcionista_actual'] ?? 'Sistema';
        
        $cierre_id = $cierreModel->registrarCierre($observaciones, $recepcionista);
        
        if ($cierre_id) {
            // Limpiar sesión
            unset($_SESSION['recepcionista_actual']);
            unset($_SESSION['caja_abierta_fecha']);
            
            $mensaje = '¡Caja cerrada exitosamente! Tu rendición ha sido guardada.';
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

// Verificar estado de la caja
$caja_abierta = isset($_SESSION['recepcionista_actual']);
$recepcionista_actual = $_SESSION['recepcionista_actual'] ?? null;
$saldo_inicial = $cierreModel->obtenerSaldoInicial();

// Obtener resumen actual solo si hay caja abierta
if ($caja_abierta) {
    $resumen_actual = $cierreModel->calcularResumenActual();
} else {
    $resumen_actual = null;
}

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

.recepcionista-card > div {
    transition: all 0.3s ease;
}

.recepcionista-card:hover > div {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
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

<?php if (!$caja_abierta): ?>
    <!-- CAJA CERRADA - Formulario para Abrir -->
    <div class="glass-card rounded-xl p-8 mb-8">
        <div class="text-center mb-6">
            <div class="inline-block mb-4">
                <img 
                    src="<?php echo BASE_PATH; ?>/assets/img/logo.png" 
                    alt="Hotel Cecil" 
                    class="w-32 h-32 object-contain mx-auto drop-shadow-lg"
                >
            </div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-2">
                La caja está cerrada
            </h2>
            <?php if ($saldo_inicial > 0): ?>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Saldo inicial disponible: <span class="font-bold text-blue-600">Bs. <?php echo number_format($saldo_inicial, 2); ?></span>
                </p>
            <?php else: ?>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Inicia en: <span class="font-bold">Bs. 0.00</span>
                </p>
            <?php endif; ?>
        </div>

        <form method="POST" class="max-w-2xl mx-auto" id="formAbrirCaja">
            <input type="hidden" name="abrir_caja" value="1">
            <input type="hidden" name="recepcionista" id="recepcionista_seleccionado" required>
            
            <div class="mb-6">
                <label class="block text-base font-medium text-gray-700 dark:text-gray-300 mb-4 text-center">
                    ¿Quién abre la caja?
                </label>
                <div class="grid grid-cols-2 gap-6">
                    <!-- Isaac Vargas -->
                    <div class="recepcionista-card cursor-pointer" onclick="seleccionarRecepcionista('Isaac Vargas', this)">
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 transition-all duration-200 text-center">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-2xl font-bold">
                                IV
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Isaac Vargas</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Recepcionista</p>
                            <div class="mt-4 hidden checkmark">
                                <i class="fas fa-check-circle text-3xl text-blue-600"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Gabriel Duran -->
                    <div class="recepcionista-card cursor-pointer" onclick="seleccionarRecepcionista('Gabriel Duran', this)">
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border-2 border-gray-200 dark:border-gray-700 hover:border-green-500 dark:hover:border-green-400 transition-all duration-200 text-center">
                            <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white text-2xl font-bold">
                                GD
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Gabriel Duran</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Recepcionista</p>
                            <div class="mt-4 hidden checkmark">
                                <i class="fas fa-check-circle text-3xl text-green-600"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button 
                type="submit"
                id="btnAbrirCaja"
                disabled
                class="w-full px-6 py-4 bg-gray-400 text-white rounded-lg font-bold text-lg transition-colors cursor-not-allowed"
            >
                <i class="fas fa-unlock mr-2"></i> Abrir Mi Caja
            </button>
        </form>
    </div>

<?php else: ?>
    <!-- CAJA ABIERTA - Resumen Actual -->
    <div class="glass-card rounded-xl p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                    Caja abierta de: <span class="text-blue-600"><?php echo htmlspecialchars($recepcionista_actual); ?></span>
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Abierta desde: <?php echo date('d/m/Y H:i', strtotime($_SESSION['caja_abierta_fecha'])); ?>
                </p>
            </div>
            <button 
                onclick="abrirModalCerrarCaja()"
                class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-colors"
            >
                <i class="fas fa-lock mr-2"></i> Cerrar Mi Caja
            </button>
        </div>

        <!-- Resumen de Totales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-700">
                <p class="text-xs text-green-600 dark:text-green-400 mb-1">Ingresos Efectivo</p>
                <p class="text-xl font-bold text-green-700 dark:text-green-300">
                    Bs. <?php echo number_format($resumen_actual['total_efectivo'], 2); ?>
                </p>
            </div>

            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 border border-purple-200 dark:border-purple-700">
                <p class="text-xs text-purple-600 dark:text-purple-400 mb-1">Pagos QR</p>
                <p class="text-xl font-bold text-purple-700 dark:text-purple-300">
                    Bs. <?php echo number_format($resumen_actual['total_qr'], 2); ?>
                </p>
            </div>

            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-700">
                <p class="text-xs text-red-600 dark:text-red-400 mb-1">Egresos</p>
                <p class="text-xl font-bold text-red-700 dark:text-red-300">
                    Bs. <?php echo number_format($resumen_actual['total_egresos'], 2); ?>
                </p>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-700">
                <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Balance Total</p>
                <p class="text-xl font-bold text-blue-700 dark:text-blue-300">
                    Bs. <?php echo number_format($resumen_actual['balance_total'], 2); ?>
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase">Recepcionista</th>
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
                                <span class="font-semibold"><?php echo htmlspecialchars($cierre['recepcionista'] ?? 'Sistema'); ?></span>
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

<!-- Modal de Cerrar Caja (solo si hay caja abierta) -->
<?php if ($caja_abierta): ?>
<div id="modalCerrarCaja" class="modal">
    <div class="modal-content">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">
            <i class="fas fa-lock text-red-600"></i> Cerrar Mi Caja
        </h2>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-6">
            <p class="text-sm text-blue-800 dark:text-blue-200">
                <strong>Recepcionista:</strong> <?php echo htmlspecialchars($recepcionista_actual); ?><br>
                <strong>Balance en efectivo que dejas:</strong> <span class="text-lg font-bold">Bs. <?php echo number_format($resumen_actual['balance_efectivo'], 2); ?></span>
            </p>
            <p class="text-xs text-blue-700 dark:text-blue-300 mt-2">
                Este monto será el saldo inicial para el próximo recepcionista.
            </p>
        </div>

        <form method="POST" id="formCerrarCaja">
            <input type="hidden" name="cerrar_caja" value="1">

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Observaciones (Opcional):
                </label>
                <textarea 
                    name="observaciones" 
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200"
                    placeholder="Ej: Entregado al dueño, deposité Bs. 1000, etc."
                ></textarea>
            </div>
            
            <div class="flex gap-3">
                <button 
                    type="submit"
                    class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-colors"
                >
                    <i class="fas fa-lock mr-2"></i> Confirmar y Cerrar Caja
                </button>
                <button 
                    type="button"
                    onclick="cerrarModal()"
                    class="px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 dark:text-gray-300 rounded-lg font-semibold transition-colors"
                >
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function abrirModalCerrarCaja() {
    document.getElementById('modalCerrarCaja').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modalCerrarCaja').style.display = 'none';
}

// Seleccionar recepcionista (con tarjetas)
function seleccionarRecepcionista(nombre, cardElement) {
    // Remover selección previa
    document.querySelectorAll('.recepcionista-card > div').forEach(card => {
        card.classList.remove('border-blue-500', 'border-green-500', 'border-4');
        card.classList.add('border-2', 'border-gray-200');
    });
    document.querySelectorAll('.checkmark').forEach(check => {
        check.classList.add('hidden');
    });
    
    // Marcar como seleccionado
    const innerDiv = cardElement.querySelector('div');
    innerDiv.classList.remove('border-2', 'border-gray-200');
    innerDiv.classList.add('border-4');
    
    if (nombre === 'Isaac Vargas') {
        innerDiv.classList.add('border-blue-500');
    } else {
        innerDiv.classList.add('border-green-500');
    }
    
    cardElement.querySelector('.checkmark').classList.remove('hidden');
    
    // Guardar valor
    document.getElementById('recepcionista_seleccionado').value = nombre;
    
    // Habilitar botón
    const btn = document.getElementById('btnAbrirCaja');
    btn.disabled = false;
    btn.classList.remove('bg-gray-400', 'cursor-not-allowed');
    btn.classList.add('bg-blue-600', 'hover:bg-blue-700', 'cursor-pointer');
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
