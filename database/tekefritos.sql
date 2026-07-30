-- ========================================================
-- AUTO-GENERATED DATABASE EXPORT
-- Database: `tekefritos` 
-- Exported on: 2026-07-30 01:10:59
-- ========================================================

CREATE DATABASE IF NOT EXISTS `tekefritos` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tekefritos`;

-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: tekefritos
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accion` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `tabla` varchar(50) DEFAULT NULL,
  `id_registro` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (1,'Eliminar Personal','Eliminó a Trabajador del sistema',11,'usuarios',8,'2026-07-12 16:53:43'),(2,'Crear usuario','Creó usuario NNE (rol: trabajador)',11,'usuarios',13,'2026-07-12 16:54:11'),(3,'Eliminar Personal','Eliminó a NNE del sistema',11,'usuarios',13,'2026-07-12 16:54:20'),(4,'Crear usuario','Creó usuario WW (rol: trabajador)',11,'usuarios',14,'2026-07-12 16:54:47'),(5,'Mover a Papelera','Movió a papelera WW (Personal)',11,'usuarios',14,'2026-07-12 17:00:52'),(6,'Activar','Habilitó WW desde Papelera',11,'usuarios',14,'2026-07-12 17:00:56'),(7,'Mover a Papelera','Movió a papelera WW (Personal)',11,'usuarios',14,'2026-07-12 17:01:00'),(8,'Eliminar Permanente','Eliminó definitivamente WW (Personal)',11,'usuarios',14,'2026-07-12 17:01:04'),(9,'Crear categoría','Creó categoría tequeño con queso',11,'categorias',NULL,'2026-07-12 17:05:32'),(10,'Crear categoría','Creó categoría Adrian',11,'categorias',NULL,'2026-07-12 17:05:42'),(11,'Eliminar categoría','Eliminó categoría Adrian (ID 2)',11,'categorias',2,'2026-07-12 17:05:47'),(12,'Crear producto','Creó producto tequeño con queso',11,'productos',1,'2026-07-12 17:08:43'),(13,'Crear pedido','Creó pedido #1 (total: Bs.1234.87, estado: Completado, entrega: Local)',11,'pedidos',1,'2026-07-12 17:15:16'),(14,'Crear contacto','Creó Cliente Adrian',11,'contactos',1,'2026-07-12 17:15:34'),(15,'Editar producto','Editó producto tequeño con queso (ID 1)',11,'productos',1,'2026-07-12 17:17:55'),(16,'Editar pedido','Editó pedido #1 (total: Bs.1234.87, estado: Completado, entrega: Local)',11,'pedidos',1,'2026-07-12 17:18:10'),(17,'Mover a Papelera','Movió a papelera Pedido #1 (Pedidos)',11,'pedidos',1,'2026-07-12 17:18:21'),(18,'Activar','Habilitó Pedido #1 desde Papelera',11,'pedidos',1,'2026-07-12 17:18:24'),(19,'Crear insumo','Creó insumo Adrian',11,'materia_prima',1,'2026-07-12 17:23:15'),(20,'Crear contacto','Creó Proveedor cine',11,'contactos',2,'2026-07-12 17:23:27'),(21,'Crear insumo','Creó insumo cine',11,'materia_prima',2,'2026-07-12 17:26:49'),(22,'Mover a Papelera','Movió a papelera Adrian (MateriaPrima)',11,'materia_prima',1,'2026-07-12 17:27:00'),(23,'Crear insumo','Creó insumo Adrian',11,'materia_prima',3,'2026-07-12 17:27:11'),(24,'Mover a Papelera','Movió a papelera cine (MateriaPrima)',11,'materia_prima',2,'2026-07-12 17:27:27'),(25,'Mover a Papelera','Movió a papelera Adrian (MateriaPrima)',11,'materia_prima',3,'2026-07-12 17:27:30'),(26,'Crear insumo','Creó insumo Tequeño de Queso y Guayaba',11,'materia_prima',4,'2026-07-12 17:28:33'),(27,'Registrar pérdida','Registró pérdida: 1 uds de tequeño con queso (Consumo interno)',11,'perdidas',1,'2026-07-12 17:30:24'),(28,'Toggle producto','Cambió tequeño con queso: Completado → Disponible',11,'productos',1,'2026-07-12 17:30:44'),(29,'Mover a Papelera','Movió a papelera tequeño con queso (Productos)',11,'productos',1,'2026-07-12 18:21:00'),(30,'Mover a Papelera','Movió a papelera Pedido #1 (Pedidos)',11,'pedidos',1,'2026-07-12 18:21:05'),(31,'Mover a Papelera','Movió a papelera Adrian (Clientes)',11,'contactos',1,'2026-07-12 18:21:09'),(32,'Mover a Papelera','Movió a papelera cine (Proveedores)',11,'contactos',2,'2026-07-12 18:21:15'),(33,'Mover a Papelera','Movió a papelera Tequeño de Queso y Guayaba (MateriaPrima)',11,'materia_prima',4,'2026-07-12 18:21:18'),(34,'Eliminar documento','Envió documento Nota de Entrega #000000 a la papelera',11,'documentos',2,'2026-07-12 18:21:35'),(35,'Eliminar documento','Envió documento Factura #000000 a la papelera',11,'documentos',1,'2026-07-12 18:21:36'),(36,'Vaciar Papelera','Vació completamente la papelera del sistema',11,'sistema',NULL,'2026-07-12 18:27:36'),(37,'Crear producto','Creó producto cine',11,'productos',2,'2026-07-13 03:42:13'),(38,'Toggle producto','Cambió cine: Disponible → Agotado',11,'productos',2,'2026-07-13 03:42:25'),(39,'Toggle producto','Cambió cine: Agotado → Disponible',11,'productos',2,'2026-07-13 03:42:27'),(40,'Mover a Papelera','Movió a papelera cine (Productos)',11,'productos',2,'2026-07-13 03:42:29'),(41,'Activar','Habilitó cine desde Papelera',11,'productos',2,'2026-07-13 03:42:32'),(42,'Crear pedido','Creó pedido #2 (total: Bs.1419.38, estado: Completado, entrega: Local)',11,'pedidos',2,'2026-07-13 03:42:43'),(43,'Editar pedido','Editó pedido #2 (total: Bs.1419.38, estado: Completado, entrega: Local)',11,'pedidos',2,'2026-07-13 03:42:49'),(44,'Mover a Papelera','Movió a papelera Pedido #2 (Pedidos)',11,'pedidos',2,'2026-07-13 03:42:52'),(45,'Activar','Habilitó Pedido #2 desde Papelera',11,'pedidos',2,'2026-07-13 03:42:54'),(46,'Crear contacto','Creó Cliente Adrian',11,'contactos',3,'2026-07-13 03:43:03'),(47,'Editar contacto','Editó Cliente Adrian1 (ID 3)',11,'contactos',3,'2026-07-13 03:43:08'),(48,'Mover a Papelera','Movió a papelera Adrian1 (Clientes)',11,'contactos',3,'2026-07-13 03:43:11'),(49,'Activar','Habilitó Adrian1 desde Papelera',11,'contactos',3,'2026-07-13 03:43:13'),(50,'Editar usuario','Editó usuario Jose Perez (ID 12)',11,'usuarios',12,'2026-07-13 03:43:25'),(51,'Mover a Papelera','Movió a papelera Jose Perez (Personal)',11,'usuarios',12,'2026-07-13 03:43:31'),(52,'Activar','Habilitó Jose Perez desde Papelera',11,'usuarios',12,'2026-07-13 03:43:33'),(53,'Crear contacto','Creó Proveedor Adrian',11,'contactos',4,'2026-07-13 03:43:50'),(54,'Crear insumo','Creó insumo Adrian',11,'materia_prima',5,'2026-07-13 03:44:03'),(55,'Registrar pérdida','Registró pérdida: 1 uds de cine (Muestra comercial)',11,'perdidas',2,'2026-07-13 03:47:26'),(56,'Vaciar Papelera','Vació completamente la papelera del sistema',11,'sistema',NULL,'2026-07-13 03:50:15'),(57,'Editar perfil','Editó su propio perfil (ID 11)',11,'usuarios',11,'2026-07-13 06:21:33'),(58,'Crear contacto','Creó Proveedor Adrian',11,'contactos',5,'2026-07-13 11:29:46'),(59,'Mover a Papelera','Movió a papelera Adrian (Proveedores)',11,'contactos',5,'2026-07-13 11:29:49'),(60,'Activar','Habilitó Adrian desde Papelera',11,'contactos',5,'2026-07-13 11:29:54'),(61,'Mover a Papelera','Movió a papelera Adrian (Proveedores)',11,'contactos',5,'2026-07-13 11:29:59'),(62,'Eliminar Permanente','Eliminó definitivamente Adrian (Proveedor)',11,'contactos',5,'2026-07-13 11:30:03'),(63,'Eliminar documento','Envió documento Factura #000000 a la papelera',11,'documentos',5,'2026-07-15 20:45:39'),(64,'Eliminar documento','Envió documento Nota de Entrega #000000 a la papelera',11,'documentos',4,'2026-07-15 20:45:41'),(65,'Eliminar documento','Envió documento Factura #000000 a la papelera',11,'documentos',3,'2026-07-15 20:45:43'),(66,'Eliminar permanente','Eliminó definitivamente documento Factura #000000',11,'documentos',3,'2026-07-15 20:45:47'),(67,'Eliminar permanente','Eliminó definitivamente documento Nota de Entrega #000000',11,'documentos',4,'2026-07-15 20:45:49'),(68,'Eliminar permanente','Eliminó definitivamente documento Factura #000000',11,'documentos',5,'2026-07-15 20:45:51');
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `orden` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (1,'tequeño con queso','Bien ricos',0);
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactos`
--

DROP TABLE IF EXISTS `contactos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contactos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('Cliente','Proveedor') NOT NULL,
  `categoria` enum('Regular','Negocio') DEFAULT 'Regular',
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactos`
--

LOCK TABLES `contactos` WRITE;
/*!40000 ALTER TABLE `contactos` DISABLE KEYS */;
INSERT INTO `contactos` VALUES (3,'Adrian1','Cliente','Regular','04128803932','coordinadora@unefa.edu.ve','Venezuela- Carabobo','2026-07-13 03:43:03','Activo'),(4,'Adrian','Proveedor',NULL,'04128803932','docente@unefa.edu.ve','Venezuela- Carabobo','2026-07-13 03:43:50','Activo');
/*!40000 ALTER TABLE `contactos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalles_pedido`
--

DROP TABLE IF EXISTS `detalles_pedido`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalles_pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `iva_aplicado` decimal(5,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `id_pedido` (`id_pedido`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `detalles_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `detalles_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalles_pedido`
--

