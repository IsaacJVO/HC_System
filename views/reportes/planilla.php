<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/RegistroOcupacion.php';

$page_title = 'Planilla de Huéspedes';

// Obtener fechas del filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

$registroModel = new RegistroOcupacion();

// Si se filtra por fechas
if (isset($_GET['filtrar'])) {
    $sql = "SELECT ro.id, ro.huesped_id, ro.habitacion_id, ro.nro_pieza, ro.prox_destino,
            ro.via_ingreso, ro.fecha_ingreso, ro.nro_dias, ro.fecha_salida_estimada, ro.fecha_salida_real,
            ro.estado,
            h.nombres_apellidos, h.ci_pasaporte, h.genero, h.edad, 
            h.estado_civil, h.nacionalidad, h.profesion, h.objeto, h.procedencia
            FROM registro_ocupacion ro
            INNER JOIN huespedes h ON ro.huesped_id = h.id
            WHERE ro.fecha_ingreso BETWEEN :fi AND :ff
            ORDER BY ro.id ASC";
    
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute([':fi' => $fecha_inicio, ':ff' => $fecha_fin]);
    $registros = $stmt->fetchAll();
} else {
    // Obtener todos los registros con todos los campos necesarios
    $sql = "SELECT ro.id, ro.huesped_id, ro.habitacion_id, ro.nro_pieza, ro.prox_destino,
            ro.via_ingreso, ro.fecha_ingreso, ro.nro_dias, ro.fecha_salida_estimada, ro.fecha_salida_real,
            ro.estado,
            h.nombres_apellidos, h.ci_pasaporte, h.genero, h.edad, 
            h.estado_civil, h.nacionalidad, h.profesion, h.objeto, h.procedencia
            FROM registro_ocupacion ro
            INNER JOIN huespedes h ON ro.huesped_id = h.id
            ORDER BY ro.id ASC
            LIMIT 100";
    
    $conn = getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $registros = $stmt->fetchAll();
}

include __DIR__ . '/../../includes/header.php';
?>

