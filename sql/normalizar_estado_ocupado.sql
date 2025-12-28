-- ============================================
-- NORMALIZAR ESTADO DE HABITACIONES OCUPADAS
-- ============================================
-- Este script asegura que las habitaciones 204 y 205 estén ocupadas

-- Verificar estado actual de las habitaciones 204 y 205
SELECT numero, estado, tipo
FROM habitaciones 
WHERE numero IN ('204', '205');

-- Cambiar el estado a 'ocupada' para las habitaciones 204 y 205
UPDATE habitaciones 
SET estado = 'ocupada' 
WHERE numero IN ('204', '205');

-- Verificar el cambio
SELECT numero, estado, tipo
FROM habitaciones 
WHERE numero IN ('204', '205');

-- Ver todas las habitaciones ocupadas
SELECT numero, estado, tipo
FROM habitaciones 
WHERE estado IN ('ocupada', 'ocupado')
ORDER BY numero;
