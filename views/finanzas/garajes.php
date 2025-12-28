<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Garaje.php';

$page_title = 'Registros de Garaje';

// Solo administradores
if (!esAdmin()) {
    header('Location: ' . BASE_PATH . '/index.php?error=acceso_denegado');
    exit;
}

// Obtener fechas del filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

$garajeModel = new Garaje();
$registros = $garajeModel->obtenerPorFechas($fecha_inicio, $fecha_fin);
$resumen = $garajeModel->obtenerResumen($fecha_inicio, $fecha_fin);

include __DIR__ . '/../../includes/header.php';
?>

<style>
@media print {
    .no-print { display: none !important; }
    @page {
        size: letter portrait;
        margin: 0.5cm;
    }
    body {
        background: white !important;
        font-size: 9pt;
    }
    .print-container {
        background: white !important;
        box-shadow: none !important;
        border: none !important;
    }
    h1 { font-size: 16pt; margin-bottom: 0.2cm; }
    h2 { font-size: 12pt; margin-bottom: 0.2cm; }
    table { page-break-inside: avoid; font-size: 8pt; }
    .resumen-box {
        border: 1px solid #000;
        padding: 0.2cm;
        margin-bottom: 0.3cm;
    }
}
</style>

<div class="container mx-auto px-4 py-6 sm:py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
        <div class="flex-1">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-1">Registros de Garaje</h1>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">Control de servicios de garaje</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button onclick="window.print()" class="no-print px-4 py-2.5 bg-noir text-white rounded-lg hover:bg-opacity-80 transition flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir Informe
            </button>
            <a href="<?php echo BASE_PATH; ?>/index.php" class="no-print px-3 py-2 sm:px-4 text-sm sm:text-base border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-all text-center">
                ← Volver
            </a>
        </div>
    </div>

    <!-- Filtro de Fechas -->
    <div class="no-print bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 mb-6">
        <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Filtrar por Período</h2>
        </div>
        
        <form method="GET" class="p-4 sm:p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" 
                           value="<?php echo $fecha_inicio; ?>"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-gray-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Fecha Fin</label>
                    <input type="date" name="fecha_fin" 
                           value="<?php echo $fecha_fin; ?>"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-gray-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 text-sm bg-gray-900 dark:bg-gray-700 text-white rounded-lg hover:bg-gray-800 dark:hover:bg-gray-600 transition-all">
                        <i class="fas fa-filter mr-2"></i>Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="resumen-box bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-blue-600 dark:text-blue-400 font-medium">Total de Vehículos</p>
                    <p class="text-2xl sm:text-3xl font-bold text-blue-900 dark:text-blue-300 mt-1"><?php echo $resumen['cantidad']; ?></p>
                </div>
                <i class="no-print fas fa-car text-3xl sm:text-4xl text-blue-300 dark:text-blue-700"></i>
            </div>
        </div>
        
        <div class="resumen-box bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-green-600 dark:text-green-400 font-medium">Costo Total a Pagar</p>
                    <p class="text-2xl sm:text-3xl font-bold text-green-900 dark:text-green-300 mt-1">Bs. <?php echo number_format($resumen['total'], 2); ?></p>
                </div>
                <i class="no-print fas fa-money-bill-wave text-3xl sm:text-4xl text-green-300 dark:text-green-700"></i>
            </div>
        </div>
    </div>

    <!-- Tabla de Registros -->
    <div class="no-print bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Registros del Período</h2>
        </div>
        
        <?php if (empty($registros)): ?>
        <div class="p-8 sm:p-12 text-center">
            <i class="fas fa-car text-4xl sm:text-5xl text-gray-300 dark:text-gray-600 mb-3"></i>
            <p class="text-gray-600 dark:text-gray-400">No hay registros de garaje en este período</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-gray-700 dark:text-gray-300 font-semibold">Fecha</th>
                        <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-gray-700 dark:text-gray-300 font-semibold">Huésped</th>
                        <th class="px-3 py-2 sm:px-4 sm:py-3 text-left text-gray-700 dark:text-gray-300 font-semibold hidden sm:table-cell">Observaciones</th>
                        <th class="px-3 py-2 sm:px-4 sm:py-3 text-right text-gray-700 dark:text-gray-300 font-semibold">Costo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($registros as $reg): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-3 py-2 sm:px-4 sm:py-3 text-gray-900 dark:text-white whitespace-nowrap">
                            <?php echo date('d/m/Y', strtotime($reg['fecha'])); ?>
                        </td>
                        <td class="px-3 py-2 sm:px-4 sm:py-3 text-gray-900 dark:text-white">
                            <?php echo htmlspecialchars($reg['huesped_nombre']); ?>
                        </td>
                        <td class="px-3 py-2 sm:px-4 sm:py-3 text-gray-600 dark:text-gray-400 hidden sm:table-cell">
                            <?php echo htmlspecialchars($reg['observaciones'] ?? '-'); ?>
                        </td>
                        <td class="px-3 py-2 sm:px-4 sm:py-3 text-right font-semibold text-gray-900 dark:text-white">
                            Bs. <?php echo number_format($reg['costo'], 2); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-gray-50 dark:bg-gray-900 border-t-2 border-gray-300 dark:border-gray-600">
                    <tr>
                        <td colspan="3" class="px-3 py-2 sm:px-4 sm:py-3 text-right font-bold text-gray-900 dark:text-white">
                            Total a Pagar a la Señora del Garaje:
                        </td>
                        <td class="px-3 py-2 sm:px-4 sm:py-3 text-right font-bold text-lg text-green-700 dark:text-green-400">
                            Bs. <?php echo number_format($resumen['total'], 2); ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Vista detallada para impresión -->
    <div class="hidden print:block print-container">
        <div style="text-align: center; margin-bottom: 0.4cm;">
            <h1 style="font-size: 16pt; font-weight: bold; margin-bottom: 0.1cm;">INFORME DE SERVICIOS DE GARAJE</h1>
            <p style="font-size: 10pt; margin-bottom: 0.1cm;">Hotel Cecil</p>
            <p style="font-size: 9pt; color: #666;">Período: <?php echo date('d/m/Y', strtotime($fecha_inicio)); ?> - <?php echo date('d/m/Y', strtotime($fecha_fin)); ?></p>
        </div>

        <!-- Resumen -->
        <div style="margin-bottom: 0.4cm; display: grid; grid-template-columns: 1fr 1fr; gap: 0.3cm;">
            <div class="resumen-box">
                <p style="font-weight: bold; margin-bottom: 0.1cm; font-size: 9pt;">Total de Vehículos</p>
                <p style="font-size: 14pt; font-weight: bold;"><?php echo $resumen['cantidad']; ?></p>
            </div>
            <div class="resumen-box">
                <p style="font-weight: bold; margin-bottom: 0.1cm; font-size: 9pt;">Costo Total a Pagar</p>
                <p style="font-size: 14pt; font-weight: bold; color: #16a34a;">Bs. <?php echo number_format($resumen['total'], 2); ?></p>
            </div>
        </div>

        <!-- Detalle de registros -->
        <?php if (!empty($registros)): ?>
        <h2 style="font-size: 12pt; font-weight: bold; margin-bottom: 0.2cm; border-bottom: 1px solid #000; padding-bottom: 0.1cm;">Detalle de Servicios</h2>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 0.4cm;">
            <thead>
                <tr style="background-color: #f3f4f6; border-bottom: 1px solid #000;">
                    <th style="padding: 0.15cm; text-align: left; font-weight: bold; font-size: 9pt;">Fecha</th>
                    <th style="padding: 0.15cm; text-align: left; font-weight: bold; font-size: 9pt;">Huésped</th>
                    <th style="padding: 0.15cm; text-align: center; font-weight: bold; font-size: 9pt;">Hab.</th>
                    <th style="padding: 0.15cm; text-align: right; font-weight: bold; font-size: 9pt;">Costo</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($registros as $registro): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 0.1cm; text-align: left; font-size: 8pt;"><?php echo date('d/m/Y', strtotime($registro['fecha'])); ?></td>
                    <td style="padding: 0.1cm; text-align: left; font-size: 8pt;"><?php echo htmlspecialchars($registro['huesped_nombre']); ?></td>
                    <td style="padding: 0.1cm; text-align: center; font-size: 8pt;"><?php echo $registro['nro_pieza'] ?? 'N/A'; ?></td>
                    <td style="padding: 0.1cm; text-align: right; font-weight: bold; font-size: 8pt;">Bs. <?php echo number_format($registro['costo'], 2); ?></td>
                </tr>
                <?php if (!empty($registro['observaciones'])): ?>
                <tr style="border-bottom: 1px solid #ddd;">
                    <td colspan="4" style="padding: 0.05cm 0.1cm; font-size: 7pt; color: #666; font-style: italic;">
                        Obs: <?php echo htmlspecialchars($registro['observaciones']); ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="border-top: 1px solid #000; background-color: #f9fafb;">
                    <td colspan="3" style="padding: 0.15cm; text-align: right; font-weight: bold; font-size: 9pt;">
                        Total a Pagar:
                    </td>
                    <td style="padding: 0.15cm; text-align: right; font-weight: bold; font-size: 10pt; color: #16a34a;">
                        Bs. <?php echo number_format($resumen['total'], 2); ?>
                    </td>
                </tr>
            </tfoot>
        </table>
        <?php else: ?>
        <p style="text-align: center; padding: 1cm; color: #666; font-size: 9pt;">No hay registros de garaje en este período</p>
        <?php endif; ?>

        <!-- Footer -->
        <div style="margin-top: 0.3cm; padding-top: 0.2cm; border-top: 1px solid #ddd; text-align: center; font-size: 8pt; color: #666;">
            <p>Informe generado el <?php echo date('d/m/Y H:i'); ?> - Hotel Cecil</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
