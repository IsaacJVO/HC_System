<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Finanzas.php';

$page_title = 'Resumen Financiero';

// Obtener fechas del filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

$finanzasModel = new Finanzas();
$resumen = $finanzasModel->obtenerResumen($fecha_inicio, $fecha_fin);
$ingresos = $finanzasModel->obtenerIngresos($fecha_inicio, $fecha_fin);
$egresos = $finanzasModel->obtenerEgresos($fecha_inicio, $fecha_fin);
$pagos_qr = $finanzasModel->obtenerPagosQR($fecha_inicio, $fecha_fin);

// Separar ingresos por método de pago
$ingresos_efectivo = array_filter($ingresos, function($ing) {
    return $ing['metodo_pago'] === 'efectivo';
});
$ingresos_qr = array_filter($ingresos, function($ing) {
    return $ing['metodo_pago'] === 'qr';
});

// Calcular totales separados
$total_efectivo = array_sum(array_column($ingresos_efectivo, 'monto'));
$total_qr = array_sum(array_column($ingresos_qr, 'monto'));
$total_egresos = array_sum(array_column($egresos, 'monto'));

// Balance del recepcionista (solo efectivo menos egresos)
$balance_recepcionista = $total_efectivo - $total_egresos;

include __DIR__ . '/../../includes/header.php';
?>

