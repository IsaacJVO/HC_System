<?php
require_once __DIR__ . '/../config/config.php';

class CierreCaja {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    /**
     * Obtener la fecha de apertura actual (última fecha de cierre o inicio del sistema)
     */
    public function obtenerFechaAperturaActual() {
        $sql = "SELECT fecha_cierre as ultima_apertura FROM cierres_caja 
                ORDER BY fecha_cierre DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result) {
            return $result['ultima_apertura'];
        }
        
        // Si no hay cierres previos, buscar el primer ingreso registrado
        $sql = "SELECT MIN(fecha) as primera_fecha FROM ingresos";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result['primera_fecha'] ?? date('Y-m-d 00:00:00');
    }
    
    /**
     * Calcular el resumen actual desde la última apertura
     */
    public function calcularResumenActual() {
        $fecha_apertura = $this->obtenerFechaAperturaActual();
        $fecha_actual = date('Y-m-d H:i:s');
        
        $resumen = [
            'fecha_apertura' => $fecha_apertura,
            'fecha_actual' => $fecha_actual,
            'total_efectivo' => 0,
            'total_qr' => 0,
            'total_egresos' => 0,
            'balance_efectivo' => 0,
            'balance_total' => 0,
            'detalles_ingresos' => [],
            'detalles_egresos' => []
        ];
        
        // Calcular ingresos en efectivo
        $sql = "SELECT SUM(monto) as total FROM ingresos 
                WHERE metodo_pago = 'efectivo' 
                AND DATE(fecha) >= DATE(:fecha_apertura)
                AND CONCAT(fecha, ' ', COALESCE(hora, '00:00:00')) >= :fecha_apertura_completa";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':fecha_apertura' => date('Y-m-d', strtotime($fecha_apertura)),
            ':fecha_apertura_completa' => $fecha_apertura
        ]);
        $result = $stmt->fetch();
        $resumen['total_efectivo'] = floatval($result['total'] ?? 0);
        
        // Calcular ingresos por QR
        $sql = "SELECT SUM(monto) as total FROM ingresos 
                WHERE metodo_pago = 'qr' 
                AND DATE(fecha) >= DATE(:fecha_apertura)
                AND CONCAT(fecha, ' ', COALESCE(hora, '00:00:00')) >= :fecha_apertura_completa";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':fecha_apertura' => date('Y-m-d', strtotime($fecha_apertura)),
            ':fecha_apertura_completa' => $fecha_apertura
        ]);
        $result = $stmt->fetch();
        $resumen['total_qr'] = floatval($result['total'] ?? 0);
        
        // Calcular egresos
        $sql = "SELECT SUM(monto) as total FROM egresos 
                WHERE DATE(fecha) >= DATE(:fecha_apertura)
                AND CONCAT(fecha, ' ', COALESCE(hora, '00:00:00')) >= :fecha_apertura_completa";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':fecha_apertura' => date('Y-m-d', strtotime($fecha_apertura)),
            ':fecha_apertura_completa' => $fecha_apertura
        ]);
        $result = $stmt->fetch();
        $resumen['total_egresos'] = floatval($result['total'] ?? 0);
        
        // Obtener detalles de ingresos
        $sql = "SELECT i.*, ro.nro_pieza, h.nombres_apellidos 
                FROM ingresos i
                LEFT JOIN registro_ocupacion ro ON i.ocupacion_id = ro.id
                LEFT JOIN huespedes h ON ro.huesped_id = h.id
                WHERE DATE(i.fecha) >= DATE(:fecha_apertura)
                AND CONCAT(i.fecha, ' ', COALESCE(i.hora, '00:00:00')) >= :fecha_apertura_completa
                ORDER BY i.fecha DESC, i.hora DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':fecha_apertura' => date('Y-m-d', strtotime($fecha_apertura)),
            ':fecha_apertura_completa' => $fecha_apertura
        ]);
        $resumen['detalles_ingresos'] = $stmt->fetchAll();
        
        // Obtener detalles de egresos
        $sql = "SELECT * FROM egresos 
                WHERE DATE(fecha) >= DATE(:fecha_apertura)
                AND CONCAT(fecha, ' ', COALESCE(hora, '00:00:00')) >= :fecha_apertura_completa
                ORDER BY fecha DESC, hora DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':fecha_apertura' => date('Y-m-d', strtotime($fecha_apertura)),
            ':fecha_apertura_completa' => $fecha_apertura
        ]);
        $resumen['detalles_egresos'] = $stmt->fetchAll();
        
        // Calcular balances
        $resumen['balance_efectivo'] = $resumen['total_efectivo'] - $resumen['total_egresos'];
        $resumen['balance_total'] = $resumen['total_efectivo'] + $resumen['total_qr'] - $resumen['total_egresos'];
        
        return $resumen;
    }
    
    /**
     * Registrar un nuevo cierre de caja
     */
    public function registrarCierre($observaciones = null) {
        try {
            $this->conn->beginTransaction();
            
            // Calcular el resumen actual
            $resumen = $this->calcularResumenActual();
            
            // Insertar el cierre
            $sql = "INSERT INTO cierres_caja 
                    (fecha_apertura, fecha_cierre, usuario_id, usuario_nombre, 
                    total_efectivo, total_qr, total_egresos, balance_efectivo, balance_total, observaciones)
                    VALUES 
                    (:fecha_apertura, :fecha_cierre, :usuario_id, :usuario_nombre,
                    :total_efectivo, :total_qr, :total_egresos, :balance_efectivo, :balance_total, :observaciones)";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':fecha_apertura' => $resumen['fecha_apertura'],
                ':fecha_cierre' => date('Y-m-d H:i:s'),
                ':usuario_id' => $_SESSION['user_id'] ?? null,
                ':usuario_nombre' => $_SESSION['usuario'] ?? 'Sistema',
                ':total_efectivo' => $resumen['total_efectivo'],
                ':total_qr' => $resumen['total_qr'],
                ':total_egresos' => $resumen['total_egresos'],
                ':balance_efectivo' => $resumen['balance_efectivo'],
                ':balance_total' => $resumen['balance_total'],
                ':observaciones' => $observaciones
            ]);
            
            if ($result) {
                $cierre_id = $this->conn->lastInsertId();
                $this->conn->commit();
                return $cierre_id;
            }
            
            $this->conn->rollBack();
            return false;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error en registrarCierre: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener el historial de cierres
     */
    public function obtenerHistorial($limit = 50) {
        $sql = "SELECT * FROM cierres_caja 
                ORDER BY fecha_cierre DESC 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener un cierre específico por ID
     */
    public function obtenerCierrePorId($id) {
        $sql = "SELECT * FROM cierres_caja WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtener detalles de ingresos de un cierre específico
     */
    public function obtenerDetallesIngresosCierre($cierre_id) {
        $cierre = $this->obtenerCierrePorId($cierre_id);
        if (!$cierre) {
            return [];
        }
        
        $sql = "SELECT i.*, ro.nro_pieza, h.nombres_apellidos 
                FROM ingresos i
                LEFT JOIN registro_ocupacion ro ON i.ocupacion_id = ro.id
                LEFT JOIN huespedes h ON ro.huesped_id = h.id
                WHERE DATE(i.fecha) >= DATE(:fecha_apertura)
                AND CONCAT(i.fecha, ' ', COALESCE(i.hora, '00:00:00')) >= :fecha_apertura_completa
                AND CONCAT(i.fecha, ' ', COALESCE(i.hora, '00:00:00')) <= :fecha_cierre
                ORDER BY i.fecha DESC, i.hora DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':fecha_apertura' => date('Y-m-d', strtotime($cierre['fecha_apertura'])),
            ':fecha_apertura_completa' => $cierre['fecha_apertura'],
            ':fecha_cierre' => $cierre['fecha_cierre']
        ]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener detalles de egresos de un cierre específico
     */
    public function obtenerDetallesEgresosCierre($cierre_id) {
        $cierre = $this->obtenerCierrePorId($cierre_id);
        if (!$cierre) {
            return [];
        }
        
        $sql = "SELECT * FROM egresos 
                WHERE DATE(fecha) >= DATE(:fecha_apertura)
                AND CONCAT(fecha, ' ', COALESCE(hora, '00:00:00')) >= :fecha_apertura_completa
                AND CONCAT(fecha, ' ', COALESCE(hora, '00:00:00')) <= :fecha_cierre
                ORDER BY fecha DESC, hora DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':fecha_apertura' => date('Y-m-d', strtotime($cierre['fecha_apertura'])),
            ':fecha_apertura_completa' => $cierre['fecha_apertura'],
            ':fecha_cierre' => $cierre['fecha_cierre']
        ]);
        return $stmt->fetchAll();
    }
    
    /**
     * Verificar si hay movimientos sin cerrar
     */
    public function hayMovimientosSinCerrar() {
        $fecha_apertura = $this->obtenerFechaAperturaActual();
        
        $sql = "SELECT COUNT(*) as total FROM ingresos 
                WHERE CONCAT(fecha, ' ', COALESCE(hora, '00:00:00')) >= :fecha_apertura";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':fecha_apertura' => $fecha_apertura]);
        $result = $stmt->fetch();
        
        return intval($result['total']) > 0;
    }
}
?>
