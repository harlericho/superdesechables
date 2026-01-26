-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.33 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             11.3.0.6295
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db_tienda
DROP DATABASE IF EXISTS `db_tienda`;
CREATE DATABASE IF NOT EXISTS `db_tienda` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_spanish_ci */;
USE `db_tienda`;

-- Dumping structure for table db_tienda.tbl_categoria
DROP TABLE IF EXISTS `tbl_categoria`;
CREATE TABLE IF NOT EXISTS `tbl_categoria` (
  `categoria_id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria_descripcion` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `categoria_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `categoria_estado` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`categoria_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_categoria: ~0 rows (approximately)
DELETE FROM `tbl_categoria`;
/*!40000 ALTER TABLE `tbl_categoria` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_categoria` ENABLE KEYS */;

-- Dumping structure for table db_tienda.tbl_cliente
DROP TABLE IF EXISTS `tbl_cliente`;
CREATE TABLE IF NOT EXISTS `tbl_cliente` (
  `cliente_id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_dni` varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  `cliente_nombres` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `cliente_apellidos` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `cliente_direccion` text COLLATE utf8_spanish_ci,
  `cliente_email` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `cliente_telefono` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  `cliente_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `cliente_updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cliente_estado` tinyint(1) NOT NULL DEFAULT '1',
  `rol_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`cliente_id`),
  KEY `IX_Relationship2` (`rol_id`),
  CONSTRAINT `Relationship2` FOREIGN KEY (`rol_id`) REFERENCES `tbl_rol` (`rol_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_cliente: ~0 rows (approximately)
DELETE FROM `tbl_cliente`;
/*!40000 ALTER TABLE `tbl_cliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_cliente` ENABLE KEYS */;

-- Dumping structure for table db_tienda.tbl_detalle
DROP TABLE IF EXISTS `tbl_detalle`;
CREATE TABLE IF NOT EXISTS `tbl_detalle` (
  `detalle_id` int(11) NOT NULL AUTO_INCREMENT,
  `detalle_cantidad` int(11) NOT NULL,
  `detalle_precio_unit` decimal(10,2) NOT NULL,
  `detalle_descuento` decimal(10,2) DEFAULT NULL,
  `detalle_total` decimal(10,2) NOT NULL,
  `detalle_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `detalle_estado` tinyint(1) NOT NULL DEFAULT '1',
  `factura_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`detalle_id`),
  KEY `IX_Relationship9` (`factura_id`),
  KEY `IX_Relationship10` (`producto_id`),
  CONSTRAINT `Relationship10` FOREIGN KEY (`producto_id`) REFERENCES `tbl_producto` (`producto_id`),
  CONSTRAINT `Relationship9` FOREIGN KEY (`factura_id`) REFERENCES `tbl_factura` (`factura_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_detalle: ~0 rows (approximately)
DELETE FROM `tbl_detalle`;
/*!40000 ALTER TABLE `tbl_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_detalle` ENABLE KEYS */;

-- Dumping structure for table db_tienda.tbl_factura
DROP TABLE IF EXISTS `tbl_factura`;
CREATE TABLE IF NOT EXISTS `tbl_factura` (
  `factura_id` int(11) NOT NULL AUTO_INCREMENT,
  `factura_num_comprobante` varchar(20) COLLATE utf8_spanish_ci NOT NULL,
  `factura_fecha` date NOT NULL,
  `factura_subtotal` decimal(10,2) DEFAULT NULL,
  `factura_impuesto` decimal(10,2) DEFAULT NULL,
  `factura_total` decimal(10,2) DEFAULT NULL,
  `factura_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `factura_updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `factura_estado` tinyint(1) NOT NULL DEFAULT '1',
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo_comp_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`factura_id`),
  KEY `IX_Relationship6` (`cliente_id`),
  KEY `IX_Relationship7` (`usuario_id`),
  KEY `IX_Relationship8` (`tipo_comp_id`),
  CONSTRAINT `Relationship6` FOREIGN KEY (`cliente_id`) REFERENCES `tbl_cliente` (`cliente_id`),
  CONSTRAINT `Relationship7` FOREIGN KEY (`usuario_id`) REFERENCES `tbl_usuario` (`usuario_id`),
  CONSTRAINT `Relationship8` FOREIGN KEY (`tipo_comp_id`) REFERENCES `tbl_tipo_comprobante` (`tipo_comp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_factura: ~0 rows (approximately)
DELETE FROM `tbl_factura`;
/*!40000 ALTER TABLE `tbl_factura` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_factura` ENABLE KEYS */;

-- Dumping structure for table db_tienda.tbl_producto
DROP TABLE IF EXISTS `tbl_producto`;
CREATE TABLE IF NOT EXISTS `tbl_producto` (
  `producto_id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_codigo` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `producto_nombre` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `producto_descripcion` text COLLATE utf8_spanish_ci,
  `producto_precio_compra` decimal(10,2) NOT NULL,
  `producto_precio_venta` decimal(10,2) NOT NULL,
  `producto_stock` int(11) NOT NULL,
  `producto_imagen` varchar(150) COLLATE utf8_spanish_ci DEFAULT NULL,
  `producto_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `producto_updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `producto_estado` tinyint(1) NOT NULL DEFAULT '1',
  `categoria_id` int(11) DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`producto_id`),
  KEY `IX_Relationship3` (`categoria_id`),
  KEY `IX_Relationship5` (`proveedor_id`),
  CONSTRAINT `Relationship3` FOREIGN KEY (`categoria_id`) REFERENCES `tbl_categoria` (`categoria_id`),
  CONSTRAINT `Relationship5` FOREIGN KEY (`proveedor_id`) REFERENCES `tbl_proveedor` (`proveedor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_producto: ~0 rows (approximately)
DELETE FROM `tbl_producto`;
/*!40000 ALTER TABLE `tbl_producto` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_producto` ENABLE KEYS */;

-- Dumping structure for table db_tienda.tbl_proveedor
DROP TABLE IF EXISTS `tbl_proveedor`;
CREATE TABLE IF NOT EXISTS `tbl_proveedor` (
  `proveedor_id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_dni` varchar(15) COLLATE utf8_spanish_ci NOT NULL,
  `proveedor_nombres` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `proveedor_email` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `proveedor_telefono` varchar(10) COLLATE utf8_spanish_ci NOT NULL,
  `proveedor_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `proveedor_estado` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`proveedor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_proveedor: ~0 rows (approximately)
DELETE FROM `tbl_proveedor`;
/*!40000 ALTER TABLE `tbl_proveedor` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_proveedor` ENABLE KEYS */;

-- Dumping structure for table db_tienda.tbl_rol
DROP TABLE IF EXISTS `tbl_rol`;
CREATE TABLE IF NOT EXISTS `tbl_rol` (
  `rol_id` int(11) NOT NULL AUTO_INCREMENT,
  `rol_descripcion` varchar(100) COLLATE utf8_spanish_ci NOT NULL,
  `rol_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rol_estado` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`rol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_rol: ~1 rows (approximately)
DELETE FROM `tbl_rol`;
/*!40000 ALTER TABLE `tbl_rol` DISABLE KEYS */;
INSERT INTO `tbl_rol` (`rol_id`, `rol_descripcion`, `rol_created_date`, `rol_estado`) VALUES
	(1, 'ADMINISTRADOR', '2022-02-22 15:21:54', 1),
  (2, 'EMPLEADO', '2022-02-22 15:21:54', 1),
  (3, 'CLIENTE', '2022-02-22 15:21:54', 1);
/*!40000 ALTER TABLE `tbl_rol` ENABLE KEYS */;

-- Dumping structure for table db_tienda.tbl_temp_detalle
DROP TABLE IF EXISTS `tbl_temp_detalle`;
CREATE TABLE IF NOT EXISTS `tbl_temp_detalle` (
  `temp_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `temp_cod_producto` varchar(15) COLLATE utf8_spanish_ci DEFAULT NULL,
  `temp_nombre_producto` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `temp_cantidad_vender` int(11) DEFAULT NULL,
  `temp_precio_producto` decimal(10,2) DEFAULT NULL,
  `temp_descuento` decimal(10,2) DEFAULT NULL,
  `temp_total` decimal(10,2) DEFAULT NULL,
  `temp_id_producto` int(11) DEFAULT NULL,
  PRIMARY KEY (`temp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_temp_detalle: ~0 rows (approximately)
DELETE FROM `tbl_temp_detalle`;
/*!40000 ALTER TABLE `tbl_temp_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_temp_detalle` ENABLE KEYS */;

-- Dumping structure for table db_tienda.tbl_tipo_comprobante
DROP TABLE IF EXISTS `tbl_tipo_comprobante`;
CREATE TABLE IF NOT EXISTS `tbl_tipo_comprobante` (
  `tipo_comp_id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_comp_descripcion` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `tipo_comp_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tipo_comp_estado` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tipo_comp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_tipo_comprobante: ~0 rows (approximately)
DELETE FROM `tbl_tipo_comprobante`;
INSERT INTO `tbl_tipo_comprobante` (`tipo_comp_id`, `tipo_comp_descripcion`, `tipo_comp_created_date`, `tipo_comp_estado`) VALUES
  (1, 'EFECTIVO', '2022-02-22 15:22:33', 1),
  (2, 'TRANSFERENCIA', '2022-02-22 15:22:33', 1),
  (3, 'CHEQUE', '2022-02-22 15:22:33', 1),
  (4, 'TARJETA', '2022-02-22 15:22:33', 1),
  (5, 'DEPOSITO', '2022-02-22 15:22:33', 1),
  (6, 'OTRO', '2022-02-22 15:22:33', 1);
/*!40000 ALTER TABLE `tbl_tipo_comprobante` DISABLE KEYS */;
/*!40000 ALTER TABLE `tbl_tipo_comprobante` ENABLE KEYS */;

-- Dumping structure for table db_tienda.tbl_usuario
DROP TABLE IF EXISTS `tbl_usuario`;
CREATE TABLE IF NOT EXISTS `tbl_usuario` (
  `usuario_id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_nombres` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `usuario_email` varchar(150) COLLATE utf8_spanish_ci NOT NULL,
  `usuario_password` varchar(255) COLLATE utf8_spanish_ci NOT NULL,
  `usuario_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_updated_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usuario_estado` tinyint(1) NOT NULL DEFAULT '1',
  `rol_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`usuario_id`),
  KEY `IX_Relationship1` (`rol_id`),
  CONSTRAINT `Relationship1` FOREIGN KEY (`rol_id`) REFERENCES `tbl_rol` (`rol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- Dumping data for table db_tienda.tbl_usuario: ~1 rows (approximately)
DELETE FROM `tbl_usuario`;
/*!40000 ALTER TABLE `tbl_usuario` DISABLE KEYS */;
INSERT INTO `tbl_usuario` (`usuario_id`, `usuario_nombres`, `usuario_email`, `usuario_password`, `usuario_created_date`, `usuario_updated_date`, `usuario_estado`, `rol_id`) VALUES
	(1, 'ADMIN', 'admin@admin.com', 'QTgyTDFRM1JNS2JxRVVVak5NNVZoUT09', '2022-02-22 15:22:33', '2022-02-22 15:22:33', 1, 1),
  (2, 'EMPLEADO', 'empleado@empleado.com', 'QXRIeTBwRWNoV1ZOdXpKTmVrdmN3dz09', '2022-02-22 15:22:33', '2022-02-22 15:22:33', 1, 2);
/*!40000 ALTER TABLE `tbl_usuario` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
DROP TABLE IF EXISTS `tbl_config_serie`;
CREATE TABLE IF NOT EXISTS `tbl_config_serie` (
  `config_id` int(11) NOT NULL AUTO_INCREMENT,
  `config_primera_serie` varchar(45) COLLATE utf8_spanish_ci NOT NULL,
  `config_segunda_serie` varchar(45) COLLATE utf8_spanish_ci NOT NULL,
  `config_secuencial` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  PRIMARY KEY (`config_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DELETE FROM `tbl_config_serie`;
/*!40000 ALTER TABLE `tbl_config_serie` DISABLE KEYS */;
INSERT INTO `tbl_config_serie` (`config_id`, `config_primera_serie`, `config_segunda_serie`, `config_secuencial`) VALUES
  (1, '001', '001', '100');

DROP TABLE IF EXISTS `tbl_movimiento_caja`;
-- Dumping structure for table db_tienda.tbl_movimiento_caja
CREATE TABLE IF NOT EXISTS `tbl_movimiento_caja` (
  `mov_id` int(11) NOT NULL AUTO_INCREMENT,
  `mov_fecharegistro` date NOT NULL,
  `mov_descripcion` text COLLATE utf8_spanish_ci NOT NULL,
  `mov_tipo` varchar(50) COLLATE utf8_spanish_ci NOT NULL,
  `mov_monto` decimal(10,2) NOT NULL,
  `mov_created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`mov_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DELETE FROM `tbl_movimiento_caja`;
