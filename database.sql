-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: uejosefinapimentel
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
-- Table structure for table `acta_estudiantes`
--

DROP TABLE IF EXISTS `acta_estudiantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acta_estudiantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acta_id` int(11) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 1,
  `apellidos_nombres` varchar(180) NOT NULL,
  `nombre_normalizado` varchar(180) NOT NULL,
  `cedula` varchar(35) DEFAULT NULL,
  `cedula_normalizada` varchar(25) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `anio_formato` tinyint(1) NOT NULL DEFAULT 2,
  `literal_nota` varchar(15) NOT NULL,
  `observacion` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_est_acta_orden` (`acta_id`,`orden`),
  KEY `idx_est_cedula_norm` (`cedula_normalizada`),
  KEY `idx_est_fecha_nac` (`fecha_nacimiento`),
  KEY `idx_est_nombre_norm` (`nombre_normalizado`),
  CONSTRAINT `acta_estudiantes_ibfk_1` FOREIGN KEY (`acta_id`) REFERENCES `actas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=261 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acta_estudiantes`
--

LOCK TABLES `acta_estudiantes` WRITE;
/*!40000 ALTER TABLE `acta_estudiantes` DISABLE KEYS */;
INSERT INTO `acta_estudiantes` VALUES (52,2,1,'Agustin Antonio, Perez Gimenez','agustin antonio perez gimenez','12345678','12345678','1999-01-03',2,'A',NULL),(53,2,2,'Felipe Andres, Osorio Colmenares','felipe andres osorio colmenares',NULL,NULL,'1989-11-07',2,'B',NULL),(54,2,3,'Franco Josue, Lopez Obrador','franco josue lopez obrador','8797666','8797666','1998-10-20',2,'C',NULL),(55,2,4,'Luis Pepe, Marquez Martinez','luis pepe marquez martinez','3232323','3232323','2001-11-09',2,'D',NULL),(56,2,5,'Axel Jose, Rodriguez Garcia','axel jose rodriguez garcia','1010212','1010212','1995-04-07',2,'I','Inasistente'),(57,2,6,'Andrade B, Gustavo A','andrade b gustavo a',NULL,NULL,'1998-02-01',2,'B',NULL),(58,2,7,'Azuaje Gonzales, Ruth Sofia','azuaje gonzales ruth sofia','342352','342352','1997-10-13',2,'C',NULL),(156,4,1,'Andrade B, Gustavo A','andrade b gustavo a','25329606','25329606','1997-03-11',2,'A',NULL),(157,4,2,'Andrade B, Gustavo A','andrade b gustavo a','25919122','25919122','1989-10-13',2,'B',NULL),(158,4,3,'Andrade B, Gustavo A','andrade b gustavo a','8797666','8797666','1997-08-08',2,'B',NULL),(159,4,4,'Andrade B, Gustavo A','andrade b gustavo a','3232323','3232323','1997-03-09',2,'C',NULL),(160,4,5,'Andrade B, Gustavo A','andrade b gustavo a','1010212','1010212','1997-01-08',2,'B',NULL),(237,3,1,'Andrade B, Gustavo A','andrade b gustavo a','25329606','25329606','1997-03-11',4,'C',NULL),(238,3,2,'Amaldi Marín, Adriana C','amaldi marin adriana c','25919122','25919122','1997-10-13',2,'B',NULL),(239,3,3,'Azuaje Y, Yoseli Del C','azuaje y yoseli del c','25882125','25882125','1997-08-08',2,'B',NULL),(240,3,4,'Bracamonte L, Jesús Manuel','bracamonte l jesus manuel','26557735','26557735','1997-03-30',2,'C',NULL),(241,3,5,'Bracamonte P, Christian C','bracamonte p christian c','25604398','25604398','1997-01-08',2,'B',NULL),(242,3,6,'Briceño N, Nayarith E','briceno n nayarith e','26757808','26757808','1997-12-01',2,'B',NULL),(243,3,7,'Briceño M, Yoxileth A','briceno m yoxileth a','26575813','26575813','1997-07-24',2,'C',NULL),(244,3,8,'Castellano A, Samuel J','castellano a samuel j','26114926','26114926','1997-06-14',2,'B',NULL),(245,3,9,'Calderón V, Yisel Patricia','calderon v yisel patricia','26413575','26413575','1997-09-13',2,'C',NULL),(246,3,10,'Daboin M, José Enrique','daboin m jose enrique','26413568','26413568','1997-10-13',2,'C',NULL),(247,3,11,'Delgado B, Magaly Del L','delgado b magaly del l','26311322','26311322','1997-12-14',2,'B',NULL),(248,3,12,'Gil Umbria, Jordan A','gil umbria jordan a','26263420','26263420','1998-03-04',2,'B',NULL),(249,3,13,'Yudiño, Anyi Del Carmen','yudino anyi del carmen','27070608','27070608','1995-05-29',2,'B',NULL),(250,3,14,'Yudiño D, Johandri J','yudino d johandri j','26557693','26557693','1997-04-09',2,'B',NULL),(251,3,15,'Matos S, Leandro D','matos s leandro d',NULL,NULL,'1997-05-15',2,'B',NULL),(252,3,16,'Mijares C, Miguel L','mijares c miguel l','26094811','26094811','1997-01-28',2,'A',NULL),(253,3,17,'Montilla V, Franklin J','montilla v franklin j','26311617','26311617','1997-05-27',2,'B',NULL),(254,3,18,'Paredes A, Yohandra G','paredes a yohandra g','26114756','26114756','1997-11-23',2,'A',NULL),(255,3,19,'Saavedra B, Noely A','saavedra b noely a','26311608','26311608','1997-10-25',2,'D',NULL),(256,3,20,'Segovia Antony Jose','segovia antony jose','27029409','27029409','1997-06-08',2,'C',NULL),(257,3,21,'Soto Angulo Jorge Javier','soto angulo jorge javier','25882771','25882771','1997-10-26',2,'C',NULL),(258,3,22,'Valera D, Yorgelys A','valera d yorgelys a','26036801','26036801','1997-08-26',2,'A',NULL),(259,3,23,'Valera H, Beyker Javier','valera h beyker javier','26114740','26114740','1998-01-20',2,'C',NULL),(260,3,24,'Villegas P, Francisco J','villegas p francisco j','26616759','26616759','1997-10-13',2,'C',NULL);
/*!40000 ALTER TABLE `acta_estudiantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acta_imagenes`
--

DROP TABLE IF EXISTS `acta_imagenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acta_imagenes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acta_id` int(11) NOT NULL,
  `orden` tinyint(4) NOT NULL DEFAULT 1,
  `archivo` varchar(255) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL DEFAULT 'image/jpeg',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_img_acta_orden` (`acta_id`,`orden`),
  CONSTRAINT `acta_imagenes_ibfk_1` FOREIGN KEY (`acta_id`) REFERENCES `actas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acta_imagenes`
