<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/Huesped.php';
require_once __DIR__ . '/../../models/Habitacion.php';
require_once __DIR__ . '/../../models/RegistroOcupacion.php';

$page_title = 'Nuevo Registro de Huésped';
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $huespedModel = new Huesped();
    $habitacionModel = new Habitacion();
    $registroModel = new RegistroOcupacion();
    
    try {
        // Buscar o crear huésped
        $huesped_existente = $huespedModel->buscarPorCI($_POST['ci_pasaporte']);
        
        if ($huesped_existente) {
            $huesped_id = $huesped_existente['id'];
            $mensaje = 'Huésped encontrado en el sistema. ';
        } else {
            $datos_huesped = [
                'nombres_apellidos' => clean_input($_POST['nombres_apellidos']),
                'genero' => $_POST['genero'],
                'edad' => (int)$_POST['edad'],
                'estado_civil' => clean_input($_POST['estado_civil']),
                'nacionalidad' => clean_input($_POST['nacionalidad']),
                'ci_pasaporte' => clean_input($_POST['ci_pasaporte']),
                'profesion' => clean_input($_POST['profesion']),
                'objeto' => clean_input($_POST['objeto']),
                'procedencia' => clean_input($_POST['procedencia'])
            ];
            
            $huesped_id = $huespedModel->crear($datos_huesped);
            if (!$huesped_id) {
                throw new Exception('Error al registrar huésped');
            }
            $mensaje = 'Huésped registrado. ';
        }
        
        // Obtener habitación
        $habitacion = $habitacionModel->obtenerPorNumero($_POST['nro_pieza']);
        if (!$habitacion) {
            throw new Exception('Habitación no encontrada');
        }
        
        // Validar capacidad de habitación vs número de personas
        $tipo_hab = $habitacion['tipo'];
        $capacidad_maxima = 2; // Por defecto
        if ($tipo_hab == 'Individual') $capacidad_maxima = 1;
        elseif ($tipo_hab == 'Doble' || $tipo_hab == 'Matrimonial') $capacidad_maxima = 2;
        elseif ($tipo_hab == 'Triple') $capacidad_maxima = 3;
        elseif ($tipo_hab == 'Familiar' || $tipo_hab == 'Suite') $capacidad_maxima = 4;
        
        $num_acompanantes = 0;
        if (isset($_POST['acomp_ci']) && is_array($_POST['acomp_ci'])) {
            foreach ($_POST['acomp_ci'] as $ci) {
                if (!empty($ci)) $num_acompanantes++;
            }
        }
        
        $total_personas = 1 + $num_acompanantes; // 1 titular + acompañantes
        if ($total_personas > $capacidad_maxima) {
            throw new Exception("La habitación {$tipo_hab} solo permite {$capacidad_maxima} persona(s). Usted intentó registrar {$total_personas} persona(s).");
        }
        
        // Calcular fecha de salida estimada
        // Si entra el 20 y se queda 1 día, sale el 21
        $fecha_ingreso = $_POST['fecha_ingreso'];
        $nro_dias = (int)$_POST['nro_dias'];
        $fecha_salida = date('Y-m-d', strtotime($fecha_ingreso . ' +' . $nro_dias . ' days'));
        
        // Registrar ocupación
        $datos_ocupacion = [
            'huesped_id' => $huesped_id,
            'habitacion_id' => $habitacion['id'],
            'nro_pieza' => clean_input($_POST['nro_pieza']),
            'prox_destino' => !empty($_POST['prox_destino']) ? clean_input($_POST['prox_destino']) : null,
            'via_ingreso' => !empty($_POST['via_ingreso']) ? clean_input($_POST['via_ingreso']) : null,
            'fecha_ingreso' => $fecha_ingreso,
            'nro_dias' => $nro_dias,
            'fecha_salida_estimada' => $fecha_salida
        ];
        
        $ocupacion_id = $registroModel->crear($datos_ocupacion);
        if (!$ocupacion_id) {
            throw new Exception('Error al registrar ocupación en la base de datos');
        }
        
        // Registrar ingreso automáticamente
        require_once __DIR__ . '/../../models/Finanzas.php';
        $finanzasModel = new Finanzas();
        
        $monto_total = $habitacion['precio_dia'] * $nro_dias;
        $metodo_pago = isset($_POST['metodo_pago']) ? $_POST['metodo_pago'] : 'efectivo';
        
        $datos_ingreso = [
            'ocupacion_id' => $ocupacion_id,
            'concepto' => 'Pago habitación ' . $_POST['nro_pieza'] . ' - ' . $nro_dias . ' día(s)',
            'monto' => $monto_total,
            'metodo_pago' => $metodo_pago,
            'fecha' => $fecha_ingreso,
            'observaciones' => 'Ingreso automático por registro de huésped'
        ];
        
        $finanzasModel->registrarIngreso($datos_ingreso);
        
        // Si es pago QR, también registrar en tabla pagos_qr
        if ($metodo_pago === 'qr') {
            $datos_qr = [
                'ocupacion_id' => $ocupacion_id,
                'monto' => $monto_total,
                'fecha' => $fecha_ingreso,
                'numero_transaccion' => isset($_POST['numero_transaccion']) ? clean_input($_POST['numero_transaccion']) : null,
                'observaciones' => 'Pago QR por habitación ' . $_POST['nro_pieza']
            ];
            $finanzasModel->registrarPagoQR($datos_qr);
        }
                // Registrar acompañantes si existen
        if (isset($_POST['acomp_ci']) && is_array($_POST['acomp_ci']) && count($_POST['acomp_ci']) > 0) {
            $acomp_count = 0;
            for ($i = 0; $i < count($_POST['acomp_ci']); $i++) {
                if (!empty($_POST['acomp_ci'][$i]) && !empty($_POST['acomp_nombres'][$i])) {
                    // Verificar si el acompañante ya existe
                    $acomp_existente = $huespedModel->buscarPorCI($_POST['acomp_ci'][$i]);
                    
                    if ($acomp_existente) {
                        $acomp_huesped_id = $acomp_existente['id'];
                    } else {
                        // Crear nuevo acompañante
                        $datos_acomp = [
                            'nombres_apellidos' => clean_input($_POST['acomp_nombres'][$i]),
                            'genero' => $_POST['acomp_genero'][$i],
                            'edad' => (int)$_POST['acomp_edad'][$i],
                            'estado_civil' => null,
                            'nacionalidad' => clean_input($_POST['acomp_nacionalidad'][$i]),
                            'ci_pasaporte' => clean_input($_POST['acomp_ci'][$i]),
                            'profesion' => null,
                            'objeto' => isset($_POST['acomp_relacion'][$i]) ? clean_input($_POST['acomp_relacion'][$i]) : null,
                            'procedencia' => null
                        ];
                        
                        $acomp_huesped_id = $huespedModel->crear($datos_acomp);
                    }
                    
                    // Registrar ocupación del acompañante (misma habitación, mismo período)
                    if ($acomp_huesped_id) {
                        $datos_ocupacion_acomp = [
                            'huesped_id' => $acomp_huesped_id,
                            'habitacion_id' => $habitacion['id'],
                            'nro_pieza' => clean_input($_POST['nro_pieza']),
                            'prox_destino' => !empty($_POST['prox_destino']) ? clean_input($_POST['prox_destino']) : null,
                            'via_ingreso' => !empty($_POST['via_ingreso']) ? clean_input($_POST['via_ingreso']) : null,
                            'fecha_ingreso' => $fecha_ingreso,
                            'nro_dias' => $nro_dias,
                            'fecha_salida_estimada' => $fecha_salida
                        ];
                        
                        $registroModel->crear($datos_ocupacion_acomp);
                        $acomp_count++;
                    }
                }
            }
            
            if ($acomp_count > 0) {
                $mensaje .= " Se registraron {$acomp_count} acompañante(s). ";
            }
        }
                $metodo_pago_texto = $metodo_pago === 'qr' ? 'QR' : 'Efectivo';
        $mensaje .= 'Ocupación e ingreso registrados correctamente. Total: Bs. ' . number_format($monto_total, 2) . ' (' . $metodo_pago_texto . ')';
        $tipo_mensaje = 'success';
        
        // Limpiar POST para evitar reenvíos
        $_POST = [];
        
    } catch (PDOException $e) {
        $mensaje = 'Error de base de datos: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
        error_log("Error en registro: " . $e->getMessage());
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
        
        // Log para debugging
        error_log("Error en registro: " . $e->getMessage());
        error_log("Datos POST: " . print_r($_POST, true));
    }
}

