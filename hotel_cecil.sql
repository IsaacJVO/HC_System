-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-02-2026 a las 04:23:59
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

--
-- Volcado de datos para la tabla `egresos`
--

INSERT INTO `egresos` (`id`, `concepto`, `monto`, `categoria`, `fecha`, `hora`, `observaciones`) VALUES
(12, 'Se compro bolsitas de bicarbonato de farmacia', 20.00, 'Externo', '2026-02-08', '21:32:38', NULL);

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
(3, '104', 'Matrimonial', 220.00, 'ocupada'),
(4, '201', 'Individual', 140.00, 'ocupada'),
(5, '202', 'Individual', 140.00, 'disponible'),
(6, '203', 'Individual', 140.00, 'limpieza'),
(7, '204', 'Individual', 140.00, 'ocupada'),
(8, '205', 'Matrimonial', 220.00, 'limpieza'),
(9, '206', 'Doble', 220.00, 'limpieza'),
(10, '207', 'Triple', 300.00, 'limpieza'),
(11, '208', 'Familiar', 320.00, 'limpieza'),
(12, '209', 'Triple', 300.00, 'limpieza'),
(13, '301', 'Suite', 340.00, 'disponible'),
(14, '302', 'Doble', 220.00, 'disponible'),
(15, '303', 'Doble', 220.00, 'limpieza'),
(16, '304', 'Doble', 220.00, 'disponible'),
(17, '305', 'Triple', 300.00, 'disponible'),
(18, '306', 'Matrimonial', 220.00, 'limpieza'),
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
(29, 'Alvaro Antonio Arias Antequera', 'M', 38, 'S', 'Boliviano', '6778333', 'Abogado', 'Trabajo', 'La Paz', '2026-01-16 05:44:25'),
(30, 'David Sulca Contreras', 'M', 53, 'S', 'Boliviano', '1147411', 'Chofer', 'Trabajo', 'Tarija', '2026-01-16 06:07:52'),
(31, 'Jorge Carlos Armella Jurado', 'M', 35, 'Soltero/a', 'Boliviano', '7257092', 'Ing. Agrónomo', 'Otro', 'Tarija', '2026-01-16 06:07:52'),
(32, 'Jaime Garcia Torres', 'M', 61, 'D', 'Boliviano', '3134636', 'Abogado', 'Paso', 'Cochabamba', '2026-01-16 06:16:25'),
(33, 'Dulia Elizabeth Vera Castro', 'F', 42, 'Soltero/a', 'Boliviano', '6458951', 'Ama de Casa', 'Otro', 'Cochabamba', '2026-01-16 06:16:25'),
(34, 'Juan Carlos Ortega Pinto', 'M', 49, 'S', 'Boliviano', '3818648', 'Estudiante', 'Paso', 'Cochabamba', '2026-01-16 06:31:05'),
(35, 'Marlene Gaspar Tohara', 'F', 51, 'S', 'Boliviano', '3971204', 'Comerciante', 'Familiar', 'Potosí', '2026-01-16 06:42:58'),
(36, 'Wendy Micaela Gaspar', 'F', 23, 'Soltero/a', 'Boliviano', '14716783', 'Estudiante', 'Familiar', 'Potosí', '2026-01-16 06:42:58'),
(37, 'Silvia Gaspar Tohara', 'F', 41, 'S', 'Boliviano', '6612154', 'Abogada', 'Familiar', 'Potosí', '2026-01-26 04:22:52'),
(38, 'David Ramiro Leon Paco', 'M', 42, 'Soltero/a', 'Boliviano', '6571094', 'Estudiante', 'Familiar', 'Potosí', '2026-01-26 04:22:52'),
(39, 'Andre Fabian Leon Gaspar', 'M', 5, 'Soltero/a', 'Boliviano', '16429965', 'Niño', 'Familiar', 'Potosí', '2026-01-26 04:22:52'),
(40, 'Leonel Maximliano Leon Gaspar', 'M', 11, 'Soltero/a', 'Boliviano', '14024978', 'Niño', 'Familiar', 'Potosí', '2026-01-26 04:22:52'),
(41, 'Carlos Andres Lopez Noguera', 'M', 47, 'S', 'Boliviano', '3656491', 'Ing. Civil', 'Turismo', 'Santa Curz', '2026-01-26 04:47:06'),
(42, 'Beatriz Ayda Daza Barrero', 'F', 37, 'Soltero/a', 'Boliviano', '7472918', 'Estudiante', 'Turismo', 'Santa Cruz', '2026-01-26 04:47:06'),
(43, 'Rufino Pasquito Tarumbara', 'M', 44, 'S', 'Boliviano', '5897582', 'Lic. en Ciencias De La Comunicación', 'Turismo', 'Santa Curz', '2026-01-26 04:53:33'),
(44, 'Lilian Cortez Palomo', 'F', 42, 'Soltero/a', 'Boliviano', '6239610', 'Universitaria', 'Turismo', 'Santa Cruz', '2026-01-26 04:53:33'),
(45, 'Roberto Carlos Acha Vasquez', 'M', 26, '', 'Boliviano', '6643920', 'Estudiante', 'Paso', 'Potosí', '2026-01-26 05:02:26'),
(46, 'Ernesto Alejandro Achacollo Zarraga', 'M', 46, 'S', 'Boliviano', '4094130', 'Cirujano Odontologo', 'Paso', 'Oruro', '2026-01-26 05:05:51'),
(47, 'Elizabeth Huarachi Alvarez', 'F', 26, 'S', 'Boliviano', '9434224', 'Estudiante', 'Paso', 'Cochabamba', '2026-01-26 05:22:30'),
(48, 'Katherine Susana Cordero Martinez', 'F', 20, 'S', 'Boliviano', '10663410', 'Estudiante', 'Paso', 'Tarija', '2026-01-26 05:35:54'),
(49, 'Loida Martinez Martinez', 'F', 43, 'S', 'Boliviano', '5685263', 'Servidora Publica', 'Paso', 'Tarija', '2026-01-26 05:50:17'),
(50, 'Erick Alejandro Serrano Martinez', 'M', 7, 'Soltero/a', 'Boliviano', '15541239', 'Niño', 'Otro', 'Tarija', '2026-01-26 05:50:17'),
(51, 'Jimmy Willy Serrano Perez', 'M', 46, 'Soltero/a', 'Boliviano', '4094576', 'Estudiante', 'Otro', 'Tarija', '2026-01-26 05:50:17'),
(52, 'Mijael Gomez Ramos', 'M', 19, 'S', 'Boliviano', '14025672', 'Estudiante', 'Paso', 'Potosí', '2026-02-01 14:10:22'),
(53, 'Edgar Estrada Condori', 'M', 36, 'S', 'Boliviano', '8576372', 'Estudiante', 'Paso', 'Potosí', '2026-02-01 14:12:04'),
(54, 'Javier Chumacero Barrios', 'M', 35, 'S', 'Boliviano', '7573025', 'Chofer', 'Paso', 'Potosí', '2026-02-01 14:14:01'),
(55, 'Mariela Vivian Quiroz Crespo', 'F', 46, 'S', 'Boliviano', '3818688', 'Independiente', 'Paso', 'Cochabamba', '2026-02-01 17:20:55'),
(56, 'Lucas Esteban Salamanca', 'M', 47, 'Soltero/a', 'Boliviano', '3594394', 'Independiente', 'Turismo', 'Cochabamba', '2026-02-01 17:20:55'),
(57, 'Natalia Luciana Salamanca Quiroz', 'F', 19, 'S', 'Boliviano', '8055823', 'Estudiante', 'Turismo', 'Cochabamba', '2026-02-01 18:17:51'),
(58, 'Ariel y Lucia Salamanca Salinas', 'F', 22, 'Soltero/a', 'Boliviano', '6555816', 'Estudiante', 'Turismo', 'Cochabamba', '2026-02-01 18:17:51'),
(59, 'Juan Carlos Cordova Rojas', 'M', 46, 'S', 'Boliviano', '4434268', 'Mecánico', 'Turismo', 'Santa Cruz', '2026-02-01 18:45:08'),
(60, 'Carlos Taiwa Cordova Torrez', 'M', 19, 'Soltero/a', 'Boliviano', '13164332', 'Estudiante', 'Turismo', 'Santa Cruz', '2026-02-01 18:45:08'),
(61, 'Brisa Adai Cordova Torrez', 'F', 15, 'Soltero/a', 'Boliviano', '14510838', 'Niña', 'Turismo', 'Santa Cruz', '2026-02-01 18:45:08'),
(62, 'Rocio Verastegui Berrios', 'F', 44, 'C', 'Boliviano', '4051731', 'Médico Cirujano', 'Turismo', 'Oruro', '2026-02-01 19:53:44'),
(63, 'Luz Camila Hoyos Verastegui', 'F', 20, 'Soltero/a', 'Boliviano', '12709751', 'Estudiante', 'Turismo', 'Oruro', '2026-02-01 19:53:44'),
(64, 'Alex Fernandez Valenzuela', 'M', 25, 'S', 'Boliviano', '8786365', 'Mecánico', 'Turismo', 'Tarija', '2026-02-01 20:23:57'),
(65, 'Victoria Siacara Vargas', 'F', 26, 'Soltero/a', 'Boliviano', '12430026', 'Estudiante', 'Turismo', 'Tarija', '2026-02-01 20:23:57'),
(66, 'Luis Fernando Ruiz Moreno', 'M', 47, 'S', 'Boliviano', '4606662', 'Mecánico', 'Turismo', 'Santa Cruz', '2026-02-01 20:34:22'),
(67, 'Sandra Liliana Suarez Medrano', 'F', 40, 'Soltero/a', 'Boliviano', '6305366', 'Estudiante', 'Turismo', 'Santa Cruz', '2026-02-01 20:34:22'),
(68, 'Diego Sebastian Vaca', 'M', 46, 'S', 'Argentino', 'E-11651548', 'Músico', 'Concierto', 'La Paz', '2026-02-01 21:52:13'),
(69, 'Carlos Arando Puma', 'M', 41, 'Soltero/a', 'Boliviano', '8549281', 'Músico', 'Otro', 'La Paz', '2026-02-01 21:52:13'),
(70, 'Limbert Ademar Vargas Flores', 'M', 41, 'S', 'Boliviano', '5998206', 'Cantante', 'Concierto', 'La Paz', '2026-02-01 22:06:23'),
(71, 'Roberto Marin Monte', 'M', 48, 'Soltero/a', 'Argentina', '25.932.768', 'Músico', 'Otro', 'La Paz', '2026-02-01 22:06:23'),
(72, 'Silvio  Marcelo Zapana', 'M', 48, 'S', 'Argentino', 'E-11496603', 'Músico', 'Concierto', 'La Paz', '2026-02-01 22:11:51'),
(73, 'Fernando Rodriguez Huchani', 'M', 50, 'S', 'Boliviano', '4378113', 'Chofer', 'Concierto', 'La Paz', '2026-02-01 22:22:17'),
(74, 'Liset Rocio Peña Silva', 'F', 46, 'S', 'Boliviano', '4749459', 'Estudiante', 'Concierto', 'La Paz', '2026-02-01 22:34:51'),
(75, 'Leonardo Federico Almiron', 'M', 35, 'S', 'Argentino', 'E-116122514', 'Músico', 'Concierto', 'La Paz', '2026-02-01 22:46:46'),
(76, 'Jorge Gabriel Yarvi', 'M', 45, 'S', 'Argentina', 'E-11496605', 'Músico', 'Concierto', 'La Paz', '2026-02-01 22:59:54'),
(77, 'Ariela Narda Jauregui Condori', 'F', 36, 'S', 'Boliviano', '6957515', 'Estudiante', 'Concierto', 'La Paz', '2026-02-01 23:08:04'),
(78, 'Juan Salvador Zapana', 'M', 23, 'Soltero/a', 'Argentina', '45.108.603', 'Músico', 'Otro', 'La Paz', '2026-02-01 23:08:04'),
(79, 'Margarita Marin Alvarez', 'F', 30, 'S', 'Boliviano', '10532200', 'Estudiante', 'Paso', 'Familiar', '2026-02-01 23:17:02'),
(80, 'Evelin Noemi Marin Alvarez', 'F', 20, 'Soltero/a', 'Boliviano', '10532275', 'Estudiante', 'Familiar', 'Potosí', '2026-02-01 23:17:02'),
(81, 'Julia Alejandra Montoya Lagrava', 'F', 29, 'S', 'Boliviano', '12813450', 'Médico Cirujano', 'Paso', 'Potosí', '2026-02-01 23:20:15'),
(82, 'Carlos Daniel Espinoza Medinaceli', 'M', 27, 'S', 'Boliviano', '10571942', 'Estudiante', 'Turismo', 'Potosí', '2026-02-02 12:22:22'),
(83, 'Martin Cuenca Nicacio', 'M', 55, 'C', 'Boliviano', '3696869', 'Chofer', 'Paso', 'Villazon', '2026-02-03 02:02:57'),
(84, 'Alberto Nina Coronado', 'M', 31, 'S', 'Boliviano', '13915285', 'Estudiante', 'Paso', 'Santa Cruz', '2026-02-05 04:28:51'),
(85, 'Orlando Quintana Escobar', 'M', 47, 'c', 'Boliviano', '5333928', 'Comerciante', 'Turismo', 'Santa Cruz', '2026-02-06 03:18:49'),
(86, 'Tatiana Alejandra Vargas Torres', 'F', 37, 'Soltero/a', 'Boliviano', '8983148', 'Independiente', 'Turismo', 'Santa Cruz', '2026-02-06 03:18:49'),
(87, 'Rosse Mary Torres Diaz', 'F', 52, 'Soltero/a', 'Boliviano', '3658606', 'Estudiante', 'Turismo', 'Santa Cruz', '2026-02-06 03:18:49'),
(88, 'Maria Teresa Vacaflor Hernandez', 'F', 71, 'S', 'Boliviano', '1048890', 'Ama de Casa', 'Turismo', 'Potosí', '2026-02-06 03:34:54'),
(89, 'Eynar Ernesto Ramos Patton', 'M', 49, 'C', 'Boliviano', '4578269', 'Ing. Agronomo', 'Turismo', 'Potosí', '2026-02-06 03:37:42'),
(90, 'Elsa Eva Coronado Vacaflor', 'F', 47, 'Soltero/a', 'Boliviano', '5004623', 'Bioquimico', 'Turismo', 'Potosí', '2026-02-06 03:37:42'),
(91, 'Juan Carlos Choqueticlla Santos', 'M', 37, 'C', 'Boliviano', '6651687', 'Minero', 'Turismo', 'Potosí', '2026-02-07 08:07:54'),
(92, 'Jheanet Soledad Choque Limachi', 'F', 34, 'Casado/a', 'Boliviano', '10536673', 'Ama de casa', 'Turismo', 'Potosí', '2026-02-07 08:07:54'),
(93, 'Carlos Abdiel Choqueticlla Choque', 'M', 7, 'Soltero/a', 'Boliviano', '17623652', 'Estudiante', 'Turismo', 'Potosí', '2026-02-07 08:07:54'),
(94, 'Alejandra Choqueticlla Choque', 'F', 16, 'Soltero/a', 'Boliviano', '16020824', 'Estudiante', 'Turismo', 'Potosí', '2026-02-07 08:07:54'),
(95, 'Marco Antonio Duran Loredo', 'M', 26, 'S', 'Boliviano', '8971364', 'Estudiante', 'Turismo', 'Potosí', '2026-02-07 23:40:04'),
(96, 'Eva Duran Loredo', 'F', 35, 'Soltero/a', 'Boliviano', '8116690', 'Ama de casa', 'Turismo', 'Potosí', '2026-02-07 23:40:04'),
(97, 'Jose Antonio Quiroz Salazar', 'M', 74, 'Soltero/a', 'Boliviano', '1537758', 'Agricultor', 'Turismo', 'Potosí', '2026-02-07 23:40:04'),
(98, 'Andres Nelson Condori Martinez', 'M', 38, 'S', 'Boliviano', '6663794', 'Agricultor', 'Turismo', 'Potosí', '2026-02-08 03:32:48'),
(99, 'Maria Eugenia Huiza Pinto', 'F', 35, 'Soltero/a', 'Boliviano', '8573540', 'Ama de casa', 'Turismo', 'Potosí', '2026-02-08 03:32:49'),
(100, 'Maribel Codori Huiza', 'F', 19, 'S', 'Boliviano', '12527265', 'Estudiante', 'Turismo', 'Potosí', '2026-02-08 03:35:07'),
(101, 'Jhandy Estefany Condori Huiza', 'F', 12, 'Soltero/a', 'Boliviano', '15484368', 'Estudiante', 'Turismo', 'Potosí', '2026-02-08 03:35:07'),
(102, 'Jose Carlos Guzman Montenegro', 'M', 34, 'S', 'Boliviano', '6325921', 'Conductor', 'Turismo', 'Santa Cruz', '2026-02-08 10:02:07'),
(103, 'Jessica Lorena Soliz Aranibar', 'F', 28, 'Soltero/a', 'Boliviano', '11366393', 'Odontóloga', 'Turismo', 'Santa Cruz', '2026-02-08 10:02:07'),
(104, 'Ruth Auster Soliz Aranibar', 'F', 45, 'S', 'Boliviano', '4032413', 'Conductor', 'Turismo', 'Santa Cruz', '2026-02-08 10:04:40'),
(105, 'Gerardo Denar Ribera Guzman', 'M', 33, 'S', 'Boliviano', '7713918', 'Estudiante', 'Trabajo', 'Cochabamba', '2026-02-08 10:06:49'),
(106, 'Cristina Condori Flores', 'F', 49, 'C', 'Boliviano', '6651142', 'Ama de Casa', 'Turismo', 'La Paz', '2026-02-08 18:11:47'),
(107, 'Roberto Huanca Luque', 'M', 48, 'Casado/a', 'Boliviano', '4848969', 'Costurero', 'Turismo', 'La Paz', '2026-02-08 18:11:47'),
(108, 'Sara Lais Huanca Condori', 'F', 18, 'S', 'Brazilera', '52.430.261-3', 'Estudiante', 'Turismo', 'La Paz', '2026-02-08 18:58:30');

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
(25, 39, 'Pago habitación 201 - 1 día(s)', 140.00, 'qr', '2025-12-01', '01:44:25', 'Ingreso automático por registro de huésped'),
(26, 40, 'Pago habitación 303 - 2 día(s)', 440.00, 'efectivo', '2025-12-01', '02:07:52', 'Ingreso automático por registro de huésped'),
(27, 42, 'Pago habitación 301 - 1 día(s) (Descuento: Bs. 120.00 - Se dió a precio de Matrimonial)', 220.00, 'efectivo', '2025-12-03', '02:16:25', 'Ingreso automático por registro de huésped'),
(28, 44, 'Pago habitación 104 - 2 día(s)', 440.00, 'efectivo', '2025-12-01', '02:31:05', 'Ingreso automático por registro de huésped'),
(29, 45, 'Pago habitación 104 - 2 día(s)', 440.00, 'efectivo', '2025-12-01', '02:32:04', 'Ingreso automático por registro de huésped'),
(30, 46, 'Pago habitación 102 - 1 día(s) (Descuento: Bs. 20.00 - Sin Desayuno)', 200.00, 'efectivo', '2025-12-06', '02:42:58', 'Ingreso automático por registro de huésped'),
(31, 48, 'Pago habitación 208 - 1 día(s)', 320.00, 'efectivo', '2025-12-06', '00:22:52', 'Ingreso automático por registro de huésped'),
(32, 52, 'Pago habitación 205 - 1 día(s)', 220.00, 'efectivo', '2025-12-17', '00:47:06', 'Ingreso automático por registro de huésped'),
(33, 54, 'Pago habitación 306 - 1 día(s)', 220.00, 'efectivo', '2025-12-18', '00:53:33', 'Ingreso automático por registro de huésped'),
(34, 56, 'Pago habitación 204 - 1 día(s)', 140.00, 'efectivo', '2025-12-19', '01:02:26', 'Ingreso automático por registro de huésped'),
(35, 57, 'Pago habitación 203 - 1 día(s)', 140.00, 'efectivo', '2025-12-19', '01:05:51', 'Ingreso automático por registro de huésped'),
(36, 58, 'Pago habitación 302 - 1 día(s) (Descuento: Bs. 80.00 - Uso como Individual)', 140.00, 'efectivo', '2025-12-20', '01:22:30', 'Ingreso automático por registro de huésped'),
(37, 59, 'Pago habitación 204 - 1 día(s) (Descuento: Bs. 20.00 - Sin Desayuno)', 120.00, 'efectivo', '2025-12-26', '01:35:54', 'Ingreso automático por registro de huésped'),
(38, 60, 'Pago habitación 208 - 1 día(s) (Descuento: Bs. 20.00 - Sin Desayuno)', 300.00, 'efectivo', '2025-12-26', '01:50:17', 'Ingreso automático por registro de huésped'),
(39, 63, 'Pago habitación 201 - 1 día(s)', 140.00, 'efectivo', '2026-01-31', '10:10:22', 'Ingreso automático por registro de huésped'),
(40, 64, 'Pago habitación 203 - 1 día(s)', 140.00, 'efectivo', '2026-01-31', '10:12:04', 'Ingreso automático por registro de huésped'),
(41, 65, 'Pago habitación 204 - 1 día(s)', 140.00, 'efectivo', '2026-01-31', '10:14:01', 'Ingreso automático por registro de huésped'),
(42, 66, 'Pago habitación 304 - 1 día(s) (Descuento: Bs. 20.00 - Sin desayuno)', 200.00, 'efectivo', '2025-12-26', '13:20:55', 'Ingreso automático por registro de huésped'),
(43, 68, 'Pago habitación 302 - 1 día(s) (Descuento: Bs. 20.00 - Sin desayuno)', 200.00, 'efectivo', '2025-12-26', '14:17:51', 'Ingreso automático por registro de huésped'),
(44, 70, 'Pago habitación 207 - 1 día(s)', 300.00, 'efectivo', '2025-12-27', '14:45:08', 'Ingreso automático por registro de huésped'),
(45, 73, 'Pago habitación 104 - 1 día(s)', 220.00, 'qr', '2025-12-28', '15:53:44', 'Ingreso automático por registro de huésped'),
(46, 75, 'Pago habitación 306 - 1 día(s)', 220.00, 'qr', '2025-12-28', '16:23:57', 'Ingreso automático por registro de huésped'),
(47, 77, 'Pago habitación 206 - 1 día(s)', 220.00, 'efectivo', '2025-12-30', '16:34:22', 'Ingreso automático por registro de huésped'),
(48, 79, 'Pago habitación 304 - 1 día(s)', 220.00, 'efectivo', '2025-12-31', '17:52:13', 'Ingreso automático por registro de huésped'),
(49, 81, 'Pago habitación 303 - 1 día(s)', 220.00, 'efectivo', '2025-12-31', '18:06:23', 'Ingreso automático por registro de huésped'),
(50, 83, 'Pago habitación 306 - 1 día(s)', 220.00, 'efectivo', '2025-12-31', '18:11:51', 'Ingreso automático por registro de huésped'),
(51, 84, 'Pago habitación 202 - 1 día(s)', 140.00, 'efectivo', '2025-12-31', '18:22:17', 'Ingreso automático por registro de huésped'),
(52, 85, 'Pago habitación 203 - 1 día(s)', 140.00, 'efectivo', '2025-12-31', '18:34:51', 'Ingreso automático por registro de huésped'),
(53, 86, 'Pago habitación 204 - 1 día(s)', 140.00, 'efectivo', '2025-12-31', '18:46:46', 'Ingreso automático por registro de huésped'),
(54, 87, 'Pago habitación 302 - 1 día(s)', 220.00, 'efectivo', '2025-12-31', '18:59:54', 'Ingreso automático por registro de huésped'),
(55, 88, 'Pago habitación 205 - 1 día(s)', 220.00, 'efectivo', '2025-12-31', '19:08:04', 'Ingreso automático por registro de huésped'),
(56, 90, 'Pago habitación 102 - 1 día(s)', 220.00, 'efectivo', '2025-12-31', '19:17:02', 'Ingreso automático por registro de huésped'),
(57, 92, 'Pago habitación 104 - 1 día(s)', 220.00, 'efectivo', '2025-12-31', '19:20:15', 'Ingreso automático por registro de huésped'),
(58, 93, 'Pago habitación 205 - 1 día(s)', 220.00, 'efectivo', '2026-02-01', '08:22:23', 'Ingreso automático por registro de huésped'),
(59, 94, 'Pago habitación 203 - 1 día(s)', 140.00, 'efectivo', '2026-02-02', '22:02:57', 'Ingreso automático por registro de huésped'),
(60, 95, 'Pago habitación 201 - 1 día(s)', 140.00, 'qr', '2026-02-04', '21:59:04', 'Ingreso automático por registro de huésped'),
(61, 96, 'Pago habitación 202 - 1 día(s)', 140.00, 'efectivo', '2026-02-04', '00:28:51', 'Ingreso automático por registro de huésped'),
(62, 97, 'Pago habitación 207 - 1 día(s)', 300.00, 'efectivo', '2026-02-05', '23:18:49', 'Ingreso automático por registro de huésped'),
(63, 100, 'Pago habitación 203 - 1 día(s)', 140.00, 'efectivo', '2026-02-05', '23:34:54', 'Ingreso automático por registro de huésped'),
(64, 101, 'Pago habitación 206 - 1 día(s)', 220.00, 'efectivo', '2026-02-05', '23:37:42', 'Ingreso automático por registro de huésped'),
(65, NULL, 'La habitacion 202 pago por medio dia mas 70bs', 70.00, 'efectivo', '2026-02-06', '07:56:11', 'Ingreso extra'),
(66, 103, 'Pago habitación 208 - 1 día(s)', 320.00, 'efectivo', '2026-02-06', '04:07:54', 'Ingreso automático por registro de huésped'),
(67, 107, 'Pago habitación 209 - 1 día(s)', 300.00, 'efectivo', '2026-02-07', '19:40:04', 'Ingreso automático por registro de huésped'),
(68, 110, 'Pago habitación 306 - 1 día(s) (Descuento: Bs. 10.00 - Descuento)', 210.00, 'efectivo', '2026-02-07', '23:32:48', 'Ingreso automático por registro de huésped'),
(69, 112, 'Pago habitación 303 - 1 día(s)', 220.00, 'efectivo', '2026-02-07', '23:35:07', 'Ingreso automático por registro de huésped'),
(70, 114, 'Pago habitación 205 - 1 día(s)', 220.00, 'qr', '2026-02-07', '06:02:07', 'Ingreso automático por registro de huésped'),
(71, 116, 'Pago habitación 203 - 1 día(s)', 140.00, 'qr', '2026-02-07', '06:04:40', 'Ingreso automático por registro de huésped'),
(72, 117, 'Pago habitación 204 - 1 día(s)', 140.00, 'efectivo', '2026-02-07', '06:06:49', 'Ingreso automático por registro de huésped'),
(73, 118, 'Pago habitación 104 - 4 día(s) (Descuento: Bs. 80.00 - 20 bs descuento por día)', 800.00, 'efectivo', '2026-02-08', '14:11:47', 'Ingreso automático por registro de huésped'),
(74, 120, 'Pago habitación 201 - 4 día(s) (Descuento: Bs. 40.00 - 10 Bs de descuento por día)', 520.00, 'efectivo', '2026-02-08', '14:58:30', 'Ingreso automático por registro de huésped');

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
(1, 'ALMACEN', 'almacen', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 10, 0, 0, 0, 0, 0, 0, 0, 0, 0, '2026-01-20 16:32:18'),
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

