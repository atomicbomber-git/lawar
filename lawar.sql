-- MySQL dump 10.13  Distrib 5.7.16, for Linux (x86_64)
--
-- Host: localhost    Database: lawar
-- ------------------------------------------------------
-- Server version	5.7.16-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cash_history`
--

DROP TABLE IF EXISTS `cash_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cash_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clerk_id` int(11) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `description` text NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_history_transactions` (`transaction_id`),
  KEY `cash_history_clerks` (`clerk_id`),
  CONSTRAINT `cash_history_clerks` FOREIGN KEY (`clerk_id`) REFERENCES `clerks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_history_transactions` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_history`
--

LOCK TABLES `cash_history` WRITE;
/*!40000 ALTER TABLE `cash_history` DISABLE KEYS */;
INSERT INTO `cash_history` VALUES (14,23,40000,'Transaksi jual beli.',45,'2016-12-08 08:22:11'),(15,17,30000,'Transaksi jual beli.',47,'2016-12-08 09:51:01'),(16,17,60000,'Transaksi jual beli.',48,'2016-12-08 10:31:49'),(17,17,90000,'Transaksi jual beli.',49,'2016-12-08 10:34:45'),(18,17,30000,'Transaksi jual beli.',50,'2016-12-08 10:39:54'),(19,17,150000,'Transaksi jual beli.',51,'2016-12-08 12:28:48'),(20,18,120000,'Transaksi jual beli.',53,'2016-12-08 12:37:48');
/*!40000 ALTER TABLE `cash_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clerks`
--

DROP TABLE IF EXISTS `clerks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clerks` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone` varchar(14) NOT NULL,
  `privilege` enum('SHOPKEEPER','MANAGER','ADMINISTRATOR') NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clerks`
--

LOCK TABLES `clerks` WRITE;
/*!40000 ALTER TABLE `clerks` DISABLE KEYS */;
INSERT INTO `clerks` VALUES (17,'james','James Patrick Keegan','089653238394','SHOPKEEPER','$2a$12$WtsDHjx0Eh2Ll8hLyi0p3OPalrh1KvVc.1kd9cJRH7aetwp1wZx7i'),(18,'annissa','Annissa Natassya','08034083','SHOPKEEPER','$2a$12$uFNiHE/XYO69uOUQAIi48OBTBvlUQpuTi19Ml7igcKO0paIwJd5w6'),(21,'jessica','','0899','SHOPKEEPER','$2a$12$r0tFNff3cJ7oSGWjpMOMxef4rVyfEKkSXCRuWAhyqSuvJhw72VHRy'),(22,'richart','','0899','MANAGER','$2a$12$f.g91/i8oEt283iSS4bB8.QG5sl9oGZfEUN5WY4kLpnmF0kuIlbnS'),(23,'max','Max Tjahjadi','1234','ADMINISTRATOR','$2a$12$y9z5Blf4CH9/9F32CSYM9uJALwh8XHvvmhVOROWm66X3MPdCwsB2a'),(25,'ted','','0899','MANAGER','$2a$12$odJj7ryhWRccldW3yUR69eeQIyjwc84P7o9tKv1aCsrC2BIy.r8Ry'),(26,'ronny','','0978','ADMINISTRATOR','$2a$12$IY4aEhM1lOs7qYmEs4Ec4uymB1f5XvdRZ.mn8c6N4079ARRgAM7Jm'),(27,'jean_pierre','Jean Pierre','011112','MANAGER','$2a$12$RoHiy/hf8/psQa/t20dgl.zNqlKloBymy1GFu7j.TYN.U6VQjgd3m');
/*!40000 ALTER TABLE `clerks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `items` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `size` varchar(20) NOT NULL,
  `price` double NOT NULL,
  `type_id` int(8) NOT NULL,
  `stock_store` int(11) NOT NULL,
  `stock_warehouse` int(11) NOT NULL,
  `stock_event` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `items_types` (`type_id`),
  CONSTRAINT `items_types` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=235 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `items`
--

