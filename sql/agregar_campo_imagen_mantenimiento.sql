-- ============================================
-- AGREGAR CAMPO DE IMAGEN A MANTENIMIENTOS
-- ============================================
-- Ejecutar este script para agregar el campo de imagen opcional
-- a la tabla de mantenimientos

ALTER TABLE `mantenimientos` 
ADD COLUMN `imagen` VARCHAR(255) NULL DEFAULT NULL AFTER `observaciones`;

-- Comentario explicativo:
-- Este campo almacenará la ruta relativa de la imagen desde assets/img/Mantenimiento/
-- Ejemplo: "fuga_agua_201_20251226_143025.jpg"
-- Si es NULL, significa que el mantenimiento no tiene imagen asociada
