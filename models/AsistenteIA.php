<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/gemini_config.php';

class AsistenteIA {
    private $pdo;
    
    public function __construct() {
        $this->pdo = getConnection();
    }
    
    /**
     * Obtiene contexto actual del hotel desde la BD
     */
    private function obtenerContextoHotel() {
        try {
            // Habitaciones
            $stmt = $this->pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'ocupada' THEN 1 ELSE 0 END) as ocupadas,
                    SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as disponibles,
                    SUM(CASE WHEN estado = 'mantenimiento' THEN 1 ELSE 0 END) as mantenimiento
                FROM habitaciones
            ");
            $habitaciones = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Huéspedes activos hoy
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as total
                FROM registro_ocupacion
                WHERE fecha_salida_estimada >= CURDATE()
            ");
            $huespedes_activos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Ingresos del mes actual
            $stmt = $this->pdo->query("
                SELECT COALESCE(SUM(monto), 0) as total
                FROM ingresos
                WHERE MONTH(fecha) = MONTH(CURDATE())
                AND YEAR(fecha) = YEAR(CURDATE())
            ");
            $ingresos_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Egresos del mes actual
            $stmt = $this->pdo->query("
                SELECT COALESCE(SUM(monto), 0) as total
                FROM egresos
                WHERE MONTH(fecha) = MONTH(CURDATE())
                AND YEAR(fecha) = YEAR(CURDATE())
            ");
            $egresos_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Mantenimientos pendientes
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as total
                FROM mantenimientos
                WHERE estado IN ('pendiente', 'en_proceso')
            ");
            $mantenimientos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Garajes ocupados hoy
            $stmt = $this->pdo->query("
                SELECT COUNT(*) as total, COALESCE(SUM(costo), 0) as total_costo
                FROM registro_garaje
                WHERE DATE(fecha) = CURDATE()
            ");
            $garajes = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $contexto = "Contexto actual del Hotel Cecil:\n\n";
            $contexto .= "HABITACIONES:\n";
            $contexto .= "- Total: {$habitaciones['total']} habitaciones\n";
            $contexto .= "- Ocupadas: {$habitaciones['ocupadas']}\n";
            $contexto .= "- Disponibles: {$habitaciones['disponibles']}\n";
            $contexto .= "- En mantenimiento: {$habitaciones['mantenimiento']}\n\n";
            
            $contexto .= "HUÉSPEDES:\n";
            $contexto .= "- Huéspedes activos: {$huespedes_activos}\n\n";
            
            $contexto .= "FINANZAS DEL MES:\n";
            $contexto .= "- Ingresos: Bs. " . number_format($ingresos_mes, 2) . "\n";
            $contexto .= "- Egresos: Bs. " . number_format($egresos_mes, 2) . "\n";
            $contexto .= "- Balance: Bs. " . number_format($ingresos_mes - $egresos_mes, 2) . "\n\n";
            
            $contexto .= "MANTENIMIENTOS:\n";
            $contexto .= "- Pendientes/En proceso: {$mantenimientos}\n\n";
            
            $contexto .= "GARAJE HOY:\n";
            $contexto .= "- Vehículos: {$garajes['total']}\n";
            $contexto .= "- Costo total: Bs. " . number_format($garajes['total_costo'], 2) . "\n";
            
            return $contexto;
            
        } catch (Exception $e) {
            error_log("Error en obtenerContextoHotel: " . $e->getMessage());
            return "Error al obtener contexto: " . $e->getMessage();
        }
    }
    
    /**
     * Consulta específica a la BD basada en la pregunta del usuario
     */
    private function consultarBD($pregunta) {
        $pregunta_lower = strtolower($pregunta);
        $resultado = "";
        
        try {
            // Detectar qué información necesita
            if (preg_match('/habitaci(o|ó)n|cuarto|pieza|libre|disponible|ocupad/i', $pregunta)) {
                $stmt = $this->pdo->query("
                    SELECT nro_pieza, estado, capacidad, precio
                    FROM habitaciones
                    ORDER BY nro_pieza
                ");
                $habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $resultado .= "\nDetalle de Habitaciones:\n";
                foreach ($habitaciones as $hab) {
                    $resultado .= "- Hab {$hab['nro_pieza']}: {$hab['estado']} (Capacidad: {$hab['capacidad']}, Precio: Bs. {$hab['precio']})\n";
                }
            }
            
            if (preg_match('/hu(e|é)sped|cliente|ocupante|registro/i', $pregunta)) {
                $stmt = $this->pdo->query("
                    SELECT h.nombre, h.apellido, r.nro_pieza, r.fecha_ingreso, r.fecha_salida_estimada
                    FROM registro_ocupacion r
                    JOIN huespedes h ON r.huesped_id = h.id
                    WHERE r.fecha_salida_estimada >= CURDATE()
                    ORDER BY r.fecha_ingreso DESC
                    LIMIT 10
                ");
                $huespedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $resultado .= "\nHuéspedes Activos:\n";
                foreach ($huespedes as $h) {
                    $resultado .= "- {$h['nombre']} {$h['apellido']} (Hab {$h['nro_pieza']}) - Sale: " . date('d/m/Y', strtotime($h['fecha_salida_estimada'])) . "\n";
                }
            }
            
            if (preg_match('/ingreso|ganancia|cobr(o|ó)|dinero que entr(o|ó)/i', $pregunta)) {
                $stmt = $this->pdo->query("
                    SELECT DATE(fecha) as dia, concepto, monto
                    FROM ingresos
                    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    ORDER BY fecha DESC
                    LIMIT 10
                ");
                $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $resultado .= "\nÚltimos Ingresos:\n";
                foreach ($ingresos as $ing) {
                    $resultado .= "- " . date('d/m/Y', strtotime($ing['dia'])) . ": {$ing['concepto']} - Bs. " . number_format($ing['monto'], 2) . "\n";
                }
            }
            
            if (preg_match('/egreso|gasto|pag(o|ó)|sali(o|ó)|gast(e|é)/i', $pregunta)) {
                $stmt = $this->pdo->query("
                    SELECT DATE(fecha) as dia, concepto, monto
                    FROM egresos
                    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    ORDER BY fecha DESC
                    LIMIT 10
                ");
                $egresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $resultado .= "\nÚltimos Egresos:\n";
                foreach ($egresos as $eg) {
                    $resultado .= "- " . date('d/m/Y', strtotime($eg['dia'])) . ": {$eg['concepto']} - Bs. " . number_format($eg['monto'], 2) . "\n";
                }
            }
            
            if (preg_match('/mantenimiento|reparaci(o|ó)n|arregl/i', $pregunta)) {
                $stmt = $this->pdo->query("
                    SELECT habitacion_numero, titulo, estado, prioridad, responsable
                    FROM mantenimientos
                    WHERE estado != 'completado'
                    ORDER BY 
                        CASE prioridad
                            WHEN 'urgente' THEN 1
                            WHEN 'alta' THEN 2
                            WHEN 'media' THEN 3
                            ELSE 4
                        END
                    LIMIT 10
                ");
                $mantenimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $resultado .= "\nMantenimientos Pendientes:\n";
                foreach ($mantenimientos as $mant) {
                    $resultado .= "- Hab {$mant['habitacion_numero']}: {$mant['titulo']} ({$mant['prioridad']}) - {$mant['estado']}\n";
                }
            }
            
            if (preg_match('/garaje|estacionamiento|auto|carro|veh(i|í)culo/i', $pregunta)) {
                $stmt = $this->pdo->query("
                    SELECT g.huesped_nombre, g.fecha, g.costo, r.nro_pieza
                    FROM registro_garaje g
                    LEFT JOIN registro_ocupacion r ON g.ocupacion_id = r.id
                    WHERE DATE(g.fecha) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    ORDER BY g.fecha DESC
                    LIMIT 10
                ");
                $garajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $resultado .= "\nRegistros de Garaje Recientes:\n";
                foreach ($garajes as $gar) {
                    $hab = $gar['nro_pieza'] ? "Hab {$gar['nro_pieza']}" : "N/A";
                    $resultado .= "- " . date('d/m/Y', strtotime($gar['fecha'])) . ": {$gar['huesped_nombre']} ({$hab}) - Bs. " . number_format($gar['costo'], 2) . "\n";
                }
            }
            
        } catch (Exception $e) {
            $resultado = "\nNo pude consultar la información específica.";
        }
        
        return $resultado;
    }
    
    /**
     * Envía consulta a Gemini con contexto del hotel
     */
    public function consultar($mensaje_usuario) {
        try {
            // Obtener contexto del hotel
            $contexto = $this->obtenerContextoHotel();
            $datos_especificos = $this->consultarBD($mensaje_usuario);
            
            // Construir prompt para Gemini
            $prompt = "Eres el asistente virtual del Hotel Cecil. Ayudas al administrador del hotel respondiendo preguntas sobre el negocio de manera amigable, concisa y profesional.\n\n";
            $prompt .= $contexto;
            $prompt .= $datos_especificos;
            $prompt .= "\n\nPregunta del administrador: {$mensaje_usuario}\n\n";
            $prompt .= "IMPORTANTE: Responde SOLO con texto plano. NO uses asteriscos, negritas, ni ningún formato markdown. Usa saltos de línea simples para separar ideas. NO uses símbolos como *, **, _, __, etc. Responde de manera clara, directa y útil. Si la pregunta no está relacionada con el hotel, indica amablemente que solo puedes ayudar con información del Hotel Cecil.";
            
            // Preparar petición a Gemini
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ];
            
            // Enviar a Gemini API
            $ch = curl_init(GEMINI_API_URL . '?key=' . GEMINI_API_KEY);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code !== 200) {
                // Debug: mostrar respuesta completa
                $error_detail = json_decode($response, true);
                $error_msg = isset($error_detail['error']['message']) ? $error_detail['error']['message'] : 'Error desconocido';
                return "Error de Gemini ({$http_code}): {$error_msg}";
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return $result['candidates'][0]['content']['parts'][0]['text'];
            }
            
            return "No pude generar una respuesta en este momento.";
            
        } catch (Exception $e) {
            return "Error al procesar la consulta: " . $e->getMessage();
        }
    }
}
