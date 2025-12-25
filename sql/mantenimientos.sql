-- ============================================
-- TABLA DE MANTENIMIENTOS DE HABITACIONES
-- ============================================

CREATE TABLE IF NOT EXISTS `mantenimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `habitacion_numero` varchar(10) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text NOT NULL,
  `prioridad` enum('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  `tipo` enum('preventivo','correctivo','emergencia') NOT NULL DEFAULT 'correctivo',
  `estado` enum('pendiente','en_proceso','completado','cancelado') NOT NULL DEFAULT 'pendiente',
  `costo_estimado` decimal(10,2) DEFAULT NULL,
  `costo_real` decimal(10,2) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin_estimada` date DEFAULT NULL,
  `fecha_fin_real` date DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_habitacion` (`habitacion_numero`),
  KEY `idx_estado` (`estado`),
  KEY `idx_prioridad` (`prioridad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS DE EJEMPLO
-- ============================================

-- Mantenimiento urgente ejemplo
INSERT INTO `mantenimientos` (`habitacion_numero`, `titulo`, `descripcion`, `prioridad`, `tipo`, `estado`, `fecha_inicio`, `responsable`) VALUES
('201', 'Reparación de tubería del baño', 'Fuga de agua en la tubería del lavabo. Requiere cambio de tubería completa.', 'urgente', 'emergencia', 'en_proceso', CURDATE(), 'Juan Pérez'),
('103', 'Cambio de cerradura', 'La cerradura de la puerta principal no funciona correctamente. Necesita reemplazo.', 'alta', 'correctivo', 'pendiente', CURDATE(), NULL),
('307', 'Pintura y retoque de paredes', 'Mantenimiento preventivo anual. Pintura de paredes y retoque de áreas dañadas.', 'media', 'preventivo', 'pendiente', DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL);
