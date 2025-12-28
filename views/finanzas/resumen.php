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
        margin: 0.5cm 1cm;
    }
    body {
        background: white !important;
        padding: 0 !important;
        font-size: 8pt;
    }
    .print-container {
        display: block !important;
        background: white !important;
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
        transform: scale(0.95);
        transform-origin: top center;
    }
    table { 
        page-break-inside: avoid; 
        font-size: 7pt;
        margin-bottom: 0.3cm !important;
    }
    table th,
    table td {
        padding: 1px 3px !important;
        line-height: 1.2 !important;
    }
    .signature-section { 
        margin-top: 1cm !important; 
        page-break-inside: avoid; 
    }
    h1 { font-size: 14pt; margin-bottom: 0.2cm !important; }
    h2 { font-size: 11pt; margin-bottom: 0.2cm !important; }
    h3 { font-size: 9pt; margin-bottom: 0.1cm !important; }
    
    /* Reducir espacios entre secciones */
    .print-container > div {
        margin-bottom: 0.3cm !important;
    }
    
    /* Hacer títulos de secciones más compactos */
    .bg-gray-800 {
        padding: 0.1cm 0.2cm !important;
    }
}

.compact-table {
    font-size: 10px;
    line-height: 1.3;
}
.compact-table th,
.compact-table td {
    padding: 3px 6px;
}

@media (min-width: 768px) {
    .compact-table {
        font-size: 11px;
    }
    .compact-table th,
    .compact-table td {
        padding: 4px 8px;
    }
}

/* Hacer tablas scrolleables en móvil */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

@media (max-width: 767px) {
    .compact-table thead th {
        font-size: 9px;
        padding: 2px 4px;
    }
    .compact-table tbody td {
        font-size: 9px;
        padding: 2px 4px;
    }
}
</style>

