<?php
require_once __DIR__ . '/../config/config.php';

class Finanzas {
    private $conn;
    
    public function __construct() {
        $this->conn = getConnection();
    }
    
    // INGRESOS
    public function registrarIngreso($datos) {
        $sql = "INSERT INTO ingresos (ocupacion_id, concepto, monto, metodo_pago, fecha, hora, observaciones)
                VALUES (:ocupacion_id, :concepto, :monto, :metodo_pago, :fecha, :hora, :observaciones)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':ocupacion_id' => $datos['ocupacion_id'] ?? null,
            ':concepto' => $datos['concepto'],
            ':monto' => $datos['monto'],
            ':metodo_pago' => $datos['metodo_pago'],
            ':fecha' => $datos['fecha'],
            ':hora' => $datos['hora'] ?? date('H:i:s'),
            ':observaciones' => $datos['observaciones'] ?? null
        ]);
    }
    
    public function obtenerIngresos($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT i.*, ro.nro_pieza, h.nombres_apellidos 
                FROM ingresos i
                LEFT JOIN registro_ocupacion ro ON i.ocupacion_id = ro.id
                LEFT JOIN huespedes h ON ro.huesped_id = h.id
                WHERE 1=1";
        
        $params = [];
        if ($fecha_inicio) {
            $sql .= " AND i.fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        if ($fecha_fin) {
            $sql .= " AND i.fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        $sql .= " ORDER BY i.fecha DESC, i.hora DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // EGRESOS
    public function registrarEgreso($datos) {
        $sql = "INSERT INTO egresos (concepto, monto, categoria, fecha, hora, observaciones)
                VALUES (:concepto, :monto, :categoria, :fecha, :hora, :observaciones)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':concepto' => $datos['concepto'],
            ':monto' => $datos['monto'],
            ':categoria' => $datos['categoria'] ?? null,
            ':fecha' => $datos['fecha'],
            ':hora' => $datos['hora'] ?? date('H:i:s'),
            ':observaciones' => $datos['observaciones'] ?? null
        ]);
    }
    
    public function obtenerEgresos($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT * FROM egresos WHERE 1=1";
        
        $params = [];
        if ($fecha_inicio) {
            $sql .= " AND fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        if ($fecha_fin) {
            $sql .= " AND fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        $sql .= " ORDER BY fecha DESC, hora DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // PAGOS QR
    public function registrarPagoQR($datos) {
        $sql = "INSERT INTO pagos_qr (ocupacion_id, monto, fecha, hora, numero_transaccion, observaciones)
                VALUES (:ocupacion_id, :monto, :fecha, :hora, :numero_transaccion, :observaciones)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':ocupacion_id' => $datos['ocupacion_id'] ?? null,
            ':monto' => $datos['monto'],
            ':fecha' => $datos['fecha'],
            ':hora' => $datos['hora'] ?? date('H:i:s'),
            ':numero_transaccion' => $datos['numero_transaccion'] ?? null,
            ':observaciones' => $datos['observaciones'] ?? null
        ]);
    }
    
    public function obtenerPagosQR($fecha_inicio = null, $fecha_fin = null) {
        $sql = "SELECT pqr.*, ro.nro_pieza, h.nombres_apellidos
                FROM pagos_qr pqr
                LEFT JOIN registro_ocupacion ro ON pqr.ocupacion_id = ro.id
                LEFT JOIN huespedes h ON ro.huesped_id = h.id
                WHERE 1=1";
        
        $params = [];
        if ($fecha_inicio) {
            $sql .= " AND pqr.fecha >= :fecha_inicio";
            $params[':fecha_inicio'] = $fecha_inicio;
        }
        if ($fecha_fin) {
            $sql .= " AND pqr.fecha <= :fecha_fin";
            $params[':fecha_fin'] = $fecha_fin;
        }
        
        $sql .= " ORDER BY pqr.fecha DESC, pqr.hora DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    // EDICIÓN Y GESTIÓN DE INGRESOS
    public function obtenerIngresoPorId($id) {
        $sql = "SELECT i.*, ro.nro_pieza, h.nombres_apellidos 
                FROM ingresos i
                LEFT JOIN registro_ocupacion ro ON i.ocupacion_id = ro.id
                LEFT JOIN huespedes h ON ro.huesped_id = h.id
                WHERE i.id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function editarMetodoPagoIngreso($id, $metodo_pago_nuevo, $numero_transaccion = null) {
        try {
            $this->conn->beginTransaction();
            
            // Obtener el ingreso actual
            $ingreso = $this->obtenerIngresoPorId($id);
            if (!$ingreso) {
                throw new Exception("Ingreso no encontrado");
            }
            
            $metodo_anterior = $ingreso['metodo_pago'];
            
            // Actualizar el método de pago en ingresos
            $sql = "UPDATE ingresos SET metodo_pago = :metodo_pago WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':metodo_pago' => $metodo_pago_nuevo,
                ':id' => $id
            ]);
            
            // Gestionar tabla pagos_qr
            if ($metodo_anterior === 'qr' && $metodo_pago_nuevo !== 'qr') {
                // Eliminar de pagos_qr si se cambió de QR a otro método
                $sql = "DELETE FROM pagos_qr WHERE ocupacion_id = :ocupacion_id 
                        AND monto = :monto AND fecha = :fecha";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':ocupacion_id' => $ingreso['ocupacion_id'],
                    ':monto' => $ingreso['monto'],
                    ':fecha' => $ingreso['fecha']
                ]);
            } elseif ($metodo_anterior !== 'qr' && $metodo_pago_nuevo === 'qr') {
                // Agregar a pagos_qr si se cambió a QR desde otro método
                $datos_qr = [
                    'ocupacion_id' => $ingreso['ocupacion_id'],
                    'monto' => $ingreso['monto'],
                    'fecha' => $ingreso['fecha'],
                    'hora' => $ingreso['hora'],
                    'numero_transaccion' => $numero_transaccion,
                    'observaciones' => 'Cambio de método de pago desde ' . strtoupper($metodo_anterior)
                ];
                $this->registrarPagoQR($datos_qr);
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error en editarMetodoPagoIngreso: " . $e->getMessage());
            return false;
        }
    }
    
    public function obtenerIngresosPendientes() {
        $sql = "SELECT i.*, ro.nro_pieza, h.nombres_apellidos, h.ci_pasaporte
                FROM ingresos i
                LEFT JOIN registro_ocupacion ro ON i.ocupacion_id = ro.id
                LEFT JOIN huespedes h ON ro.huesped_id = h.id
                WHERE i.metodo_pago = 'pendiente'
                ORDER BY i.fecha DESC, i.hora DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function completarPagoPendiente($id, $metodo_pago, $numero_transaccion = null) {
        // Usar la función de editar método de pago
        return $this->editarMetodoPagoIngreso($id, $metodo_pago, $numero_transaccion);
    }
    
    // RESUMEN
    public function obtenerResumen($fecha_inicio, $fecha_fin) {
        $resumen = [
            'total_ingresos' => 0,
            'total_egresos' => 0,
            'total_qr' => 0,
            'balance' => 0
        ];
        
        // Total ingresos
        $sql = "SELECT SUM(monto) as total FROM ingresos WHERE fecha BETWEEN :fi AND :ff";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':fi' => $fecha_inicio, ':ff' => $fecha_fin]);
        $result = $stmt->fetch();
        $resumen['total_ingresos'] = $result['total'] ?? 0;
        
        // Total egresos
        $sql = "SELECT SUM(monto) as total FROM egresos WHERE fecha BETWEEN :fi AND :ff";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':fi' => $fecha_inicio, ':ff' => $fecha_fin]);
        $result = $stmt->fetch();
        $resumen['total_egresos'] = $result['total'] ?? 0;
        
        // Total QR
        $sql = "SELECT SUM(monto) as total FROM pagos_qr WHERE fecha BETWEEN :fi AND :ff";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':fi' => $fecha_inicio, ':ff' => $fecha_fin]);
        $result = $stmt->fetch();
        $resumen['total_qr'] = $result['total'] ?? 0;
        
        $resumen['balance'] = $resumen['total_ingresos'] - $resumen['total_egresos'];
        
        return $resumen;
    }
}
?>
