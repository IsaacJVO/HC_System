<?php
require_once __DIR__ . '/config/config.php';

// Script para corregir el estado de las habitaciones ocupadas

$conn = getConnection();

try {
    // Buscar todas las habitaciones con ocupaciones activas
    $sql = "SELECT DISTINCT hab.id, hab.numero, hab.estado
            FROM habitaciones hab
            INNER JOIN registro_ocupacion ro ON hab.id = ro.habitacion_id
            WHERE ro.estado = 'activo'";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $habitaciones_ocupadas = $stmt->fetchAll();
    
    echo "<h2>Corrigiendo estados de habitaciones:</h2>";
    echo "<pre>";
    
    foreach ($habitaciones_ocupadas as $hab) {
        echo "Habitación {$hab['numero']}: estado actual = '{$hab['estado']}'\n";
        
        // Si el estado no es 'ocupada', actualizarlo
        if ($hab['estado'] !== 'ocupada') {
            $sql_update = "UPDATE habitaciones SET estado = 'ocupada' WHERE id = :id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([':id' => $hab['id']]);
            
            echo "   ✅ Actualizado a 'ocupada'\n\n";
        } else {
            echo "   ✓ Ya está correcta\n\n";
        }
    }
    
    echo "\n=== CORRECCIÓN COMPLETADA ===\n";
    echo "</pre>";
    
    echo "<br><a href='views/habitaciones/estado.php'>→ Ver estado de habitaciones</a>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
