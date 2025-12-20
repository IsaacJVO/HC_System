<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Finanzas.php';

$page_title = 'Registro de Egresos';
$mensaje = '';
$tipo_mensaje = '';

// Procesar registro de egreso
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_egreso'])) {
    try {
        $finanzasModel = new Finanzas();
        
        $datos = [
            'concepto' => clean_input($_POST['concepto']),
            'monto' => floatval($_POST['monto']),
            'categoria' => !empty($_POST['categoria']) ? clean_input($_POST['categoria']) : null,
            'fecha' => $_POST['fecha'],
            'observaciones' => !empty($_POST['observaciones']) ? clean_input($_POST['observaciones']) : null
        ];
        
        if ($finanzasModel->registrarEgreso($datos)) {
            $mensaje = 'Egreso registrado correctamente.';
            $tipo_mensaje = 'success';
            // Limpiar POST para que el formulario se resetee
            $_POST = [];
        } else {
            throw new Exception('Error al registrar el egreso en la base de datos');
        }
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
        error_log("Error en registro de egreso: " . $e->getMessage());
    }
}

// Obtener egresos
$finanzasModel = new Finanzas();
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$egresos = $finanzasModel->obtenerEgresos($fecha_inicio, $fecha_fin);

include __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-noir mb-2">Egresos - Salidas de Caja</h1>
            <p class="text-gray-500">Registra gastos externos y de cafetería</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>/index.php" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-mist transition-all duration-200">
            ← Volver
        </a>
    </div>
</div>

<!-- Alert Messages -->
<?php if ($mensaje): ?>
    <div class="mb-8 animate-fade-in">
        <div class="bg-<?php echo $tipo_mensaje === 'success' ? 'green' : 'red'; ?>-50 border border-<?php echo $tipo_mensaje === 'success' ? 'green' : 'red'; ?>-200 rounded-xl p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <?php if ($tipo_mensaje === 'success'): ?>
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    <?php else: ?>
                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-<?php echo $tipo_mensaje === 'success' ? 'green' : 'red'; ?>-800">
                        <?php echo $mensaje; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Formulario de Registro -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden sticky top-4">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-red-50 to-white">
                <h2 class="text-xl font-semibold text-noir">Nueva Salida</h2>
                <p class="text-sm text-gray-500 mt-1">Registro rápido de egresos</p>
            </div>
            
            <form method="POST" action="" class="p-6 space-y-5">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Tipo de Egreso <span class="text-red-500">*</span>
                    </label>
                    <select name="categoria" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 text-noir appearance-none bg-white">
                        <option value="">Selecciona el tipo</option>
                        <option value="Externo" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 'Externo') ? 'selected' : ''; ?>>💼 Gastos Externos (factores externos)</option>
                        <option value="Cafetería" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 'Cafetería') ? 'selected' : ''; ?>>☕ Gastos de Cafetería</option>
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Descripción <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="concepto" 
                           value="<?php echo isset($_POST['concepto']) ? htmlspecialchars($_POST['concepto']) : ''; ?>"
                           required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                           placeholder="Descripción breve...">
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Monto (Bs.) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" name="monto" 
                           value="<?php echo isset($_POST['monto']) ? htmlspecialchars($_POST['monto']) : ''; ?>"
                           min="0.01" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                           placeholder="0.00">
                </div>
                
                <input type="hidden" name="fecha" value="<?php echo date('Y-m-d'); ?>">
                <input type="hidden" name="observaciones" value="">
                
                <button type="submit" name="registrar_egreso" class="w-full px-6 py-3.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    ✓ Registrar Egreso
                </button>
            </form>
        </div>
    </div>
    
    <!-- Lista de Egresos -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-xl font-semibold text-noir">Lista de Egresos</h2>
                <p class="text-sm text-gray-500 mt-1">Historial de gastos registrados</p>
            </div>
            
            <div class="p-6">
                <!-- Filtros -->
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-mist rounded-xl">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" 
                               value="<?php echo $fecha_inicio; ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-noir focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Fin</label>
                        <input type="date" name="fecha_fin" 
                               value="<?php echo $fecha_fin; ?>"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-noir focus:border-transparent">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full px-4 py-2.5 bg-noir text-white font-medium rounded-lg hover:bg-gray-800 transition-all duration-200">
                            🔍 Filtrar
                        </button>
                    </div>
                </form>
                
                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b-2 border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Concepto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Categoría</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php 
                            $total = 0;
                            foreach ($egresos as $egr): 
                                $total += $egr['monto'];
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-900"><?php echo formatDate($egr['fecha']); ?></td>
                                    <td class="px-4 py-3 text-sm font-medium text-noir"><?php echo htmlspecialchars($egr['concepto']); ?></td>
                                    <td class="px-4 py-3">
                                        <?php if ($egr['categoria']): ?>
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                                                <?php 
                                                if ($egr['categoria'] == 'Suministros') echo 'bg-blue-100 text-blue-800';
                                                elseif ($egr['categoria'] == 'Servicios') echo 'bg-yellow-100 text-yellow-800';
                                                elseif ($egr['categoria'] == 'Mantenimiento') echo 'bg-orange-100 text-orange-800';
                                                elseif ($egr['categoria'] == 'Personal') echo 'bg-purple-100 text-purple-800';
                                                else echo 'bg-gray-100 text-gray-800';
                                                ?>">
                                                <?php echo htmlspecialchars($egr['categoria']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-sm font-bold text-red-600">Bs. <?php echo formatMoney($egr['monto']); ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($egresos)): ?>
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        No hay egresos registrados en este período
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-noir text-white">
                            <tr>
                                <th colspan="3" class="px-4 py-4 text-right text-sm font-semibold uppercase tracking-wider">TOTAL:</th>
                                <th class="px-4 py-4 text-right text-lg font-bold">Bs. <?php echo formatMoney($total); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
