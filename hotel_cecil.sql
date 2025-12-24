-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-12-2025 a las 10:36:06
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
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nombre_completo` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` datetime DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
-- Usuario: Hotel Cecil
-- Contraseña: rodolfo106control (hasheada con PASSWORD_DEFAULT de PHP)
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `nombre_completo`, `activo`) VALUES
(1, 'Hotel Cecil', '$2y$10$KtVhWU3au1rkpbMUIX/UUu7QUYn0OviukezCp3EHyANfJB2Ykz6B2', 'Hotel Cecil - Administrador', 1);

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
(1, '102', 'Doble', 220.00, 'mantenimiento'),
(2, '103', 'Matrimonial', 220.00, 'disponible'),
(3, '104', 'Matrimonial', 220.00, 'disponible'),
(4, '201', 'Individual', 140.00, 'mantenimiento'),
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

--
-- Volcado de datos para la tabla `ingresos`
--

INSERT INTO `ingresos` (`id`, `ocupacion_id`, `concepto`, `monto`, `metodo_pago`, `fecha`, `hora`, `observaciones`) VALUES
(3, 14, 'Pago habitación 205 - 2 día(s)', 440.00, 'efectivo', '2025-12-15', '03:59:02', 'Ingreso automático por registro de huésped');

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

--
-- Volcado de datos para la tabla `registro_ocupacion`
--

INSERT INTO `registro_ocupacion` (`id`, `huesped_id`, `habitacion_id`, `nro_pieza`, `prox_destino`, `via_ingreso`, `fecha_ingreso`, `nro_dias`, `fecha_salida_estimada`, `fecha_salida_real`, `estado`) VALUES
(14, 11, 8, '205', 'Potosí', 'T', '2025-12-15', 2, '2025-12-17', '2025-12-20', 'finalizado');

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
-- Indices de la tabla `pagos_qr`
--
ALTER TABLE `pagos_qr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ocupacion_id` (`ocupacion_id`),
  ADD KEY `idx_fecha` (`fecha`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `huespedes`
--
ALTER TABLE `huespedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pagos_qr`
--
ALTER TABLE `pagos_qr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registro_ocupacion`
--
ALTER TABLE `registro_ocupacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- Filtros para la tabla `registro_ocupacion`
--
ALTER TABLE `registro_ocupacion`
  ADD CONSTRAINT `registro_ocupacion_ibfk_1` FOREIGN KEY (`huesped_id`) REFERENCES `huespedes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registro_ocupacion_ibfk_2` FOREIGN KEY (`habitacion_id`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
