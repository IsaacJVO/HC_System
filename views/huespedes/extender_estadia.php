<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/RegistroOcupacion.php';
require_once __DIR__ . '/../../models/Habitacion.php';
require_once __DIR__ . '/../../models/Finanzas.php';

$page_title = 'Extender Estadía';
$mensaje = '';
$tipo_mensaje = '';

// Verificar que se recibió un ID de ocupación
if (!isset($_GET['id']) && !isset($_POST['ocupacion_id'])) {
    header('Location: activos.php');
    exit;
}

$ocupacion_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_POST['ocupacion_id'];

$registroModel = new RegistroOcupacion();
$finanzasModel = new Finanzas();

// Obtener información de la ocupación
$sql = "SELECT ro.*, h.nombres_apellidos, h.ci_pasaporte, hab.numero, hab.precio_dia 
        FROM registro_ocupacion ro
        INNER JOIN huespedes h ON ro.huesped_id = h.id
        INNER JOIN habitaciones hab ON ro.habitacion_id = hab.id
        WHERE ro.id = :id";

$stmt = $registroModel->conn->prepare($sql);
$stmt->execute([':id' => $ocupacion_id]);
$ocupacion = $stmt->fetch();

if (!$ocupacion) {
    header('Location: activos.php');
    exit;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $dias_adicionales = (int)$_POST['dias_adicionales'];
        $metodo_pago = $_POST['metodo_pago'];
        
        if ($dias_adicionales < 1) {
            throw new Exception('Debe especificar al menos 1 día adicional');
        }
        
        // Extender la estadía
        $resultado = $registroModel->extenderEstadia($ocupacion_id, $dias_adicionales);
        
        if ($resultado['success']) {
            // Calcular monto y registrar pago
            $monto_a_pagar = $ocupacion['precio_dia'] * $dias_adicionales;
            
            // Aplicar descuento si existe
            $descuento = 0;
            $motivo_descuento = '';
            if (isset($_POST['descuento']) && !empty($_POST['descuento'])) {
                $descuento = floatval($_POST['descuento']);
                $monto_a_pagar = $monto_a_pagar - $descuento;
                $motivo_descuento = isset($_POST['motivo_descuento']) && !empty($_POST['motivo_descuento']) 
                    ? clean_input($_POST['motivo_descuento']) 
                    : 'Descuento aplicado';
            }
            
            // Preparar concepto
            $concepto = "Extensión de estadía - Hab. {$ocupacion['numero']} - {$ocupacion['nombres_apellidos']} ({$dias_adicionales} " . ($dias_adicionales == 1 ? 'día' : 'días') . ")";
            if ($descuento > 0) {
                $concepto .= " (Descuento: Bs. " . number_format($descuento, 2) . " - {$motivo_descuento})";
            }
            
            // Registrar ingreso financiero
            $datos_ingreso = [
                'concepto' => $concepto,
                'monto' => $monto_a_pagar,
                'fecha' => date('Y-m-d'),
                'metodo_pago' => $metodo_pago,
                'categoria' => 'alojamiento'
            ];
            
            $ingreso_id = $finanzasModel->registrarIngreso($datos_ingreso);
            
            if ($ingreso_id) {
                $mensaje = "Estadía extendida exitosamente. Nueva fecha de salida: " . date('d/m/Y', strtotime($resultado['nueva_fecha_salida']));
                $tipo_mensaje = 'success';
                
                // Redirigir después de 2 segundos
                header("refresh:2;url=activos.php");
            } else {
                throw new Exception('Error al registrar el pago');
            }
        } else {
            throw new Exception($resultado['error'] ?? 'Error al extender estadía');
        }
        
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Título -->
        <div class="mb-6">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                <i class="fas fa-calendar-plus text-blue-600 dark:text-blue-400 mr-2"></i>
                Extender Estadía
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Añadir días adicionales a una estadía existente
            </p>
        </div>

        <?php if ($ocupacion['estado'] === 'finalizado'): ?>
        <div class="mb-6 p-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-600 dark:text-blue-400 mt-0.5 mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Reactivando estadía</p>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        Esta ocupación está finalizada. Al extender, se reactivará automáticamente y la habitación volverá a estado "ocupada".
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($mensaje): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $tipo_mensaje === 'success' ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800'; ?>">
            <p class="<?php echo $tipo_mensaje === 'success' ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'; ?>">
                <i class="fas <?php echo $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
                <?php echo $mensaje; ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Información de la Ocupación Actual -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                Información de la Estadía Actual
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Huésped</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo $ocupacion['nombres_apellidos']; ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">CI/Pasaporte</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo $ocupacion['ci_pasaporte']; ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Habitación</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo $ocupacion['numero']; ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Precio por Noche</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Bs. <?php echo number_format($ocupacion['precio_dia'], 2); ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Fecha de Ingreso</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo date('d/m/Y', strtotime($ocupacion['fecha_ingreso'])); ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Fecha de Salida Actual</p>
                    <p class="font-semibold text-red-600 dark:text-red-400"><?php echo date('d/m/Y', strtotime($ocupacion['fecha_salida_estimada'])); ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Días Actuales</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo $ocupacion['nro_dias']; ?> <?php echo $ocupacion['nro_dias'] == 1 ? 'día' : 'días'; ?></p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Estado</p>
                    <p class="font-semibold">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            <?php echo $ocupacion['estado'] === 'activo' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'; ?>">
                            <?php echo ucfirst($ocupacion['estado']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario de Extensión -->
        <form method="POST" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <input type="hidden" name="ocupacion_id" value="<?php echo $ocupacion_id; ?>">
            
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <i class="fas fa-calendar-plus text-green-500 mr-2"></i>
                Datos de la Extensión
            </h2>

            <div class="space-y-4">
                <!-- Días Adicionales -->
                <div>
                    <label for="dias_adicionales" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Días Adicionales <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="dias_adicionales" 
                           name="dias_adicionales" 
                           min="1" 
                           max="30"
                           required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           oninput="calcularTotal()">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        ¿Cuántos días más se quedará el huésped?
                    </p>
                </div>

                <!-- Nueva Fecha de Salida (calculada) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nueva Fecha de Salida
                    </label>
                    <div id="nueva_fecha_salida" class="px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg text-blue-900 dark:text-blue-200 font-semibold">
                        Ingrese los días adicionales
                    </div>
                </div>

                <!-- Total de Días -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Total de Días
                    </label>
                    <div id="total_dias" class="px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-semibold">
                        <?php echo $ocupacion['nro_dias']; ?> días actuales
                    </div>
                </div>

                <!-- Monto a Pagar -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Monto a Pagar por Extensión
                    </label>
                    <div id="monto_pagar" class="px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <span class="text-2xl font-bold text-green-700 dark:text-green-300">Bs. 0.00</span>
                    </div>
                </div>

                <!-- Descuento (Opcional) -->
                <div>
                    <label for="descuento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Descuento (Opcional)
                    </label>
                    <input type="number" 
                           id="descuento" 
                           name="descuento" 
                           min="0" 
                           step="0.01"
                           placeholder="0.00"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           oninput="calcularTotal()">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Monto del descuento en Bs.
                    </p>
                </div>

                <!-- Motivo del Descuento -->
                <div id="motivo_div" style="display: none;">
                    <label for="motivo_descuento" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Motivo del Descuento
                    </label>
                    <input type="text" 
                           id="motivo_descuento" 
                           name="motivo_descuento" 
                           placeholder="Ej: Cliente frecuente, Estadía larga, etc."
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                <!-- Monto Final -->
                <div id="monto_final_div" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Monto Final a Pagar
                    </label>
                    <div id="monto_final" class="px-4 py-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                        <span class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">Bs. 0.00</span>
                    </div>
                </div>

                <!-- Método de Pago -->
                <div>
                    <label for="metodo_pago" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Método de Pago <span class="text-red-500">*</span>
                    </label>
                    <select id="metodo_pago" 
                            name="metodo_pago" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Seleccione...</option>
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="qr">📱 QR (Don Rodolfo)</option>
                        <option value="pendiente">⏳ Pago Pendiente</option>
                    </select>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                <button type="submit" 
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-lg transition-colors">
                    <i class="fas fa-check mr-2"></i>
                    Confirmar Extensión
                </button>
                <a href="activos.php" 
                   class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2.5 px-4 rounded-lg text-center transition-colors">
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const precioNoche = <?php echo $ocupacion['precio_dia']; ?>;
const fechaSalidaActual = new Date('<?php echo $ocupacion['fecha_salida_estimada']; ?>');
const diasActuales = <?php echo $ocupacion['nro_dias']; ?>;

function calcularTotal() {
    const diasAdicionales = parseInt(document.getElementById('dias_adicionales').value) || 0;
    const descuento = parseFloat(document.getElementById('descuento').value) || 0;
    
    // Mostrar/ocultar campo de motivo del descuento
    if (descuento > 0) {
        document.getElementById('motivo_div').style.display = 'block';
        document.getElementById('monto_final_div').style.display = 'block';
    } else {
        document.getElementById('motivo_div').style.display = 'none';
        document.getElementById('monto_final_div').style.display = 'none';
    }
    
    if (diasAdicionales > 0) {
        // Calcular nueva fecha
        const nuevaFecha = new Date(fechaSalidaActual);
        nuevaFecha.setDate(nuevaFecha.getDate() + diasAdicionales);
        
        const opciones = { year: 'numeric', month: '2-digit', day: '2-digit' };
        const fechaFormateada = nuevaFecha.toLocaleDateString('es-BO', opciones);
        
        document.getElementById('nueva_fecha_salida').innerHTML = 
            `<i class="fas fa-calendar-check mr-2"></i>${fechaFormateada}`;
        
        // Total de días
        const totalDias = diasActuales + diasAdicionales;
        document.getElementById('total_dias').innerHTML = 
            `<i class="fas fa-moon mr-2"></i>${totalDias} ${totalDias === 1 ? 'día' : 'días'} (${diasActuales} + ${diasAdicionales})`;
        
        // Monto a pagar (sin descuento)
        const monto = precioNoche * diasAdicionales;
        document.getElementById('monto_pagar').innerHTML = 
            `<span class="text-2xl font-bold text-green-700 dark:text-green-300">Bs. ${monto.toFixed(2)}</span>
             <span class="text-xs text-green-600 dark:text-green-400 ml-2">(${diasAdicionales} ${diasAdicionales === 1 ? 'noche' : 'noches'} × Bs. ${precioNoche.toFixed(2)})</span>`;
        
        // Monto final con descuento
        if (descuento > 0) {
            const montoFinal = monto - descuento;
            if (montoFinal < 0) {
                document.getElementById('monto_final').innerHTML = 
                    `<span class="text-lg font-bold text-red-700 dark:text-red-300">⚠️ El descuento no puede ser mayor al monto</span>`;
            } else {
                document.getElementById('monto_final').innerHTML = 
                    `<span class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">Bs. ${montoFinal.toFixed(2)}</span>
                     <span class="text-xs text-yellow-600 dark:text-yellow-400 ml-2">(Bs. ${monto.toFixed(2)} - Bs. ${descuento.toFixed(2)})</span>`;
            }
        }
    } else {
        document.getElementById('nueva_fecha_salida').textContent = 'Ingrese los días adicionales';
        document.getElementById('total_dias').innerHTML = `${diasActuales} días actuales`;
        document.getElementById('monto_pagar').innerHTML = 
            '<span class="text-2xl font-bold text-green-700 dark:text-green-300">Bs. 0.00</span>';
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
