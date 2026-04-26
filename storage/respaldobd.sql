-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: estructurasjg
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
-- Current Database: `estructurasjg`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `estructurasjg` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `estructurasjg`;

--
-- Table structure for table `bodegas`
--

DROP TABLE IF EXISTS `bodegas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bodegas` (
  `id_bodega` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ubicacion` varchar(150) DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_bodega`),
  UNIQUE KEY `uk_bodegas_codigo` (`codigo`),
  UNIQUE KEY `uk_bodegas_nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bodegas`
--

LOCK TABLES `bodegas` WRITE;
/*!40000 ALTER TABLE `bodegas` DISABLE KEYS */;
INSERT INTO `bodegas` VALUES (1,'BOD-01','Bodega 1','Principal poblado',1,'2026-04-19 11:33:58'),(2,'BOD-02','Bodega 2','Segunda poblado',1,'2026-04-19 11:34:17');
/*!40000 ALTER TABLE `bodegas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `compras`
--

DROP TABLE IF EXISTS `compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `compras` (
  `id_compra` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `id_bodega` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_compra`),
  KEY `fk_compras_proveedor` (`id_proveedor`),
  KEY `fk_compras_bodega` (`id_bodega`),
  KEY `fk_compras_usuario` (`id_usuario`),
  CONSTRAINT `fk_compras_bodega` FOREIGN KEY (`id_bodega`) REFERENCES `bodegas` (`id_bodega`) ON UPDATE CASCADE,
  CONSTRAINT `fk_compras_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE CASCADE,
  CONSTRAINT `fk_compras_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `compras`
--

LOCK TABLES `compras` WRITE;
/*!40000 ALTER TABLE `compras` DISABLE KEYS */;
INSERT INTO `compras` VALUES (1,'CMP-20260419194046','Tubo estructural 100x100 calibre 18',1,1,1,10,200000.00,2000000.00,'2026-04-19 12:40:46'),(2,'CMP-20260419194149','Tubo cuadrado 3x1 calibre 18',1,1,1,20,80000.00,1600000.00,'2026-04-19 12:41:49'),(3,'CMP-20260419194229','Varilla entorchada calibre 20',1,2,1,15,40000.00,600000.00,'2026-04-19 12:42:29'),(4,'CMP-20260419194445','Teja pvc',1,1,1,20,300000.00,6000000.00,'2026-04-19 12:44:45'),(5,'CMP-20260419202012','Paquete soldadura',1,1,1,2,60000.00,120000.00,'2026-04-19 13:20:12'),(6,'FAC-2026-1111','Factura de entrada con 2 producto(s)',1,1,1,25,17000.00,425000.00,'2026-04-19 13:49:18'),(7,'FAC-2026-1114','Factura de entrada con 1 producto(s)',1,1,1,12,5000.00,60000.00,'2026-04-19 14:53:55'),(8,'FAC-2026-1114','Factura de entrada con 1 producto(s)',1,1,1,12,5000.00,60000.00,'2026-04-19 14:56:45'),(9,'FAC-2026-1115','Factura de entrada con 1 producto(s)',1,1,1,10,500000.00,5000000.00,'2026-04-22 19:37:57');
/*!40000 ALTER TABLE `compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_compras`
--

DROP TABLE IF EXISTS `detalle_compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_compras` (
  `id_detallecompra` int(11) NOT NULL AUTO_INCREMENT,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detallecompra`),
  KEY `fk_detallecompras_compra` (`id_compra`),
  KEY `fk_detallecompras_producto` (`id_producto`),
  CONSTRAINT `fk_detallecompras_compra` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detallecompras_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_compras`
--

LOCK TABLES `detalle_compras` WRITE;
/*!40000 ALTER TABLE `detalle_compras` DISABLE KEYS */;
INSERT INTO `detalle_compras` VALUES (1,1,2,10,200000.00),(2,2,3,20,80000.00),(3,3,4,15,40000.00),(4,4,5,20,300000.00),(5,5,6,2,60000.00),(6,6,7,10,20000.00),(7,6,8,15,15000.00),(8,7,9,12,5000.00),(9,8,9,12,5000.00),(10,9,10,10,500000.00);
/*!40000 ALTER TABLE `detalle_compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_ventas`
--

DROP TABLE IF EXISTS `detalle_ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `detalle_ventas` (
  `id_detalleventa` int(11) NOT NULL AUTO_INCREMENT,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_detalleventa`),
  KEY `fk_detalleventas_venta` (`id_venta`),
  KEY `fk_detalleventas_producto` (`id_producto`),
  CONSTRAINT `fk_detalleventas_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE,
  CONSTRAINT `fk_detalleventas_venta` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id_venta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_ventas`
--

LOCK TABLES `detalle_ventas` WRITE;
/*!40000 ALTER TABLE `detalle_ventas` DISABLE KEYS */;
INSERT INTO `detalle_ventas` VALUES (1,1,5,2,300000.00),(2,2,6,1,60000.00),(3,3,5,1,300000.00),(4,4,3,3,80000.00),(5,4,2,2,200000.00),(6,5,2,2,200000.00),(7,6,10,5,500000.00);
/*!40000 ALTER TABLE `detalle_ventas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `productos` (
  `id_producto` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `precio` decimal(10,2) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_producto`),
  UNIQUE KEY `uk_productos_codigo` (`codigo`),
  KEY `fk_productos_proveedor` (`id_proveedor`),
  CONSTRAINT `fk_productos_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id_proveedor`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (2,'1','Tubo estructural 100x100 calibre 18',1,6,200000.00,'2026-04-19 12:40:46'),(3,'2','Tubo cuadrado 3x1 calibre 18',1,17,80000.00,'2026-04-19 12:41:49'),(4,'3','Varilla entorchada calibre 20',1,15,40000.00,'2026-04-19 12:42:29'),(5,'4','Teja pvc',1,17,300000.00,'2026-04-19 12:44:45'),(6,'5','Paquete soldadura',1,1,60000.00,'2026-04-19 13:20:12'),(7,'6','Discos de corte',1,10,20000.00,'2026-04-19 13:49:18'),(8,'7','Discos de pulir',1,15,15000.00,'2026-04-19 13:49:18'),(9,'8','Tornillos',1,24,5000.00,'2026-04-19 14:53:55'),(10,'9','Compresor',1,5,500000.00,'2026-04-22 19:37:57');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proveedores` (
  `id_proveedor` int(11) NOT NULL AUTO_INCREMENT,
  `ruc` varchar(30) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `direccion` varchar(150) NOT NULL,
  PRIMARY KEY (`id_proveedor`),
  UNIQUE KEY `uk_proveedores_ruc` (`ruc`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'10001','Andrea Candelo Vargas','3127474566','Los naranjos, Cali');
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `p_registrar_productos` tinyint(1) NOT NULL DEFAULT 0,
  `p_modificar_productos` tinyint(1) NOT NULL DEFAULT 0,
  `p_registrar_movimientos` tinyint(1) NOT NULL DEFAULT 0,
  `p_consultar_movimientos` tinyint(1) NOT NULL DEFAULT 0,
  `p_gestionar_roles` tinyint(1) NOT NULL DEFAULT 0,
  `p_configuracion` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador',1,1,1,1,1,1,'2026-03-19 02:08:31','2026-03-19 02:08:31'),(2,'Empleado',1,0,1,1,0,0,'2026-03-19 02:08:31','2026-04-19 22:48:21');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_bodega`
--

DROP TABLE IF EXISTS `stock_bodega`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_bodega` (
  `id_stock_bodega` int(11) NOT NULL AUTO_INCREMENT,
  `id_bodega` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 0,
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_stock_bodega`),
  UNIQUE KEY `uk_stock_bodega` (`id_bodega`,`id_producto`),
  KEY `fk_stock_bodega_producto` (`id_producto`),
  CONSTRAINT `fk_stock_bodega_bodega` FOREIGN KEY (`id_bodega`) REFERENCES `bodegas` (`id_bodega`) ON UPDATE CASCADE,
  CONSTRAINT `fk_stock_bodega_producto` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id_producto`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_bodega`
--

LOCK TABLES `stock_bodega` WRITE;
/*!40000 ALTER TABLE `stock_bodega` DISABLE KEYS */;
INSERT INTO `stock_bodega` VALUES (1,1,2,6,0,'2026-04-19 16:55:47'),(2,1,3,17,0,'2026-04-19 14:37:04'),(3,2,4,15,0,'2026-04-19 12:42:29'),(4,1,5,17,0,'2026-04-19 14:36:19'),(5,1,6,1,0,'2026-04-19 13:20:41'),(6,1,7,10,0,'2026-04-19 13:49:18'),(7,1,8,15,0,'2026-04-19 13:49:18'),(8,1,9,24,0,'2026-04-19 14:56:45'),(9,1,10,5,0,'2026-04-22 19:39:06');
/*!40000 ALTER TABLE `stock_bodega` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario` (`usuario`),
  KEY `fk_usuarios_roles` (`id_rol`),
  CONSTRAINT `fk_usuarios_roles` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'Administrador','admin','$2y$10$TLXCi0ix/YTvmTb7XJc.ne7orTruqtXKnBMxj4f8juNbVsTipyXZq',1,1,'2026-04-22 19:36:56','2026-03-19 02:27:08','2026-04-23 00:36:56'),(2,'Sayi Ramirez','empleado1','$2y$10$f9HYh1suIFFZa.WLEO3Y9e8HjHlMogg9m1YnSm/MNP.Qqgu39bX6q',2,1,'2026-04-19 17:50:47','2026-03-19 03:33:22','2026-04-19 22:50:47'),(3,'Giuliano Jaramillo','empleado2','$2y$10$cJLbJDs1j4veAXRV5/B26esa6L3vaG0Bk9By58Hwas7ADsDYp1S6.',2,1,'2026-04-19 17:47:47','2026-04-19 17:04:54','2026-04-19 22:47:47');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ventas`
--

DROP TABLE IF EXISTS `ventas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(30) NOT NULL,
  `id_bodega` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `descripcion` varchar(30) NOT NULL,
  `motivo_salida` enum('normal','devolucion','fallo') NOT NULL DEFAULT 'normal',
  `cantidad` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_venta`),
  KEY `fk_ventas_usuario` (`id_usuario`),
  KEY `fk_ventas_bodega` (`id_bodega`),
  CONSTRAINT `fk_ventas_bodega` FOREIGN KEY (`id_bodega`) REFERENCES `bodegas` (`id_bodega`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ventas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ventas`
--

LOCK TABLES `ventas` WRITE;
/*!40000 ALTER TABLE `ventas` DISABLE KEYS */;
INSERT INTO `ventas` VALUES (1,'VTA-20260419201157',1,1,'Teja pvc','normal',2,'2026-04-19 13:11:57'),(2,'VTA-20260419202041',1,1,'Paquete soldadura','normal',1,'2026-04-19 13:20:41'),(3,'FAC-2026-1112',1,1,'Factura con 1 prod','normal',1,'2026-04-19 14:36:19'),(4,'FAC-2026-1113',1,1,'Factura con 2 prod','normal',5,'2026-04-19 14:37:04'),(5,'VTA-20260419235547',1,2,'Factura con 1 prod','normal',2,'2026-04-19 16:55:47'),(6,'FAC-2026-1116',1,1,'Factura con 1 prod','normal',5,'2026-04-22 19:39:06');
/*!40000 ALTER TABLE `ventas` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-26 16:11:01