LOCK TABLES `detalles_pedido` WRITE;
/*!40000 ALTER TABLE `detalles_pedido` DISABLE KEYS */;
INSERT INTO `detalles_pedido` VALUES (4,2,2,2,709.69,0.00);
/*!40000 ALTER TABLE `detalles_pedido` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documentos`
--

DROP TABLE IF EXISTS `documentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_contacto` int(11) DEFAULT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `archivo_real` varchar(255) NOT NULL,
  `datos` text DEFAULT NULL,
  `tipo` enum('Nota de Entrega','Factura','Otro') DEFAULT 'Otro',
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `id_usuario` int(11) DEFAULT NULL,
  `estado` enum('Disponible','Activo','Inactivo') DEFAULT 'Disponible',
  PRIMARY KEY (`id`),
  KEY `id_contacto` (`id_contacto`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `documentos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documentos`
--

LOCK TABLES `documentos` WRITE;
/*!40000 ALTER TABLE `documentos` DISABLE KEYS */;
/*!40000 ALTER TABLE `documentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materia_prima`
--

DROP TABLE IF EXISTS `materia_prima`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `materia_prima` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unidad` varchar(20) NOT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `minimo` decimal(10,2) NOT NULL DEFAULT 5.00,
  `estado` varchar(50) DEFAULT 'Disponible',
  `id_proveedor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_mp_proveedor` (`id_proveedor`),
  CONSTRAINT `fk_mp_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `contactos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materia_prima`
--

LOCK TABLES `materia_prima` WRITE;
/*!40000 ALTER TABLE `materia_prima` DISABLE KEYS */;
INSERT INTO `materia_prima` VALUES (5,'Adrian',111.00,'g','2026-07-13 03:44:03',5.00,'Disponible',4);
/*!40000 ALTER TABLE `materia_prima` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pedidos`
--

DROP TABLE IF EXISTS `pedidos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL,
  `estado` enum('Completado','Cancelado','Pendiente','En preparacion','Inactivo') DEFAULT 'Completado',
  `tipo_entrega` enum('A domicilio','Local','Negocio externo') DEFAULT 'Local',
  `direccion` text DEFAULT NULL,
  `id_contacto` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_contacto` (`id_contacto`),
  CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_contacto`) REFERENCES `contactos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pedidos`
--

LOCK TABLES `pedidos` WRITE;
/*!40000 ALTER TABLE `pedidos` DISABLE KEYS */;
INSERT INTO `pedidos` VALUES (2,'2026-07-13 03:42:43',1419.38,'Completado','Local',NULL,NULL);
/*!40000 ALTER TABLE `pedidos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perdidas`
--

DROP TABLE IF EXISTS `perdidas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perdidas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` varchar(50) DEFAULT 'Disponible',
  PRIMARY KEY (`id`),
  KEY `id_producto` (`id_producto`),
  CONSTRAINT `perdidas_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perdidas`
--

LOCK TABLES `perdidas` WRITE;
/*!40000 ALTER TABLE `perdidas` DISABLE KEYS */;
INSERT INTO `perdidas` VALUES (2,2,1,'Muestra comercial','2026-07-13 03:47:26','Disponible');
/*!40000 ALTER TABLE `perdidas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `precio_usd` decimal(10,2) DEFAULT 0.00,
  `iva` decimal(5,2) DEFAULT 0.00,
  `aplica_iva` tinyint(1) DEFAULT 0,
  `costo` decimal(10,2) DEFAULT 0.00,
  `costo_usd` decimal(10,2) DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `peso` varchar(50) DEFAULT '0g',
  `categoria_id` int(11) DEFAULT NULL,
  `permite_relleno` tinyint(1) DEFAULT 0,
  `imagen` varchar(255) DEFAULT 'placeholder.png',
  `estado` enum('Disponible','Agotado','Inactivo') DEFAULT 'Disponible',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock_minimo` int(11) NOT NULL DEFAULT 10,
  `tipo` enum('Elaboracion','Sin elaboracion') DEFAULT 'Elaboracion',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `productos_ibfk_1` (`categoria_id`),
  CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (2,'PROD-16','cine','harina de trigo',732.48,1.00,0.00,0,366.24,0.50,17,'170 kg',1,0,'1783914133_6a545e9565a02.webp','Disponible','2026-07-13 03:42:13',10,'Elaboracion');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sistema_config`
--

DROP TABLE IF EXISTS `sistema_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sistema_config` (
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sistema_config`
--

LOCK TABLES `sistema_config` WRITE;
/*!40000 ALTER TABLE `sistema_config` DISABLE KEYS */;
INSERT INTO `sistema_config` VALUES ('social_facebook','https://www.facebook.com/share/1EKWSiT7MN/','2026-07-12 15:05:54'),('social_instagram','https://www.instagram.com/tekefritos?igsh=d3lkMWNzYWhxY3N2','2026-07-12 15:05:54'),('social_tiktok','https://www.tiktok.com/@tekefritos?_r=1&_t=ZS-97yTD2VgUZ2','2026-07-12 15:05:54'),('social_whatsapp','+584144240080','2026-07-12 15:05:54'),('tasa_iva','16','2026-07-07 22:39:17'),('tasa_sync_anterior','732.48','2026-07-18 15:57:30');
/*!40000 ALTER TABLE `sistema_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `rol` varchar(50) DEFAULT 'Empleado',
  `contrasena` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (9,'Cliente',NULL,'cliente@tekefritos.com','trabajador','$2y$10$ESRrzifKmpYzssrrSDj.C.GZo0o89ILTU3JIy3ltEE1n5iHJBXfIm','2026-06-23 01:38:39','Activo'),(11,'Admin SEXI',NULL,'admin@tekefritos.com','admin','$2y$10$ESRrzifKmpYzssrrSDj.C.GZo0o89ILTU3JIy3ltEE1n5iHJBXfIm','2026-06-24 01:59:00','Activo'),(12,'Jose Perez',NULL,'Vendedor@tekefritos.com','vendedor','$2y$10$sJHoE0CDRSMqit5p6y4Gf.RhBZY6HYT5mjArm/O9oibEAk3gf/H5G','2026-07-08 02:13:44','Activo');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'tekefritos'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-29 19:11:00
