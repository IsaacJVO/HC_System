<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/RegistroOcupacion.php';

$page_title = 'Huéspedes Activos';

$registroModel = new RegistroOcupacion();
$ocupaciones = $registroModel->obtenerActivos();

// Procesar finalización de ocupación
if (isset($_POST['finalizar_ocupacion'])) {
    $ocupacion_id = $_POST['ocupacion_id'];
    $fecha_salida = $_POST['fecha_salida'] ?? date('Y-m-d');
    
    if ($registroModel->finalizarOcupacion($ocupacion_id, $fecha_salida)) {
        header('Location: activos.php?msg=finalizado');
        exit;
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-noir mb-2">Huéspedes Activos</h1>
            <p class="text-gray-500">Estadías actuales en el hotel</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>/views/huespedes/nuevo.php" class="px-6 py-3 bg-noir text-white rounded-xl font-medium hover:bg-gray-800 transition-all duration-200 shadow-lg">
            + Nuevo Registro
        </a>
    </div>
</div>

<!-- Success Message -->
<?php if (isset($_GET['msg']) && $_GET['msg'] == 'finalizado'): ?>
    <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">Ocupación finalizada correctamente</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Content -->
<?php if (empty($ocupaciones)): ?>
    <div class="bg-white rounded-2xl border border-gray-200 p-16 text-center">
        <div class="w-24 h-24 bg-mist rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
        </div>
        <h3 class="text-xl font-semibold text-noir mb-2">Sin huéspedes activos</h3>
        <p class="text-gray-500 mb-6">No hay estadías registradas en este momento</p>
        <a href="<?php echo BASE_PATH; ?>/views/huespedes/nuevo.php" class="inline-flex items-center px-6 py-3 bg-noir text-white rounded-xl font-medium hover:bg-gray-800 transition-all duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Registrar Primer Huésped
        </a>
    </div>
<?php else: ?>
    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Activos</p>
                    <p class="text-3xl font-bold text-noir"><?php echo count($ocupaciones); ?></p>
                </div>
                <div class="w-12 h-12 bg-noir rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-mist border-b border-gray-200">
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Huésped</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Documento</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Info</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Habitación</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estadía</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Procedencia</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($ocupaciones as $idx => $ocu): ?>
                        <tr class="hover:bg-mist transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-noir flex items-center justify-center text-white font-semibold mr-3">
                                        <?php echo strtoupper(substr($ocu['nombres_apellidos'], 0, 2)); ?>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-noir"><?php echo htmlspecialchars($ocu['nombres_apellidos']); ?></div>
                                        <div class="text-xs text-gray-500">
                                            <?php echo $ocu['genero'] == 'M' ? 'Masculino' : 'Femenino'; ?> · <?php echo $ocu['edad']; ?> años
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-noir"><?php echo htmlspecialchars($ocu['ci_pasaporte']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($ocu['nacionalidad']); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-700"><?php echo htmlspecialchars($ocu['profesion'] ?: 'N/A'); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($ocu['objeto'] ?: 'Sin especificar'); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-noir text-white">
                                    <?php echo $ocu['nro_pieza']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-noir"><?php echo formatDate($ocu['fecha_ingreso']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo $ocu['nro_dias']; ?> día<?php echo $ocu['nro_dias'] > 1 ? 's' : ''; ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-700"><?php echo htmlspecialchars($ocu['procedencia'] ?: 'N/A'); ?></div>
                                <div class="text-xs text-gray-500">→ <?php echo htmlspecialchars($ocu['prox_destino'] ?: 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button 
                                    type="button" 
                                    onclick="abrirModalCheckout(<?php echo $ocu['id']; ?>, '<?php echo htmlspecialchars($ocu['nombres_apellidos']); ?>', '<?php echo $ocu['nro_pieza']; ?>')"
                                    class="px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 transition-colors duration-200"
                                >
                                    Check-out
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Modal de Confirmación de Checkout -->
<div id="modal_checkout" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Content -->
            <h3 class="text-2xl font-bold text-center text-noir mb-2">Confirmar Check-out</h3>
            <p class="text-center text-gray-600 mb-6">¿Está seguro de finalizar la estadía?</p>
            
            <!-- Info del huésped -->
            <div class="bg-mist rounded-xl p-4 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Huésped:</span>
                    <span class="text-sm font-semibold text-noir" id="modal_nombre_huesped"></span>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600">Habitación:</span>
                    <span class="text-sm font-semibold text-noir" id="modal_habitacion"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Fecha de salida:</span>
                    <span class="text-sm font-semibold text-noir"><?php echo date('d/m/Y'); ?></span>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex gap-3">
                <button 
                    type="button" 
                    onclick="cerrarModalCheckout()"
                    class="flex-1 px-6 py-3 border-2 border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 transition-all duration-200"
                >
                    Cancelar
                </button>
                <button 
                    type="button" 
                    onclick="confirmarCheckout()"
                    class="flex-1 px-6 py-3 bg-red-500 rounded-xl text-white font-semibold hover:bg-red-600 transition-all duration-200 shadow-lg"
                >
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Formulario oculto para checkout -->
<form id="form_checkout" method="POST" style="display: none;">
    <input type="hidden" name="ocupacion_id" id="checkout_ocupacion_id">
    <input type="hidden" name="fecha_salida" value="<?php echo date('Y-m-d'); ?>">
    <input type="hidden" name="finalizar_ocupacion" value="1">
</form>

<script>
function abrirModalCheckout(ocupacionId, nombreHuesped, habitacion) {
    document.getElementById('modal_nombre_huesped').textContent = nombreHuesped;
    document.getElementById('modal_habitacion').textContent = habitacion;
    document.getElementById('checkout_ocupacion_id').value = ocupacionId;
    document.getElementById('modal_checkout').classList.remove('hidden');
}

function cerrarModalCheckout() {
    document.getElementById('modal_checkout').classList.add('hidden');
}

function confirmarCheckout() {
    document.getElementById('form_checkout').submit();
}

// Cerrar modal al hacer clic fuera
document.getElementById('modal_checkout').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalCheckout();
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