// Obtener habitaciones disponibles
$habitacionModel = new Habitacion();
$habitaciones = $habitacionModel->obtenerDisponibles();

include __DIR__ . '/../../includes/header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-noir mb-2">Nuevo Registro</h1>
            <p class="text-gray-500">Complete la información del huésped y asigne una habitación</p>
        </div>
        <a href="<?php echo BASE_PATH; ?>/views/huespedes/activos.php" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-mist transition-all duration-200">
            Cancelar
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

<!-- Main Form -->
<form method="POST" action="" class="space-y-8">
    
    <!-- Sección: Información Personal -->
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h2 class="text-xl font-semibold text-noir">Información Personal</h2>
            <p class="text-sm text-gray-500 mt-1">Datos de identificación del huésped</p>
        </div>
        
        <div class="p-8 space-y-6">
            <!-- Fila 1: CI y Nombres -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        CI o Pasaporte <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="ci_pasaporte"
                            name="ci_pasaporte" 
                            onblur="buscarHuespedPorCI()"
                            value="<?php echo isset($_POST['ci_pasaporte']) ? htmlspecialchars($_POST['ci_pasaporte']) : ''; ?>"
                            required
                            class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                            placeholder="Ingrese CI o Pasaporte"
                        >
                        <!-- Indicador de búsqueda -->
                        <div id="busqueda_indicador" class="hidden absolute right-3 top-1/2 transform -translate-y-1/2">
                            <div class="w-5 h-5 border-2 border-gray-300 border-t-noir rounded-full animate-spin"></div>
                        </div>
                    </div>
                    <!-- Mensaje de estado -->
                    <div id="busqueda_mensaje" class="text-xs mt-1.5"></div>
                    <p class="text-xs text-gray-500 mt-1.5">Si existe en el sistema, los datos se autocompletarán</p>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Nombres y Apellidos <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nombres_apellidos"
                        name="nombres_apellidos" 
                        value="<?php echo isset($_POST['nombres_apellidos']) ? htmlspecialchars($_POST['nombres_apellidos']) : ''; ?>"
                        required
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                        placeholder="Nombre completo del huésped"
                    >
                </div>
            </div>
            
            <!-- Fila 2: Género, Fecha Nacimiento, Edad, E. Civil, Nacionalidad -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Género <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="genero"
                        name="genero" 
                        required
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir appearance-none bg-white"
                    >
                        <option value="">Seleccione</option>
                        <option value="M" <?php echo (isset($_POST['genero']) && $_POST['genero'] == 'M') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="F" <?php echo (isset($_POST['genero']) && $_POST['genero'] == 'F') ? 'selected' : ''; ?>>Femenino</option>
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Fecha de Nacimiento <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha_nacimiento"
                        onchange="calcularEdad()"
                        required
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir"
                    >
                    <p class="text-xs text-gray-500 mt-1.5">La edad se calcula automáticamente</p>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Edad <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="edad"
                        name="edad" 
                        value="<?php echo isset($_POST['edad']) ? htmlspecialchars($_POST['edad']) : ''; ?>"
                        required
                        readonly
                        min="1"
                        max="120"
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl bg-gray-50 text-noir font-semibold cursor-not-allowed"
                        placeholder="0"
                    >
                    <p class="text-xs text-gray-500 mt-1.5">Se calcula automáticamente</p>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">Estado Civil</label>
                    <input 
                        type="text" 
                        id="estado_civil"
                        name="estado_civil"
                        value="<?php echo isset($_POST['estado_civil']) ? htmlspecialchars($_POST['estado_civil']) : ''; ?>"
                        maxlength="1"
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                        placeholder="S, C, D, V"
                    >
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Nacionalidad <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="nacionalidad"
                        name="nacionalidad" 
                        required
                        value="<?php echo isset($_POST['nacionalidad']) ? htmlspecialchars($_POST['nacionalidad']) : 'Boliviano'; ?>"
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                        placeholder="País"
                    >
                </div>
            </div>
            
            <!-- Fila 3: Profesión, Objeto, Procedencia -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">Profesión</label>
                    <input 
                        type="text" 
                        id="profesion"
                        name="profesion"
                        value="<?php echo isset($_POST['profesion']) ? htmlspecialchars($_POST['profesion']) : ''; ?>"
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                        placeholder="Ocupación laboral"
                    >
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">Motivo de Estadía</label>
                    <input 
                        type="text" 
                        id="objeto"
                        name="objeto"
                        value="<?php echo isset($_POST['objeto']) ? htmlspecialchars($_POST['objeto']) : ''; ?>"
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                        placeholder="Turismo, Negocios, Paso..."
                    >
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">Procedencia</label>
                    <input 
                        type="text" 
                        id="procedencia"
                        name="procedencia"
                        value="<?php echo isset($_POST['procedencia']) ? htmlspecialchars($_POST['procedencia']) : ''; ?>"
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                        placeholder="Ciudad de origen"
                    >
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sección: Acompañantes (Huéspedes Adicionales) -->
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden" id="seccion_acompanantes">
        <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-noir">Acompañantes</h2>
                    <p class="text-sm text-gray-500 mt-1">Para habitaciones compartidas (dobles, triples, matrimoniales)</p>
                </div>
                <button 
                    type="button" 
                    id="btn_agregar_acompanante"
                    onclick="agregarAcompanante()" 
                    disabled
                    class="px-4 py-2 bg-gray-400 text-white rounded-xl text-sm font-medium cursor-not-allowed transition-all duration-200"
                >
                    + Agregar Acompañante
                </button>
            </div>
        </div>
        
        <div class="p-8" id="lista_acompanantes">
            <div class="text-center text-gray-400 py-6" id="mensaje_sin_acompanantes">
                <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p class="text-sm">No hay acompañantes agregados</p>
                <p class="text-xs mt-1">Haz clic en "Agregar Acompañante" para registrar más personas en la misma habitación</p>
            </div>
        </div>
    </div>
    
    <!-- Sección: Detalles de Reserva -->
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <h2 class="text-xl font-semibold text-noir">Detalles de Reserva</h2>
            <p class="text-sm text-gray-500 mt-1">Asignación de habitación y fechas de estadía</p>
        </div>
        
        <div class="p-8 space-y-6">
            <!-- Fila 1: Habitación y Destino -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Habitación <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="nro_pieza" 
                        id="habitacion_select"
                        onchange="actualizarCapacidadHabitacion()"
                        required
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir appearance-none bg-white"
                    >
                        <option value="">Seleccione una habitación</option>
                        <?php foreach ($habitaciones as $hab): ?>
                            <option value="<?php echo $hab['numero']; ?>" 
                                    data-tipo="<?php echo $hab['tipo']; ?>" 
                                    data-capacidad="<?php 
                                        $tipo = $hab['tipo'];
                                        if ($tipo == 'Individual') echo '1';
                                        elseif ($tipo == 'Doble' || $tipo == 'Matrimonial') echo '2';
                                        elseif ($tipo == 'Triple') echo '3';
                                        elseif ($tipo == 'Familiar' || $tipo == 'Suite') echo '4';
                                        else echo '2';
                                    ?>"
                                    <?php echo (isset($_POST['nro_pieza']) && $_POST['nro_pieza'] == $hab['numero']) ? 'selected' : ''; ?>>
                                Habitación <?php echo $hab['numero']; ?> - <?php echo $hab['tipo']; ?> - Bs. <?php echo number_format($hab['precio_dia'], 2); ?>/día
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="mensaje_capacidad" class="mt-2 text-sm text-gray-600 hidden">
                        <i class="fas fa-users mr-1"></i>
                        <span id="texto_capacidad"></span>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">Próximo Destino</label>
                    <input 
                        type="text" 
                        name="prox_destino"
                        value="<?php echo isset($_POST['prox_destino']) ? htmlspecialchars($_POST['prox_destino']) : ''; ?>"
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                        placeholder="Ciudad o país de destino"
                    >
                </div>
            </div>
            
            <!-- Fila 2: Vía, Fecha Ingreso, Días -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">Vía de Ingreso</label>
                    <select 
                        name="via_ingreso"
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir appearance-none bg-white"
                    >
                        <option value="">Seleccione</option>
                        <option value="T" <?php echo (isset($_POST['via_ingreso']) && $_POST['via_ingreso'] == 'T') ? 'selected' : ''; ?>>Terrestre</option>
                        <option value="A" <?php echo (isset($_POST['via_ingreso']) && $_POST['via_ingreso'] == 'A') ? 'selected' : ''; ?>>Aéreo</option>
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Fecha de Ingreso <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha_ingreso"
                        name="fecha_ingreso" 
                        value="<?php echo isset($_POST['fecha_ingreso']) ? htmlspecialchars($_POST['fecha_ingreso']) : ''; ?>"
                        onchange="calcularFechaSalida()"
                        required
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir"
                    >
                </div>
                
                <div class="space-y-2">
                    <label class="block text-sm font-semibold text-noir">
                        Número de Días <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="nro_dias"
                        name="nro_dias" 
                        value="<?php echo isset($_POST['nro_dias']) ? htmlspecialchars($_POST['nro_dias']) : ''; ?>"
                        onchange="calcularFechaSalida()"
                        min="1"
                        required
                        class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-noir focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                        placeholder="Días"
                    >
                </div>
            </div>
            
            <!-- Método de Pago -->
            <div class="space-y-3">
                <label class="block text-sm font-semibold text-noir">
                    Método de Pago <span class="text-red-500">*</span>
                </label>
                
                <div class="grid grid-cols-2 gap-4">
                    <!-- Efectivo -->
                    <label class="cursor-pointer">
                        <input 
                            type="radio" 
                            name="metodo_pago" 
                            value="efectivo" 
                            id="metodo_efectivo"
                            checked
                            onchange="cambiarMetodoPago('efectivo')"
                            class="hidden"
                        >
                        <div id="btn_efectivo" class="flex items-center justify-center gap-3 p-4 border-2 border-green-500 bg-green-50 rounded-xl transition-all duration-200 hover:shadow-md">
                            <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
                            <span class="font-semibold text-green-700">Efectivo</span>
                        </div>
                    </label>
                    
                    <!-- QR -->
                    <label class="cursor-pointer">
                        <input 
                            type="radio" 
                            name="metodo_pago" 
                            value="qr" 
                            id="metodo_qr"
                            onchange="cambiarMetodoPago('qr')"
                            class="hidden"
                        >
                        <div id="btn_qr" class="flex items-center justify-center gap-3 p-4 border-2 border-gray-300 bg-white rounded-xl transition-all duration-200 hover:shadow-md hover:border-purple-300">
                            <i class="fas fa-qrcode text-2xl text-gray-600"></i>
                            <span class="font-semibold text-gray-700">QR</span>
                        </div>
                    </label>
                </div>
                
                <!-- Imagen QR (se muestra al seleccionar QR) -->
                <div id="qr_imagen_container" style="display: none;" class="mt-4 p-4 bg-purple-50 border-2 border-purple-300 rounded-xl text-center">
                    <p class="text-sm font-semibold text-purple-700 mb-3">Escanea el código QR para realizar el pago:</p>
                    <img src="<?php echo BASE_PATH; ?>/assets/img/QR.jpeg" alt="QR de pago" class="mx-auto max-w-xs w-full rounded-lg shadow-lg">
                    
                    <!-- Número de Transacción -->
                    <div class="mt-4">
                        <label class="block text-sm font-semibold text-noir mb-2">
                            Número de Transacción (Opcional)
                        </label>
                        <input 
                            type="text" 
                            id="numero_transaccion"
                            name="numero_transaccion"
                            value="<?php echo isset($_POST['numero_transaccion']) ? htmlspecialchars($_POST['numero_transaccion']) : ''; ?>"
                            class="w-full px-4 py-3 border border-purple-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 text-noir placeholder-gray-400"
                            placeholder="Ingrese el número de transacción"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Fecha Salida Estimada (calculada) -->
            <div class="bg-mist rounded-xl p-6 border border-gray-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block text-sm font-semibold text-noir mb-1">Fecha de Salida Estimada</label>
                        <p class="text-xs text-gray-500">Se calcula automáticamente según los días de estadía</p>
                    </div>
                    <input 
                        type="date" 
                        id="fecha_salida_estimada"
                        readonly
                        class="px-4 py-3 border border-gray-300 rounded-xl bg-white text-noir font-semibold"
                    >
                </div>
                <div id="mensaje_salida" class="text-sm font-medium text-blue-700 bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                    <i class="fas fa-info-circle mr-1"></i> Complete fecha de ingreso y número de días para calcular
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="flex items-center justify-between pt-6">
        <a href="<?php echo BASE_PATH; ?>/views/huespedes/activos.php" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-mist transition-all duration-200">
            Cancelar
        </a>
        <button 
            type="submit" 
            class="px-8 py-3.5 bg-noir text-white font-semibold rounded-xl hover:bg-gray-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105"
        >
            Registrar Huésped
        </button>
    </div>
