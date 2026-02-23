<?php
/**
 * Script de migración: Agregar campo 'concepto' a tabla pagos_qr
 * Permite registrar cobros externos no vinculados a huéspedes
 * 
 * Ejecutar una sola vez
 */

require_once __DIR__ . '/config/config.php';

echo "<h2>Migración de Base de Datos - Pagos QR</h2>";
echo "<hr>";

try {
    $conn = getConnection();
    
    // Verificar si el campo 'concepto' ya existe
    $sql = "SHOW COLUMNS FROM pagos_qr LIKE 'concepto'";
    $stmt = $conn->query($sql);
    
    if ($stmt->rowCount() == 0) {
        // El campo no existe, agregarlo
        echo "<p style='color: blue;'>⏳ Agregando campo 'concepto'...</p>";
        
        $sql = "ALTER TABLE pagos_qr 
                ADD COLUMN concepto VARCHAR(255) NULL AFTER observaciones";
        $conn->exec($sql);
        
        echo "<p style='color: green;'>✓ Campo 'concepto' agregado exitosamente</p>";
    } else {
        echo "<p style='color: gray;'>ℹ El campo 'concepto' ya existe</p>";
    }
    
    // Verificar si el campo 'tipo' ya existe
    $sql = "SHOW COLUMNS FROM pagos_qr LIKE 'tipo'";
    $stmt = $conn->query($sql);
    
    if ($stmt->rowCount() == 0) {
        // El campo no existe, agregarlo
        echo "<p style='color: blue;'>⏳ Agregando campo 'tipo'...</p>";
        
        $sql = "ALTER TABLE pagos_qr 
                ADD COLUMN tipo ENUM('huesped', 'externo') DEFAULT 'huesped' AFTER concepto";
        $conn->exec($sql);
        
        echo "<p style='color: green;'>✓ Campo 'tipo' agregado exitosamente</p>";
    } else {
        echo "<p style='color: gray;'>ℹ El campo 'tipo' ya existe</p>";
    }
    
    echo "<hr>";
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ Migración completada exitosamente</p>";
    echo "<p>Ahora puedes registrar cobros externos por QR</p>";
    echo "<br><a href='views/finanzas/pagos_qr.php' style='padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 5px;'>Ir a Pagos QR</a>";
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-weight: bold;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Si el error persiste, ejecuta manualmente el archivo EJECUTAR_MIGRACION.sql en phpMyAdmin</p>";
}
?>