--
-- Volcado de datos para la tabla `mantenimientos`
--

INSERT INTO `mantenimientos` (`id`, `habitacion_numero`, `titulo`, `descripcion`, `prioridad`, `tipo`, `estado`, `costo_estimado`, `costo_real`, `fecha_inicio`, `fecha_fin_estimada`, `fecha_fin_real`, `responsable`, `observaciones`, `imagen`, `created_at`, `updated_at`) VALUES
(28, '202', 'Supuesto colchón con resorte', 'Huéspedes informaron que el colchón tiene un resorte salido en la sección mensionada, aun que no es visible al apoyarse se siente y escucha un sonido similar al de un resorte', 'media', 'emergencia', 'pendiente', NULL, NULL, '2026-02-02', '2026-02-04', NULL, 'Nisan', 'Sin más observaciones', '202_20260202_090950.jpg', '2026-02-02 13:09:50', '2026-02-02 13:09:50');

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

--
-- Volcado de datos para la tabla `pagos_qr`
--

INSERT INTO `pagos_qr` (`id`, `ocupacion_id`, `monto`, `fecha`, `hora`, `numero_transaccion`, `observaciones`) VALUES
(14, 39, 140.00, '2025-12-01', '01:44:25', '', 'Pago QR por habitación 201'),
(15, 73, 220.00, '2025-12-28', '15:53:44', '', 'Pago QR por habitación 104'),
(16, 75, 220.00, '2025-12-28', '16:23:57', '', 'Pago QR por habitación 306'),
(17, 95, 140.00, '2026-02-04', '21:59:04', '', 'Pago QR por habitación 201'),
(18, 114, 220.00, '2026-02-07', '06:02:07', '', 'Pago QR por habitación 205'),
(19, 116, 140.00, '2026-02-07', '06:04:40', '', 'Pago QR por habitación 203');

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