</form>

<script>
let contadorAcompanantes = 0;

// Función para eliminar acompañante
function eliminarAcompanante(id) {
    const elemento = document.getElementById(`acompanante_${id}`);
    if (elemento) {
        elemento.remove();
        
        // Contar acompañantes restantes
        const acompanantesRestantes = document.querySelectorAll('[id^="acompanante_"]').length;
        if (acompanantesRestantes === 0) {
            document.getElementById('mensaje_sin_acompanantes').style.display = 'block';
        }
        
        // Actualizar estado del botón de agregar acompañante
        actualizarBotonAcompanante();
    }
}

// Variables globales para capacidad
let capacidadHabitacion = 0;
let tipoHabitacion = '';

// Función para actualizar capacidad cuando cambia la habitación
function actualizarCapacidadHabitacion() {
    const select = document.getElementById('habitacion_select');
    const opcionSeleccionada = select.options[select.selectedIndex];
    
    if (!opcionSeleccionada.value) {
        // No hay habitación seleccionada
        capacidadHabitacion = 0;
        tipoHabitacion = '';
        document.getElementById('mensaje_capacidad').classList.add('hidden');
        document.getElementById('btn_agregar_acompanante').disabled = true;
        document.getElementById('btn_agregar_acompanante').className = 'px-4 py-2 bg-gray-400 text-white rounded-xl text-sm font-medium cursor-not-allowed transition-all duration-200';
        return;
    }
    
    capacidadHabitacion = parseInt(opcionSeleccionada.dataset.capacidad);
    tipoHabitacion = opcionSeleccionada.dataset.tipo;
    
    // Mostrar mensaje de capacidad
    const mensajeCapacidad = document.getElementById('mensaje_capacidad');
    const textoCapacidad = document.getElementById('texto_capacidad');
    
    let textoPersonas = capacidadHabitacion === 1 ? 'persona' : 'personas';
    textoCapacidad.textContent = `Habitación ${tipoHabitacion}: Capacidad máxima ${capacidadHabitacion} ${textoPersonas}`;
    mensajeCapacidad.classList.remove('hidden');
    
    // Actualizar estado del botón
    actualizarBotonAcompanante();
    
    // Verificar si hay que eliminar acompañantes excedentes
    verificarCapacidadExcedida();
}

