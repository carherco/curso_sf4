-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:8889
-- Tiempo de generación: 16-11-2018 a las 12:38:02
-- Versión del servidor: 5.6.38
-- Versión de PHP: 7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sf4_mo2o`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `id` int(11) NOT NULL,
  `n_expediente` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `apellidos` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `sexo` tinyint(1) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `grado_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `alumno`
--

INSERT INTO `alumno` (`id`, `n_expediente`, `nombre`, `apellidos`, `fecha_nacimiento`, `sexo`, `email`, `telefono`, `grado_id`) VALUES
(1, 13472, 'Carlos ', 'Herrera Conejero', '2017-10-10', 0, 'carherco@gmail.com', '197687432', 1),
(4, 13483, 'Carmen', 'Hernández Colomina', '2007-10-10', 0, 'carherco+2@gmail.com', '545758758768', 2),
(9, 25434, 'afdsd', 'asdf', NULL, 0, 'carh4erco@gmail.com', '600735405', NULL),
(13, 876566, 'hgdfsd', 'sf', NULL, 0, 'carher4c3o@gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos_asignaturas`
--

CREATE TABLE `alumnos_asignaturas` (
  `asignatura_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `alumnos_asignaturas`
--

INSERT INTO `alumnos_asignaturas` (`asignatura_id`, `alumno_id`) VALUES
(3, 1),
(4, 1),
(6, 1),
(6, 4),
(8, 4),
(10, 4),
(11, 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulo`
--

CREATE TABLE `articulo` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenido` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `articulo`
--

INSERT INTO `articulo` (`id`, `titulo`, `contenido`, `estado`) VALUES
(4, 'asdfa', 'asdafsd', '{\"publicado\":1}'),
(5, 'asdf', 'asdfasdf', '{\"corregido\":1,\"rechazado\":1}'),
(6, 'adfasdf', 'asdfasdf', '{\"publicado\":1}'),
(7, 'fasdf', 'asdfasd', '{\"publicado\":1}'),
(8, 'vasfas', 'asdfasd', '{\"rechazado\":1,\"corregido\":1}'),
(9, 'Título del artículo', 'Contenido del artículo', '{\"publicado\":1}');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignatura`
--

CREATE TABLE `asignatura` (
  `id` int(11) NOT NULL,
  `codigo` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nombre_ingles` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `credects` int(11) DEFAULT NULL,
  `grado_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `asignatura`
--

INSERT INTO `asignatura` (`id`, `codigo`, `nombre`, `nombre_ingles`, `credects`, `grado_id`) VALUES
(3, 1343, 'Física I', 'Physics I', 6, 1),
(4, 4231, 'Física II', 'Physics II', 6, 1),
(5, 435354, 'Cálculo', 'Cálculo', 3, 1),
(6, 5565, 'Comunicaciones ópticas', 'Comunicaciones ópticas', 5, 2),
(7, 334, 'Programación', 'Programación', 8, 1),
(8, 24124, 'Antenas', 'Antenas', 4, 2),
(9, 765432, 'Geografía', 'Geografía', 6, 1),
(10, 2147483647, 'Microondas', 'Microondas', 6, 2),
(11, 542552345, 'Programación', 'Programación', 4, 2),
(12, 654, 'sadfad', 'asdf', 5, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloque`
--

CREATE TABLE `bloque` (
  `id` int(11) NOT NULL,
  `idencuesta` int(11) DEFAULT NULL,
  `descripcion` varchar(254) DEFAULT NULL,
  `orden` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `bloque`
--

INSERT INTO `bloque` (`id`, `idencuesta`, `descripcion`, `orden`) VALUES
(1, 1, 'preguntas generales', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `concepto`
--

CREATE TABLE `concepto` (
  `id` bigint(20) NOT NULL,
  `idencuesta` int(11) NOT NULL,
  `codigo` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(254) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta`
--

CREATE TABLE `encuesta` (
  `id` int(11) NOT NULL,
  `idasignatura` varchar(10) NOT NULL,
  `descripcion` varchar(128) DEFAULT NULL,
  `curso_academico` smallint(6) DEFAULT NULL,
  `fecha_ini` varchar(12) DEFAULT NULL,
  `fecha_fin` varchar(12) DEFAULT NULL,
  `fecha_cierre` varchar(12) DEFAULT NULL,
  `gestor` varchar(15) DEFAULT NULL,
  `estado` smallint(6) DEFAULT NULL,
  `modificable` tinyint(1) DEFAULT NULL,
  `anonima` tinyint(1) DEFAULT NULL,
  `multiconcepto` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `encuesta`
--

INSERT INTO `encuesta` (`id`, `idasignatura`, `descripcion`, `curso_academico`, `fecha_ini`, `fecha_fin`, `fecha_cierre`, `gestor`, `estado`, `modificable`, `anonima`, `multiconcepto`) VALUES
(1, '0', 'encuesta formación', 2017, '2017-11-01', NULL, NULL, '1', 1, 1, 1, 1),
(2, '', 'Otra encuesta', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id`, `descripcion`) VALUES
(1, 'Abierta'),
(2, 'cerrada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grado`
--

CREATE TABLE `grado` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `grado`
--

INSERT INTO `grado` (`id`, `nombre`) VALUES
(1, 'Ingeniería de montes'),
(2, 'Ingeniería de telecomunicaciones');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nota`
--

CREATE TABLE `nota` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) DEFAULT NULL,
  `asignatura_id` int(11) DEFAULT NULL,
  `n_convocatoria` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `nota` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `nota`
--

INSERT INTO `nota` (`id`, `alumno_id`, `asignatura_id`, `n_convocatoria`, `fecha`, `nota`) VALUES
(1, 1, 3, 1, '2016-06-10', 3.7),
(2, 1, 3, 2, '2017-10-24', 6.2),
(3, 1, 4, 1, '2017-10-27', 8),
(4, 4, 8, 1, '2018-08-14', 6),
(5, 4, 6, 1, '2018-08-21', 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pregunta`
--

CREATE TABLE `pregunta` (
  `id` int(11) NOT NULL,
  `idencuesta` int(11) DEFAULT NULL,
  `idbloque` int(11) DEFAULT NULL,
  `idtipo` int(11) DEFAULT NULL,
  `orden` smallint(6) NOT NULL,
  `descripcion` varchar(254) DEFAULT NULL,
  `pie` varchar(254) DEFAULT NULL,
  `salida` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `pregunta`
--

INSERT INTO `pregunta` (`id`, `idencuesta`, `idbloque`, `idtipo`, `orden`, `descripcion`, `pie`, `salida`) VALUES
(1, 1, 1, 1, 1, 'Valora el aula', NULL, 1),
(2, 1, 1, 2, 2, 'Comentarios adicionales', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `precio` int(11) DEFAULT NULL,
  `descripcion` tinytext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id`, `nombre`, `precio`, `descripcion`) VALUES
(2, 'Nissan', 20000, '2 años de antigüedad'),
(3, 'Volvo', 28000, 'Menos de 10.000 km'),
(4, 'Toyota', 35000, 'Recién pasada la ITV'),
(5, 'Ford Fiesta', 19000, 'En perfecto estado'),
(26, 'product 0', 987777, ''),
(27, 'product 1', 51, ''),
(28, 'product 2', 17, ''),
(29, 'product 3', 100, ''),
(30, 'product 4', 30, ''),
(31, 'product 5', 14, ''),
(32, 'product 6', 32, ''),
(33, 'product 7', 37, ''),
(34, 'product 8', 47, ''),
(35, 'product 9', 100, ''),
(36, 'product 10', 87, ''),
(37, 'product 11', 91, ''),
(38, 'product 12', 81, ''),
(39, 'product 13', 96, ''),
(40, 'product 14', 56, ''),
(41, 'product 15', 63, ''),
(42, 'product 16', 28, ''),
(43, 'product 17', 21, ''),
(44, 'product 18', 96, ''),
(45, 'product 19', 11, ''),
(46, 'adf', 55, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyecto`
--

CREATE TABLE `proyecto` (
  `id` int(11) NOT NULL,
  `tipo_id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin_prevista` date NOT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta`
--

CREATE TABLE `respuesta` (
  `id` int(11) NOT NULL,
  `idpregunta` int(11) DEFAULT NULL,
  `orden` smallint(6) NOT NULL,
  `descripcion` varchar(254) NOT NULL,
  `valor` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `respuesta`
--

INSERT INTO `respuesta` (`id`, `idpregunta`, `orden`, `descripcion`, `valor`) VALUES
(1, 1, 1, 'bien', '10'),
(2, 1, 2, 'regular', '5'),
(3, 1, 3, 'mal', '0'),
(4, 2, 1, 'Valoración ', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultado`
--

CREATE TABLE `resultado` (
  `id` int(11) NOT NULL,
  `idencuesta` int(11) DEFAULT NULL,
  `idconcepto` varchar(255) NOT NULL,
  `idpregunta` int(11) DEFAULT NULL,
  `idrespuesta` int(11) DEFAULT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `valor` text,
  `fecha_auto` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo`
--

CREATE TABLE `tipo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_pregunta`
--

CREATE TABLE `tipo_pregunta` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Volcado de datos para la tabla `tipo_pregunta`
--

INSERT INTO `tipo_pregunta` (`id`, `descripcion`) VALUES
(1, 'cerrada numérica'),
(2, 'abierta alfanumérica');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_1435D52D91A441CC` (`grado_id`);

--
-- Indices de la tabla `alumnos_asignaturas`
--
ALTER TABLE `alumnos_asignaturas`
  ADD PRIMARY KEY (`asignatura_id`,`alumno_id`),
  ADD KEY `IDX_D57EE88C5C70C5B` (`asignatura_id`),
  ADD KEY `IDX_D57EE88FC28E5EE` (`alumno_id`);

--
-- Indices de la tabla `articulo`
--
ALTER TABLE `articulo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asignatura`
--
ALTER TABLE `asignatura`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_9243D6CE20332D99` (`codigo`),
  ADD KEY `IDX_9243D6CE91A441CC` (`grado_id`);

--
-- Indices de la tabla `bloque`
--
ALTER TABLE `bloque`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `idencuesta` (`idencuesta`);

--
-- Indices de la tabla `concepto`
--
ALTER TABLE `concepto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `grado`
--
ALTER TABLE `grado`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `nota`
--
ALTER TABLE `nota`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_C8D03E0DFC28E5EE` (`alumno_id`),
  ADD KEY `IDX_C8D03E0DC5C70C5B` (`asignatura_id`);

--
-- Indices de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `idencuesta` (`idencuesta`),
  ADD KEY `idbloque` (`idbloque`),
  ADD KEY `idtipo` (`idtipo`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_6FD202B9A9276E6C` (`tipo_id`);

--
-- Indices de la tabla `respuesta`
--
ALTER TABLE `respuesta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `idpregunta` (`idpregunta`);

--
-- Indices de la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `idencuesta` (`idencuesta`),
  ADD KEY `idpregunta` (`idpregunta`),
  ADD KEY `idrespuesta` (`idrespuesta`);

--
-- Indices de la tabla `tipo`
--
ALTER TABLE `tipo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_pregunta`
--
ALTER TABLE `tipo_pregunta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alumno`
--
ALTER TABLE `alumno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `articulo`
--
ALTER TABLE `articulo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `asignatura`
--
ALTER TABLE `asignatura`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `bloque`
--
ALTER TABLE `bloque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `concepto`
--
ALTER TABLE `concepto`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `grado`
--
ALTER TABLE `grado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `nota`
--
ALTER TABLE `nota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `proyecto`
--
ALTER TABLE `proyecto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuesta`
--
ALTER TABLE `respuesta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `resultado`
--
ALTER TABLE `resultado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo`
--
ALTER TABLE `tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_pregunta`
--
ALTER TABLE `tipo_pregunta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD CONSTRAINT `FK_1435D52D91A441CC` FOREIGN KEY (`grado_id`) REFERENCES `grado` (`id`);

--
-- Filtros para la tabla `alumnos_asignaturas`
--
ALTER TABLE `alumnos_asignaturas`
  ADD CONSTRAINT `FK_D57EE88C5C70C5B` FOREIGN KEY (`asignatura_id`) REFERENCES `asignatura` (`id`),
  ADD CONSTRAINT `FK_D57EE88FC28E5EE` FOREIGN KEY (`alumno_id`) REFERENCES `alumno` (`id`);

--
-- Filtros para la tabla `asignatura`
--
ALTER TABLE `asignatura`
  ADD CONSTRAINT `FK_9243D6CE91A441CC` FOREIGN KEY (`grado_id`) REFERENCES `grado` (`id`);

--
-- Filtros para la tabla `bloque`
--
ALTER TABLE `bloque`
  ADD CONSTRAINT `FK_F1DA68E8188E204C` FOREIGN KEY (`idencuesta`) REFERENCES `encuesta` (`id`);

--
-- Filtros para la tabla `nota`
--
ALTER TABLE `nota`
  ADD CONSTRAINT `FK_C8D03E0DC5C70C5B` FOREIGN KEY (`asignatura_id`) REFERENCES `asignatura` (`id`),
  ADD CONSTRAINT `FK_C8D03E0DFC28E5EE` FOREIGN KEY (`alumno_id`) REFERENCES `alumno` (`id`);

--
-- Filtros para la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD CONSTRAINT `pregunta_ibfk_1` FOREIGN KEY (`idencuesta`) REFERENCES `encuesta` (`id`),
  ADD CONSTRAINT `pregunta_ibfk_2` FOREIGN KEY (`idbloque`) REFERENCES `bloque` (`id`),
  ADD CONSTRAINT `pregunta_ibfk_3` FOREIGN KEY (`idtipo`) REFERENCES `tipo_pregunta` (`id`);

--
-- Filtros para la tabla `proyecto`
--
ALTER TABLE `proyecto`
  ADD CONSTRAINT `FK_6FD202B9A9276E6C` FOREIGN KEY (`tipo_id`) REFERENCES `tipo` (`id`);

--
-- Filtros para la tabla `respuesta`
--
ALTER TABLE `respuesta`
  ADD CONSTRAINT `respuesta_ibfk_1` FOREIGN KEY (`idpregunta`) REFERENCES `pregunta` (`id`);

--
-- Filtros para la tabla `resultado`
--
ALTER TABLE `resultado`
  ADD CONSTRAINT `resultado_ibfk_1` FOREIGN KEY (`idencuesta`) REFERENCES `encuesta` (`id`),
  ADD CONSTRAINT `resultado_ibfk_2` FOREIGN KEY (`idpregunta`) REFERENCES `pregunta` (`id`),
  ADD CONSTRAINT `resultado_ibfk_3` FOREIGN KEY (`idrespuesta`) REFERENCES `respuesta` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
