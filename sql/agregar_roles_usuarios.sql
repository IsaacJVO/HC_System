-- ============================================
-- AGREGAR SISTEMA DE ROLES A USUARIOS
-- ============================================

-- 1. Agregar campo 'rol' a la tabla usuarios
ALTER TABLE `usuarios` ADD COLUMN `rol` ENUM('administrador', 'usuario') NOT NULL DEFAULT 'usuario' AFTER `nombre_completo`;

-- 2. Actualizar el usuario existente como administrador
UPDATE `usuarios` SET `rol` = 'administrador' WHERE `id` = 1;

-- 3. Insertar los nuevos usuarios con sus roles correspondientes

-- Isaac Vargas - Usuario común (recepcionista)
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre_completo`, `rol`, `activo`) VALUES
(2, 'Isaac Vargas', '$2y$10$Cy/hv5u8LKYkQpcpXbFwHeWpRwCL4j4iZfYCGqbznO3r3luwHNyna', 'Isaac Vargas', 'usuario', 1);

-- Rodrigo Moscoso - Administrador
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre_completo`, `rol`, `activo`) VALUES
(3, 'Rodrigo Moscoso', '$2y$10$araBXv3y3anM6qj3M8haDO9qK2AHUgWDNz4YWiSIuidOpmfkVJL9S', 'Rodrigo Moscoso', 'administrador', 1);

-- Claudia Limpieza - Usuario común (limpieza)
INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre_completo`, `rol`, `activo`) VALUES
(4, 'Claudia Limpieza', '$2y$10$Phf7aIZaogQwdS.fFvpa4e28SkxLOOiGGmdz5MohF2heCj1EAQn9q', 'Claudia Limpieza', 'usuario', 1);

-- ============================================
-- REFERENCIA DE USUARIOS Y CONTRASEÑAS
-- ============================================
-- ID 1: Hotel Cecil (administrador) - rodolfo106control
-- ID 2: Isaac Vargas (usuario) - nisanrecepcion106
-- ID 3: Rodrigo Moscoso (administrador) - rodrigo106control
-- ID 4: Claudia Limpieza (usuario) - claudia106
-- ============================================
