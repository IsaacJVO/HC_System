-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-12-2025 a las 02:56:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hotel_cecil`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `egresos`
--

CREATE TABLE `egresos` (
  `id` int(11) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id` int(11) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `precio_dia` decimal(10,2) NOT NULL,
  `estado` enum('disponible','ocupada','limpieza','mantenimiento') DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id`, `numero`, `tipo`, `precio_dia`, `estado`) VALUES
(1, '102', 'Doble', 220.00, 'disponible'),
(2, '103', 'Matrimonial', 220.00, 'disponible'),
(3, '104', 'Matrimonial', 220.00, 'disponible'),
(4, '201', 'Individual', 140.00, 'disponible'),
(5, '202', 'Individual', 140.00, 'disponible'),
(6, '203', 'Individual', 140.00, 'disponible'),
(7, '204', 'Individual', 140.00, 'disponible'),
(8, '205', 'Matrimonial', 220.00, 'disponible'),
(9, '206', 'Doble', 220.00, 'disponible'),
(10, '207', 'Triple', 300.00, 'disponible'),
(11, '208', 'Familiar', 320.00, 'disponible'),
(12, '209', 'Triple', 300.00, 'disponible'),
(13, '301', 'Suite', 340.00, 'disponible'),
(14, '302', 'Doble', 220.00, 'disponible'),
(15, '303', 'Doble', 220.00, 'disponible'),
(16, '304', 'Doble', 220.00, 'disponible'),
(17, '305', 'Triple', 300.00, 'disponible'),
(18, '306', 'Matrimonial', 220.00, 'disponible'),
(19, '307', 'Suite', 340.00, 'disponible');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `huespedes`
--

CREATE TABLE `huespedes` (
  `id` int(11) NOT NULL,
  `nombres_apellidos` varchar(255) NOT NULL,
  `genero` enum('M','F') NOT NULL,
  `edad` int(11) NOT NULL,
  `estado_civil` varchar(50) DEFAULT NULL,
  `nacionalidad` varchar(100) NOT NULL,
  `ci_pasaporte` varchar(100) NOT NULL,
  `profesion` varchar(150) DEFAULT NULL,
  `objeto` varchar(255) DEFAULT NULL,
  `procedencia` varchar(150) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `huespedes`
--

INSERT INTO `huespedes` (`id`, `nombres_apellidos`, `genero`, `edad`, `estado_civil`, `nacionalidad`, `ci_pasaporte`, `profesion`, `objeto`, `procedencia`, `fecha_registro`) VALUES
(11, 'Isaac Vargas Oropeza', 'M', 20, 'S', 'Boliviano', '12642012', 'Estudiante', 'Turismo', 'Cochabamba', '2025-12-20 07:59:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingresos`
--