--

LOCK TABLES `acta_imagenes` WRITE;
/*!40000 ALTER TABLE `acta_imagenes` DISABLE KEYS */;
INSERT INTO `acta_imagenes` VALUES (2,2,1,'acta_d341e644f942fa61598ddb76c002.jpeg','WhatsApp Image 2026-11-06 at 8.15.09 AM.jpeg','image/jpeg','2026-11-06 15:36:22'),(3,2,2,'acta_008187261c9a55ff550442512fc4.jpeg','WhatsApp Image 2026-11-06 at 8.15.14 AM.jpeg','image/jpeg','2026-11-06 15:36:22'),(4,2,3,'acta_c2eac8697736a05bd3f736c0d8ef.jpeg','WhatsApp Image 2026-11-06 at 8.15.11 AM.jpeg','image/jpeg','2026-11-06 15:36:22'),(5,3,1,'acta_8be1f84dba2d027de30617b59a20.jpeg','WhatsApp Image 2026-11-06 at 8.15.07 AM.jpeg','image/jpeg','2026-11-06 16:05:48'),(6,3,2,'acta_f48824ce8b4985310db1b756fb5e.jpeg','WhatsApp Image 2026-11-06 at 8.15.07 AM (1).jpeg','image/jpeg','2026-11-06 16:05:48'),(7,3,3,'acta_f46835fbf577f375fb7bdebe7e90.jpeg','WhatsApp Image 2026-11-06 at 8.15.07 AM (2).jpeg','image/jpeg','2026-11-06 16:05:48'),(8,4,1,'acta_4463f83f85d21fd8dc44ba7f26a1.jpg','HT-ZwhrX0AE8P0r.jpg','image/jpeg','2026-11-06 19:45:52');
/*!40000 ALTER TABLE `acta_imagenes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `actas`
--

DROP TABLE IF EXISTS `actas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `anio_escolar` varchar(20) NOT NULL,
  `numero_acta` int(11) NOT NULL DEFAULT 1,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `usuario_id` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `idx_actas_anio_activo` (`anio_escolar`,`activo`),
  KEY `idx_actas_activo` (`activo`),
  CONSTRAINT `actas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `actas`
--

LOCK TABLES `actas` WRITE;
/*!40000 ALTER TABLE `actas` DISABLE KEYS */;
INSERT INTO `actas` VALUES (2,'2010-2011',1,1,3,'2026-11-06 14:47:53','2026-11-06 14:47:53'),(3,'2008-2009',1,1,3,'2026-11-06 15:57:15','2026-11-06 15:57:15'),(4,'1999-2000',1,0,6,'2026-11-06 19:45:52','2026-11-06 19:46:07');
/*!40000 ALTER TABLE `actas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expediente_personal`
--

DROP TABLE IF EXISTS `expediente_personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expediente_personal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `personal_id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `personal_id` (`personal_id`),
  CONSTRAINT `expediente_personal_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expediente_personal`
--

LOCK TABLES `expediente_personal` WRITE;
/*!40000 ALTER TABLE `expediente_personal` DISABLE KEYS */;
INSERT INTO `expediente_personal` VALUES (3,1,'Hola amigos','holiras','8c029daf824867d1001d944285a62036.png','image/png',NULL,'2026-11-05 06:10:35'),(4,1,'Sangre de nariz Sangre de nariz Sangre de nariz Sangre de nariz Sangre de nariz Sangre de nariz','DOuh','8ac5143819dc458319f2ec1544e733c8.pdf','application/pdf','2026-11-04','2026-11-05 15:50:52'),(5,3,'Tumbaron la aplicacion','Binance fue hackeado y no podra asistir por una semana','f3e4bd542fe215a6d5829709fa1e6148.jpg','image/jpeg','2026-11-12','2026-11-05 18:27:56'),(6,4,'Justificativo medico 11-6-2026','Justifico medico por x enfermedad hasta el dia tal','1aaec317578b10a1f25df97ce1cebfbb.png','image/png','2026-11-12','2026-11-05 20:19:41'),(7,5,'Justifico Medico 1 semana','Tal esta cya csm','2f52d6818257f04b4eb93250809a3407.jpeg','image/jpeg','2026-11-13','2026-11-06 17:16:10'),(8,5,'sadasd','adssf','027e57f3876361c7b6b94d62c77ae6bc.pdf','application/pdf','2026-11-05','2026-11-06 17:17:40'),(9,5,'Gif prueba','ds','a89ede969e1d83d5f578eeb768069ffe.gif','image/gif',NULL,'2026-11-08 20:39:15');
/*!40000 ALTER TABLE `expediente_personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) DEFAULT NULL,
  `usuario_nombre` varchar(150) NOT NULL,
  `accion` text NOT NULL,
  `ip_origen` varchar(45) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso.','::1','2026-11-04 21:20:04'),(2,3,'Luis Fernando Marquez Gelves','Registró al integrante del personal: Pana Poncio Perez Gimenez (Cédula: V-987654321).','::1','2026-11-04 21:22:35'),(3,3,'Luis Fernando Marquez Gelves','Añadió un registro al expediente de Pana Poncio Perez Gimenez: \'El poncio se fue\'.','::1','2026-11-04 21:23:12'),(4,3,'Luis Fernando Marquez Gelves','Registró al integrante del personal: afdfas fdsaf (Cédula: V-23123123).','::1','2026-11-04 21:24:23'),(5,3,'Luis Fernando Marquez Gelves','Añadió un nuevo Código RAC (\'312123123\').','::1','2026-11-04 21:48:12'),(6,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso.','::1','2026-11-05 02:24:14'),(7,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-05 02:24:18'),(8,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso.','::1','2026-11-05 02:24:39'),(9,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-05 02:47:11'),(10,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-05 02:47:39'),(11,3,'Luis Fernando Marquez Gelves','Añadió un nuevo registro al expediente de Pana Poncio Perez Gimenez: \'Sangre de nariz\'.','::1','2026-11-05 06:09:38'),(12,3,'Luis Fernando Marquez Gelves','Modificó el registro de expediente #2: \'Sangre de nariz\'.','::1','2026-11-05 06:10:00'),(13,3,'Luis Fernando Marquez Gelves','Añadió un nuevo registro al expediente de Pana Poncio Perez Gimenez: \'Hola amigos\'.','::1','2026-11-05 06:10:35'),(14,3,'Luis Fernando Marquez Gelves','Modificó el registro de expediente #1: \'El poncio se fue\'.','::1','2026-11-05 06:28:26'),(15,3,'Luis Fernando Marquez Gelves','Modificó el registro de expediente #1: \'El poncio se fue\'.','::1','2026-11-05 06:29:19'),(16,3,'Luis Fernando Marquez Gelves','Eliminó el registro de expediente: \'Sangre de nariz\' (ID: 2).','::1','2026-11-05 06:48:45'),(17,3,'Luis Fernando Marquez Gelves','Modificó el registro de expediente #1: \'El poncio se fue\'.','::1','2026-11-05 15:36:22'),(18,3,'Luis Fernando Marquez Gelves','Añadió un nuevo registro al expediente de Pana Poncio Perez Gimenez: \'Sangre de nariz Sangre de nariz Sangre de nariz Sangre de nariz Sangre de nariz Sangre de nariz\'.','::1','2026-11-05 15:50:52'),(19,3,'Luis Fernando Marquez Gelves','Registró al personal: Binance Andres Hackiao Lopez (Cédula: E-123456788).','::1','2026-11-05 18:26:43'),(20,3,'Luis Fernando Marquez Gelves','Añadió un nuevo registro al expediente de Binance Andres Hackiao Lopez: \'Tumbaron la aplicacion\'.','::1','2026-11-05 18:27:56'),(21,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-05 18:37:40'),(22,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-05 18:37:57'),(23,3,'Luis Fernando Marquez Gelves','Deshabilitó al personal: Binance Andres Hackiao Lopez (ID: 3).','::1','2026-11-05 19:02:03'),(24,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-05 20:10:20'),(25,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-05 20:13:05'),(26,3,'Luis Fernando Marquez Gelves','Registró al personal: Ana Maria Garcia Rodriguez (Cédula: V-87878787).','::1','2026-11-05 20:18:03'),(27,3,'Luis Fernando Marquez Gelves','Añadió un nuevo registro al expediente de Ana Maria Garcia Rodriguez: \'Justificativo medico 11-6-2026\'.','::1','2026-11-05 20:19:41'),(28,3,'Luis Fernando Marquez Gelves','Eliminó el registro de expediente: \'El poncio se fue\' (ID: 1).','::1','2026-11-05 20:21:05'),(29,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-05 20:29:38'),(30,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-05 20:30:45'),(31,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-05 20:31:02'),(32,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-05 20:32:56'),(33,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-05 20:33:11'),(34,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-06 03:54:52'),(35,3,'Luis Fernando Marquez Gelves','Actualizó los datos del usuario ID #4: Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez (Rol: regular).','::1','2026-11-06 04:47:58'),(36,3,'Luis Fernando Marquez Gelves','Actualizó los datos del usuario ID #4: Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez (Rol: regular).','::1','2026-11-06 04:48:10'),(37,3,'Luis Fernando Marquez Gelves','Actualizó los datos del usuario ID #4: Maria Antoniea Perez Gimenez (Rol: regular).','::1','2026-11-06 04:49:10'),(38,3,'Luis Fernando Marquez Gelves','Actualizó los datos del usuario ID #6: Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez (Rol: regular).','::1','2026-11-06 04:51:42'),(39,3,'Luis Fernando Marquez Gelves','Registró una nueva Acta de Evaluación #2 (Año Escolar 2010-2011) con 5 estudiante(s).','::1','2026-11-06 14:47:53'),(40,3,'Luis Fernando Marquez Gelves','Actualizó el Acta de Graduación #2 (Año Escolar 2010-2011, 5 estudiantes).','::1','2026-11-06 15:36:22'),(41,3,'Luis Fernando Marquez Gelves','Registró una nueva Acta de Graduación #3 (Año Escolar 2008-2009) con 15 estudiante(s).','::1','2026-11-06 15:57:15'),(42,3,'Luis Fernando Marquez Gelves','Actualizó el Acta de Graduación #3 (Año Escolar 2008-2009, 24 estudiantes).','::1','2026-11-06 16:05:48'),(43,3,'Luis Fernando Marquez Gelves','Actualizó el Acta de Graduación #2 (Año Escolar 2010-2011, 7 estudiantes).','::1','2026-11-06 16:09:11'),(44,3,'Luis Fernando Marquez Gelves','Actualizó el Acta de Graduación #3 (Año Escolar 2008-2009, 25 estudiantes).','::1','2026-11-06 16:25:45'),(45,3,'Luis Fernando Marquez Gelves','Actualizó el Acta de Graduación #3 (Año Escolar 2008-2009, 24 estudiantes).','::1','2026-11-06 16:32:46'),(46,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-06 16:53:11'),(47,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-06 16:53:30'),(48,3,'Luis Fernando Marquez Gelves','Actualizó el Acta de Graduación #3 (Año Escolar 2008-2009, 24 estudiantes).','::1','2026-11-06 16:59:44'),(49,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-06 17:01:16'),(50,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-06 17:01:46'),(51,3,'Luis Fernando Marquez Gelves','Eliminó al usuario ID #4: Maria Antoniea Perez Gimenez (Cédula: V-1234567890).','::1','2026-11-06 17:04:54'),(52,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-06 17:10:17'),(53,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-06 17:11:38'),(54,3,'Luis Fernando Marquez Gelves','Registró al personal: Maria Sofia Garcia Azuaje (Cédula: V-4567979).','::1','2026-11-06 17:14:46'),(55,3,'Luis Fernando Marquez Gelves','Añadió un nuevo registro al expediente de Maria Sofia Garcia Azuaje: \'Justifico Medico 1 semana\'.','::1','2026-11-06 17:16:10'),(56,3,'Luis Fernando Marquez Gelves','Añadió un nuevo registro al expediente de Maria Sofia Garcia Azuaje: \'sadasd\'.','::1','2026-11-06 17:17:40'),(57,3,'Luis Fernando Marquez Gelves','Actualizó el Acta de Graduación #3 (Año Escolar 2008-2009, 24 estudiantes).','::1','2026-11-06 17:21:48'),(58,3,'Luis Fernando Marquez Gelves','Actualizó los datos del usuario ID #6: Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria (Rol: regular).','::1','2026-11-06 19:42:43'),(59,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-06 19:43:18'),(60,6,'Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria','Inicio de sesión exitoso (V-987654321).','::1','2026-11-06 19:43:27'),(61,6,'Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria','Registró una nueva Acta de Graduación #4 (Año Escolar 1999-2000) con 5 estudiante(s).','::1','2026-11-06 19:45:52'),(62,6,'Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria','Desactivó el Acta de Graduación #4 (Año Escolar 1999-2000).','::1','2026-11-06 19:46:07'),(63,6,'Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria','Cerró sesión en el sistema.','::1','2026-11-06 19:51:04'),(64,3,'Luis Fernando Marquez Gelves','Inicio de sesión exitoso (V-123456789).','::1','2026-11-06 19:51:29'),(65,3,'Luis Fernando Marquez Gelves','Actualizó los datos del usuario ID #1: Administrador (Rol: admin).','::1','2026-11-06 19:52:19'),(66,3,'Luis Fernando Marquez Gelves','Cerró sesión en el sistema.','::1','2026-11-06 19:52:22'),(67,1,'Administrador','Inicio de sesión exitoso (V-12345678).','::1','2026-11-06 19:52:30'),(68,1,'Administrador','Actualizó los datos del usuario ID #6: Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria (Rol: regular).','::1','2026-11-06 19:54:34'),(69,6,'Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria','Inicio de sesión exitoso (V-987654321).','::1','2026-11-06 19:55:20'),(70,6,'Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria','Cerró sesión en el sistema.','::1','2026-11-06 19:57:41'),(71,6,'Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria','Inicio de sesión exitoso (V-987654321).','::1','2026-11-06 19:58:15'),(72,1,'Administrador','Actualizó el Acta de Graduación #3 (Año Escolar 2008-2009, 24 estudiantes).','::1','2026-11-06 22:22:05'),(73,1,'Administrador','Actualizó el Acta de Graduación #3 (Año Escolar 2008-2009, 24 estudiantes).','::1','2026-11-06 22:40:10'),(74,1,'Administrador','Actualizó el Acta de Graduación #3 (Año Escolar 2008-2009, 24 estudiantes).','::1','2026-11-06 22:40:37'),(75,1,'Administrador','Actualizó el Acta de Graduación #3 (Año Escolar 2008-2009, 24 estudiantes).','::1','2026-11-06 22:40:47'),(76,1,'Administrador','Actualizó los datos del personal: afdfas fdsaf (Cédula: V-23123123).','::1','2026-11-06 22:41:53'),(77,1,'Administrador','Modificó el registro de expediente #7: \'Justifico Medico 1 semana\'.','::1','2026-11-07 02:55:57'),(78,1,'Administrador','Cerró sesión en el sistema.','::1','2026-11-07 15:41:09'),(83,3,'Pana Poncio Esteban Quito','Actualizó los datos del usuario ID #3: Pana Poncio Esteban Quito (Rol: admin_mayor).','::1','2026-11-07 20:57:40'),(84,3,'Pana Poncio Esteban Quito','Cerró sesión en el sistema.','::1','2026-11-07 21:12:35'),(85,3,'Pana Poncio Esteban Quito','Inicio de sesión exitoso (V-123456789).','::1','2026-11-07 21:13:06'),(86,3,'Pana Poncio Esteban Quito','Añadió un nuevo registro al expediente de Maria Sofia Garcia Azuaje: \'Gif prueba\'.','::1','2026-11-08 20:39:15');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal`
--

DROP TABLE IF EXISTS `personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `nacionalidad` enum('V','E') NOT NULL DEFAULT 'V',
  `cedula` int(10) unsigned NOT NULL,
  `direccion` text NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `estado_civil` enum('Soltero/a','Casado/a','Viudo/a') NOT NULL,
  `sexo` enum('F','M') NOT NULL,
  `cargo` enum('Docente','Obrero','Administrativo','Directivo') NOT NULL,
  `padece_enfermedad` enum('Si','No') NOT NULL DEFAULT 'No',
  `detalle_enfermedad` varchar(255) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_personal_nacionalidad_cedula` (`nacionalidad`,`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal`
--

LOCK TABLES `personal` WRITE;
/*!40000 ALTER TABLE `personal` DISABLE KEYS */;
INSERT INTO `personal` VALUES (1,'Pana Poncio','Perez Gimenez','V',987654321,'La Orqueta estado zzzz','1999-03-31','342432423','rafasdf@gmail.com','Soltero/a','F','Docente','Si','Enfermedad 1, Enfermedad 2','e27af90920d4eff1a5d111436960e097.png',1,'2026-11-04 21:22:35'),(2,'afdfas','fdsaf','V',23123123,'fsdadfdfadf','2026-11-20','3422143','sdfadf@gmail.com','Soltero/a','M','Directivo','No',NULL,'ea126c194064d754715818595ae33ea3.png',1,'2026-11-04 21:24:23'),(3,'Binance Andres','Hackiao Lopez','E',123456788,'Monay, La orqueta','1900-05-15','34221434','binance@gmail.com','Viudo/a','M','Obrero','Si','Hackeado, Virus, Manco','e0d5b1be855f4ff07e8f30fbcc5f13fa.png',0,'2026-11-05 18:26:43'),(4,'Ana Maria','Garcia Rodriguez','V',87878787,'Sector la orqueta, calle las delicias','1990-01-01','04269707751','binance@gmail.com','Soltero/a','F','Docente','Si','Diabetes tipo II','845d7048c6c20e7c3f93b95aa7ebcf85.jpg',1,'2026-11-05 20:18:03'),(5,'Maria Sofia','Garcia Azuaje','V',4567979,'La orqueta, calle las delicias','1990-01-01','04169707751','mariasofia@gmail.com','Casado/a','F','Administrativo','Si','Hipertension, hipertrofia muscular','2b9ebd9b0e24da7bf85a1d312384fe1f.jpg',1,'2026-11-06 17:14:46');
/*!40000 ALTER TABLE `personal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nacionalidad` enum('V','E') NOT NULL DEFAULT 'V',
  `cedula` int(10) unsigned NOT NULL,
  `nombres_completos` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(30) NOT NULL DEFAULT 'regular',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultima_actividad` datetime DEFAULT NULL,
  `debe_cambiar_clave` tinyint(1) NOT NULL DEFAULT 0,
  `intentos_fallidos` int(11) DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_nacionalidad_cedula` (`nacionalidad`,`cedula`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,'V',12345678,'Administrador','$2y$10$F3JXDUFIvSOoCfrX/gxo1.k9aHhSXqtaKQ.R7P8vZ97oRT7iqsWJq','admin',1,'2026-11-08 15:54:36',0,0,NULL,'2026-11-04 20:56:01'),(3,'V',123456789,'Pana Poncio Esteban Quito','$2y$10$23cO3yzPoWs/wAHXGyN1xOyaeh7XbJiBi3HJS9eoiGnyOdirFvqVS','admin_mayor',1,'2026-11-15 15:27:48',0,0,NULL,'2026-11-04 21:19:17'),(6,'V',987654321,'Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria Antoniea Perez Gimenez Maria','$2y$10$pZmxbi/11diDxEDnEfmqNu2tzm.52JgFJBQZ2j5UqHUYEfUX4opty','regular',1,'2026-11-06 18:47:22',1,0,NULL,'2026-11-04 22:54:12');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-15 16:03:22