<!-- Botones de acción (no se imprimen) -->
<div class="no-print mb-8">
    <?php if (isset($_GET['error']) && $_GET['error'] === 'acceso_denegado'): ?>
    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
        <div class="flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
            <div>
                <p class="text-sm font-semibold text-red-900 dark:text-red-300">Acceso Denegado</p>
                <p class="text-xs text-red-700 dark:text-red-400 mt-1">No tienes permisos para acceder a esa sección. Solo los administradores pueden registrar ingresos y egresos.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-4xl font-bold text-noir dark:text-white mb-2">Resumen Financiero</h1>
            <p class="text-sm md:text-base text-gray-500 dark:text-gray-400">Informe detallado de caja para liquidación</p>
        </div>
        <div class="flex gap-2 md:gap-3">
            <button onclick="window.print()" class="flex-1 md:flex-none px-3 md:px-6 py-2 md:py-3 bg-gray-900 dark:bg-gray-700 text-white rounded-lg md:rounded-xl text-sm md:text-base font-medium hover:bg-gray-800 dark:hover:bg-gray-600 transition-all duration-200">
                <span class="hidden md:inline"></span>Imprimir
            </button>
            <a href="<?php echo BASE_PATH; ?>/index.php" class="flex-1 md:flex-none px-3 md:px-6 py-2 md:py-3 border border-gray-300 dark:border-gray-700 rounded-lg md:rounded-xl text-gray-700 dark:text-gray-300 text-sm md:text-base font-medium hover:bg-mist dark:hover:bg-gray-800 transition-all duration-200 text-center">
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
                        class="w-full px-6 py-3.5 bg-gray-900 dark:bg-gray-700 text-white font-semibold rounded-xl hover:bg-gray-800 dark:hover:bg-gray-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    Actualizar Resumen
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
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 md:gap-3 mb-4 text-xs">
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded p-2">
            <p class="text-green-700 dark:text-green-400 font-semibold mb-1 text-[10px] md:text-xs">Ingresos Efectivo</p>
            <p class="text-sm md:text-lg font-bold text-green-900 dark:text-green-300">Bs. <?php echo formatMoney($total_efectivo); ?></p>
            <p class="text-[9px] md:text-[10px] text-green-600 dark:text-green-500"><?php echo count($ingresos_efectivo); ?> transac.</p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded p-2">
            <p class="text-blue-700 dark:text-blue-400 font-semibold mb-1 text-[10px] md:text-xs">Ingresos QR</p>
            <p class="text-sm md:text-lg font-bold text-blue-900 dark:text-blue-300">Bs. <?php echo formatMoney($total_qr); ?></p>
            <p class="text-[9px] md:text-[10px] text-blue-600 dark:text-blue-500"><?php echo count($ingresos_qr); ?> transac.</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded p-2">
            <p class="text-red-700 dark:text-red-400 font-semibold mb-1 text-[10px] md:text-xs">Egresos</p>
            <p class="text-sm md:text-lg font-bold text-red-900 dark:text-red-300">Bs. <?php echo formatMoney($total_egresos); ?></p>
            <p class="text-[9px] md:text-[10px] text-red-600 dark:text-red-500"><?php echo count($egresos); ?> transac.</p>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded p-2">
            <p class="text-yellow-700 dark:text-yellow-400 font-semibold mb-1 text-[10px] md:text-xs">Balance Caja</p>
            <p class="text-sm md:text-lg font-bold text-yellow-900 dark:text-yellow-300">Bs. <?php echo formatMoney($balance_recepcionista); ?></p>
            <p class="text-[9px] md:text-[10px] text-yellow-600 dark:text-yellow-500">A entregar</p>
        </div>
    </div>

    <!-- SECCIÓN 1: INGRESOS EN EFECTIVO -->
    <div class="mb-4">
        <div class="bg-green-600 text-white px-2 md:px-3 py-1.5 mb-2 flex flex-col md:flex-row md:items-center md:justify-between gap-1">
            <h3 class="text-xs md:text-sm font-bold">1. INGRESOS EN EFECTIVO</h3>
            <span class="text-[10px] md:text-xs opacity-90 hidden md:inline">Dinero físico manejado por el recepcionista</span>
        </div>
        
        <div class="table-responsive">
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
    </div>

    <!-- SECCIÓN 2: INGRESOS POR QR -->
    <div class="mb-4">
        <div class="bg-blue-600 text-white px-2 md:px-3 py-1.5 mb-2 flex flex-col md:flex-row md:items-center md:justify-between gap-1">
            <h3 class="text-xs md:text-sm font-bold">2. INGRESOS POR QR</h3>
            <span class="text-[10px] md:text-xs opacity-90 hidden md:inline">Transferencias bancarias directas</span>
        </div>
        
        <div class="table-responsive">
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
    </div>

    <!-- SECCIÓN 3: EGRESOS -->
    <div class="mb-4">
        <div class="bg-red-600 text-white px-2 md:px-3 py-1.5 mb-2 flex flex-col md:flex-row md:items-center md:justify-between gap-1">
            <h3 class="text-xs md:text-sm font-bold">3. EGRESOS</h3>
            <span class="text-[10px] md:text-xs opacity-90 hidden md:inline">Salidas de caja del recepcionista</span>
        </div>
        
        <div class="table-responsive">
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
    </div>

    <!-- RESUMEN CONSOLIDADO Y LIQUIDACIÓN -->
    <div class="mb-3 border border-gray-800 dark:border-gray-600">
        <div class="bg-gray-800 dark:bg-gray-700 text-white px-3 py-1">
            <h3 class="text-xs font-bold uppercase tracking-wide">Resumen y Liquidación</h3>
        </div>
        
        <div class="p-2 sm:p-3">
            <table class="w-full text-[10px] sm:text-xs">
                <tbody>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-1 text-gray-700 dark:text-gray-300">Ingresos Efectivo (Caja)</td>
                        <td class="py-1 text-right font-semibold text-green-600 dark:text-green-400">+ Bs. <?php echo formatMoney($total_efectivo); ?></td>
                    </tr>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-1 text-gray-700 dark:text-gray-300">Egresos (Gastos)</td>
                        <td class="py-1 text-right font-semibold text-red-600 dark:text-red-400">- Bs. <?php echo formatMoney($total_egresos); ?></td>
                    </tr>
                    <tr class="bg-yellow-50 dark:bg-yellow-900/10 border-y border-yellow-300 dark:border-yellow-800">
                        <td class="py-1 sm:py-1.5 font-semibold text-yellow-800 dark:text-yellow-300">Efectivo a Entregar</td>
                        <td class="py-1 sm:py-1.5 text-right font-bold text-base sm:text-lg text-yellow-800 dark:text-yellow-300">Bs. <?php echo formatMoney($balance_recepcionista); ?></td>
                    </tr>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-1 text-gray-600 dark:text-gray-400 text-[9px] sm:text-xs">Ingresos QR (Banco Sol)</td>
                        <td class="py-1 text-right font-semibold text-blue-600 dark:text-blue-400">Bs. <?php echo formatMoney($total_qr); ?></td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-t border-gray-300 dark:border-gray-700">
                        <td class="py-1 sm:py-1.5 font-semibold text-gray-900 dark:text-white">Ingreso Bruto Total</td>
                        <td class="py-1 sm:py-1.5 text-right font-bold text-sm sm:text-base text-gray-900 dark:text-white">Bs. <?php echo formatMoney($total_efectivo + $total_qr); ?></td>
                    </tr>
                    <tr class="bg-green-50 dark:bg-green-900/10">
                        <td class="py-1 sm:py-1.5 font-semibold text-green-800 dark:text-green-300">Utilidad Neta</td>
                        <td class="py-1 sm:py-1.5 text-right font-bold text-sm sm:text-base text-green-700 dark:text-green-400">Bs. <?php echo formatMoney($total_efectivo + $total_qr - $total_egresos); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FIRMAS Y VALIDACIÓN -->
    <div class="signature-section mt-6">
        <div class="grid grid-cols-2 gap-4 md:gap-8 text-center text-[10px] md:text-xs mb-3">
            <div>
                <div class="border-t border-gray-800 dark:border-gray-600 pt-1 mb-1 mt-8 md:mt-12">
                    <p class="font-bold text-gray-900 dark:text-white">RECEPCIONISTA</p>
                </div>
                <p class="text-gray-600 dark:text-gray-400">Isaac Vargas</p>
            </div>
            <div>
                <div class="border-t border-gray-800 dark:border-gray-600 pt-1 mb-1 mt-8 md:mt-12">
                    <p class="font-bold text-gray-900 dark:text-white">PROPIETARIO</p>
                </div>
                <p class="text-gray-600 dark:text-gray-400">Don Rodolfo</p>
            </div>
        </div>
        
        <div class="text-center text-[9px] md:text-[10px] text-gray-500 dark:text-gray-400 border-t border-gray-300 dark:border-gray-700 pt-2">
            <p>Este documento certifica la liquidación de caja del período indicado.</p>
            <p>Documento generado automáticamente el <?php echo date('d/m/Y'); ?> a las <?php echo date('H:i'); ?> hs. - Sistema Hotel Cecil v1.0</p>
        </div>
    </div>

</div>

<!-- Vista rápida en pantalla (no se imprime) -->
<div class="no-print mt-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Efectivo (Caja) -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">Efectivo (Caja)</span>
                <i class="fas fa-money-bill-wave text-gray-400 dark:text-gray-500 text-sm"></i>
            </div>
            <p class="text-3xl font-semibold text-gray-900 dark:text-white">Bs. <?php echo formatMoney($total_efectivo); ?></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Ingresos en efectivo</p>
        </div>

        <!-- QR (Don Rodolfo) -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">QR (Don Rodolfo)</span>
                <i class="fas fa-qrcode text-gray-400 dark:text-gray-500 text-sm"></i>
            </div>
            <p class="text-3xl font-semibold text-gray-900 dark:text-white">Bs. <?php echo formatMoney($total_qr); ?></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Pagos directos a Don Rodolfo</p>
        </div>

        <!-- A Entregar -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">A Entregar</span>
                <i class="fas fa-hand-holding-usd text-gray-400 dark:text-gray-500 text-sm"></i>
            </div>
            <p class="text-3xl font-semibold text-gray-900 dark:text-white">Bs. <?php echo formatMoney($balance_recepcionista); ?></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Efectivo - Egresos</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
