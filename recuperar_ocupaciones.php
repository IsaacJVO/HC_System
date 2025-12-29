<?php
require_once __DIR__ . '/config/config.php';

// Script para recuperar ocupaciones finalizadas incorrectamente por el bug

$conn = getConnection();

try {
    // Buscar ocupaciones finalizadas hoy que deberían estar activas
    // (fecha de salida estimada >= hoy)
    $sql = "SELECT ro.*, hab.numero as habitacion_numero
            FROM registro_ocupacion ro
            INNER JOIN habitaciones hab ON ro.habitacion_id = hab.id
            WHERE ro.estado = 'finalizado'
            AND ro.fecha_salida_real = CURDATE()
            AND ro.fecha_salida_estimada >= CURDATE()
            ORDER BY ro.id DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $ocupaciones = $stmt->fetchAll();
    
    echo "<h2>Ocupaciones a recuperar:</h2>";
    echo "<pre>";
    
    if (empty($ocupaciones)) {
        echo "No se encontraron ocupaciones para recuperar.\n";
    } else {
        foreach ($ocupaciones as $ocup) {
            echo "ID: {$ocup['id']} - Habitación: {$ocup['habitacion_numero']} - Huésped ID: {$ocup['huesped_id']}\n";
            echo "   Fecha ingreso: {$ocup['fecha_ingreso']} - Fecha salida estimada: {$ocup['fecha_salida_estimada']}\n";
            echo "   Días: {$ocup['nro_dias']}\n\n";
            
            // Reactivar ocupación
            $sql_update = "UPDATE registro_ocupacion 
                          SET estado = 'activo', fecha_salida_real = NULL 
                          WHERE id = :id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([':id' => $ocup['id']]);
            
            // Cambiar habitación a ocupado
            $sql_hab = "UPDATE habitaciones SET estado = 'ocupado' WHERE id = :hab_id";
            $stmt_hab = $conn->prepare($sql_hab);
            $stmt_hab->execute([':hab_id' => $ocup['habitacion_id']]);
            
            echo "   ✅ RECUPERADO - Estado cambiado a 'activo' y habitación a 'ocupado'\n\n";
        }
        
        echo "\n=== RECUPERACIÓN COMPLETADA ===\n";
        echo "Total recuperados: " . count($ocupaciones) . "\n";
    }
    
    echo "</pre>";
    
    echo "<br><a href='index.php'>← Volver al inicio</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