// Función para actualizar el botón de agregar acompañante
function actualizarBotonAcompanante() {
    const btn = document.getElementById('btn_agregar_acompanante');
    const numAcompanantesActuales = document.querySelectorAll('[id^="acompanante_"]').length;
    const totalPersonas = 1 + numAcompanantesActuales; // 1 titular + acompañantes
    
    if (capacidadHabitacion === 0) {
        // No hay habitación seleccionada
        btn.disabled = true;
        btn.className = 'px-4 py-2 bg-gray-400 text-white rounded-xl text-sm font-medium cursor-not-allowed transition-all duration-200';
        btn.innerHTML = '+ Agregar Acompañante';
    } else if (totalPersonas >= capacidadHabitacion) {
        // Capacidad alcanzada
        btn.disabled = true;
        btn.className = 'px-4 py-2 bg-gray-400 text-white rounded-xl text-sm font-medium cursor-not-allowed transition-all duration-200';
        btn.innerHTML = `Capacidad completa (${totalPersonas}/${capacidadHabitacion})`;
    } else {
        // Puede agregar más acompañantes
        btn.disabled = false;
        btn.className = 'px-4 py-2 bg-purple-600 text-white rounded-xl text-sm font-medium hover:bg-purple-700 transition-all duration-200';
        btn.innerHTML = `+ Agregar Acompañante (${totalPersonas}/${capacidadHabitacion})`;
    }
}