--
-- Volcado de datos para la tabla `registro_garaje`
--

INSERT INTO `registro_garaje` (`id`, `ocupacion_id`, `huesped_nombre`, `fecha`, `costo`, `observaciones`, `created_at`) VALUES
(5, 65, 'Javier Chumacero Barrios', '2026-01-31', 10.00, 'Habitación 204', '2026-02-01 14:14:01'),
(6, 94, 'Martin Cuenca Nicacio', '2026-02-02', 10.00, 'Habitación 203', '2026-02-03 02:02:57'),
(7, 101, 'Eynar Ernesto Ramos Patton', '2026-02-05', 10.00, 'Habitación 206', '2026-02-06 03:37:42'),
(8, 103, 'Juan Carlos Choqueticlla Santos', '2026-02-06', 10.00, 'Habitación 208', '2026-02-07 08:07:54'),
(9, 107, 'Marco Antonio Duran Loredo', '2026-02-07', 10.00, 'Habitación 209', '2026-02-07 23:40:04'),
(10, 110, 'Andres Nelson Condori Martinez', '2026-02-07', 10.00, 'Habitación 306', '2026-02-08 03:32:49'),
(11, 118, 'Cristina Condori Flores', '2026-02-08', 10.00, 'Habitación 104', '2026-02-08 18:11:47');

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
(39, 29, 4, '201', 'La Paz', 'T', '2025-12-01', 1, '2025-12-02', '2026-01-16', 'finalizado'),
(40, 30, 15, '303', 'Tarija', 'T', '2025-12-01', 2, '2025-12-03', '2026-01-16', 'finalizado'),
(41, 31, 15, '303', 'Tarija', 'T', '2025-12-01', 2, '2025-12-03', '2026-01-16', 'finalizado'),
(42, 32, 13, '301', 'Cochabamba', 'T', '2025-12-03', 1, '2025-12-04', '2026-01-16', 'finalizado'),
(43, 33, 13, '301', 'Cochabamba', 'T', '2025-12-03', 1, '2025-12-04', '2026-01-16', 'finalizado'),
(44, 34, 3, '104', 'Cochabamba', 'T', '2025-12-01', 2, '2025-12-03', '2026-01-16', 'finalizado'),
(45, 34, 3, '104', 'Cochabamba', 'T', '2025-12-01', 2, '2025-12-03', '2026-01-16', 'finalizado'),
(46, 35, 1, '102', 'Potosí', 'T', '2025-12-06', 1, '2025-12-07', '2026-01-16', 'finalizado'),
(47, 36, 1, '102', 'Potosí', 'T', '2025-12-06', 1, '2025-12-07', '2026-01-16', 'finalizado'),
(48, 37, 11, '208', 'Potosí', 'T', '2025-12-06', 1, '2025-12-07', '2026-01-26', 'finalizado'),
(49, 38, 11, '208', 'Potosí', 'T', '2025-12-06', 1, '2025-12-07', '2026-01-26', 'finalizado'),
(50, 39, 11, '208', 'Potosí', 'T', '2025-12-06', 1, '2025-12-07', '2026-01-26', 'finalizado'),
(51, 40, 11, '208', 'Potosí', 'T', '2025-12-06', 1, '2025-12-07', '2026-01-26', 'finalizado'),
(52, 41, 8, '205', 'Santa Cruz', 'T', '2025-12-17', 1, '2025-12-18', '2026-01-26', 'finalizado'),
(53, 42, 8, '205', 'Santa Cruz', 'T', '2025-12-17', 1, '2025-12-18', '2026-01-26', 'finalizado'),
(54, 43, 18, '306', 'Santa Cruz', 'T', '2025-12-18', 1, '2025-12-19', '2026-01-26', 'finalizado'),
(55, 44, 18, '306', 'Santa Cruz', 'T', '2025-12-18', 1, '2025-12-19', '2026-01-26', 'finalizado'),
(56, 45, 7, '204', 'Potosí', 'T', '2025-12-19', 1, '2025-12-20', '2026-01-26', 'finalizado'),
(57, 46, 6, '203', 'Oruro', 'T', '2025-12-19', 1, '2025-12-20', '2026-01-26', 'finalizado'),
(58, 47, 14, '302', 'Cochabamba', 'T', '2025-12-20', 1, '2025-12-21', '2026-01-26', 'finalizado'),
(59, 48, 7, '204', 'Tarija', 'T', '2025-12-26', 1, '2025-12-27', '2026-01-26', 'finalizado'),
(60, 49, 11, '208', 'Tarija', 'T', '2025-12-26', 1, '2025-12-27', '2026-01-26', 'finalizado'),
(61, 50, 11, '208', 'Tarija', 'T', '2025-12-26', 1, '2025-12-27', '2026-01-26', 'finalizado'),
(62, 51, 11, '208', 'Tarija', 'T', '2025-12-26', 1, '2025-12-27', '2026-01-26', 'finalizado'),
(63, 52, 4, '201', 'Potosí', 'T', '2026-01-31', 1, '2026-02-01', '2026-02-01', 'finalizado'),
(64, 53, 6, '203', 'Potosí', 'T', '2026-01-31', 1, '2026-02-01', '2026-02-01', 'finalizado'),
(65, 54, 7, '204', 'Potosí', 'T', '2026-01-31', 1, '2026-02-01', '2026-02-01', 'finalizado'),
(66, 55, 16, '304', 'Cochabamba', 'T', '2025-12-26', 1, '2025-12-27', '2026-02-01', 'finalizado'),
(67, 56, 16, '304', 'Cochabamba', 'T', '2025-12-26', 1, '2025-12-27', '2026-02-01', 'finalizado'),
(68, 57, 14, '302', 'Cochabamba', 'T', '2025-12-26', 1, '2025-12-27', '2026-02-01', 'finalizado'),
(69, 58, 14, '302', 'Cochabamba', 'T', '2025-12-26', 1, '2025-12-27', '2026-02-01', 'finalizado'),
(70, 59, 10, '207', 'Santa Cruz', 'T', '2025-12-27', 1, '2025-12-28', '2026-02-01', 'finalizado'),
(71, 60, 10, '207', 'Santa Cruz', 'T', '2025-12-27', 1, '2025-12-28', '2026-02-01', 'finalizado'),
(72, 61, 10, '207', 'Santa Cruz', 'T', '2025-12-27', 1, '2025-12-28', '2026-02-01', 'finalizado'),
(73, 62, 3, '104', 'Oruro', 'T', '2025-12-28', 1, '2025-12-29', '2026-02-01', 'finalizado'),
(74, 63, 3, '104', 'Oruro', 'T', '2025-12-28', 1, '2025-12-29', '2026-02-01', 'finalizado'),
(75, 64, 18, '306', 'Tarija', 'T', '2025-12-28', 1, '2025-12-29', '2026-02-01', 'finalizado'),
(76, 65, 18, '306', 'Tarija', 'T', '2025-12-28', 1, '2025-12-29', '2026-02-01', 'finalizado'),
(77, 66, 9, '206', 'Santa Cruz', 'T', '2025-12-30', 1, '2025-12-31', '2026-02-01', 'finalizado'),
(78, 67, 9, '206', 'Santa Cruz', 'T', '2025-12-30', 1, '2025-12-31', '2026-02-01', 'finalizado'),
(79, 68, 16, '304', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(80, 69, 16, '304', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(81, 70, 15, '303', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(82, 71, 15, '303', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(83, 72, 18, '306', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(84, 73, 5, '202', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(85, 74, 6, '203', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(86, 75, 7, '204', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(87, 76, 14, '302', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(88, 77, 8, '205', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(89, 78, 8, '205', 'La Paz', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(90, 79, 1, '102', 'Potosí', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(91, 80, 1, '102', 'Potosí', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(92, 81, 3, '104', 'Potosí', 'T', '2025-12-31', 1, '2026-01-01', '2026-02-01', 'finalizado'),
(93, 82, 8, '205', 'Potosí', 'T', '2026-02-01', 1, '2026-02-02', '2026-02-02', 'finalizado'),
(94, 83, 6, '203', 'Cochabamba', 'T', '2026-02-02', 1, '2026-02-03', '2026-02-04', 'finalizado'),
(95, 29, 4, '201', 'La paz', 'T', '2026-02-04', 1, '2026-02-05', '2026-02-05', 'finalizado'),
(96, 84, 5, '202', 'Potosí', 'T', '2026-02-04', 1, '2026-02-05', '2026-02-05', 'finalizado'),
(97, 85, 10, '207', 'Cochabamba', 'T', '2026-02-05', 1, '2026-02-06', '2026-02-07', 'finalizado'),
(98, 86, 10, '207', 'Cochabamba', 'T', '2026-02-05', 1, '2026-02-06', '2026-02-07', 'finalizado'),
(99, 87, 10, '207', 'Cochabamba', 'T', '2026-02-05', 1, '2026-02-06', '2026-02-07', 'finalizado'),
(100, 88, 6, '203', 'Santa Cruz', 'T', '2026-02-05', 1, '2026-02-06', '2026-02-07', 'finalizado'),
(101, 89, 9, '206', 'Santa Cruz', 'T', '2026-02-05', 1, '2026-02-06', '2026-02-07', 'finalizado'),
(102, 90, 9, '206', 'Santa Cruz', 'T', '2026-02-05', 1, '2026-02-06', '2026-02-07', 'finalizado'),
(103, 91, 11, '208', 'Potosí', 'T', '2026-02-06', 1, '2026-02-07', '2026-02-07', 'finalizado'),
(104, 92, 11, '208', 'Potosí', 'T', '2026-02-06', 1, '2026-02-07', '2026-02-07', 'finalizado'),
(105, 93, 11, '208', 'Potosí', 'T', '2026-02-06', 1, '2026-02-07', '2026-02-07', 'finalizado'),
(106, 94, 11, '208', 'Potosí', 'T', '2026-02-06', 1, '2026-02-07', '2026-02-07', 'finalizado'),
(107, 95, 12, '209', 'Cochabamba', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(108, 96, 12, '209', 'Cochabamba', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(109, 97, 12, '209', 'Cochabamba', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(110, 98, 18, '306', 'Potosí', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(111, 99, 18, '306', 'Potosí', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(112, 100, 15, '303', 'Potosí', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(113, 101, 15, '303', 'Potosí', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(114, 102, 8, '205', 'Santa Cruz', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(115, 103, 8, '205', 'Santa Cruz', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(116, 104, 6, '203', 'Santa Cruz', 'T', '2026-02-07', 1, '2026-02-08', '2026-02-08', 'finalizado'),
(117, 105, 7, '204', 'Santa Cruz', 'T', '2026-02-07', 1, '2026-02-09', NULL, 'activo'),
(118, 106, 3, '104', 'La paz', 'T', '2026-02-08', 4, '2026-02-12', NULL, 'activo'),
(119, 107, 3, '104', 'La paz', 'T', '2026-02-08', 4, '2026-02-12', NULL, 'activo'),
(120, 108, 4, '201', 'La paz', 'T', '2026-02-08', 4, '2026-02-12', NULL, 'activo');

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
(1, 'Hotel Cecil', '$2y$10$KtVhWU3au1rkpbMUIX/UUu7QUYn0OviukezCp3EHyANfJB2Ykz6B2', 'Hotel Cecil', 'administrador', '2025-12-24 00:21:00', '2026-02-08 21:42:02', 1),
(2, 'Isaac Vargas', '$2y$10$Cy/hv5u8LKYkQpcpXbFwHeWpRwCL4j4iZfYCGqbznO3r3luwHNyna', 'Isaac Vargas', 'administrador', '2025-12-24 23:29:05', '2026-01-15 19:27:30', 1),
(7, 'Rodrigo Moscoso', '$2b$10$4VBoH/33EniTZ5dxJtodaOMd8SQcSU3K9l/o6otPM0q3PvZJW1I2O', 'Rodrigo Moscoso', 'administrador', '2025-12-29 02:18:36', NULL, 1),
(8, 'Usuario Hotel', '$2b$10$h8ERlsj5VTRQLHqhl46qre/zlje25g6mTRzyiXMCBmI8MX9JaFpZK', 'Usuario Hotel', 'usuario', '2025-12-29 02:18:36', '2025-12-28 22:19:37', 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `huespedes`
--
ALTER TABLE `huespedes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT de la tabla `ingresos`
--
ALTER TABLE `ingresos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT de la tabla `inventario_habitaciones`
--
ALTER TABLE `inventario_habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `pagos_qr`
--
ALTER TABLE `pagos_qr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `registro_garaje`
--
ALTER TABLE `registro_garaje`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `registro_ocupacion`
--
ALTER TABLE `registro_ocupacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
