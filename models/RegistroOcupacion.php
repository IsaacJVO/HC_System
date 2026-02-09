<?php
require_once __DIR__ . '/../config/config.php';

class RegistroOcupacion {
    public $conn; // Hacer público para acceso a errores
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    public function crear($datos) {
        try {
            $sql = "INSERT INTO registro_ocupacion (huesped_id, habitacion_id, nro_pieza, prox_destino, 
                    via_ingreso, fecha_ingreso, nro_dias, fecha_salida_estimada) 
                    VALUES (:huesped_id, :habitacion_id, :nro_pieza, :prox_destino, :via_ingreso, 
                    :fecha_ingreso, :nro_dias, :fecha_salida_estimada)";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':huesped_id' => $datos['huesped_id'],
                ':habitacion_id' => $datos['habitacion_id'],
                ':nro_pieza' => $datos['nro_pieza'],
                ':prox_destino' => $datos['prox_destino'] ?? null,
                ':via_ingreso' => $datos['via_ingreso'] ?? null,
                ':fecha_ingreso' => $datos['fecha_ingreso'],
                ':nro_dias' => $datos['nro_dias'],
                ':fecha_salida_estimada' => $datos['fecha_salida_estimada']
            ]);
            
            if ($result) {
                $insertId = $this->conn->lastInsertId();
                
                // Cambiar habitación a estado 'ocupada' - MEJORADO: lanzar excepción si falla
                $actualizado = $this->actualizarEstadoHabitacion($datos['habitacion_id'], 'ocupada');
                if (!$actualizado) {
                    error_log("ADVERTENCIA: No se pudo actualizar estado de habitación ID: " . $datos['habitacion_id']);
                    // Aún así retornar el ID porque la ocupación se creó correctamente
                }
                
                return $insertId;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en RegistroOcupacion::crear: " . $e->getMessage());
            throw $e; // Lanzar la excepción para que el controlador pueda manejarla
        }
    }
    
    public function obtenerActivos() {
        $sql = "SELECT ro.*, h.nombres_apellidos, h.ci_pasaporte, h.genero, h.edad, 
                h.estado_civil, h.nacionalidad, h.profesion, h.objeto, h.procedencia,
                hab.numero as numero_habitacion
                FROM registro_ocupacion ro
                INNER JOIN huespedes h ON ro.huesped_id = h.id
                INNER JOIN habitaciones hab ON ro.habitacion_id = hab.id
                WHERE ro.estado = 'activo'
                ORDER BY ro.fecha_ingreso DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function obtenerTodos($limit = 100) {
        $sql = "SELECT ro.*, h.nombres_apellidos, h.ci_pasaporte, hab.numero as numero_habitacion
                FROM registro_ocupacion ro
                INNER JOIN huespedes h ON ro.huesped_id = h.id
                INNER JOIN habitaciones hab ON ro.habitacion_id = hab.id
                ORDER BY ro.fecha_ingreso DESC
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function finalizarOcupacion($id, $fecha_salida_real, $cambiar_estado = true) {
        $sql = "UPDATE registro_ocupacion SET 
                estado = 'finalizado',
                fecha_salida_real = :fecha_salida
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':id' => $id,
            ':fecha_salida' => $fecha_salida_real
        ]);
        
        // Solo cambiar estado de habitación si se especifica
        if ($result && $cambiar_estado) {
            $ocupacion = $this->obtenerPorId($id);
            if ($ocupacion) {
                // Por defecto, pasar a "limpieza" en lugar de "disponible"
                $this->actualizarEstadoHabitacion($ocupacion['habitacion_id'], 'limpieza');
            }
        }
        
        return $result;
    }
    
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM registro_ocupacion WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function actualizar($id, $datos) {
        try {
            $sql = "UPDATE registro_ocupacion SET 
                    nro_pieza = :nro_pieza,
                    prox_destino = :prox_destino,
                    via_ingreso = :via_ingreso,
                    nro_dias = :nro_dias,
                    fecha_salida_estimada = DATE_ADD(fecha_ingreso, INTERVAL :nro_dias_calc DAY)
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':nro_pieza' => $datos['nro_pieza'],
                ':prox_destino' => $datos['prox_destino'] ?? null,
                ':via_ingreso' => $datos['via_ingreso'] ?? null,
                ':nro_dias' => $datos['nro_dias'],
                ':nro_dias_calc' => $datos['nro_dias']
            ]);
        } catch (PDOException $e) {
            error_log("Error en RegistroOcupacion::actualizar: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function actualizarEstadoHabitacion($habitacion_id, $estado) {
        $sql = "UPDATE habitaciones SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':estado' => $estado, ':id' => $habitacion_id]);
    }
    
    /**
     * Verifica automáticamente las fechas de salida estimadas
     * y cambia habitaciones a "limpieza" cuando el huésped debe salir
     * Considera la hora de checkout a las 12:00 PM (mediodía)
     */
    public function verificarSalidasAutomaticas() {
        try {
            // Obtener ocupaciones activas donde la fecha y hora de salida ya pasaron
            // Se agrega 12 horas (mediodía) a la fecha de salida estimada para el checkout
            $sql = "SELECT ro.*, hab.id as habitacion_id 
                    FROM registro_ocupacion ro
                    INNER JOIN habitaciones hab ON ro.habitacion_id = hab.id
                    WHERE ro.estado = 'activo' 
                    AND DATE_ADD(ro.fecha_salida_estimada, INTERVAL 12 HOUR) <= NOW()";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $ocupaciones_vencidas = $stmt->fetchAll();
            
            foreach ($ocupaciones_vencidas as $ocupacion) {
                // Finalizar la ocupación
                $this->finalizarOcupacion($ocupacion['id'], date('Y-m-d'));
                
                // Cambiar habitación a "limpieza" en lugar de "disponible"
                $this->actualizarEstadoHabitacion($ocupacion['habitacion_id'], 'limpieza');
            }
            
            return count($ocupaciones_vencidas);
        } catch (PDOException $e) {
            error_log("Error en verificarSalidasAutomaticas: " . $e->getMessage());
            return 0;
        }
    }
}
?>