LOCK TABLES `items` WRITE;
/*!40000 ALTER TABLE `items` DISABLE KEYS */;
INSERT INTO `items` VALUES (30,'Baju Mantep','When in the course of human events it becomes necessary for one people to dissolve the political bands which have connected them with another and to assume among the Powers','XL',20000,12,385,47,2,'2016-12-07'),(31,'Baju Keren','When I was younger that so much younger than todaaay\r\n','1212',11212,15,3,16,0,'2016-12-07'),(32,'James','Awesome','1212',100000,12,0,0,0,'2016-12-07'),(33,'Item number 0.','Whatever 0','XL',10000,12,15,0,0,'2016-12-07'),(34,'Item number 1.','Whatever 1','XL',10000,12,20,10,0,'2016-12-07'),(35,'Item number 2.','Whatever 2','XL',10000,12,20,10,0,'2016-12-07'),(36,'Item number 3.','Whatever 3','XL',10000,12,20,10,0,'2016-12-07'),(37,'Item number 4.','Whatever 4','XL',10000,12,20,10,0,'2016-12-07'),(38,'Item number 5.','Whatever 5','XL',10000,12,20,10,0,'2016-12-07'),(39,'Item number 6.','Whatever 6','XL',10000,12,20,10,0,'2016-12-07'),(40,'Item number 7.','Whatever 7','XL',10000,12,0,1,0,'2016-12-07'),(41,'Item number 8.','Whatever 8','XL',10000,12,1,1,0,'2016-12-07'),(42,'Item number 9.','Whatever 9','XL',10000,12,8,1,0,'2016-12-07'),(43,'Item number 10.','Whatever 10','XL',10000,12,14,10,0,'2016-12-07'),(44,'Item number 11.','Whatever 11','XL',10000,12,20,10,0,'2016-12-07'),(45,'Item number 12.','Whatever 12','XL',10000,12,20,1,0,'2016-12-07'),(46,'Item number 13.','Whatever 13','XL',10000,12,20,10,0,'2016-12-07'),(47,'Item number 14.','Whatever 14','XL',10000,12,20,10,0,'2016-12-07'),(48,'Item number 15.','Whatever 15','XL',10000,12,11,10,0,'2016-12-07'),(49,'Item number 16.','Whatever 16','XL',10000,12,20,10,0,'2016-12-07'),(50,'Item number 17.','Whatever 17','XL',10000,12,20,10,0,'2016-12-07'),(51,'Item number 18.','Whatever 18','XL',10000,12,20,10,0,'2016-12-07'),(52,'Item number 19.','Whatever 19','XL',10000,12,20,10,0,'2016-12-07'),(53,'Item number 21.','Whatever 21','XL',10000,12,20,10,0,'2016-12-07'),(54,'Item number 22.','Whatever 22','XL',10000,12,20,10,0,'2016-12-07'),(55,'Item number 23.','Whatever 23','XL',10000,12,20,10,0,'2016-12-07'),(56,'Item number 24.','Whatever 24','XL',10000,12,20,10,0,'2016-12-07'),(57,'Item number 25.','Whatever 25','XL',10000,12,20,10,0,'2016-12-07'),(58,'Item number 26.','Whatever 26','XL',10000,12,20,10,0,'2016-12-07'),(59,'Item number 27.','Whatever 27','XL',10000,12,20,10,0,'2016-12-07'),(60,'Item number 28.','Whatever 28','XL',10000,12,20,10,0,'2016-12-07'),(61,'Item number 29.','Whatever 29','XL',10000,12,20,10,0,'2016-12-07'),(62,'Item number 30.','Whatever 30','XL',10000,12,20,10,0,'2016-12-07'),(63,'Item number 31.','Whatever 31','XL',10000,12,20,10,0,'2016-12-07'),(64,'Item number 32.','Whatever 32','XL',10000,12,20,10,0,'2016-12-07'),(65,'Item number 33.','Whatever 33','XL',10000,12,20,10,0,'2016-12-07'),(66,'Item number 34.','Whatever 34','XL',10000,12,20,10,0,'2016-12-07'),(67,'Item number 35.','Whatever 35','XL',10000,12,20,10,0,'2016-12-07'),(68,'Item number 36.','Whatever 36','XL',10000,12,20,10,0,'2016-12-07'),(69,'Item number 37.','Whatever 37','XL',10000,12,20,10,0,'2016-12-07'),(70,'Item number 38.','Whatever 38','XL',10000,12,20,10,0,'2016-12-07'),(71,'Item number 39.','Whatever 39','XL',10000,12,20,10,0,'2016-12-07'),(72,'Item number 40.','Whatever 40','XL',10000,12,20,10,0,'2016-12-07'),(73,'Item number 41.','Whatever 41','XL',10000,12,20,10,0,'2016-12-07'),(74,'Item number 42.','Whatever 42','XL',10000,12,20,10,0,'2016-12-07'),(75,'Item number 43.','Whatever 43','XL',10000,12,20,10,0,'2016-12-07'),(76,'Item number 44.','Whatever 44','XL',10000,12,20,10,0,'2016-12-07'),(77,'Item number 45.','Whatever 45','XL',10000,12,20,10,0,'2016-12-07'),(78,'Item number 46.','Whatever 46','XL',10000,12,20,10,0,'2016-12-07'),(79,'Item number 47.','Whatever 47','XL',10000,12,20,10,0,'2016-12-07'),(80,'Item number 48.','Whatever 48','XL',10000,12,20,10,0,'2016-12-07'),(81,'Item number 49.','Whatever 49','XL',10000,12,20,10,0,'2016-12-07'),(82,'Item number 50.','Whatever 50','XL',10000,12,20,10,0,'2016-12-07'),(83,'Item number 51.','Whatever 51','XL',10000,12,20,10,0,'2016-12-07'),(84,'Item number 52.','Whatever 52','XL',10000,12,20,10,0,'2016-12-07'),(85,'Item number 53.','Whatever 53','XL',10000,12,20,10,0,'2016-12-07'),(86,'Item number 54.','Whatever 54','XL',10000,12,20,10,0,'2016-12-07'),(87,'Item number 55.','Whatever 55','XL',10000,12,20,10,0,'2016-12-07'),(88,'Item number 56.','Whatever 56','XL',10000,12,20,10,0,'2016-12-07'),(89,'Item number 57.','Whatever 57','XL',10000,12,20,10,0,'2016-12-07'),(90,'Item number 58.','Whatever 58','XL',10000,12,20,10,0,'2016-12-07'),(91,'Item number 59.','Whatever 59','XL',10000,12,20,10,0,'2016-12-07'),(92,'Item number 60.','Whatever 60','XL',10000,12,20,10,0,'2016-12-07'),(93,'Item number 61.','Whatever 61','XL',10000,12,20,10,0,'2016-12-07'),(94,'Item number 62.','Whatever 62','XL',10000,12,20,10,0,'2016-12-07'),(95,'Item number 63.','Whatever 63','XL',10000,12,20,10,0,'2016-12-07'),(96,'Item number 64.','Whatever 64','XL',10000,12,20,10,0,'2016-12-07'),(97,'Item number 65.','Whatever 65','XL',10000,12,20,10,0,'2016-12-07'),(98,'Item number 66.','Whatever 66','XL',10000,12,20,10,0,'2016-12-07'),(99,'Item number 67.','Whatever 67','XL',10000,12,20,10,0,'2016-12-07'),(100,'Item number 68.','Whatever 68','XL',10000,12,20,10,0,'2016-12-07'),(101,'Item number 69.','Whatever 69','XL',10000,12,20,10,0,'2016-12-07'),(102,'Item number 70.','Whatever 70','XL',10000,12,20,10,0,'2016-12-07'),(103,'Item number 71.','Whatever 71','XL',10000,12,20,10,0,'2016-12-07'),(104,'Item number 72.','Whatever 72','XL',10000,12,20,10,0,'2016-12-07'),(105,'Item number 73.','Whatever 73','XL',10000,12,20,10,0,'2016-12-07'),(106,'Item number 74.','Whatever 74','XL',10000,12,20,10,0,'2016-12-07'),(107,'Item number 75.','Whatever 75','XL',10000,12,20,10,0,'2016-12-07'),(108,'Item number 76.','Whatever 76','XL',10000,12,20,10,0,'2016-12-07'),(109,'Item number 77.','Whatever 77','XL',10000,12,20,10,0,'2016-12-07'),(110,'Item number 78.','Whatever 78','XL',10000,12,20,10,0,'2016-12-07'),(111,'Item number 79.','Whatever 79','XL',10000,12,20,10,0,'2016-12-07'),(112,'Item number 80.','Whatever 80','XL',10000,12,20,10,0,'2016-12-07'),(113,'Item number 81.','Whatever 81','XL',10000,12,20,10,0,'2016-12-07'),(114,'Item number 82.','Whatever 82','XL',10000,12,20,10,0,'2016-12-07'),(115,'Item number 83.','Whatever 83','XL',10000,12,20,10,0,'2016-12-07'),(116,'Item number 84.','Whatever 84','XL',10000,12,20,10,0,'2016-12-07'),(117,'Item number 85.','Whatever 85','XL',10000,12,20,10,0,'2016-12-07'),(118,'Item number 86.','Whatever 86','XL',10000,12,20,10,0,'2016-12-07'),(119,'Item number 87.','Whatever 87','XL',10000,12,20,10,0,'2016-12-07'),(120,'Item number 88.','Whatever 88','XL',10000,12,20,10,0,'2016-12-07'),(121,'Item number 89.','Whatever 89','XL',10000,12,20,10,0,'2016-12-07'),(122,'Item number 90.','Whatever 90','XL',10000,12,20,10,0,'2016-12-07'),(123,'Item number 91.','Whatever 91','XL',10000,12,20,10,0,'2016-12-07'),(124,'Item number 92.','Whatever 92','XL',10000,12,20,10,0,'2016-12-07'),(125,'Item number 93.','Whatever 93','XL',10000,12,20,10,0,'2016-12-07'),(126,'Item number 94.','Whatever 94','XL',10000,12,20,10,0,'2016-12-07'),(127,'Item number 95.','Whatever 95','XL',10000,12,20,10,0,'2016-12-07'),(128,'Item number 96.','Whatever 96','XL',10000,12,20,10,0,'2016-12-07'),(129,'Item number 97.','Whatever 97','XL',10000,12,20,10,0,'2016-12-07'),(130,'Item number 98.','Whatever 98','XL',10000,12,20,10,0,'2016-12-07'),(131,'Item number 99.','Whatever 99','XL',10000,12,20,10,0,'2016-12-07'),(132,'Item number 100.','Whatever 100','XL',10000,12,20,10,0,'2016-12-07'),(133,'Item number 101.','Whatever 101','XL',10000,12,20,10,0,'2016-12-07'),(134,'Item number 102.','Whatever 102','XL',10000,12,20,10,0,'2016-12-07'),(135,'Item number 103.','Whatever 103','XL',10000,12,20,10,0,'2016-12-07'),(136,'Item number 104.','Whatever 104','XL',10000,12,20,10,0,'2016-12-07'),(137,'Item number 105.','Whatever 105','XL',10000,12,20,10,0,'2016-12-07'),(138,'Item number 106.','Whatever 106','XL',10000,12,20,10,0,'2016-12-07'),(139,'Item number 107.','Whatever 107','XL',10000,12,20,10,0,'2016-12-07'),(140,'Item number 108.','Whatever 108','XL',10000,12,20,10,0,'2016-12-07'),(141,'Item number 109.','Whatever 109','XL',10000,12,20,10,0,'2016-12-07'),(142,'Item number 110.','Whatever 110','XL',10000,12,20,10,0,'2016-12-07'),(143,'Item number 111.','Whatever 111','XL',10000,12,20,10,0,'2016-12-07'),(144,'Item number 112.','Whatever 112','XL',10000,12,20,10,0,'2016-12-07'),(145,'Item number 113.','Whatever 113','XL',10000,12,20,10,0,'2016-12-07'),(146,'Item number 114.','Whatever 114','XL',10000,12,20,10,0,'2016-12-07'),(147,'Item number 115.','Whatever 115','XL',10000,12,20,10,0,'2016-12-07'),(148,'Item number 116.','Whatever 116','XL',10000,12,20,10,0,'2016-12-07'),(149,'Item number 117.','Whatever 117','XL',10000,12,20,10,0,'2016-12-07'),(150,'Item number 118.','Whatever 118','XL',10000,12,20,10,0,'2016-12-07'),(151,'Item number 119.','Whatever 119','XL',10000,12,20,10,0,'2016-12-07'),(152,'Item number 120.','Whatever 120','XL',10000,12,20,10,0,'2016-12-07'),(153,'Item number 121.','Whatever 121','XL',10000,12,20,10,0,'2016-12-07'),(154,'Item number 122.','Whatever 122','XL',10000,12,20,10,0,'2016-12-07'),(155,'Item number 123.','Whatever 123','XL',10000,12,20,10,0,'2016-12-07'),(156,'Item number 124.','Whatever 124','XL',10000,12,20,10,0,'2016-12-07'),(157,'Item number 125.','Whatever 125','XL',10000,12,20,10,0,'2016-12-07'),(158,'Item number 126.','Whatever 126','XL',10000,12,20,10,0,'2016-12-07'),(159,'Item number 127.','Whatever 127','XL',10000,12,20,10,0,'2016-12-07'),(160,'Item number 128.','Whatever 128','XL',10000,12,20,10,0,'2016-12-07'),(161,'Item number 129.','Whatever 129','XL',10000,12,20,10,0,'2016-12-07'),(162,'Item number 130.','Whatever 130','XL',10000,12,20,10,0,'2016-12-07'),(163,'Item number 131.','Whatever 131','XL',10000,12,20,10,0,'2016-12-07'),(164,'Item number 132.','Whatever 132','XL',10000,12,20,10,0,'2016-12-07'),(165,'Item number 133.','Whatever 133','XL',10000,12,20,10,0,'2016-12-07'),(166,'Item number 134.','Whatever 134','XL',10000,12,20,10,0,'2016-12-07'),(167,'Item number 135.','Whatever 135','XL',10000,12,20,10,0,'2016-12-07'),(168,'Item number 136.','Whatever 136','XL',10000,12,20,10,0,'2016-12-07'),(169,'Item number 137.','Whatever 137','XL',10000,12,20,10,0,'2016-12-07'),(170,'Item number 138.','Whatever 138','XL',10000,12,20,10,0,'2016-12-07'),(171,'Item number 139.','Whatever 139','XL',10000,12,20,10,0,'2016-12-07'),(172,'Item number 140.','Whatever 140','XL',10000,12,20,10,0,'2016-12-07'),(173,'Item number 141.','Whatever 141','XL',10000,12,20,10,0,'2016-12-07'),(174,'Item number 142.','Whatever 142','XL',10000,12,20,10,0,'2016-12-07'),(175,'Item number 143.','Whatever 143','XL',10000,12,20,10,0,'2016-12-07'),(176,'Item number 144.','Whatever 144','XL',10000,12,20,10,0,'2016-12-07'),(177,'Item number 145.','Whatever 145','XL',10000,12,20,10,0,'2016-12-07'),(178,'Item number 146.','Whatever 146','XL',10000,12,20,10,0,'2016-12-07'),(179,'Item number 147.','Whatever 147','XL',10000,12,20,10,0,'2016-12-07'),(180,'Item number 148.','Whatever 148','XL',10000,12,20,10,0,'2016-12-07'),(181,'Item number 149.','Whatever 149','XL',10000,12,20,10,0,'2016-12-07'),(182,'Item number 150.','Whatever 150','XL',10000,12,20,10,0,'2016-12-07'),(183,'Item number 151.','Whatever 151','XL',10000,12,20,10,0,'2016-12-07'),(184,'Item number 152.','Whatever 152','XL',10000,12,20,10,0,'2016-12-07'),(185,'Item number 153.','Whatever 153','XL',10000,12,20,10,0,'2016-12-07'),(186,'Item number 154.','Whatever 154','XL',10000,12,20,10,0,'2016-12-07'),(187,'Item number 155.','Whatever 155','XL',10000,12,20,10,0,'2016-12-07'),(188,'Item number 156.','Whatever 156','XL',10000,12,20,10,0,'2016-12-07'),(189,'Item number 157.','Whatever 157','XL',10000,12,20,10,0,'2016-12-07'),(190,'Item number 158.','Whatever 158','XL',10000,12,20,10,0,'2016-12-07'),(191,'Item number 159.','Whatever 159','XL',10000,12,20,10,0,'2016-12-07'),(192,'Item number 160.','Whatever 160','XL',10000,12,20,10,0,'2016-12-07'),(193,'Item number 161.','Whatever 161','XL',10000,12,20,10,0,'2016-12-07'),(194,'Item number 162.','Whatever 162','XL',10000,12,20,10,0,'2016-12-07'),(195,'Item number 163.','Whatever 163','XL',10000,12,20,10,0,'2016-12-07'),(196,'Item number 164.','Whatever 164','XL',10000,12,20,10,0,'2016-12-07'),(197,'Item number 165.','Whatever 165','XL',10000,12,20,10,0,'2016-12-07'),(198,'Item number 166.','Whatever 166','XL',10000,12,20,10,0,'2016-12-07'),(199,'Item number 167.','Whatever 167','XL',10000,12,20,10,0,'2016-12-07'),(200,'Item number 168.','Whatever 168','XL',10000,12,20,10,0,'2016-12-07'),(201,'Item number 169.','Whatever 169','XL',10000,12,20,10,0,'2016-12-07'),(202,'Item number 170.','Whatever 170','XL',10000,12,20,10,0,'2016-12-07'),(203,'Item number 171.','Whatever 171','XL',10000,12,20,10,0,'2016-12-07'),(204,'Item number 172.','Whatever 172','XL',10000,12,20,10,0,'2016-12-07'),(205,'Item number 173.','Whatever 173','XL',10000,12,20,10,0,'2016-12-07'),(206,'Item number 174.','Whatever 174','XL',10000,12,20,10,0,'2016-12-07'),(207,'Item number 175.','Whatever 175','XL',10000,12,20,10,0,'2016-12-07'),(208,'Item number 176.','Whatever 176','XL',10000,12,20,10,0,'2016-12-07'),(209,'Item number 177.','Whatever 177','XL',10000,12,20,10,0,'2016-12-07'),(210,'Item number 178.','Whatever 178','XL',10000,12,20,10,0,'2016-12-07'),(211,'Item number 179.','Whatever 179','XL',10000,12,20,10,0,'2016-12-07'),(212,'Item number 180.','Whatever 180','XL',10000,12,20,10,0,'2016-12-07'),(213,'Item number 181.','Whatever 181','XL',10000,12,20,10,0,'2016-12-07'),(214,'Item number 182.','Whatever 182','XL',10000,12,20,10,0,'2016-12-07'),(215,'Item number 183.','Whatever 183','XL',10000,12,20,10,0,'2016-12-07'),(216,'Item number 184.','Whatever 184','XL',10000,12,20,10,0,'2016-12-07'),(217,'Item number 185.','Whatever 185','XL',10000,12,20,10,0,'2016-12-07'),(218,'Item number 186.','Whatever 186','XL',10000,12,20,10,0,'2016-12-07'),(219,'Item number 187.','Whatever 187','XL',10000,12,20,10,0,'2016-12-07'),(220,'Item number 188.','Whatever 188','XL',10000,12,20,10,0,'2016-12-07'),(221,'Item number 189.','Whatever 189','XL',10000,12,20,10,0,'2016-12-07'),(222,'Item number 190.','Whatever 190','XL',10000,12,20,10,0,'2016-12-07'),(223,'Item number 191.','Whatever 191','XL',10000,12,20,10,0,'2016-12-07'),(224,'Item number 192.','Whatever 192','XL',10000,12,20,10,0,'2016-12-07'),(225,'Item number 193.','Whatever 193','XL',10000,12,20,10,0,'2016-12-07'),(226,'Item number 194.','Whatever 194','XL',10000,12,20,10,0,'2016-12-07'),(227,'Item number 195.','Whatever 195','XL',10000,12,20,10,0,'2016-12-07'),(228,'Item number 196.','Whatever 196','XL',10000,12,20,10,0,'2016-12-07'),(229,'Item number 197.','Whatever 197','XL',10000,12,20,10,0,'2016-12-07'),(230,'Item number 198.','Whatever 198','XL',10000,12,20,10,0,'2016-12-07'),(231,'Item number 199.','Whatever 199','XL',10000,12,20,10,0,'2016-12-07'),(232,'','','',10000,12,0,0,0,'2016-12-23'),(233,'','','',1000,12,0,0,0,'2016-12-14'),(234,'','','',100000,14,0,0,0,'2016-12-22');
/*!40000 ALTER TABLE `items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaction_items`
--

DROP TABLE IF EXISTS `transaction_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transaction_items` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `size` varchar(20) NOT NULL,
  `price` double NOT NULL,
  `type` varchar(50) NOT NULL,
  `stock_store` int(11) NOT NULL,
  `stock_warehouse` int(11) NOT NULL,
  `stock_event` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transaction_items_transactions` (`transaction_id`),
  CONSTRAINT `transaction_items_transactions` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaction_items`
--

LOCK TABLES `transaction_items` WRITE;
/*!40000 ALTER TABLE `transaction_items` DISABLE KEYS */;
INSERT INTO `transaction_items` VALUES (67,41,45,'Item number 8.','Whatever 8','XL',10000,'Sepatu',0,4,0),(68,41,47,'Item number 8.','Whatever 8','XL',10000,'Sepatu',0,3,0),(69,42,48,'Item number 9.','Whatever 9','XL',10000,'Sepatu',0,6,0),(70,45,49,'Item number 12.','Whatever 12','XL',10000,'Sepatu',0,9,0),(71,42,50,'Item number 9.','Whatever 9','XL',10000,'Sepatu',0,3,0),(72,43,51,'Item number 10.','Whatever 10','XL',10000,'Sepatu',6,0,0),(73,48,51,'Item number 15.','Whatever 15','XL',10000,'Sepatu',9,0,0),(74,42,53,'Item number 9.','Whatever 9','XL',10000,'Sepatu',12,0,0);
/*!40000 ALTER TABLE `transaction_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clerk_id` int(11) DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `is_finished` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `transactions_clerks` (`clerk_id`),
  CONSTRAINT `transactions_clerks` FOREIGN KEY (`clerk_id`) REFERENCES `clerks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (45,23,'2016-12-08 08:21:51',1),(46,23,'2016-12-08 08:22:11',0),(47,17,'2016-12-08 09:48:32',1),(48,17,'2016-12-08 09:51:01',1),(49,17,'2016-12-08 10:34:45',1),(50,17,'2016-12-08 10:39:54',1),(51,17,'2016-12-08 12:28:48',1),(52,17,'2016-12-08 12:28:48',0),(53,18,'2016-12-08 12:37:48',1),(54,18,'2016-12-08 12:37:48',0);
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `types`
--

DROP TABLE IF EXISTS `types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `types` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `types`
--

LOCK TABLES `types` WRITE;
/*!40000 ALTER TABLE `types` DISABLE KEYS */;
INSERT INTO `types` VALUES (12,'Sepatu'),(13,'Baju'),(14,'Baju Berkerah'),(15,'Sweater'),(16,'Android ');
/*!40000 ALTER TABLE `types` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-12-08 12:43:54