CREATE TABLE `ingresos` (
  `id` int(11) NOT NULL,
  `ocupacion_id` int(11) DEFAULT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('efectivo','qr','tarjeta','otro') DEFAULT 'efectivo',
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_habitaciones`
--

CREATE TABLE `inventario_habitaciones` (
  `id` int(11) NOT NULL,
  `habitacion_numero` varchar(10) NOT NULL,
  `tipo` varchar(20) DEFAULT 'habitacion',
  `cortinas` int(11) DEFAULT 0,
  `veladores` int(11) DEFAULT 0,
  `roperos` int(11) DEFAULT 0,
  `colgadores` int(11) DEFAULT 0,
  `basureros` int(11) DEFAULT 0,
  `shampoo` int(11) DEFAULT 0,
  `jabon_liquido` int(11) DEFAULT 0,
  `sillas` int(11) DEFAULT 0,
  `sillones` int(11) DEFAULT 0,
  `alfombras` int(11) DEFAULT 0,
  `camas` int(11) DEFAULT 0,
  `television` int(11) DEFAULT 0,
  `lamparas` int(11) DEFAULT 0,
  `manteles` int(11) DEFAULT 0,
  `cubrecamas` int(11) DEFAULT 0,
  `sabanas_media_plaza` int(11) DEFAULT 0,
  `sabanas_doble_plaza` int(11) DEFAULT 0,
  `almohadas` int(11) DEFAULT 0,
  `fundas` int(11) DEFAULT 0,
  `frazadas` int(11) DEFAULT 0,
  `toallas` int(11) DEFAULT 0,
  `cortinas_almacen` int(11) DEFAULT 0,
  `alfombras_almacen` int(11) DEFAULT 0,
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventario_habitaciones`
--

INSERT INTO `inventario_habitaciones` (`id`, `habitacion_numero`, `tipo`, `cortinas`, `veladores`, `roperos`, `colgadores`, `basureros`, `shampoo`, `jabon_liquido`, `sillas`, `sillones`, `alfombras`, `camas`, `television`, `lamparas`, `manteles`, `cubrecamas`, `sabanas_media_plaza`, `sabanas_doble_plaza`, `almohadas`, `fundas`, `frazadas`, `toallas`, `cortinas_almacen`, `alfombras_almacen`, `ultima_actualizacion`) VALUES
(1, 'ALMACEN', 'almacen', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-28 00:38:19'),
(2, '102', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-28 00:38:37'),
(3, '103', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(4, '104', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(5, '201', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(6, '202', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(7, '203', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(8, '204', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(9, '205', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(10, '206', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(11, '207', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(12, '208', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(13, '209', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(14, '301', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(15, '302', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(16, '303', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(17, '304', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(18, '305', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(19, '306', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23'),
(20, '307', 'habitacion', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2025-12-24 05:42:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimientos`
--

CREATE TABLE `mantenimientos` (
  `id` int(11) NOT NULL,
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
  `imagen` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_qr`
--

CREATE TABLE `pagos_qr` (
  `id` int(11) NOT NULL,
  `ocupacion_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `numero_transaccion` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_garaje`
--

CREATE TABLE `registro_garaje` (
  `id` int(11) NOT NULL,
  `ocupacion_id` int(11) NOT NULL,
  `huesped_nombre` varchar(255) NOT NULL,
  `fecha` date NOT NULL,
  `costo` decimal(10,2) NOT NULL DEFAULT 10.00,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_ocupacion`
--

CREATE TABLE `registro_ocupacion` (
  `id` int(11) NOT NULL,
  `huesped_id` int(11) NOT NULL,
  `habitacion_id` int(11) NOT NULL,
  `nro_pieza` varchar(10) NOT NULL,
  `prox_destino` varchar(150) DEFAULT NULL,
  `via_ingreso` varchar(50) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `nro_dias` int(11) NOT NULL,
  `fecha_salida_estimada` date DEFAULT NULL,
  `fecha_salida_real` date DEFAULT NULL,
  `estado` enum('activo','finalizado') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(255) DEFAULT NULL,
  `rol` enum('administrador','usuario') NOT NULL DEFAULT 'usuario',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre_completo`, `rol`, `fecha_creacion`, `ultimo_acceso`, `activo`) VALUES
(1, 'Hotel Cecil', '$2y$10$KtVhWU3au1rkpbMUIX/UUu7QUYn0OviukezCp3EHyANfJB2Ykz6B2', 'Hotel Cecil - Administrador', 'administrador', '2025-12-24 00:21:00', '2025-12-27 19:55:22', 1),
(2, 'Isaac Vargas', '$2y$10$Cy/hv5u8LKYkQpcpXbFwHeWpRwCL4j4iZfYCGqbznO3r3luwHNyna', 'Isaac Vargas', 'usuario', '2025-12-24 23:29:05', '2025-12-26 21:16:07', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `egresos`
--
ALTER TABLE `egresos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_categoria` (`categoria`);

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `huespedes`
--
ALTER TABLE `huespedes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ci_pasaporte` (`ci_pasaporte`),
  ADD KEY `idx_ci` (`ci_pasaporte`),
  ADD KEY `idx_nombres` (`nombres_apellidos`);

--
-- Indices de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ocupacion_id` (`ocupacion_id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_metodo_pago` (`metodo_pago`);

--
-- Indices de la tabla `inventario_habitaciones`
--
ALTER TABLE `inventario_habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `habitacion_numero` (`habitacion_numero`),
  ADD KEY `idx_habitacion` (`habitacion_numero`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_habitacion` (`habitacion_numero`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_prioridad` (`prioridad`);

--
-- Indices de la tabla `pagos_qr`
--
ALTER TABLE `pagos_qr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ocupacion_id` (`ocupacion_id`),
  ADD KEY `idx_fecha` (`fecha`);

--
-- Indices de la tabla `registro_garaje`
--
ALTER TABLE `registro_garaje`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_ocupacion` (`ocupacion_id`);

--
-- Indices de la tabla `registro_ocupacion`
--
ALTER TABLE `registro_ocupacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `huesped_id` (`huesped_id`),
  ADD KEY `habitacion_id` (`habitacion_id`),
  ADD KEY `idx_fecha_ingreso` (`fecha_ingreso`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `egresos`
--
ALTER TABLE `egresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `huespedes`
--
ALTER TABLE `huespedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `inventario_habitaciones`
--
ALTER TABLE `inventario_habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `pagos_qr`
--
ALTER TABLE `pagos_qr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `registro_garaje`
--
ALTER TABLE `registro_garaje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `registro_ocupacion`
--
ALTER TABLE `registro_ocupacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ingresos`
--
ALTER TABLE `ingresos`
  ADD CONSTRAINT `ingresos_ibfk_1` FOREIGN KEY (`ocupacion_id`) REFERENCES `registro_ocupacion` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pagos_qr`
--
ALTER TABLE `pagos_qr`
  ADD CONSTRAINT `pagos_qr_ibfk_1` FOREIGN KEY (`ocupacion_id`) REFERENCES `registro_ocupacion` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `registro_garaje`
--
ALTER TABLE `registro_garaje`
  ADD CONSTRAINT `registro_garaje_ibfk_1` FOREIGN KEY (`ocupacion_id`) REFERENCES `registro_ocupacion` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registro_ocupacion`
--
ALTER TABLE `registro_ocupacion`
  ADD CONSTRAINT `registro_ocupacion_ibfk_1` FOREIGN KEY (`huesped_id`) REFERENCES `huespedes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registro_ocupacion_ibfk_2` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
