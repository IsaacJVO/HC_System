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
    /* Ocultar elementos no necesarios en impresión */
    .no-print {
        display: none !important;
    }
    
    /* Configuración de página */
    @page {
        size: letter portrait;
        margin: 1.5cm;
    }
    
    body {
        background: white !important;
        padding: 0 !important;
    }
    
    /* Hacer todo visible en impresión */
    .print-container {
        display: block !important;
        background: white !important;
        box-shadow: none !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    /* Saltos de página */
    .page-break {
        page-break-after: always;
    }
    
    /* Tablas */
    table {
        page-break-inside: avoid;
    }
    
    /* Firmas */
    .signature-section {
        margin-top: 3cm;
        page-break-inside: avoid;
    }
}
</style>

<!-- Botones de acción (no se imprimen) -->
<div class="no-print mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-noir mb-2">Resumen Financiero</h1>
            <p class="text-gray-500">Informe detallado de caja para liquidación</p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.print()" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 transition-all duration-200">
                🖨️ Imprimir / PDF
            </button>
            <a href="<?php echo BASE_PATH; ?>/index.php" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-mist transition-all duration-200">
                ← Volver
            </a>
        </div>
    </div>
</div>

<!-- Filtro de Fechas (no se imprime) -->
<div class="no-print bg-white rounded-2xl border border-gray-200 overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white">
        <h2 class="text-xl font-semibold text-noir">Período de Análisis</h2>
        <p class="text-sm text-gray-500 mt-1">Selecciona el rango de fechas para el informe</p>
    </div>
    
    <form method="GET" class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-noir">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" 
                       value="<?php echo $fecha_inicio; ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-noir">
            </div>
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-noir">Fecha Fin</label>
                <input type="date" name="fecha_fin" 
                       value="<?php echo $fecha_fin; ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-noir">
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
<div class="print-container bg-white rounded-2xl border border-gray-200 overflow-hidden p-8">
    
    <!-- Header del Informe -->
    <div class="text-center mb-8 border-b-2 border-gray-800 pb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">HOTEL CECIL</h1>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">INFORME DE LIQUIDACIÓN DE CAJA</h2>
        <div class="flex justify-between text-sm text-gray-600">
            <span><strong>Período:</strong> <?php echo formatDate($fecha_inicio); ?> al <?php echo formatDate($fecha_fin); ?></span>
            <span><strong>Fecha de emisión:</strong> <?php echo date('d/m/Y'); ?></span>
        </div>
    </div>

    <!-- SECCIÓN 1: INGRESOS EN EFECTIVO (RECEPCIONISTA) -->
    <div class="mb-8">
        <div class="bg-green-50 border-l-4 border-green-600 px-4 py-3 mb-4">
            <h3 class="text-lg font-bold text-green-800">1. INGRESOS EN EFECTIVO - CAJA DEL RECEPCIONISTA</h3>
            <p class="text-sm text-green-700">Dinero manejado por el recepcionista</p>
        </div>
        
        <table class="w-full mb-4 text-sm">
            <thead>
                <tr class="border-b-2 border-gray-800">
                    <th class="text-left py-2 px-3">Fecha</th>
                    <th class="text-left py-2 px-3">Concepto</th>
                    <th class="text-left py-2 px-3">Habitación</th>
                    <th class="text-right py-2 px-3">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ingresos_efectivo)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">No hay ingresos en efectivo en este período</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ingresos_efectivo as $ing): ?>
                        <tr class="border-b border-gray-200">
                            <td class="py-2 px-3"><?php echo formatDate($ing['fecha']); ?></td>
                            <td class="py-2 px-3"><?php echo htmlspecialchars($ing['concepto']); ?></td>
                            <td class="py-2 px-3"><?php echo $ing['nro_pieza'] ?? '-'; ?></td>
                            <td class="py-2 px-3 text-right font-semibold"><?php echo formatMoney($ing['monto']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-green-100 font-bold border-t-2 border-gray-800">
                        <td colspan="3" class="py-3 px-3 text-right">TOTAL EFECTIVO:</td>
                        <td class="py-3 px-3 text-right text-lg">Bs. <?php echo formatMoney($total_efectivo); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- SECCIÓN 2: INGRESOS POR QR (DUEÑO) -->
    <div class="mb-8">
        <div class="bg-blue-50 border-l-4 border-blue-600 px-4 py-3 mb-4">
            <h3 class="text-lg font-bold text-blue-800">2. INGRESOS POR QR - PAGOS DIRECTOS AL DUEÑO</h3>
            <p class="text-sm text-blue-700">Pagos transferidos directamente a la cuenta bancaria del propietario</p>
        </div>
        
        <table class="w-full mb-4 text-sm">
            <thead>
                <tr class="border-b-2 border-gray-800">
                    <th class="text-left py-2 px-3">Fecha</th>
                    <th class="text-left py-2 px-3">Concepto</th>
                    <th class="text-left py-2 px-3">Habitación</th>
                    <th class="text-right py-2 px-3">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($ingresos_qr)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">No hay ingresos por QR en este período</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($ingresos_qr as $ing): ?>
                        <tr class="border-b border-gray-200">
                            <td class="py-2 px-3"><?php echo formatDate($ing['fecha']); ?></td>
                            <td class="py-2 px-3"><?php echo htmlspecialchars($ing['concepto']); ?></td>
                            <td class="py-2 px-3"><?php echo $ing['nro_pieza'] ?? '-'; ?></td>
                            <td class="py-2 px-3 text-right font-semibold"><?php echo formatMoney($ing['monto']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-blue-100 font-bold border-t-2 border-gray-800">
                        <td colspan="3" class="py-3 px-3 text-right">TOTAL QR (DUEÑO):</td>
                        <td class="py-3 px-3 text-right text-lg">Bs. <?php echo formatMoney($total_qr); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- SECCIÓN 3: EGRESOS (SALIDAS DE CAJA) -->
    <div class="mb-8">
        <div class="bg-red-50 border-l-4 border-red-600 px-4 py-3 mb-4">
            <h3 class="text-lg font-bold text-red-800">3. EGRESOS - SALIDAS DE CAJA DEL RECEPCIONISTA</h3>
            <p class="text-sm text-red-700">Gastos realizados desde la caja del recepcionista</p>
        </div>
        
        <table class="w-full mb-4 text-sm">
            <thead>
                <tr class="border-b-2 border-gray-800">
                    <th class="text-left py-2 px-3">Fecha</th>
                    <th class="text-left py-2 px-3">Categoría</th>
                    <th class="text-left py-2 px-3">Concepto</th>
                    <th class="text-right py-2 px-3">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($egresos)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-gray-500">No hay egresos en este período</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($egresos as $egr): ?>
                        <tr class="border-b border-gray-200">
                            <td class="py-2 px-3"><?php echo formatDate($egr['fecha']); ?></td>
                            <td class="py-2 px-3"><?php echo htmlspecialchars($egr['categoria'] ?? 'Sin categoría'); ?></td>
                            <td class="py-2 px-3"><?php echo htmlspecialchars($egr['concepto']); ?></td>
                            <td class="py-2 px-3 text-right font-semibold"><?php echo formatMoney($egr['monto']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="bg-red-100 font-bold border-t-2 border-gray-800">
                        <td colspan="3" class="py-3 px-3 text-right">TOTAL EGRESOS:</td>
                        <td class="py-3 px-3 text-right text-lg">Bs. <?php echo formatMoney($total_egresos); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- SECCIÓN 4: RESUMEN Y LIQUIDACIÓN -->
    <div class="mb-8 bg-gray-50 border-2 border-gray-800 p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">RESUMEN DE LIQUIDACIÓN</h3>
        
        <div class="space-y-3 text-base">
            <div class="flex justify-between py-2 border-b border-gray-300">
                <span class="font-semibold">Total Ingresos en Efectivo:</span>
                <span class="font-bold text-green-700">Bs. <?php echo formatMoney($total_efectivo); ?></span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-300">
                <span class="font-semibold">Total Egresos:</span>
                <span class="font-bold text-red-700">Bs. <?php echo formatMoney($total_egresos); ?></span>
            </div>
            <div class="flex justify-between py-3 bg-yellow-100 px-4 rounded border-2 border-yellow-600">
                <span class="text-lg font-bold">EFECTIVO A ENTREGAR AL DON RODOLFO:</span>
                <span class="text-2xl font-bold text-yellow-800">Bs. <?php echo formatMoney($balance_recepcionista); ?></span>
            </div>
            <div class="flex justify-between py-2 border-t-2 border-gray-800 pt-3">
                <span class="font-semibold text-gray-600">Total Ingresos por QR (ya en cuenta Banco Sol):</span>
                <span class="font-semibold text-blue-700">Bs. <?php echo formatMoney($total_qr); ?></span>
            </div>
            <div class="flex justify-between py-3 bg-gray-800 text-white px-4 rounded">
                <span class="text-lg font-bold">INGRESO TOTAL DEL HOTEL:</span>
                <span class="text-2xl font-bold">Bs. <?php echo formatMoney($total_efectivo + $total_qr); ?></span>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 5: FIRMAS -->
    <div class="signature-section mt-16">
        <div class="grid grid-cols-2 gap-16 text-center">
            <div>
                <div class="border-t-2 border-gray-800 pt-2 mb-2">
                    <p class="font-bold">RECEPCIONISTA</p>
                </div>
                <p class="text-sm text-gray-600">Isaac Vargas</p>
            </div>
            <div>
                <div class="border-t-2 border-gray-800 pt-2 mb-2">
                    <p class="font-bold">PROPIETARIO</p>
                </div>
                <p class="text-sm text-gray-600">Don Rodolfo</p>
            </div>
        </div>
        
        <div class="mt-8 text-center text-xs text-gray-500">
            <p>Este documento certifica la liquidación de caja correspondiente al período indicado.</p>
            <p>Generado el <?php echo date('d/m/Y'); ?> a las <?php echo date('H:i'); ?> horas.</p>
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