<style>
    /* Estilos para impresión */
    @media print {
        body * {
            visibility: hidden;
        }
        .planilla-print, .planilla-print * {
            visibility: visible;
        }
        .planilla-print {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .no-print {
            display: none !important;
        }
        
        /* Configuración de página para oficio (8.5" x 13") */
        /* Eliminar encabezados y pies de página del navegador */
        @page {
            size: legal landscape;
            margin: 0.5cm;
            margin-top: 0.5cm;
            margin-bottom: 0.5cm;
        }
        
        /* Ocultar encabezado y pie de página del navegador */
        html {
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
        }
        
        .planilla-print {
            padding: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px !important;
        }
        
        th, td {
            border: 1px solid #000 !important;
            padding: 3px !important;
        }
        
        thead {
            background-color: #f0f0f0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        h2, h4 {
            margin: 5px 0 !important;
        }
    }
    
    /* Estilos en pantalla */
    @media screen {
        .planilla-print {
            max-width: 100%;
            overflow-x: auto;
        }
        
        .dark .planilla-print * {
            color: white !important;
        }
        
        .dark .planilla-print td,
        .dark .planilla-print th {
            border-color: #666 !important;
        }
        
        .print-table {
            font-size: 11px;
        }
        
        .print-table th,
        .print-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        
        .print-table thead {
            background-color: #2c3e50;
            color: white;
        }
    }
</style>

<!-- Controles de filtro -->
<div class="no-print mb-8">
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-noir">Planilla de Huéspedes</h1>
                    <p class="text-sm text-gray-500 mt-1">Registro oficial de ocupaciones</p>
                </div>
                <div class="flex gap-3">
                    <a href="<?php echo BASE_PATH; ?>/index.php" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-all">
                        ← Volver
                    </a>
                    <button onclick="window.print()" class="px-6 py-2 bg-noir text-white font-semibold rounded-lg hover:bg-gray-800 transition-all shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Imprimir / PDF
                    </button>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <button type="submit" name="filtrar" class="w-full px-4 py-2.5 bg-noir text-white font-medium rounded-lg hover:bg-gray-800 transition-all">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Planilla para imprimir -->
<div class="planilla-print">
    <!-- Encabezado de la planilla -->
    <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px;">
        <h2 style="margin: 5px 0; font-size: 20px; font-weight: bold;">HOTEL CECIL</h2>
        <h4 style="margin: 5px 0; font-size: 14px;">PLANILLA DE REGISTRO DE HUÉSPEDES</h4>
    </div>
    
    <!-- Tabla de registros -->
    <div style="width: 100%; overflow-x: auto;">
        <table class="print-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: center; font-weight: bold;">Nro</th>
                    <th style="text-align: left; font-weight: bold;">Nombres y Apellidos</th>
                    <th style="text-align: center; font-weight: bold;">G</th>
                    <th style="text-align: center; font-weight: bold;">Edad</th>
                    <th style="text-align: center; font-weight: bold;">E.C.</th>
                    <th style="text-align: left; font-weight: bold;">Nacionalidad</th>
                    <th style="text-align: left; font-weight: bold;">C.I./Pasaporte</th>
                    <th style="text-align: left; font-weight: bold;">Profesión</th>
                    <th style="text-align: left; font-weight: bold;">Objeto</th>
                    <th style="text-align: center; font-weight: bold;">Pieza</th>
                    <th style="text-align: left; font-weight: bold;">Procedencia</th>
                    <th style="text-align: left; font-weight: bold;">Próx. Destino</th>
                    <th style="text-align: center; font-weight: bold;">Vía</th>
                    <th style="text-align: center; font-weight: bold;">F. Ingreso</th>
                    <th style="text-align: center; font-weight: bold;">Días</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="15" style="text-align: center; padding: 20px; color: #666;">
                            No hay registros para mostrar en el período seleccionado
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registros as $idx => $reg): ?>
                        <tr>
                            <td style="text-align: center;"><?php echo $idx + 1; ?></td>
                            <td style="text-align: left;"><?php echo htmlspecialchars($reg['nombres_apellidos']); ?></td>
                            <td style="text-align: center;"><?php echo $reg['genero']; ?></td>
                            <td style="text-align: center;"><?php echo $reg['edad']; ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($reg['estado_civil'] ?? '-'); ?></td>
                            <td style="text-align: left;"><?php echo htmlspecialchars($reg['nacionalidad']); ?></td>
                            <td style="text-align: left;"><?php echo htmlspecialchars($reg['ci_pasaporte']); ?></td>
                            <td style="text-align: left;"><?php echo htmlspecialchars($reg['profesion'] ?? '-'); ?></td>
                            <td style="text-align: left;"><?php echo htmlspecialchars($reg['objeto'] ?? '-'); ?></td>
                            <td style="text-align: center; font-weight: bold;"><?php echo $reg['nro_pieza']; ?></td>
                            <td style="text-align: left;"><?php echo htmlspecialchars($reg['procedencia'] ?? '-'); ?></td>
                            <td style="text-align: left;"><?php echo htmlspecialchars($reg['prox_destino'] ?? '-'); ?></td>
                            <td style="text-align: center;"><?php echo $reg['via_ingreso'] ? strtoupper($reg['via_ingreso']) : '-'; ?></td>
                            <td style="text-align: center;"><?php echo formatDate($reg['fecha_ingreso']); ?></td>
                            <td style="text-align: center;"><?php echo $reg['nro_dias']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Footer con totales -->
    <div style="margin-top: 20px; padding-top: 10px; border-top: 2px solid #000;">
        <p style="margin: 5px 0; font-weight: bold;">Total de registros: <?php echo count($registros); ?></p>
        <p style="margin: 5px 0; font-size: 10px; color: #666;">
            Documento generado electrónicamente por el Sistema de Gestión Hotel Cecil
        </p>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
