-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-06-2026 a las 23:11:31
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
-- Base de datos: `control_taxis`
--
CREATE DATABASE IF NOT EXISTS `control_taxis` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `control_taxis`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conductores`
--

DROP TABLE IF EXISTS `conductores`;
CREATE TABLE `conductores` (
  `id_conductor` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `dni` varchar(8) NOT NULL,
  `licencia` varchar(20) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `estado_conductor` enum('disponible','ocupado','inactivo') DEFAULT 'disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `conductores`
--

INSERT INTO `conductores` (`id_conductor`, `id_usuario`, `dni`, `licencia`, `telefono`, `estado_conductor`) VALUES
(1, 4, '11111111', 'Q11111111', '999111222', 'disponible'),
(2, 5, '22222222', 'A22222222', '999333444', 'ocupado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas_viaje`
--

DROP TABLE IF EXISTS `ofertas_viaje`;
CREATE TABLE `ofertas_viaje` (
  `id_oferta` int(11) NOT NULL,
  `id_viaje` int(11) NOT NULL,
  `id_conductor` int(11) NOT NULL,
  `monto_ofrecido` decimal(10,2) NOT NULL,
  `estado_oferta` enum('pendiente','aceptada','rechazada') DEFAULT 'pendiente',
  `fecha_oferta` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `rol` enum('admin','pasajero','conductor') NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `contrasena`, `nombre_completo`, `correo`, `rol`, `fecha_registro`) VALUES
(1, 'admin', '123456', 'Administrador Principal', 'admin@taxis.com', 'admin', '2026-06-03 16:04:28'),
(2, 'ana', '123456', 'Ana Rodríguez', 'ana@gmail.com', 'pasajero', '2026-06-03 16:04:28'),
(3, 'carlos', '123456', 'Carlos Vargas', 'carlos@gmail.com', 'pasajero', '2026-06-03 16:04:28'),
(4, 'pedro', '123456', 'Pedro Chofer', 'pedro@taxis.com', 'conductor', '2026-06-03 16:04:28'),
(5, 'luis', '123456', 'Luis Chofer', 'luis@taxis.com', 'conductor', '2026-06-03 16:04:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

DROP TABLE IF EXISTS `vehiculos`;
CREATE TABLE `vehiculos` (
  `id_vehiculo` int(11) NOT NULL,
  `id_conductor` int(11) DEFAULT NULL,
  `modelo` varchar(100) NOT NULL,
  `placa` varchar(15) NOT NULL,
  `anio` int(11) NOT NULL,
  `color` varchar(30) NOT NULL,
  `estado_vehiculo` enum('activo','taller','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id_vehiculo`, `id_conductor`, `modelo`, `placa`, `anio`, `color`, `estado_vehiculo`) VALUES
(1, NULL, 'Toyota Yaris', 'ABC-123', 2023, 'Blanco', 'activo'),
(2, NULL, 'Kia Rio', 'XYZ-987', 2022, 'Negro', 'activo'),
(3, NULL, 'Hyundai Accent', 'LMN-456', 2020, 'Plata', 'taller');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `viajes`
--

DROP TABLE IF EXISTS `viajes`;
CREATE TABLE `viajes` (
  `id_viaje` int(11) NOT NULL,
  `id_pasajero` int(11) NOT NULL,
  `id_conductor` int(11) DEFAULT NULL,
  `id_vehiculo` int(11) DEFAULT NULL,
  `origen` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `tarifa_propuesta` decimal(10,2) NOT NULL,
  `tarifa_final` decimal(10,2) DEFAULT NULL,
  `estado_viaje` enum('solicitado','aceptado','en_curso','completado','cancelado') DEFAULT 'solicitado',
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `viajes`
--

INSERT INTO `viajes` (`id_viaje`, `id_pasajero`, `id_conductor`, `id_vehiculo`, `origen`, `destino`, `tarifa_propuesta`, `tarifa_final`, `estado_viaje`, `fecha_solicitud`, `fecha_inicio`, `fecha_fin`) VALUES
(1, 2, NULL, NULL, 'Óvalo de Miraflores', 'Jockey Plaza', 0.00, NULL, 'solicitado', '2026-06-03 16:04:28', NULL, NULL),
(2, 3, 2, 2, 'Plaza San Miguel', 'Aeropuerto Jorge Chávez', 0.00, 45.00, 'en_curso', '2026-06-03 16:04:28', NULL, NULL),
(3, 2, 1, 1, 'Barranco', 'Surco', 0.00, 20.00, 'completado', '2026-06-03 16:04:28', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `conductores`
--
ALTER TABLE `conductores`
  ADD PRIMARY KEY (`id_conductor`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `licencia` (`licencia`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `ofertas_viaje`
--
ALTER TABLE `ofertas_viaje`
  ADD PRIMARY KEY (`id_oferta`),
  ADD KEY `id_viaje` (`id_viaje`),
  ADD KEY `id_conductor` (`id_conductor`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id_vehiculo`),
  ADD UNIQUE KEY `placa` (`placa`),
  ADD KEY `id_conductor` (`id_conductor`);

--
-- Indices de la tabla `viajes`
--
ALTER TABLE `viajes`
  ADD PRIMARY KEY (`id_viaje`),
  ADD KEY `id_pasajero` (`id_pasajero`),
  ADD KEY `id_conductor` (`id_conductor`),
  ADD KEY `id_vehiculo` (`id_vehiculo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `conductores`
--
ALTER TABLE `conductores`
  MODIFY `id_conductor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ofertas_viaje`
--
ALTER TABLE `ofertas_viaje`
  MODIFY `id_oferta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id_vehiculo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `viajes`
--
ALTER TABLE `viajes`
  MODIFY `id_viaje` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `conductores`
--
ALTER TABLE `conductores`
  ADD CONSTRAINT `conductores_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ofertas_viaje`
--
ALTER TABLE `ofertas_viaje`
  ADD CONSTRAINT `ofertas_viaje_ibfk_1` FOREIGN KEY (`id_viaje`) REFERENCES `viajes` (`id_viaje`) ON DELETE CASCADE,
  ADD CONSTRAINT `ofertas_viaje_ibfk_2` FOREIGN KEY (`id_conductor`) REFERENCES `conductores` (`id_conductor`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`id_conductor`) REFERENCES `conductores` (`id_conductor`) ON DELETE SET NULL;

--
-- Filtros para la tabla `viajes`
--
ALTER TABLE `viajes`
  ADD CONSTRAINT `viajes_ibfk_1` FOREIGN KEY (`id_pasajero`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `viajes_ibfk_2` FOREIGN KEY (`id_conductor`) REFERENCES `conductores` (`id_conductor`) ON DELETE SET NULL,
  ADD CONSTRAINT `viajes_ibfk_3` FOREIGN KEY (`id_vehiculo`) REFERENCES `vehiculos` (`id_vehiculo`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