// Función para verificar si hay acompañantes excedentes y notificar
function verificarCapacidadExcedida() {
    const numAcompanantesActuales = document.querySelectorAll('[id^="acompanante_"]').length;
    const totalPersonas = 1 + numAcompanantesActuales;
    
    if (totalPersonas > capacidadHabitacion && capacidadHabitacion > 0) {
        const exceso = totalPersonas - capacidadHabitacion;
        alert(`⚠️ ATENCIÓN: La habitación ${tipoHabitacion} solo permite ${capacidadHabitacion} persona(s).\n\nActualmente tiene ${totalPersonas} persona(s) registrada(s).\n\nPor favor elimine ${exceso} acompañante(s) o cambie a una habitación de mayor capacidad.`);
    }
}

// Modificar función agregarAcompanante original para actualizar botón
const agregarAcompananteOriginal = agregarAcompanante;
function agregarAcompanante() {
    // Verificar capacidad antes de agregar
    const numAcompanantesActuales = document.querySelectorAll('[id^="acompanante_"]').length;
    const totalPersonas = 1 + numAcompanantesActuales;
    
    if (totalPersonas >= capacidadHabitacion) {
        alert(`La habitación ${tipoHabitacion} solo permite ${capacidadHabitacion} persona(s).\n\nNo puede agregar más acompañantes.`);
        return;
    }
    
    // Llamar función original (código existente)
    contadorAcompanantes++;
    const container = document.getElementById('lista_acompanantes');
    const mensajeSin = document.getElementById('mensaje_sin_acompanantes');
    
    // Ocultar mensaje si es el primer acompañante
    if (document.querySelectorAll('[id^="acompanante_"]').length === 0) {
        mensajeSin.style.display = 'none';
    }
    
    const acompananteHTML = `
        <div class="border border-gray-300 rounded-xl p-6 mb-4 bg-purple-50/30" id="acompanante_${contadorAcompanantes}">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-noir">Acompañante #${contadorAcompanantes}</h3>
                <button 
                    type="button" 
                    onclick="eliminarAcompanante(${contadorAcompanantes})"
                    class="text-red-600 hover:text-red-800 text-sm font-medium"
                >
                    ✕ Eliminar
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-noir mb-1">CI/Pasaporte <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="acomp_ci[]" 
                        id="acomp_ci_${contadorAcompanantes}"
                        onblur="buscarAcompanantePorCI(${contadorAcompanantes})"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                        placeholder="Número de documento"
                    >
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-noir mb-1">Nombres y Apellidos <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="acomp_nombres[]" 
                        id="acomp_nombres_${contadorAcompanantes}"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                        placeholder="Nombre completo"
                    >
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-noir mb-1">Género <span class="text-red-500">*</span></label>
                    <select 
                        name="acomp_genero[]" 
                        id="acomp_genero_${contadorAcompanantes}"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 bg-white"
                    >
                        <option value="">Seleccione</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-noir mb-1">Edad <span class="text-red-500">*</span></label>
                    <input 
                        type="number" 
                        name="acomp_edad[]" 
                        id="acomp_edad_${contadorAcompanantes}"
                        required
                        min="1"
                        max="120"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                        placeholder="Edad"
                    >
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-noir mb-1">Nacionalidad <span class="text-red-500">*</span></label>
                    <input 
                        type="text" 
                        name="acomp_nacionalidad[]" 
                        id="acomp_nacionalidad_${contadorAcompanantes}"
                        required
                        value="Boliviano"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                        placeholder="País"
                    >
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-noir mb-1">Relación</label>
                    <input 
                        type="text" 
                        name="acomp_relacion[]" 
                        id="acomp_relacion_${contadorAcompanantes}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-purple-500"
                        placeholder="Ej: Esposo/a, Hijo/a, Amigo/a"
                    >
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', acompananteHTML);
    
    // Actualizar estado del botón después de agregar
    actualizarBotonAcompanante();
}

// Función para calcular edad automáticamente
function calcularEdad() {
    const fechaNacimiento = document.getElementById('fecha_nacimiento').value;
    
    if (!fechaNacimiento) {
        document.getElementById('edad').value = '';
        return;
    }
    
    const hoy = new Date();
    const nacimiento = new Date(fechaNacimiento);
    
    let edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();
    
    // Ajustar edad si aún no ha cumplido años este año
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
        edad--;
    }
    
    document.getElementById('edad').value = edad;
}

// Función existente para buscar huésped
function buscarHuespedPorCI() {
    const ci = document.getElementById('ci_buscar').value;
    if (!ci) return;
    
    fetch('<?php echo BASE_PATH; ?>/controllers/buscar_huesped.php?ci=' + encodeURIComponent(ci))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.huesped) {
                document.getElementById('nombres_apellidos').value = data.huesped.nombres_apellidos;
                document.getElementById('genero').value = data.huesped.genero;
                document.getElementById('edad').value = data.huesped.edad;
                document.getElementById('estado_civil').value = data.huesped.estado_civil || '';
                document.getElementById('nacionalidad').value = data.huesped.nacionalidad;
                document.getElementById('profesion').value = data.huesped.profesion || '';
                document.getElementById('objeto').value = data.huesped.objeto || '';
                document.getElementById('procedencia').value = data.huesped.procedencia || '';
            }
        })
        .catch(error => console.error('Error:', error));
}

// Función para buscar acompañante por CI y autocompletar datos
function buscarAcompanantePorCI(id) {
    const ci = document.getElementById('acomp_ci_' + id).value.trim();
    
    if (!ci || ci.length < 3) {
        return;
    }
    
    fetch('<?php echo BASE_PATH; ?>/controllers/buscar_huesped.php?ci=' + encodeURIComponent(ci))
        .then(response => response.json())
        .then(data => {
            if (data.success && data.huesped) {
                // Autocompletar datos del acompañante
                document.getElementById('acomp_nombres_' + id).value = data.huesped.nombres_apellidos || '';
                document.getElementById('acomp_genero_' + id).value = data.huesped.genero || '';
                document.getElementById('acomp_edad_' + id).value = data.huesped.edad || '';
                document.getElementById('acomp_nacionalidad_' + id).value = data.huesped.nacionalidad || '';
                
                // Mostrar mensaje de éxito
                const acompananteDiv = document.getElementById('acompanante_' + id);
                
                // Remover mensaje anterior si existe
                const mensajeAnterior = acompananteDiv.querySelector('.mensaje-encontrado');
                if (mensajeAnterior) {
                    mensajeAnterior.remove();
                }
                
                // Agregar nuevo mensaje
                const mensaje = document.createElement('div');
                mensaje.className = 'mensaje-encontrado mt-3 p-2 bg-green-100 border border-green-300 rounded-lg text-sm text-green-700 flex items-center gap-2';
                mensaje.innerHTML = '<i class="fas fa-check-circle"></i> Datos encontrados y completados automáticamente';
                acompananteDiv.appendChild(mensaje);
                
                // Remover mensaje después de 3 segundos
                setTimeout(() => {
                    mensaje.remove();
                }, 3000);
            }
        })
        .catch(error => {
            console.log('No se encontró el acompañante en el sistema');
        });
}

// Función existente para calcular fecha de salida
function calcularFechaSalida() {
    const fechaIngreso = document.getElementById('fecha_ingreso').value;
    const nroDias = document.getElementById('nro_dias').value;
    
    if (fechaIngreso && nroDias) {
        // Parsear la fecha correctamente evitando problemas de zona horaria
        const partes = fechaIngreso.split('-');
        const fecha = new Date(parseInt(partes[0]), parseInt(partes[1]) - 1, parseInt(partes[2]));
        
        // Sumar los días
        fecha.setDate(fecha.getDate() + parseInt(nroDias));
        
        const year = fecha.getFullYear();
        const month = String(fecha.getMonth() + 1).padStart(2, '0');
        const day = String(fecha.getDate()).padStart(2, '0');
        
        document.getElementById('fecha_salida_estimada').value = `${year}-${month}-${day}`;
        
        // Actualizar el mensaje de salida
        const mensajeSalida = document.getElementById('mensaje_salida');
        if (mensajeSalida) {
            const meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            mensajeSalida.innerHTML = `<i class="fas fa-clock mr-1"></i> Salida: ${day} de ${meses[fecha.getMonth()]} de ${year} hasta las 12:00 del mediodía`;
        }
    }
}

// Establecer fecha de ingreso por defecto a hoy
document.addEventListener('DOMContentLoaded', function() {
    const hoy = new Date();
    const year = hoy.getFullYear();
    const month = String(hoy.getMonth() + 1).padStart(2, '0');
    const day = String(hoy.getDate()).padStart(2, '0');
    
    document.getElementById('fecha_ingreso').value = `${year}-${month}-${day}`;
});

// Función para cambiar método de pago y actualizar estilos
function cambiarMetodoPago(metodo) {
    const btnEfectivo = document.getElementById('btn_efectivo');
    const btnQr = document.getElementById('btn_qr');
    const qrContainer = document.getElementById('qr_imagen_container');
    
    if (metodo === 'efectivo') {
        // Estilo activo para efectivo
        btnEfectivo.className = 'flex items-center justify-center gap-3 p-4 border-2 border-green-500 bg-green-50 rounded-xl transition-all duration-200 hover:shadow-md';
        btnEfectivo.querySelector('i').className = 'fas fa-money-bill-wave text-2xl text-green-600';
        btnEfectivo.querySelector('span').className = 'font-semibold text-green-700';
        
        // Estilo inactivo para QR
        btnQr.className = 'flex items-center justify-center gap-3 p-4 border-2 border-gray-300 bg-white rounded-xl transition-all duration-200 hover:shadow-md hover:border-purple-300';
        btnQr.querySelector('i').className = 'fas fa-qrcode text-2xl text-gray-600';
        btnQr.querySelector('span').className = 'font-semibold text-gray-700';
        
        // Ocultar QR
        qrContainer.style.display = 'none';
    } else {
        // Estilo inactivo para efectivo
        btnEfectivo.className = 'flex items-center justify-center gap-3 p-4 border-2 border-gray-300 bg-white rounded-xl transition-all duration-200 hover:shadow-md hover:border-green-300';
        btnEfectivo.querySelector('i').className = 'fas fa-money-bill-wave text-2xl text-gray-600';
        btnEfectivo.querySelector('span').className = 'font-semibold text-gray-700';
        
        // Estilo activo para QR
        btnQr.className = 'flex items-center justify-center gap-3 p-4 border-2 border-purple-500 bg-purple-50 rounded-xl transition-all duration-200 hover:shadow-md';
        btnQr.querySelector('i').className = 'fas fa-qrcode text-2xl text-purple-600';
        btnQr.querySelector('span').className = 'font-semibold text-purple-700';
        
        // Mostrar QR
        qrContainer.style.display = 'block';
    }
}

// Función para buscar huésped por CI
function buscarHuespedPorCI() {
    const ci = document.getElementById('ci_pasaporte').value.trim();
    
    if (!ci) {
        return;
    }
    
    const indicador = document.getElementById('busqueda_indicador');
    const mensaje = document.getElementById('busqueda_mensaje');
    
    // Mostrar indicador de carga
    indicador.classList.remove('hidden');
    mensaje.innerHTML = '';
    
    fetch('<?php echo BASE_PATH; ?>/controllers/buscar_huesped_ci.php?ci=' + encodeURIComponent(ci))
        .then(response => response.json())
        .then(data => {
            indicador.classList.add('hidden');
            
            if (data.error) {
                mensaje.innerHTML = '<span class="text-red-600">⚠️ Error al buscar</span>';
                return;
            }
            
            if (data.encontrado) {
                // Autocompletar campos
                const d = data.datos;
                
                document.getElementById('nombres_apellidos').value = d.nombres_apellidos || '';
                document.getElementById('genero').value = d.genero || '';
                document.getElementById('estado_civil').value = d.estado_civil || '';
                document.getElementById('nacionalidad').value = d.nacionalidad || '';
                document.getElementById('profesion').value = d.profesion || '';
                document.getElementById('objeto').value = d.objeto || '';
                document.getElementById('procedencia').value = d.procedencia || '';
                
                // Fecha de nacimiento y edad
                if (d.fecha_nacimiento) {
                    document.getElementById('fecha_nacimiento').value = d.fecha_nacimiento;
                    calcularEdad();
                } else if (d.edad) {
                    document.getElementById('edad').value = d.edad;
                }
                
                // Mensaje de éxito con animación
                mensaje.innerHTML = '<span class="text-green-600 font-medium animate-pulse">✓ Huésped encontrado - Datos autocompletados</span>';
                
                // Quitar mensaje después de 3 segundos
                setTimeout(() => {
                    mensaje.innerHTML = '';
                }, 3000);
                
            } else {
                mensaje.innerHTML = '<span class="text-gray-500">ℹ️ Huésped nuevo - Complete los datos</span>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            indicador.classList.add('hidden');
            mensaje.innerHTML = '<span class="text-red-600">⚠️ Error de conexión</span>';
        });
}

</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