<style>
@media print {
    .no-print { display: none !important; }
    @page {
        size: letter portrait;
        margin: 1cm 1.5cm;
    }
    body {
        background: white !important;
        padding: 0 !important;
        font-size: 9pt;
    }
    .print-container {
        display: block !important;
        background: white !important;
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    table { page-break-inside: avoid; font-size: 8pt; }
    .signature-section { margin-top: 2cm; page-break-inside: avoid; }
    h1 { font-size: 16pt; }
    h2 { font-size: 13pt; }
    h3 { font-size: 11pt; }
}

.compact-table {
    font-size: 11px;
    line-height: 1.3;
}
.compact-table th,
.compact-table td {
    padding: 4px 8px;
}
</style>

<!-- Botones de acción (no se imprimen) -->
<div class="no-print mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-noir dark:text-white mb-2">Resumen Financiero</h1>
            <p class="text-gray-500 dark:text-gray-400">Informe detallado de caja para liquidación</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-all duration-200">
                🖨️ Imprimir / PDF
            </button>
            <a href="<?php echo BASE_PATH; ?>/index.php" class="px-6 py-3 border border-gray-300 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-300 font-medium hover:bg-mist dark:hover:bg-gray-800 transition-all duration-200">
                ← Volver
            </a>
        </div>
    </div>
</div>

<!-- Filtro de Fechas (no se imprime) -->
<div class="no-print bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 bg-gradient-to-r from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-900">
        <h2 class="text-xl font-semibold text-noir dark:text-white">Período de Análisis</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Selecciona el rango de fechas para el informe</p>
    </div>
    
    <form method="GET" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-noir dark:text-white">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" 
                       value="<?php echo $fecha_inicio; ?>"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-noir dark:text-white bg-white dark:bg-gray-800">
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-noir dark:text-white">Fecha Fin</label>
                <input type="date" name="fecha_fin" 
                       value="<?php echo $fecha_fin; ?>"
                       class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-noir dark:text-white bg-white dark:bg-gray-800">
            </div>
            <div class="flex items-end">
                <button type="submit" 
                        class="w-full px-6 py-3.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    🔄 Actualizar Resumen
                </button>
            </div>
        </div>
    </form>
</div>

<!-- INFORME IMPRIMIBLE -->
<div class="print-container bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden p-6">
    
    <!-- Header del Informe -->
    <div class="mb-4 pb-3 border-b-2 border-gray-800 dark:border-gray-600">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">HOTEL CECIL</h1>
                <h2 class="text-base font-semibold text-gray-700 dark:text-gray-300">Informe de Liquidación de Caja</h2>
            </div>
            <div class="text-right text-xs text-gray-600 dark:text-gray-400">
                <p><strong>Período:</strong> <?php echo formatDate($fecha_inicio); ?> - <?php echo formatDate($fecha_fin); ?></p>
                <p><strong>Emitido:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                <p><strong>Recepcionista:</strong> Isaac Vargas</p>
            </div>
        </div>
    </div>

    <!-- Resumen Ejecutivo -->
    <div class="grid grid-cols-4 gap-3 mb-4 text-xs">
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded p-2">
            <p class="text-green-700 dark:text-green-400 font-semibold mb-1">Ingresos Efectivo</p>
            <p class="text-lg font-bold text-green-900 dark:text-green-300">Bs. <?php echo formatMoney($total_efectivo); ?></p>
            <p class="text-[10px] text-green-600 dark:text-green-500"><?php echo count($ingresos_efectivo); ?> transacciones</p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded p-2">
            <p class="text-blue-700 dark:text-blue-400 font-semibold mb-1">Ingresos QR</p>
            <p class="text-lg font-bold text-blue-900 dark:text-blue-300">Bs. <?php echo formatMoney($total_qr); ?></p>
            <p class="text-[10px] text-blue-600 dark:text-blue-500"><?php echo count($ingresos_qr); ?> transacciones</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-2">
            <p class="text-red-700 dark:text-red-400 font-semibold mb-1">Egresos</p>
            <p class="text-lg font-bold text-red-900 dark:text-red-300">Bs. <?php echo formatMoney($total_egresos); ?></p>
            <p class="text-[10px] text-red-600 dark:text-red-500"><?php echo count($egresos); ?> transacciones</p>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded p-2">
            <p class="text-yellow-700 dark:text-yellow-400 font-semibold mb-1">Balance Caja</p>
            <p class="text-lg font-bold text-yellow-900 dark:text-yellow-300">Bs. <?php echo formatMoney($balance_recepcionista); ?></p>
            <p class="text-[10px] text-yellow-600 dark:text-yellow-500">A entregar</p>
        </div>
    </div>

    <!-- SECCIÓN 1: INGRESOS EN EFECTIVO -->
    <div class="mb-4">
        <div class="bg-green-600 text-white px-3 py-1.5 mb-2 flex items-center justify-between">
            <h3 class="text-sm font-bold">1. INGRESOS EN EFECTIVO - Caja del Recepcionista</h3>
            <span class="text-xs opacity-90">Dinero físico manejado por el recepcionista</span>
        </div>
        
        <table class="w-full compact-table border border-gray-300 dark:border-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="text-left border-b border-gray-300 dark:border-gray-700">Fecha</th>
                    <th class="text-left border-b border-gray-300 dark:border-gray-700">Concepto/Descripción</th>
                    <th class="text-center border-b border-gray-300 dark:border-gray-700">Hab.</th>
                    <th class="text-left border-b border-gray-300 dark:border-gray-700">Huésped</th>
                    <th class="text-right border-b border-gray-300 dark:border-gray-700">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 dark:text-gray-300">
                <?php if (empty($ingresos_efectivo)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-3 text-gray-500 dark:text-gray-400 italic">Sin movimientos en efectivo</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ingresos_efectivo as $ing): ?>
                        <tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap"><?php echo date('d/m/Y', strtotime($ing['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($ing['concepto']); ?></td>
                            <td class="text-center"><?php echo $ing['nro_pieza'] ?? '-'; ?></td>
                            <td class="text-xs"><?php echo $ing['nombres_apellidos'] ? htmlspecialchars($ing['nombres_apellidos']) : '-'; ?></td>
                            <td class="text-right font-semibold"><?php echo formatMoney($ing['monto']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-green-100 dark:bg-green-900/30 font-bold">
                        <td colspan="4" class="text-right py-2">SUBTOTAL EFECTIVO:</td>
                        <td class="text-right">Bs. <?php echo formatMoney($total_efectivo); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- SECCIÓN 2: INGRESOS POR QR -->
    <div class="mb-4">
        <div class="bg-blue-600 text-white px-3 py-1.5 mb-2 flex items-center justify-between">
            <h3 class="text-sm font-bold">2. INGRESOS POR QR - Transferencias Bancarias Directas</h3>
            <span class="text-xs opacity-90">Pagos directos a cuenta Banco Sol del propietario</span>
        </div>
        
        <table class="w-full compact-table border border-gray-300 dark:border-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="text-left border-b border-gray-300 dark:border-gray-700">Fecha</th>
                    <th class="text-left border-b border-gray-300 dark:border-gray-700">Concepto/Descripción</th>
                    <th class="text-center border-b border-gray-300 dark:border-gray-700">Hab.</th>
                    <th class="text-left border-b border-gray-300 dark:border-gray-700">Huésped</th>
                    <th class="text-right border-b border-gray-300 dark:border-gray-700">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 dark:text-gray-300">
                <?php if (empty($ingresos_qr)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-3 text-gray-500 dark:text-gray-400 italic">Sin pagos QR registrados</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ingresos_qr as $ing): ?>
                        <tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap"><?php echo date('d/m/Y', strtotime($ing['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($ing['concepto']); ?></td>
                            <td class="text-center"><?php echo $ing['nro_pieza'] ?? '-'; ?></td>
                            <td class="text-xs"><?php echo $ing['nombres_apellidos'] ? htmlspecialchars($ing['nombres_apellidos']) : '-'; ?></td>
                            <td class="text-right font-semibold"><?php echo formatMoney($ing['monto']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-blue-100 dark:bg-blue-900/30 font-bold">
                        <td colspan="4" class="text-right py-2">SUBTOTAL QR (YA EN BANCO):</td>
                        <td class="text-right">Bs. <?php echo formatMoney($total_qr); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- SECCIÓN 3: EGRESOS -->
    <div class="mb-4">
        <div class="bg-red-600 text-white px-3 py-1.5 mb-2 flex items-center justify-between">
            <h3 class="text-sm font-bold">3. EGRESOS - Salidas de Caja del Recepcionista</h3>
            <span class="text-xs opacity-90">Gastos realizados con dinero en efectivo de la caja</span>
        </div>
        
        <table class="w-full compact-table border border-gray-300 dark:border-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="text-left border-b border-gray-300 dark:border-gray-700">Fecha</th>
                    <th class="text-left border-b border-gray-300 dark:border-gray-700">Categoría</th>
                    <th class="text-left border-b border-gray-300 dark:border-gray-700">Descripción del Gasto</th>
                    <th class="text-right border-b border-gray-300 dark:border-gray-700">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody class="text-gray-900 dark:text-gray-300">
                <?php if (empty($egresos)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-3 text-gray-500 dark:text-gray-400 italic">Sin egresos registrados</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $egresos_por_categoria = [];
                    foreach ($egresos as $egr) {
                        $cat = $egr['categoria'] ?? 'Sin categoría';
                        if (!isset($egresos_por_categoria[$cat])) {
                            $egresos_por_categoria[$cat] = 0;
                        }
                        $egresos_por_categoria[$cat] += $egr['monto'];
                    ?>
                        <tr class="border-b border-gray-200 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap"><?php echo date('d/m/Y', strtotime($egr['fecha'])); ?></td>
                            <td class="text-xs"><?php echo htmlspecialchars($cat); ?></td>
                            <td><?php echo htmlspecialchars($egr['concepto']); ?></td>
                            <td class="text-right font-semibold"><?php echo formatMoney($egr['monto']); ?></td>
                        </tr>
                    <?php } ?>
                    <tr class="bg-red-100 dark:bg-red-900/30 font-bold">
                        <td colspan="3" class="text-right py-2">TOTAL EGRESOS:</td>
                        <td class="text-right">Bs. <?php echo formatMoney($total_egresos); ?></td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-800 text-xs">
                        <td colspan="4" class="py-2 px-3">
                            <strong>Desglose por categoría:</strong>
                            <?php foreach ($egresos_por_categoria as $cat => $monto): ?>
                                <span class="inline-block mr-3"><?php echo htmlspecialchars($cat); ?>: Bs. <?php echo formatMoney($monto); ?></span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- RESUMEN CONSOLIDADO Y LIQUIDACIÓN -->
    <div class="mb-4 border-2 border-gray-800 dark:border-gray-600">
        <div class="bg-gray-800 dark:bg-gray-700 text-white px-3 py-1.5">
            <h3 class="text-sm font-bold">RESUMEN CONSOLIDADO Y LIQUIDACIÓN FINAL</h3>
        </div>
        
        <div class="p-3">
            <table class="w-full text-xs mb-3">
                <tbody>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-1.5 font-semibold">A) Total Ingresos en Efectivo (Caja)</td>
                        <td class="py-1.5 text-right font-bold text-green-700 dark:text-green-400">+ Bs. <?php echo formatMoney($total_efectivo); ?></td>
                    </tr>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-1.5 font-semibold">B) Total Egresos (Gastos de Caja)</td>
                        <td class="py-1.5 text-right font-bold text-red-700 dark:text-red-400">- Bs. <?php echo formatMoney($total_egresos); ?></td>
                    </tr>
                    <tr class="bg-yellow-100 dark:bg-yellow-900/30 border-y-2 border-yellow-600">
                        <td class="py-2 font-bold text-base">EFECTIVO A ENTREGAR AL PROPIETARIO (A-B)</td>
                        <td class="py-2 text-right font-bold text-xl text-yellow-900 dark:text-yellow-300">Bs. <?php echo formatMoney($balance_recepcionista); ?></td>
                    </tr>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-1.5 font-semibold text-gray-600 dark:text-gray-400">C) Ingresos por QR (Ya depositados en Banco Sol)</td>
                        <td class="py-1.5 text-right font-bold text-blue-700 dark:text-blue-400">Bs. <?php echo formatMoney($total_qr); ?></td>
                    </tr>
                    <tr class="bg-gray-800 dark:bg-gray-700 text-white border-t-2 border-gray-800">
                        <td class="py-2 font-bold text-base">INGRESO BRUTO TOTAL DEL HOTEL (A+C)</td>
                        <td class="py-2 text-right font-bold text-xl">Bs. <?php echo formatMoney($total_efectivo + $total_qr); ?></td>
                    </tr>
                    <tr class="bg-gray-100 dark:bg-gray-800">
                        <td class="py-2 font-bold text-base">UTILIDAD NETA DEL PERÍODO (A+C-B)</td>
                        <td class="py-2 text-right font-bold text-xl text-green-700 dark:text-green-400">Bs. <?php echo formatMoney($total_efectivo + $total_qr - $total_egresos); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <div class="grid grid-cols-3 gap-2 text-[10px] bg-gray-50 dark:bg-gray-800 p-2 rounded">
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-300">Transacciones:</p>
                    <p class="text-gray-600 dark:text-gray-400"><?php echo count($ingresos) + count($egresos); ?> movimientos</p>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-300">Ticket promedio:</p>
                    <p class="text-gray-600 dark:text-gray-400">Bs. <?php echo count($ingresos) > 0 ? formatMoney(($total_efectivo + $total_qr) / count($ingresos)) : '0.00'; ?></p>
                </div>
                <div>
                    <p class="font-semibold text-gray-700 dark:text-gray-300">Días del período:</p>
                    <p class="text-gray-600 dark:text-gray-400"><?php 
                        $dias = (strtotime($fecha_fin) - strtotime($fecha_inicio)) / 86400 + 1;
                        echo round($dias) . ' días';
                    ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- FIRMAS Y VALIDACIÓN -->
    <div class="signature-section mt-6">
        <div class="grid grid-cols-2 gap-8 text-center text-xs mb-3">
            <div>
                <div class="border-t border-gray-800 dark:border-gray-600 pt-1 mb-1 mt-12">
                    <p class="font-bold text-gray-900 dark:text-white">RECEPCIONISTA</p>
                </div>
                <p class="text-gray-600 dark:text-gray-400">Isaac Vargas</p>
                <p class="text-[10px] text-gray-500 dark:text-gray-500">CI: __________________</p>
            </div>
            <div>
                <div class="border-t border-gray-800 dark:border-gray-600 pt-1 mb-1 mt-12">
                    <p class="font-bold text-gray-900 dark:text-white">PROPIETARIO</p>
                </div>
                <p class="text-gray-600 dark:text-gray-400">Don Rodolfo</p>
                <p class="text-[10px] text-gray-500 dark:text-gray-500">CI: __________________</p>
            </div>
        </div>
        
        <div class="text-center text-[10px] text-gray-500 dark:text-gray-400 border-t border-gray-300 dark:border-gray-700 pt-2">
            <p>Este documento certifica la liquidación de caja del período indicado.</p>
            <p>Documento generado automáticamente el <?php echo date('d/m/Y'); ?> a las <?php echo date('H:i'); ?> hs. - Sistema Hotel Cecil v1.0</p>
        </div>
    </div>

</div>

<!-- Vista rápida en pantalla (no se imprime) -->
<div class="no-print mt-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-green-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-noir">Efectivo (Tu Caja)</h3>
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <span class="text-xl">💵</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <p class="text-3xl font-bold text-green-600">Bs. <?php echo formatMoney($total_efectivo); ?></p>
                <p class="text-sm text-gray-500 mt-2">Ingresos en efectivo</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-noir">QR (Dueño)</h3>
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <span class="text-xl">📱</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <p class="text-3xl font-bold text-blue-600">Bs. <?php echo formatMoney($total_qr); ?></p>
                <p class="text-sm text-gray-500 mt-2">Pagos directos al dueño</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-noir">A Entregar</h3>
                    <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <span class="text-xl">🤝</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <p class="text-3xl font-bold text-yellow-600">Bs. <?php echo formatMoney($balance_recepcionista); ?></p>
                <p class="text-sm text-gray-500 mt-2">Efectivo - Egresos</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
