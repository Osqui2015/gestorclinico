-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: gestor_clinico
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account_transactions`
--

DROP TABLE IF EXISTS `account_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_account_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `type` enum('charge','payment','credit','write_off','interest','adjustment','refund') COLLATE utf8mb4_unicode_ci NOT NULL,
  `concept` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `amount` decimal(12,2) NOT NULL,
  `balance_after` decimal(12,2) NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint unsigned DEFAULT NULL,
  `voucher_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `payment_method` enum('cash','check','transfer','credit_card','debit_card','promissory_note','credit','insurance','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_transactions_patient_account_id_index` (`patient_account_id`),
  KEY `account_transactions_created_by_index` (`created_by`),
  KEY `account_transactions_type_index` (`type`),
  KEY `account_transactions_transaction_date_index` (`transaction_date`),
  KEY `account_transactions_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  KEY `account_transactions_patient_account_id_transaction_date_index` (`patient_account_id`,`transaction_date`),
  CONSTRAINT `account_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `account_transactions_patient_account_id_foreign` FOREIGN KEY (`patient_account_id`) REFERENCES `patient_accounts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_transactions`
--

LOCK TABLES `account_transactions` WRITE;
/*!40000 ALTER TABLE `account_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ambulances`
--

DROP TABLE IF EXISTS `ambulances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ambulances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `internal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plate_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `year` int unsigned DEFAULT NULL,
  `current_mileage` bigint unsigned DEFAULT NULL,
  `base_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('available','in_transfer','maintenance','out_of_service') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `last_service_at` date DEFAULT NULL,
  `next_service_at` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ambulances_internal_code_unique` (`internal_code`),
  UNIQUE KEY `ambulances_plate_number_unique` (`plate_number`),
  KEY `ambulances_created_by_foreign` (`created_by`),
  KEY `ambulances_status_base_location_index` (`status`,`base_location`),
  CONSTRAINT `ambulances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ambulances`
--

LOCK TABLES `ambulances` WRITE;
/*!40000 ALTER TABLE `ambulances` DISABLE KEYS */;
INSERT INTO `ambulances` VALUES (1,'MOV-01','AB123CD','Mercedes-Benz','Sprinter',2022,52340,'Base Central','available',NULL,NULL,NULL,9,'2026-03-08 01:29:28','2026-03-08 01:29:28',NULL),(2,'MOV-02','AC456EF','Renault','Master',2021,68410,'Anexo Norte','maintenance',NULL,NULL,NULL,9,'2026-03-08 01:29:28','2026-03-08 01:29:28',NULL);
/*!40000 ALTER TABLE `ambulances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `doctor_id` bigint unsigned NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `health_insurance_id` bigint unsigned DEFAULT NULL,
  `coseguro` decimal(10,2) DEFAULT NULL COMMENT 'Monto de coseguro a cobrar por la consulta',
  `scheduled_at` datetime NOT NULL,
  `duration` int NOT NULL DEFAULT '30' COMMENT 'Duration in minutes',
  `is_walk_in` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Sobreturno',
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `checked_in_at` timestamp NULL DEFAULT NULL COMMENT 'Hora de llegada',
  `no_show_count` int NOT NULL DEFAULT '0' COMMENT 'Contador de inasistencias',
  `status` enum('pending','called','attending','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appointments_doctor_id_scheduled_at_unique` (`doctor_id`,`scheduled_at`),
  KEY `appointments_patient_id_foreign` (`patient_id`),
  KEY `appointments_scheduled_at_index` (`scheduled_at`),
  KEY `appointments_health_insurance_id_foreign` (`health_insurance_id`),
  CONSTRAINT `appointments_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_health_insurance_id_foreign` FOREIGN KEY (`health_insurance_id`) REFERENCES `health_insurances` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,3,1,NULL,NULL,'2026-03-27 17:00:00',30,0,0,NULL,NULL,0,'called','Tos persistente','Primera vez consultando.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(2,3,1,NULL,NULL,'2026-03-14 15:00:00',30,0,0,NULL,NULL,0,'called','Problemas digestivos','Requiere pruebas adicionales.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(3,3,2,NULL,NULL,'2026-04-06 11:30:00',30,0,0,NULL,NULL,0,'called','Insomnio','Derivación externa.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(4,4,3,NULL,NULL,'2026-03-13 15:45:00',30,0,0,NULL,NULL,0,'called','Tos persistente',NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(5,2,4,NULL,NULL,'2026-03-16 10:45:00',30,0,0,NULL,NULL,0,'called','Gripe','Seguimiento de tratamiento.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(6,4,5,NULL,NULL,'2026-03-16 09:15:00',30,0,0,NULL,NULL,0,'called','Control de presión arterial',NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(7,3,5,NULL,NULL,'2026-03-24 11:15:00',30,0,0,NULL,NULL,0,'called','Dolor de espalda','Seguimiento de tratamiento.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(8,4,6,NULL,NULL,'2026-03-20 16:00:00',30,0,0,NULL,NULL,0,'called','Insomnio','Primera vez consultando.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(9,3,6,NULL,NULL,'2026-03-25 12:00:00',30,0,0,NULL,NULL,0,'called','Gripe','Requiere pruebas adicionales.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(10,2,7,NULL,NULL,'2026-03-12 16:45:00',30,0,0,NULL,NULL,0,'called','Ansiedad',NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(11,2,7,NULL,NULL,'2026-03-20 10:30:00',30,0,0,NULL,NULL,0,'called','Chequeo general','Primera vez consultando.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(12,4,7,NULL,NULL,'2026-03-08 17:30:00',30,0,0,NULL,NULL,0,'called','Insomnio','Consulta de urgencia.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(13,2,8,NULL,NULL,'2026-03-20 09:45:00',30,0,0,NULL,NULL,0,'called','Chequeo general','Empeoramiento de síntomas.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(14,4,8,NULL,NULL,'2026-04-02 10:30:00',30,0,0,NULL,NULL,0,'called','Gripe','Control periódico.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(15,3,9,NULL,NULL,'2026-04-05 14:15:00',30,0,0,NULL,NULL,0,'called','Control de presión arterial','Requiere pruebas adicionales.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(16,3,9,NULL,NULL,'2026-03-29 16:15:00',30,0,0,NULL,NULL,0,'called','Diabetes check-up','Consulta de urgencia.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(17,3,9,NULL,NULL,'2026-03-29 14:00:00',30,0,0,NULL,NULL,0,'called','Problemas digestivos','Control periódico.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(18,3,10,NULL,NULL,'2026-03-21 14:15:00',30,0,0,NULL,NULL,0,'called','Diabetes check-up',NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(19,3,10,NULL,NULL,'2026-03-29 16:00:00',30,0,0,NULL,NULL,0,'called','Dolor de cabeza','Consulta de urgencia.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(20,4,11,NULL,NULL,'2026-03-21 15:15:00',30,0,0,NULL,NULL,0,'called','Chequeo general',NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(21,2,11,NULL,NULL,'2026-03-27 12:30:00',30,0,0,NULL,NULL,0,'called','Ansiedad',NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(22,4,11,NULL,NULL,'2026-04-04 12:45:00',30,0,0,NULL,NULL,0,'called','Dolor de espalda','Control periódico.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(23,3,12,NULL,NULL,'2026-04-01 17:30:00',30,0,0,NULL,NULL,0,'called','Problemas digestivos','Primera vez consultando.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(24,4,12,NULL,NULL,'2026-03-19 14:15:00',30,0,0,NULL,NULL,0,'called','Ansiedad','Consulta de urgencia.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(25,4,12,NULL,NULL,'2026-03-08 11:45:00',30,0,0,NULL,NULL,0,'called','Chequeo general','Empeoramiento de síntomas.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(26,2,13,NULL,NULL,'2026-04-06 12:45:00',30,0,0,NULL,NULL,0,'called','Control de presión arterial',NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(27,3,13,NULL,NULL,'2026-04-05 16:30:00',30,0,0,NULL,NULL,0,'called','Ansiedad',NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(28,3,14,NULL,NULL,'2026-03-19 12:00:00',30,0,0,NULL,NULL,0,'called','Diabetes check-up','Requiere pruebas adicionales.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(29,3,14,NULL,NULL,'2026-03-17 12:15:00',30,0,0,NULL,NULL,0,'called','Control de presión arterial','Primera vez consultando.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(30,3,15,NULL,NULL,'2026-03-20 12:15:00',30,0,0,NULL,NULL,0,'called','Control de presión arterial','Requiere pruebas adicionales.',NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_id` bigint unsigned DEFAULT NULL,
  `action` enum('created','updated','deleted','viewed','downloaded','signed','verified','dispensed','annulled','restored','exported') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` text COLLATE utf8mb4_unicode_ci,
  `cuir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `renapdis_relevant` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_index` (`user_id`),
  KEY `audit_logs_model_index` (`model`),
  KEY `audit_logs_model_id_index` (`model_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  KEY `audit_logs_cuir_index` (`cuir`),
  FULLTEXT KEY `audit_logs_description_fulltext` (`description`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audits`
--

DROP TABLE IF EXISTS `audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `auditable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auditable_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audits_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `audits_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audits`
--

LOCK TABLES `audits` WRITE;
/*!40000 ALTER TABLE `audits` DISABLE KEYS */;
INSERT INTO `audits` VALUES (1,'App\\Models\\User',8,NULL,'created','[]','{\"id\": 8, \"name\": \"Tecnico de Mantenimiento\", \"role\": \"maintenance\", \"email\": \"mantenimiento@clinica.com\", \"password\": \"$2y$12$HXv2Fyjyq9r5pCmqp9hOEORAqcvJUXghm4IKrJSefI0nbxAIuZ6nq\", \"created_at\": \"2026-03-07 22:29:27\", \"updated_at\": \"2026-03-07 22:29:27\"}','127.0.0.1','Symfony','2026-03-08 01:29:27','2026-03-08 01:29:27'),(2,'App\\Models\\User',9,NULL,'created','[]','{\"id\": 9, \"name\": \"Paramedico de Guardia\", \"role\": \"paramedic\", \"email\": \"paramedico@clinica.com\", \"password\": \"$2y$12$a8t5iUnuiPiOFasVCLYLYuSOPaZgyUvrA3e7rE07MSIykZ/./EIL.\", \"created_at\": \"2026-03-07 22:29:28\", \"updated_at\": \"2026-03-07 22:29:28\"}','127.0.0.1','Symfony','2026-03-08 01:29:28','2026-03-08 01:29:28'),(3,'App\\Models\\User',10,NULL,'created','[]','{\"id\": 10, \"dni\": \"12345678\", \"name\": \"Administrador Sistema\", \"role\": \"admin\", \"email\": \"admin@gestor.com\", \"password\": \"$2y$12$bvrTU0RcS2U0kWxb2HmsJe/mSvS8YkAd/EPrAubgPgR3SKKfMx/6C\", \"created_at\": \"2026-03-08 21:16:03\", \"updated_at\": \"2026-03-08 21:16:03\"}','127.0.0.1','Symfony','2026-03-09 00:16:04','2026-03-09 00:16:04'),(4,'App\\Models\\User',11,NULL,'created','[]','{\"id\": 11, \"dni\": \"34567890\", \"name\": \"Dr. Juan Pérez\", \"role\": \"doctor\", \"email\": \"doctor@gestor.com\", \"phone\": \"261-7654321\", \"password\": \"$2y$12$DdnlCfKu8c3CWAT2ew480uNNKQkWdfiS/iZmVRuq0E/GZcH9IDg.O\", \"specialty\": \"Medicina General\", \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-08 21:16:04\", \"matricula_nacional\": \"MN123456\", \"provincia_matricula\": \"Mendoza\", \"matricula_provincial\": \"MP789012\"}','127.0.0.1','Symfony','2026-03-09 00:16:04','2026-03-09 00:16:04'),(5,'App\\Models\\User',12,NULL,'created','[]','{\"id\": 12, \"dni\": \"23456789\", \"name\": \"María González\", \"role\": \"secretary\", \"email\": \"secretaria@gestor.com\", \"phone\": \"261-4567890\", \"password\": \"$2y$12$W36.tGnKjYs4/5IqkAhrI.Is1X1EwYFiN8b/iPE.Ma8WbhvjFN2cm\", \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-08 21:16:04\"}','127.0.0.1','Symfony','2026-03-09 00:16:04','2026-03-09 00:16:04'),(6,'App\\Models\\User',13,NULL,'created','[]','{\"id\": 13, \"dni\": \"45678901\", \"name\": \"Ana Martínez\", \"role\": \"pharmacy\", \"email\": \"farmacia@gestor.com\", \"phone\": \"261-5551234\", \"password\": \"$2y$12$LzXd04KQARmDmXRABsDwgO2iTdDo4sieSKcoLCBQCb4ztZeQEW0XC\", \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-08 21:16:04\"}','127.0.0.1','Symfony','2026-03-09 00:16:04','2026-03-09 00:16:04'),(7,'App\\Models\\User',14,NULL,'created','[]','{\"id\": 14, \"dni\": \"56789012\", \"name\": \"Carlos Ruiz\", \"role\": \"operating_room_manager\", \"email\": \"quirofano@gestor.com\", \"phone\": \"261-5552345\", \"password\": \"$2y$12$Ne3Ar4mz2uak799XjHCwIeUhGxmAOh25RP4pxjj/vmsoFaJIc0I1G\", \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-08 21:16:04\"}','127.0.0.1','Symfony','2026-03-09 00:16:04','2026-03-09 00:16:04'),(8,'App\\Models\\User',15,NULL,'created','[]','{\"id\": 15, \"dni\": \"67890123\", \"name\": \"Laura Fernández\", \"role\": \"nurse\", \"email\": \"enfermera@gestor.com\", \"phone\": \"261-5553456\", \"password\": \"$2y$12$DtK3GxLP1ZCrlLba7e3ST.mgws/sSd0oxxvhB3mWRemJHsNu6GqIG\", \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-08 21:16:05\"}','127.0.0.1','Symfony','2026-03-09 00:16:05','2026-03-09 00:16:05'),(9,'App\\Models\\User',16,NULL,'created','[]','{\"id\": 16, \"dni\": \"78901234\", \"name\": \"Dr. Roberto Sánchez\", \"role\": \"emergency\", \"email\": \"emergencia@gestor.com\", \"phone\": \"261-5554567\", \"password\": \"$2y$12$IWddsAtDJ8XsmrodrXM/NOoCO/KE8NBAFVrGIYgizp00yW7gDYdtm\", \"specialty\": \"Emergencias\", \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-08 21:16:05\"}','127.0.0.1','Symfony','2026-03-09 00:16:05','2026-03-09 00:16:05'),(10,'App\\Models\\User',17,NULL,'created','[]','{\"id\": 17, \"dni\": \"89012345\", \"name\": \"Patricia López\", \"role\": \"accountant\", \"email\": \"contador@gestor.com\", \"phone\": \"261-5555678\", \"password\": \"$2y$12$gOn58kWqxDUSWWeIyFX/kOa71ppEqBnCihhf4DGNPUmK47BXxKXUm\", \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-08 21:16:05\"}','127.0.0.1','Symfony','2026-03-09 00:16:05','2026-03-09 00:16:05'),(11,'App\\Models\\User',18,NULL,'created','[]','{\"id\": 18, \"dni\": \"90123456\", \"name\": \"Miguel Torres\", \"role\": \"maintenance\", \"email\": \"mantenimiento@gestor.com\", \"phone\": \"261-5556789\", \"password\": \"$2y$12$V4sVBL8h/Dvj929pCIV9SuUz/3N1HcORLlZ5xPLyLPysaG35sFnQa\", \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-08 21:16:05\"}','127.0.0.1','Symfony','2026-03-09 00:16:05','2026-03-09 00:16:05'),(12,'App\\Models\\User',19,NULL,'created','[]','{\"id\": 19, \"dni\": \"01234567\", \"name\": \"Jorge Ramírez\", \"role\": \"paramedic\", \"email\": \"paramedico@gestor.com\", \"phone\": \"261-5557890\", \"password\": \"$2y$12$4dWKMCj/vw6u5a3hPJLR4eAa1rK.6cOsyQuovAst1dm2nzOYL.5LW\", \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-08 21:16:05\"}','127.0.0.1','Symfony','2026-03-09 00:16:05','2026-03-09 00:16:05'),(13,'App\\Models\\User',11,11,'updated','{\"id\": 11, \"dni\": \"34567890\", \"cuil\": null, \"name\": \"Dr. Juan Pérez\", \"role\": \"doctor\", \"email\": \"doctor@gestor.com\", \"phone\": \"261-7654321\", \"address\": null, \"password\": \"$2y$12$DdnlCfKu8c3CWAT2ew480uNNKQkWdfiS/iZmVRuq0E/GZcH9IDg.O\", \"specialty\": \"Medicina General\", \"created_at\": \"2026-03-09T00:16:04.000000Z\", \"updated_at\": \"2026-03-09T00:16:04.000000Z\", \"license_number\": null, \"remember_token\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": \"MN123456\", \"provincia_matricula\": \"Mendoza\", \"consultorio_telefono\": null, \"matricula_provincial\": \"MP789012\", \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 11, \"dni\": \"34567890\", \"cuil\": null, \"name\": \"Dr. Juan Pérez\", \"role\": \"doctor\", \"email\": \"doctor@gestor.com\", \"phone\": \"261-7654321\", \"address\": null, \"password\": \"$2y$12$DdnlCfKu8c3CWAT2ew480uNNKQkWdfiS/iZmVRuq0E/GZcH9IDg.O\", \"specialty\": \"Medicina General\", \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-08 21:16:04\", \"license_number\": null, \"remember_token\": \"3iXJkydmd05IF2uChzjZdJCwgYJC9OcgNbtGVS4wVmAmrYX3EWGNqqQBAld8\", \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": \"MN123456\", \"provincia_matricula\": \"Mendoza\", \"consultorio_telefono\": null, \"matricula_provincial\": \"MP789012\", \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-09 00:18:17','2026-03-09 00:18:17'),(14,'App\\Models\\User',11,11,'updated','{\"id\": 11, \"dni\": \"34567890\", \"cuil\": null, \"name\": \"Dr. Juan Pérez\", \"role\": \"doctor\", \"email\": \"doctor@gestor.com\", \"phone\": \"261-7654321\", \"address\": null, \"password\": \"$2y$12$DdnlCfKu8c3CWAT2ew480uNNKQkWdfiS/iZmVRuq0E/GZcH9IDg.O\", \"specialty\": \"Medicina General\", \"created_at\": \"2026-03-09T00:16:04.000000Z\", \"updated_at\": \"2026-03-09T00:16:04.000000Z\", \"license_number\": null, \"remember_token\": \"3iXJkydmd05IF2uChzjZdJCwgYJC9OcgNbtGVS4wVmAmrYX3EWGNqqQBAld8\", \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": \"MN123456\", \"provincia_matricula\": \"Mendoza\", \"consultorio_telefono\": null, \"matricula_provincial\": \"MP789012\", \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 11, \"dni\": \"34567890\", \"cuil\": null, \"name\": \"Dr. Juan Pérez\", \"role\": \"doctor\", \"email\": \"doctor@gestor.com\", \"phone\": \"261-7654321\", \"address\": null, \"password\": \"$2y$12$DdnlCfKu8c3CWAT2ew480uNNKQkWdfiS/iZmVRuq0E/GZcH9IDg.O\", \"specialty\": \"Medicina General\", \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-08 21:16:04\", \"license_number\": null, \"remember_token\": \"v3EYiQ0X4JtOAo1iWnArO2KLzekZoLbnQ16RZATqo7aki5oVOjWQZON2AgPn\", \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": \"MN123456\", \"provincia_matricula\": \"Mendoza\", \"consultorio_telefono\": null, \"matricula_provincial\": \"MP789012\", \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-09 00:22:30','2026-03-09 00:22:30'),(15,'App\\Models\\User',10,10,'updated','{\"id\": 10, \"dni\": \"12345678\", \"cuil\": null, \"name\": \"Administrador Sistema\", \"role\": \"admin\", \"email\": \"admin@gestor.com\", \"phone\": null, \"address\": null, \"password\": \"$2y$12$bvrTU0RcS2U0kWxb2HmsJe/mSvS8YkAd/EPrAubgPgR3SKKfMx/6C\", \"specialty\": null, \"created_at\": \"2026-03-09T00:16:03.000000Z\", \"updated_at\": \"2026-03-09T00:16:03.000000Z\", \"license_number\": null, \"remember_token\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 10, \"dni\": \"12345678\", \"cuil\": null, \"name\": \"Administrador Sistema\", \"role\": \"admin\", \"email\": \"admin@gestor.com\", \"phone\": null, \"address\": null, \"password\": \"$2y$12$bvrTU0RcS2U0kWxb2HmsJe/mSvS8YkAd/EPrAubgPgR3SKKfMx/6C\", \"specialty\": null, \"created_at\": \"2026-03-08 21:16:03\", \"updated_at\": \"2026-03-08 21:16:03\", \"license_number\": null, \"remember_token\": \"78T2iVJLobK5gyft7X2xDZdfaKznUemIE8S3un7HYxKrPljHWM0OwLEXkbJP\", \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36','2026-03-09 00:22:36','2026-03-09 00:22:36'),(16,'App\\Models\\User',10,NULL,'updated','{\"id\": 10, \"dni\": \"12345678\", \"cuil\": null, \"name\": \"Administrador Sistema\", \"role\": \"admin\", \"email\": \"admin@gestor.com\", \"phone\": null, \"address\": null, \"password\": \"$2y$12$bvrTU0RcS2U0kWxb2HmsJe/mSvS8YkAd/EPrAubgPgR3SKKfMx/6C\", \"specialty\": null, \"created_at\": \"2026-03-09T00:16:03.000000Z\", \"updated_at\": \"2026-03-09T00:16:03.000000Z\", \"license_number\": null, \"remember_token\": \"78T2iVJLobK5gyft7X2xDZdfaKznUemIE8S3un7HYxKrPljHWM0OwLEXkbJP\", \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 10, \"dni\": \"12345678\", \"cuil\": null, \"name\": \"Administrador Sistema\", \"role\": \"admin\", \"email\": \"admin@gestor.com\", \"phone\": null, \"address\": null, \"password\": \"$2y$12$kYr3xntAZoY9DIgkfY7zce/8uZy0O6jvJ5.f26UQuqdqXhqQ8QTrW\", \"specialty\": null, \"created_at\": \"2026-03-08 21:16:03\", \"updated_at\": \"2026-03-10 07:40:46\", \"license_number\": null, \"remember_token\": \"78T2iVJLobK5gyft7X2xDZdfaKznUemIE8S3un7HYxKrPljHWM0OwLEXkbJP\", \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:46','2026-03-10 10:40:46'),(17,'App\\Models\\User',11,NULL,'updated','{\"id\": 11, \"dni\": \"34567890\", \"cuil\": null, \"name\": \"Dr. Juan Pérez\", \"role\": \"doctor\", \"email\": \"doctor@gestor.com\", \"phone\": \"261-7654321\", \"address\": null, \"password\": \"$2y$12$DdnlCfKu8c3CWAT2ew480uNNKQkWdfiS/iZmVRuq0E/GZcH9IDg.O\", \"specialty\": \"Medicina General\", \"created_at\": \"2026-03-09T00:16:04.000000Z\", \"updated_at\": \"2026-03-09T00:16:04.000000Z\", \"license_number\": null, \"remember_token\": \"v3EYiQ0X4JtOAo1iWnArO2KLzekZoLbnQ16RZATqo7aki5oVOjWQZON2AgPn\", \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": \"MN123456\", \"provincia_matricula\": \"Mendoza\", \"consultorio_telefono\": null, \"matricula_provincial\": \"MP789012\", \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 11, \"dni\": \"34567890\", \"cuil\": null, \"name\": \"Dr. Juan Pérez\", \"role\": \"doctor\", \"email\": \"doctor@gestor.com\", \"phone\": \"261-7654321\", \"address\": null, \"password\": \"$2y$12$Qc3lPhEDi1WYPE5SAVVXruFMcLNvL1vvwJRlrkPzBZx8QxuaQImjy\", \"specialty\": \"Medicina General\", \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-10 07:40:47\", \"license_number\": null, \"remember_token\": \"v3EYiQ0X4JtOAo1iWnArO2KLzekZoLbnQ16RZATqo7aki5oVOjWQZON2AgPn\", \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": \"MN123456\", \"provincia_matricula\": \"Mendoza\", \"consultorio_telefono\": null, \"matricula_provincial\": \"MP789012\", \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:47','2026-03-10 10:40:47'),(18,'App\\Models\\User',12,NULL,'updated','{\"id\": 12, \"dni\": \"23456789\", \"cuil\": null, \"name\": \"María González\", \"role\": \"secretary\", \"email\": \"secretaria@gestor.com\", \"phone\": \"261-4567890\", \"address\": null, \"password\": \"$2y$12$W36.tGnKjYs4/5IqkAhrI.Is1X1EwYFiN8b/iPE.Ma8WbhvjFN2cm\", \"specialty\": null, \"created_at\": \"2026-03-09T00:16:04.000000Z\", \"updated_at\": \"2026-03-09T00:16:04.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 12, \"dni\": \"23456789\", \"cuil\": null, \"name\": \"María González\", \"role\": \"secretary\", \"email\": \"secretaria@gestor.com\", \"phone\": \"261-4567890\", \"address\": null, \"password\": \"$2y$12$q1FUF1KjVKrypleTJ8Nk6.eP4pkMfDxExErBqyNGW95oyWko5E0Ni\", \"specialty\": null, \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-10 07:40:47\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:47','2026-03-10 10:40:47'),(19,'App\\Models\\User',13,NULL,'updated','{\"id\": 13, \"dni\": \"45678901\", \"cuil\": null, \"name\": \"Ana Martínez\", \"role\": \"pharmacy\", \"email\": \"farmacia@gestor.com\", \"phone\": \"261-5551234\", \"address\": null, \"password\": \"$2y$12$LzXd04KQARmDmXRABsDwgO2iTdDo4sieSKcoLCBQCb4ztZeQEW0XC\", \"specialty\": null, \"created_at\": \"2026-03-09T00:16:04.000000Z\", \"updated_at\": \"2026-03-09T00:16:04.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 13, \"dni\": \"45678901\", \"cuil\": null, \"name\": \"Ana Martínez\", \"role\": \"pharmacy\", \"email\": \"farmacia@gestor.com\", \"phone\": \"261-5551234\", \"address\": null, \"password\": \"$2y$12$aOdDdWchIiqZsPl4u/5gwedPC7CbFg3lUlTvvv6OrA9WQk6vmChki\", \"specialty\": null, \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-10 07:40:47\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:47','2026-03-10 10:40:47'),(20,'App\\Models\\User',14,NULL,'updated','{\"id\": 14, \"dni\": \"56789012\", \"cuil\": null, \"name\": \"Carlos Ruiz\", \"role\": \"operating_room_manager\", \"email\": \"quirofano@gestor.com\", \"phone\": \"261-5552345\", \"address\": null, \"password\": \"$2y$12$Ne3Ar4mz2uak799XjHCwIeUhGxmAOh25RP4pxjj/vmsoFaJIc0I1G\", \"specialty\": null, \"created_at\": \"2026-03-09T00:16:04.000000Z\", \"updated_at\": \"2026-03-09T00:16:04.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 14, \"dni\": \"56789012\", \"cuil\": null, \"name\": \"Carlos Ruiz\", \"role\": \"operating_room_manager\", \"email\": \"quirofano@gestor.com\", \"phone\": \"261-5552345\", \"address\": null, \"password\": \"$2y$12$Nvo6DGENIAicHAl9ExJcnuMiR7nUO/lwgqNJtdahkwCK4.VmeF07m\", \"specialty\": null, \"created_at\": \"2026-03-08 21:16:04\", \"updated_at\": \"2026-03-10 07:40:47\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:47','2026-03-10 10:40:47'),(21,'App\\Models\\User',15,NULL,'updated','{\"id\": 15, \"dni\": \"67890123\", \"cuil\": null, \"name\": \"Laura Fernández\", \"role\": \"nurse\", \"email\": \"enfermera@gestor.com\", \"phone\": \"261-5553456\", \"address\": null, \"password\": \"$2y$12$DtK3GxLP1ZCrlLba7e3ST.mgws/sSd0oxxvhB3mWRemJHsNu6GqIG\", \"specialty\": null, \"created_at\": \"2026-03-09T00:16:05.000000Z\", \"updated_at\": \"2026-03-09T00:16:05.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 15, \"dni\": \"67890123\", \"cuil\": null, \"name\": \"Laura Fernández\", \"role\": \"nurse\", \"email\": \"enfermera@gestor.com\", \"phone\": \"261-5553456\", \"address\": null, \"password\": \"$2y$12$frsha5/Wz1dG3fydQmYIkeT96m7XkUo50VYnMjEMPeYJxovlXHUaq\", \"specialty\": null, \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-10 07:40:47\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:47','2026-03-10 10:40:47'),(22,'App\\Models\\User',16,NULL,'updated','{\"id\": 16, \"dni\": \"78901234\", \"cuil\": null, \"name\": \"Dr. Roberto Sánchez\", \"role\": \"emergency\", \"email\": \"emergencia@gestor.com\", \"phone\": \"261-5554567\", \"address\": null, \"password\": \"$2y$12$IWddsAtDJ8XsmrodrXM/NOoCO/KE8NBAFVrGIYgizp00yW7gDYdtm\", \"specialty\": \"Emergencias\", \"created_at\": \"2026-03-09T00:16:05.000000Z\", \"updated_at\": \"2026-03-09T00:16:05.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 16, \"dni\": \"78901234\", \"cuil\": null, \"name\": \"Dr. Roberto Sánchez\", \"role\": \"emergency\", \"email\": \"emergencia@gestor.com\", \"phone\": \"261-5554567\", \"address\": null, \"password\": \"$2y$12$KtA/9e.nc7kxFzxcbuQDw.jR.9OmMYJuYAMaFSk8icPwcFk.Y5/be\", \"specialty\": \"Emergencias\", \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-10 07:40:47\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:48','2026-03-10 10:40:48'),(23,'App\\Models\\User',17,NULL,'updated','{\"id\": 17, \"dni\": \"89012345\", \"cuil\": null, \"name\": \"Patricia López\", \"role\": \"accountant\", \"email\": \"contador@gestor.com\", \"phone\": \"261-5555678\", \"address\": null, \"password\": \"$2y$12$gOn58kWqxDUSWWeIyFX/kOa71ppEqBnCihhf4DGNPUmK47BXxKXUm\", \"specialty\": null, \"created_at\": \"2026-03-09T00:16:05.000000Z\", \"updated_at\": \"2026-03-09T00:16:05.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 17, \"dni\": \"89012345\", \"cuil\": null, \"name\": \"Patricia López\", \"role\": \"accountant\", \"email\": \"contador@gestor.com\", \"phone\": \"261-5555678\", \"address\": null, \"password\": \"$2y$12$UsxBjtwwcth6NmUksUNEtOVYU/BW3CuSK/OVWwTvakYhW.Rjvmr0.\", \"specialty\": null, \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-10 07:40:48\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:48','2026-03-10 10:40:48'),(24,'App\\Models\\User',18,NULL,'updated','{\"id\": 18, \"dni\": \"90123456\", \"cuil\": null, \"name\": \"Miguel Torres\", \"role\": \"maintenance\", \"email\": \"mantenimiento@gestor.com\", \"phone\": \"261-5556789\", \"address\": null, \"password\": \"$2y$12$V4sVBL8h/Dvj929pCIV9SuUz/3N1HcORLlZ5xPLyLPysaG35sFnQa\", \"specialty\": null, \"created_at\": \"2026-03-09T00:16:05.000000Z\", \"updated_at\": \"2026-03-09T00:16:05.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 18, \"dni\": \"90123456\", \"cuil\": null, \"name\": \"Miguel Torres\", \"role\": \"maintenance\", \"email\": \"mantenimiento@gestor.com\", \"phone\": \"261-5556789\", \"address\": null, \"password\": \"$2y$12$CwTmrw2ZSwgqNC5paD.SOOYO04lIlhLhe7x73q9niY8y1oQmvyv.2\", \"specialty\": null, \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-10 07:40:48\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:48','2026-03-10 10:40:48'),(25,'App\\Models\\User',19,NULL,'updated','{\"id\": 19, \"dni\": \"01234567\", \"cuil\": null, \"name\": \"Jorge Ramírez\", \"role\": \"paramedic\", \"email\": \"paramedico@gestor.com\", \"phone\": \"261-5557890\", \"address\": null, \"password\": \"$2y$12$4dWKMCj/vw6u5a3hPJLR4eAa1rK.6cOsyQuovAst1dm2nzOYL.5LW\", \"specialty\": null, \"created_at\": \"2026-03-09T00:16:05.000000Z\", \"updated_at\": \"2026-03-09T00:16:05.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 19, \"dni\": \"01234567\", \"cuil\": null, \"name\": \"Jorge Ramírez\", \"role\": \"paramedic\", \"email\": \"paramedico@gestor.com\", \"phone\": \"261-5557890\", \"address\": null, \"password\": \"$2y$12$GXZh1QBVK9zY74v.4T55Ku3c0gmV8AlyogR2e4ak9imcRKegxwAEK\", \"specialty\": null, \"created_at\": \"2026-03-08 21:16:05\", \"updated_at\": \"2026-03-10 07:40:48\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": null, \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:40:48','2026-03-10 10:40:48'),(26,'App\\Models\\User',6,NULL,'updated','{\"id\": 6, \"dni\": null, \"cuil\": null, \"name\": \"Enfermera Jefe\", \"role\": \"nurse\", \"email\": \"enfermeria@clinica.com\", \"phone\": \"+54 381 555-9001\", \"address\": null, \"password\": \"$2y$12$NMOO7iALTd4rRqX91I7hC.MIZTdxVoBbxCRtoVlgaZnUF11NRPh7y\", \"specialty\": null, \"created_at\": \"2026-03-08T00:46:42.000000Z\", \"updated_at\": \"2026-03-08T00:46:42.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": \"2026-03-08T00:46:42.000000Z\", \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 6, \"dni\": null, \"cuil\": null, \"name\": \"Enfermera Jefe\", \"role\": \"nurse\", \"email\": \"enfermeria@clinica.com\", \"phone\": \"+54 381 555-9001\", \"address\": null, \"password\": \"$2y$12$d3q8a/PeTvMeQ1qYWJ7fg.M/CHSkU0p5QPp7Lor/r7by8rJYhVaRa\", \"specialty\": null, \"created_at\": \"2026-03-07 21:46:42\", \"updated_at\": \"2026-03-10 07:42:03\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": \"2026-03-10 07:42:03\", \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:42:03','2026-03-10 10:42:03'),(27,'App\\Models\\User',7,NULL,'updated','{\"id\": 7, \"dni\": null, \"cuil\": null, \"name\": \"Enfermero Turno Noche\", \"role\": \"nurse\", \"email\": \"enfermeria.noche@clinica.com\", \"phone\": \"+54 381 555-9002\", \"address\": null, \"password\": \"$2y$12$gO2BiFMekYundV5SoS6agunwuVSVGDjHiykt7iP3URtI4DksLfVcm\", \"specialty\": null, \"created_at\": \"2026-03-08T00:46:42.000000Z\", \"updated_at\": \"2026-03-08T00:46:42.000000Z\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": false, \"email_verified_at\": \"2026-03-08T00:46:42.000000Z\", \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": false}','{\"id\": 7, \"dni\": null, \"cuil\": null, \"name\": \"Enfermero Turno Noche\", \"role\": \"nurse\", \"email\": \"enfermeria.noche@clinica.com\", \"phone\": \"+54 381 555-9002\", \"address\": null, \"password\": \"$2y$12$vrPkCFPdUscVkdezt4z05OnTJIrrpgAChps/Qcclm83aCUdxJthvO\", \"specialty\": null, \"created_at\": \"2026-03-07 21:46:42\", \"updated_at\": \"2026-03-10 07:42:04\", \"license_number\": null, \"remember_token\": null, \"allowed_modules\": null, \"professional_id\": null, \"validado_refeps\": 0, \"email_verified_at\": \"2026-03-10 07:42:04\", \"matricula_nacional\": null, \"provincia_matricula\": null, \"consultorio_telefono\": null, \"matricula_provincial\": null, \"consultorio_direccion\": null, \"fecha_validacion_refeps\": null, \"firma_electronica_metodo\": null, \"firma_digital_certificado\": null, \"firma_electronica_habilitada\": 0}','127.0.0.1','Symfony','2026-03-10 10:42:04','2026-03-10 10:42:04');
/*!40000 ALTER TABLE `audits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bed_cleaning_logs`
--

DROP TABLE IF EXISTS `bed_cleaning_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bed_cleaning_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bed_id` bigint unsigned NOT NULL,
  `cleaned_by` bigint unsigned NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NOT NULL,
  `cleaning_type` enum('routine','deep','discharge','disinfection') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'routine',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bed_cleaning_logs_cleaned_by_foreign` (`cleaned_by`),
  KEY `bed_cleaning_logs_bed_id_index` (`bed_id`),
  KEY `bed_cleaning_logs_completed_at_index` (`completed_at`),
  KEY `bed_cleaning_logs_bed_id_completed_at_index` (`bed_id`,`completed_at`),
  CONSTRAINT `bed_cleaning_logs_bed_id_foreign` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bed_cleaning_logs_cleaned_by_foreign` FOREIGN KEY (`cleaned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bed_cleaning_logs`
--

LOCK TABLES `bed_cleaning_logs` WRITE;
/*!40000 ALTER TABLE `bed_cleaning_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `bed_cleaning_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beds`
--

DROP TABLE IF EXISTS `beds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `beds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned NOT NULL,
  `bed_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('available','occupied','pending_cleaning','cleaning','maintenance') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `bed_type` enum('standard','intensive_care','isolation','pediatric','psychiatric') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `observations` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bed_room_number_unique` (`room_id`,`bed_number`),
  KEY `beds_status_index` (`status`),
  KEY `beds_status_bed_type_index` (`status`,`bed_type`),
  CONSTRAINT `beds_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beds`
--

LOCK TABLES `beds` WRITE;
/*!40000 ALTER TABLE `beds` DISABLE KEYS */;
INSERT INTO `beds` VALUES (1,1,'A','occupied','standard',NULL,1,'2026-03-08 00:46:41','2026-03-10 10:42:04'),(2,1,'B','available','standard',NULL,1,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(3,2,'A','pending_cleaning','standard',NULL,1,'2026-03-08 00:46:41','2026-03-10 10:42:04'),(4,2,'B','available','standard',NULL,1,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(5,3,'1','occupied','intensive_care',NULL,1,'2026-03-08 00:46:41','2026-03-10 10:42:04'),(6,4,'1','available','isolation',NULL,1,'2026-03-08 00:46:41','2026-03-08 00:46:41');
/*!40000 ALTER TABLE `beds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor_exceptions`
--

DROP TABLE IF EXISTS `doctor_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctor_exceptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `doctor_id` bigint unsigned NOT NULL,
  `exception_date` date NOT NULL,
  `type` enum('vacation','sick_leave','holiday','conference','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_all_day` tinyint(1) NOT NULL DEFAULT '1',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doctor_exceptions_doctor_id_exception_date_index` (`doctor_id`,`exception_date`),
  CONSTRAINT `doctor_exceptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor_exceptions`
--

LOCK TABLES `doctor_exceptions` WRITE;
/*!40000 ALTER TABLE `doctor_exceptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `doctor_exceptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor_schedules`
--

DROP TABLE IF EXISTS `doctor_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctor_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `doctor_id` bigint unsigned NOT NULL,
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday','sunday') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration` int NOT NULL DEFAULT '30' COMMENT 'Duration in minutes',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doctor_schedules_doctor_id_day_of_week_is_active_index` (`doctor_id`,`day_of_week`,`is_active`),
  CONSTRAINT `doctor_schedules_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor_schedules`
--

LOCK TABLES `doctor_schedules` WRITE;
/*!40000 ALTER TABLE `doctor_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `doctor_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emergency_admissions`
--

DROP TABLE IF EXISTS `emergency_admissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emergency_admissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `attending_doctor_id` bigint unsigned DEFAULT NULL,
  `nurse_id` bigint unsigned DEFAULT NULL,
  `admission_time` timestamp NOT NULL,
  `triage_time` timestamp NULL DEFAULT NULL,
  `discharged_at` timestamp NULL DEFAULT NULL,
  `triage_level` enum('1','2','3','4','5') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chief_complaint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `triage_notes` text COLLATE utf8mb4_unicode_ci,
  `systolic_pressure` decimal(5,2) DEFAULT NULL,
  `diastolic_pressure` decimal(5,2) DEFAULT NULL,
  `heart_rate` decimal(5,2) DEFAULT NULL,
  `respiratory_rate` decimal(5,2) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `oxygen_saturation` decimal(5,2) DEFAULT NULL,
  `glucose` decimal(5,2) DEFAULT NULL,
  `consciousness_level` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('waiting','in_care','observation','discharged','admitted','transferred') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `preliminary_diagnosis` text COLLATE utf8mb4_unicode_ci,
  `treatment_given` text COLLATE utf8mb4_unicode_ci,
  `discharge_diagnosis` text COLLATE utf8mb4_unicode_ci,
  `discharge_instructions` text COLLATE utf8mb4_unicode_ci,
  `observations` text COLLATE utf8mb4_unicode_ci,
  `clinical_evolution` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emergency_admissions_nurse_id_foreign` (`nurse_id`),
  KEY `emergency_admissions_patient_id_index` (`patient_id`),
  KEY `emergency_admissions_attending_doctor_id_index` (`attending_doctor_id`),
  KEY `emergency_admissions_status_index` (`status`),
  KEY `emergency_admissions_triage_level_index` (`triage_level`),
  KEY `emergency_admissions_admission_time_index` (`admission_time`),
  KEY `emergency_admissions_patient_id_status_index` (`patient_id`,`status`),
  CONSTRAINT `emergency_admissions_attending_doctor_id_foreign` FOREIGN KEY (`attending_doctor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `emergency_admissions_nurse_id_foreign` FOREIGN KEY (`nurse_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `emergency_admissions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emergency_admissions`
--

LOCK TABLES `emergency_admissions` WRITE;
/*!40000 ALTER TABLE `emergency_admissions` DISABLE KEYS */;
INSERT INTO `emergency_admissions` VALUES (1,1,2,NULL,'2026-03-10 09:42:04','2026-03-10 09:47:04',NULL,'2','Dolor abdominal agudo y vómitos','Paciente con signos de deshidratación',145.00,95.00,102.00,20.00,38.50,96.00,NULL,NULL,'in_care','Posible appendicitis',NULL,NULL,NULL,NULL,NULL,'2026-03-10 10:42:04','2026-03-10 10:42:04',NULL),(2,2,3,NULL,'2026-03-10 09:57:04','2026-03-10 10:00:04',NULL,'3','Herida lacerante en mano derecha','Herida con sangrado controlado',120.00,80.00,78.00,16.00,36.80,98.00,NULL,NULL,'waiting','Herida lacerante mano derecha 3cm',NULL,NULL,NULL,NULL,NULL,'2026-03-10 10:42:04','2026-03-10 10:42:04',NULL),(3,3,2,NULL,'2026-03-10 10:27:04','2026-03-10 10:28:04',NULL,'1','Dificultad respiratoria severa','Paciente cianótico, requiere O2 urgente',102.00,65.00,125.00,35.00,36.20,78.00,NULL,'GCS 14','in_care','Asma aguda severa',NULL,NULL,NULL,NULL,NULL,'2026-03-10 10:42:04','2026-03-10 10:42:04',NULL),(4,4,NULL,NULL,'2026-03-09 17:30:00','2026-03-09 17:35:00','2026-03-09 19:00:00','4','Mareos y debilidad',NULL,110.00,70.00,72.00,18.00,36.90,98.00,NULL,NULL,'discharged',NULL,NULL,'Hipotensión ortostática','Reposo, hidratación oral, control de PA',NULL,NULL,'2026-03-10 10:42:04','2026-03-10 10:42:04',NULL);
/*!40000 ALTER TABLE `emergency_admissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emergency_evolutions`
--

DROP TABLE IF EXISTS `emergency_evolutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emergency_evolutions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `emergency_admission_id` bigint unsigned NOT NULL,
  `recorded_by` bigint unsigned NOT NULL,
  `recorded_at` timestamp NOT NULL,
  `systolic_pressure` decimal(5,2) DEFAULT NULL,
  `diastolic_pressure` decimal(5,2) DEFAULT NULL,
  `heart_rate` decimal(5,2) DEFAULT NULL,
  `respiratory_rate` decimal(5,2) DEFAULT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `oxygen_saturation` decimal(5,2) DEFAULT NULL,
  `glucose` decimal(5,2) DEFAULT NULL,
  `clinical_notes` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `treatment_notes` text COLLATE utf8mb4_unicode_ci,
  `medications_given` text COLLATE utf8mb4_unicode_ci,
  `tests_performed` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emergency_evolutions_emergency_admission_id_index` (`emergency_admission_id`),
  KEY `emergency_evolutions_recorded_by_index` (`recorded_by`),
  KEY `emergency_evolutions_recorded_at_index` (`recorded_at`),
  CONSTRAINT `emergency_evolutions_emergency_admission_id_foreign` FOREIGN KEY (`emergency_admission_id`) REFERENCES `emergency_admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `emergency_evolutions_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emergency_evolutions`
--

LOCK TABLES `emergency_evolutions` WRITE;
/*!40000 ALTER TABLE `emergency_evolutions` DISABLE KEYS */;
INSERT INTO `emergency_evolutions` VALUES (1,1,2,'2026-03-10 10:12:04',142.00,92.00,98.00,20.00,38.20,96.00,NULL,'Paciente con mejoría, dolor reducido','Analgesia EV, hidratación parenteral','Ketorolac 30mg IV, Plasmalyte 1L',NULL,'2026-03-10 10:42:04','2026-03-10 10:42:04'),(2,3,2,'2026-03-10 10:42:04',115.00,75.00,102.00,24.00,36.50,94.00,NULL,'Paciente con buena respuesta a broncodilatadores','Salbutamol nebulizado, metilprednisolona IV','Albuterol 2.5mg neb, Metilprednisolona 125mg IV',NULL,'2026-03-10 10:42:04','2026-03-10 10:42:04');
/*!40000 ALTER TABLE `emergency_evolutions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emergency_transfers`
--

DROP TABLE IF EXISTS `emergency_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emergency_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned DEFAULT NULL,
  `requested_by` bigint unsigned DEFAULT NULL,
  `ambulance_id` bigint unsigned DEFAULT NULL,
  `origin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transfer_type` enum('emergency','scheduled','interhospital','discharge') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'emergency',
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('requested','assigned','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested',
  `requested_at` timestamp NOT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `departed_at` timestamp NULL DEFAULT NULL,
  `arrived_at` timestamp NULL DEFAULT NULL,
  `clinical_summary` text COLLATE utf8mb4_unicode_ci,
  `crew_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emergency_transfers_patient_id_foreign` (`patient_id`),
  KEY `emergency_transfers_requested_by_foreign` (`requested_by`),
  KEY `emergency_transfers_ambulance_id_foreign` (`ambulance_id`),
  KEY `emergency_transfers_status_priority_index` (`status`,`priority`),
  KEY `emergency_transfers_requested_at_index` (`requested_at`),
  CONSTRAINT `emergency_transfers_ambulance_id_foreign` FOREIGN KEY (`ambulance_id`) REFERENCES `ambulances` (`id`) ON DELETE SET NULL,
  CONSTRAINT `emergency_transfers_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `emergency_transfers_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emergency_transfers`
--

LOCK TABLES `emergency_transfers` WRITE;
/*!40000 ALTER TABLE `emergency_transfers` DISABLE KEYS */;
INSERT INTO `emergency_transfers` VALUES (1,1,9,1,'Clinica Central','Hospital Regional','interhospital','high','assigned','2026-03-07 11:00:00','2026-03-07 11:10:00',NULL,NULL,'Paciente post-quirurgico para evaluacion especializada.',NULL,'2026-03-08 01:29:28','2026-03-08 01:29:28',NULL),(2,2,9,NULL,'Domicilio Paciente','Guardia Clinica','emergency','critical','requested','2026-03-07 13:00:00',NULL,NULL,NULL,'Disnea de inicio brusco.',NULL,'2026-03-08 01:29:28','2026-03-08 01:29:28',NULL),(3,1,9,1,'Clinica Central','Hospital Regional','interhospital','high','assigned','2026-03-10 11:00:00','2026-03-10 11:10:00',NULL,NULL,'Paciente post-quirurgico para evaluacion especializada.',NULL,'2026-03-10 10:42:05','2026-03-10 10:42:05',NULL),(4,2,9,NULL,'Domicilio Paciente','Guardia Clinica','emergency','critical','requested','2026-03-10 13:00:00',NULL,NULL,NULL,'Disnea de inicio brusco.',NULL,'2026-03-10 10:42:05','2026-03-10 10:42:05',NULL);
/*!40000 ALTER TABLE `emergency_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `health_insurances`
--

DROP TABLE IF EXISTS `health_insurances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `health_insurances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copay_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Copago fijo',
  `copay_percentage` int NOT NULL DEFAULT '0' COMMENT 'Porcentaje de copago',
  `requires_authorization` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `health_insurances_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `health_insurances`
--

LOCK TABLES `health_insurances` WRITE;
/*!40000 ALTER TABLE `health_insurances` DISABLE KEYS */;
/*!40000 ALTER TABLE `health_insurances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hospitalizations`
--

DROP TABLE IF EXISTS `hospitalizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hospitalizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `bed_id` bigint unsigned NOT NULL,
  `operation_id` bigint unsigned DEFAULT NULL,
  `doctor_id` bigint unsigned NOT NULL,
  `admission_date` timestamp NOT NULL,
  `expected_discharge_date` date DEFAULT NULL,
  `actual_discharge_date` timestamp NULL DEFAULT NULL,
  `admission_reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `admission_type` enum('emergency','scheduled','post_surgical','transfer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `status` enum('active','discharged','transferred','deceased') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `discharge_notes` text COLLATE utf8mb4_unicode_ci,
  `discharge_authorized_by` bigint unsigned DEFAULT NULL,
  `discharged_by` bigint unsigned DEFAULT NULL,
  `diagnosis` text COLLATE utf8mb4_unicode_ci,
  `treatment` text COLLATE utf8mb4_unicode_ci,
  `daily_observations` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hospitalizations_operation_id_foreign` (`operation_id`),
  KEY `hospitalizations_discharge_authorized_by_foreign` (`discharge_authorized_by`),
  KEY `hospitalizations_discharged_by_foreign` (`discharged_by`),
  KEY `hospitalizations_status_index` (`status`),
  KEY `hospitalizations_admission_date_index` (`admission_date`),
  KEY `hospitalizations_patient_id_status_index` (`patient_id`,`status`),
  KEY `hospitalizations_doctor_id_status_index` (`doctor_id`,`status`),
  KEY `hospitalizations_bed_id_status_index` (`bed_id`,`status`),
  CONSTRAINT `hospitalizations_bed_id_foreign` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hospitalizations_discharge_authorized_by_foreign` FOREIGN KEY (`discharge_authorized_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hospitalizations_discharged_by_foreign` FOREIGN KEY (`discharged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hospitalizations_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hospitalizations_operation_id_foreign` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `hospitalizations_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hospitalizations`
--

LOCK TABLES `hospitalizations` WRITE;
/*!40000 ALTER TABLE `hospitalizations` DISABLE KEYS */;
INSERT INTO `hospitalizations` VALUES (1,1,1,NULL,2,'2026-03-10 04:42:04','2026-03-13',NULL,'Cirugía programada de apendicectomía. Postoperatorio inmediato.','scheduled','active',NULL,NULL,NULL,'Apendicitis aguda','Apendicectomía laparoscópica. Analgesia EV. Hidratación parenteral.','Paciente estable. Signos vitales normales. Dolor controlado con analgesia.','2026-03-08 00:46:42','2026-03-10 10:42:04',NULL),(2,2,5,NULL,3,'2026-03-08 17:30:00',NULL,NULL,'Insuficiencia respiratoria aguda. Requiere monitoreo intensivo.','emergency','active',NULL,NULL,NULL,'Insuficiencia respiratoria aguda, probable SDRA','Ventilación mecánica invasiva. Sedoanalgesia. ATB empírico.','Paciente en ventilación mecánica. Evolución favorable. Parámetros hemodinámicos estables.','2026-03-08 00:46:42','2026-03-10 10:42:04',NULL),(3,3,3,NULL,2,'2026-03-05 10:42:04','2026-03-08','2026-03-09 14:45:00','Neumonía adquirida en la comunidad. Antibioticoterapia endovenosa.','scheduled','discharged','Alta médica. Mejoría clínica. Continuar con antibióticos vía oral por 7 días.',NULL,NULL,'Neumonía adquirida en la comunidad','Ceftriaxona + Azitromicina EV. Hidratación. Oxigenoterapia.','Paciente con evolución favorable. Afebril hace 48hs. Laboratorio normalizado.','2026-03-08 00:46:42','2026-03-10 10:42:04',NULL);
/*!40000 ALTER TABLE `hospitalizations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `appointment_id` bigint unsigned DEFAULT NULL,
  `health_insurance_id` bigint unsigned DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `insurance_coverage` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','partially_paid','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` enum('cash','card','transfer','insurance','other') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  KEY `invoices_appointment_id_foreign` (`appointment_id`),
  KEY `invoices_health_insurance_id_foreign` (`health_insurance_id`),
  KEY `invoices_created_by_foreign` (`created_by`),
  KEY `invoices_patient_id_invoice_date_index` (`patient_id`,`invoice_date`),
  KEY `invoices_status_invoice_date_index` (`status`,`invoice_date`),
  CONSTRAINT `invoices_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_health_insurance_id_foreign` FOREIGN KEY (`health_insurance_id`) REFERENCES `health_insurances` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maintenance_orders`
--

DROP TABLE IF EXISTS `maintenance_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenance_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `medical_equipment_id` bigint unsigned NOT NULL,
  `reported_by` bigint unsigned DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','on_hold','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `reported_at` timestamp NOT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text COLLATE utf8mb4_unicode_ci,
  `cost` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_orders_medical_equipment_id_foreign` (`medical_equipment_id`),
  KEY `maintenance_orders_reported_by_foreign` (`reported_by`),
  KEY `maintenance_orders_assigned_to_foreign` (`assigned_to`),
  KEY `maintenance_orders_status_priority_index` (`status`,`priority`),
  KEY `maintenance_orders_reported_at_index` (`reported_at`),
  CONSTRAINT `maintenance_orders_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_orders_medical_equipment_id_foreign` FOREIGN KEY (`medical_equipment_id`) REFERENCES `medical_equipments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `maintenance_orders_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maintenance_orders`
--

LOCK TABLES `maintenance_orders` WRITE;
/*!40000 ALTER TABLE `maintenance_orders` DISABLE KEYS */;
INSERT INTO `maintenance_orders` VALUES (1,2,8,8,'Revision preventiva de ventilador','Control de presion, sensores y bateria.','high','open','2026-03-06 01:29:38',NULL,NULL,NULL,NULL,'2026-03-08 01:29:38','2026-03-08 01:29:38',NULL),(2,3,8,8,'Calibracion ecografo','Ajuste de imagen y verificacion de transductor.','critical','in_progress','2026-03-07 01:29:38','2026-03-07 17:29:38',NULL,NULL,NULL,'2026-03-08 01:29:38','2026-03-08 01:29:38',NULL),(3,2,8,8,'Revision preventiva de ventilador','Control de presion, sensores y bateria.','high','open','2026-03-08 10:42:05',NULL,NULL,NULL,NULL,'2026-03-10 10:42:05','2026-03-10 10:42:05',NULL),(4,3,8,8,'Calibracion ecografo','Ajuste de imagen y verificacion de transductor.','critical','in_progress','2026-03-09 10:42:05','2026-03-10 02:42:05',NULL,NULL,NULL,'2026-03-10 10:42:05','2026-03-10 10:42:05',NULL);
/*!40000 ALTER TABLE `maintenance_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_equipments`
--

DROP TABLE IF EXISTS `medical_equipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medical_equipments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` enum('monitoring','imaging','life_support','laboratory','surgical','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serial_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('operational','maintenance_required','in_maintenance','out_of_service') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operational',
  `last_maintenance_at` timestamp NULL DEFAULT NULL,
  `next_maintenance_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `medical_equipments_code_unique` (`code`),
  KEY `medical_equipments_created_by_foreign` (`created_by`),
  KEY `medical_equipments_status_category_index` (`status`,`category`),
  KEY `medical_equipments_location_index` (`location`),
  CONSTRAINT `medical_equipments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_equipments`
--

LOCK TABLES `medical_equipments` WRITE;
/*!40000 ALTER TABLE `medical_equipments` DISABLE KEYS */;
INSERT INTO `medical_equipments` VALUES (1,'Monitor Multiparametrico UCI 01','EQ-MON-001','monitoring','Mindray','BeneVision N12',NULL,'UCI - Box 1','operational',NULL,'2026-04-22 01:29:38',NULL,8,'2026-03-08 01:29:38','2026-03-08 01:29:38',NULL),(2,'Ventilador Mecanico Adulto 02','EQ-VENT-002','life_support','Drager','Evita V300',NULL,'UCI - Box 2','maintenance_required',NULL,'2026-04-22 01:29:38',NULL,8,'2026-03-08 01:29:38','2026-03-08 01:29:38',NULL),(3,'Ecografo Portatil Guardia','EQ-IMG-003','imaging','Philips','Lumify',NULL,'Guardia','in_maintenance',NULL,'2026-04-22 01:29:38',NULL,8,'2026-03-08 01:29:38','2026-03-08 01:29:38',NULL),(4,'Bomba de Infusion Pediatria','EQ-INF-004','life_support','Baxter','Sigma Spectrum',NULL,'Pediatria','operational',NULL,'2026-04-22 01:29:38',NULL,8,'2026-03-08 01:29:38','2026-03-08 01:29:38',NULL);
/*!40000 ALTER TABLE `medical_equipments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_records`
--

DROP TABLE IF EXISTS `medical_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medical_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `doctor_id` bigint unsigned NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `diagnosis` text COLLATE utf8mb4_unicode_ci,
  `treatment` text COLLATE utf8mb4_unicode_ci,
  `private_notes` text COLLATE utf8mb4_unicode_ci,
  `is_first_consultation` tinyint(1) NOT NULL DEFAULT '0',
  `health_background` text COLLATE utf8mb4_unicode_ci,
  `attachments` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `appointment_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `medical_records_patient_id_index` (`patient_id`),
  KEY `medical_records_doctor_id_index` (`doctor_id`),
  KEY `medical_records_created_at_index` (`created_at`),
  KEY `medical_records_appointment_id_foreign` (`appointment_id`),
  CONSTRAINT `medical_records_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`),
  CONSTRAINT `medical_records_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `medical_records_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_records`
--

LOCK TABLES `medical_records` WRITE;
/*!40000 ALTER TABLE `medical_records` DISABLE KEYS */;
INSERT INTO `medical_records` VALUES (1,1,2,'Dolor de cabeza','Insomnio primario','Seguimiento en 1 semana','eyJpdiI6Iit4Rnp5bllpK2FZNVpEYnBiSXg0OWc9PSIsInZhbHVlIjoiZjdjem43amJzMmpRelZYUWswckZhTEFDb2prU2R5eHRSK3ErbGFxM09BR1d3RHQvY21YY2ZTUlh3aXJKekJyVSIsIm1hYyI6IjY3MzhiNGRkYWJkNjJhMzZjNTgxMzUwMDBjNmNkZjBiYWM0ZTYzZTg4MjRhNzNkYjY0Y2M1OGNhOTgwMWFmZWUiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-10-21 00:46:41','2025-10-30 00:46:41',NULL),(2,1,4,'Insomnio','Paciente estable','Higiene del sueño','eyJpdiI6InRvNFB6UktLdUQxbVNpQjNHZTVpcFE9PSIsInZhbHVlIjoiSHJYL1dsb2FHZmw1Wm9JK1BEdjJ1L05JczEzMmNsVGlubllkaVlvRXlMd1RmUUh4YVkxcmRVbXJHeXRkancrZzdqLzFvS0k2ZUJZQXVyMm95eFhuR0E9PSIsIm1hYyI6Ijk2MGY5OWMyN2ZmZjRlYWZiM2U3MzRmZWRkMDYyZGRjMTRhY2M0OGUxZjVjODYwZjliNzM5YzI2YTI4OGQyZmEiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-01-20 00:46:41','2026-02-09 00:46:41',NULL),(3,2,2,'Control de presión arterial','Hipertensión controlada','Ajuste de insulina','eyJpdiI6ImJOTXUzaEtRemVOV2RLamV2KzBHbHc9PSIsInZhbHVlIjoiVmdOT0pDZ3pkbEVyZjVpR29uVjRyZEoyVThFejFJbVpTK2NaWXVlOVI1VHMwZWJvQTNzOVNKTVRBekU4VllKeE9uNUNDVExjZkUwb3haMUNLaWo4RGc9PSIsIm1hYyI6IjVlMDU3ZjU0N2UxZDdlMzE4NzE1ODZlNzNmOGEzZTIzYTY3NWYwZWU1MzI1ZWZjMWJmYTIyOTQ5M2MxNmYwZjciLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-02-07 00:46:41','2025-12-08 00:46:41',NULL),(4,2,2,'Dolor de cabeza','Hipertensión controlada','Dieta blanda','eyJpdiI6IlVrbXVLMmVNeTdaY3drWWxUSTUwbHc9PSIsInZhbHVlIjoiNmJmKzNaWlErR2J1azJZM1ZMZkZoMU1YMklNdC9RSlA4bDlLdlRvVUFtQUZYK0xMbW43c3hYbWNTWHRkTnFvRCIsIm1hYyI6IjZhZmZmZTFjNzY2YzBmY2Q0ZmVjYzMxZDQyMjJjMTI1NmU2Mzg2N2NjYWJmOTU2MWI5M2I2MDQxYTNkZDU4ZmUiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-09-19 00:46:41','2025-09-09 00:46:41',NULL),(5,2,3,'Control de presión arterial','Gastritis','Antibióticos si es necesario','eyJpdiI6Iml5WDVORmJvY0dNR1hZZUlpYW1NMHc9PSIsInZhbHVlIjoiOXl3eHExcnZEbC85QlB4dmlkOGFGeHN0QWErRkFSbFFpNnJvYWZzTVFXNHhVNVpuVnlMMjc3ckJLWUVObGxqbSIsIm1hYyI6IjAwZmIwNTc3MDdkNmI1Njk1N2YyMDIxYzE3Yjk1YzkzMjc0MmM0NWY3MTRmMWM3OThkYmFhZmFhNDQwMDlhMTAiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-09-12 00:46:41','2025-12-02 00:46:41',NULL),(6,2,3,'Tos persistente','Hipertensión controlada','Seguimiento en 1 semana','eyJpdiI6Imw2cnJtWGxwUEkrQWxPbzRtMlVqekE9PSIsInZhbHVlIjoiajJOYzh0bjBOOVlsVFRGOVBQYlVBMFUzWjYxY09VcmlCTHlMU1c4NDl1SHhYb25zdWNNdDRENFlxTGpGazZyYiIsIm1hYyI6IjU4MjJhN2I4Mjc1NjM3MDIwNDEwOGViZDEyNjRjMWIzOGEwNWE0ZWJlYzZmMjRiM2Q4MjI4ODQxN2IyNWRhMGMiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-10-30 00:46:41','2025-09-13 00:46:41',NULL),(7,3,2,'Gripe','Diabetes tipo 2 compensada','Higiene del sueño','eyJpdiI6IlpqankxSjNJOGlzeDZmTzBMQldEZ0E9PSIsInZhbHVlIjoiUFE2ckhXUlpldlRkdkdnc01SQ0tqSU02ckdndWtXMVNmRlV2MDRNaWJNTElTQXd1QUJvcHZMRFpUSXpEZnN6TTZMejlHTER0N1I4Nld5N0wrTjBKbHc9PSIsIm1hYyI6ImIwZjI4MjA2M2NjNzE4ODkyOGIyYTU2OWIyOTdiN2JlYWYyZmU4MGJlYzdhZGU4YzdmOWYzNmUwOWVhYzNkZTYiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-01-04 00:46:41','2025-09-24 00:46:41',NULL),(8,3,4,'Diabetes check-up','Bronquitis leve','Antiinflamatorios','eyJpdiI6IjBuVkxZdG0vZFkvUnJnclY2c0xIQ1E9PSIsInZhbHVlIjoiVzkwQm9odXZQbDJmb2dudjU1S0tqVUdmWDlPRGVkalJweUt0WHhnSE5yQXVmaDB5T20zaVVFYzJRV1NNcnlOQiIsIm1hYyI6ImNiOWRlZGY3NzhiZTEzMzVjNGUxYWZjMzljMTBlYzBiMDY1ZmE1N2Y5ZjI0NGU1NTY4YTIxZTQzNDUyMDc0M2IiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-02-28 00:46:41','2026-01-25 00:46:41',NULL),(9,3,2,'Dolor de espalda','Insomnio primario','Higiene del sueño','eyJpdiI6Ii93TUMwOXlLVTVwWVU5aXlESXNwNkE9PSIsInZhbHVlIjoiUHVyN2lKSzVzM0U1V2ltRk5CTXVCWEpja0RZakFtWU9JbGV1dlRXRmN1MXI1bGliZER3cWJGd1lGbk1YY3dXZTJESnRzbDdoWjkwQ1d0dFMydDJGaGc9PSIsIm1hYyI6IjhlNWE1ODBlNGVmZjgzYTgzZDk5M2Q4NDdlZmY5ZjFhYjk5NWY2ZTVlMWZhOGY0YTc4NTVmZmY2MGNlNjZkYzYiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-12-05 00:46:41','2025-11-21 00:46:41',NULL),(10,3,3,'Tos persistente','Bronquitis leve','Higiene del sueño','eyJpdiI6InhjMTVZc0wwbDZKcE5YV0thcnptT1E9PSIsInZhbHVlIjoiZU9EZGhVcE9FRnJpSjMwWVAvS1hneVFBSVJyTVR0MTNnRFVJcGp3aUsrOXVxN21mbWIzQVJaUHJGWVFDanRnWjU5SFNrV2dtbVE2OGRZSVJFZE1CbkE9PSIsIm1hYyI6IjVlODk1NmQxNzMxNTFkZjM4YTgzNGZkZDJlOTIxYTIzMjk2MmU4YWIwNTI0ZDhiNDA3MTE1ZjEzZTk1OTBkZGIiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-03-02 00:46:41','2025-09-19 00:46:41',NULL),(11,4,2,'Problemas digestivos','Insomnio primario','Psicoterapia y relajación','eyJpdiI6IlVkSy9pbERlOHBYUDk1OGRhRE9Fb0E9PSIsInZhbHVlIjoiREZ2T0VoakJacUxoYVZRYjE1UmZWQ081SXh4TUdZc1hnTkljckNqNFNmMTBVRzByQ1BoSDZFYWtJdm9CT1FlZnVvRWFlY3VGeGg1K3UyZU8zNmRZTnc9PSIsIm1hYyI6ImM1YjQ3MzMzOGQxZjRlZWIyMDI0YmIxYTk0YWQzM2JhNzFhY2NjODZlMTYxMTAxM2JmZGUzNzg0OThjOWNlMDciLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-11-24 00:46:41','2026-02-09 00:46:41',NULL),(12,4,2,'Ansiedad','Diabetes tipo 2 compensada','Psicoterapia y relajación','eyJpdiI6Ik8xa0hPRTR0MW9DRlpObmpFdzcxbUE9PSIsInZhbHVlIjoiOHJoU3owc0JBUjlaOTlSaW5RYXpEVnBTSCtPalJWK0t1REhnT3k3WFd4YjdUQUxBV1hrZDZId0FUYU1sSCsvMDUxSXR5T3YwYzNndFp5eFliUS9ZeWc9PSIsIm1hYyI6IjViMjczZmRhMTVkZWNmMTcyZDU4NTUxY2M3NDM0YmUxMzllNWQxM2VkZGMyMTMyZDU1NzYxZDRkMWYyOWZlOGYiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-10-06 00:46:41','2025-09-26 00:46:41',NULL),(13,4,2,'Ansiedad','Paciente estable','Dieta blanda','eyJpdiI6IlMybDZlc3V2NlViZmU5R3R4ZHlTblE9PSIsInZhbHVlIjoiUE5SZ3J3aFVvNEdjdzlxM3lvNUhVUlNhSXB0SG5Namk0RmxvWUltMGFnZTR6WSt4dWZPTVR5NC9KOEQ5ZTlWQWdlQ2VnelVJSnJqL3Y1TkVJaTAzWEE9PSIsIm1hYyI6IjkxZjdjMWVhNWQ0MTY2YjFmYjczZjc1YTcyM2UyYTg4ZWU5YWZiYjM0YTRhNTA1NTljYThjMDAwN2VjZDE2MjciLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-11-30 00:46:41','2025-10-25 00:46:41',NULL),(14,4,2,'Dolor de cabeza','Hipertensión controlada','Seguimiento en 1 semana','eyJpdiI6IkFYWnBRaVFlM3dvbkZSZ21md3RhU3c9PSIsInZhbHVlIjoiNEdUWVRMblRYdG9KOW1RY0FOUThHWUgrbE9XOTdpVENWaEsvbnRDMnJIVWo4cFlia1lFdXJQVXgvem1wU1V3dSIsIm1hYyI6ImIzNWNhMDAwNTk3NDYyZTM3NGU3MzE0OTg0ZjY2ZmVlNjY2MjAxZDYzZjk4YTI0NWNjYmQyZTJlYjAyMjg1NmEiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-11-25 00:46:41','2025-10-11 00:46:41',NULL),(15,5,3,'Insomnio','Bronquitis leve','Antibióticos si es necesario','eyJpdiI6IjE0YkNsMHZsbmEvNzA2UlBDaUwzcFE9PSIsInZhbHVlIjoieUFhaU54UnV0blNYMEltSGQra01CS0JhQThCRkR5MW1BcFVJOE8xV1IyeVRycHo5QmtwbjJjZTBIN1Bybk00VSIsIm1hYyI6ImUzNzExNzNiNjVlODk2ZGZjOGYyMzc0Y2Y2YzIzZjg5YjM0MGU2Mjg1ZDg2YmUxZTI5MGFjMGUyZTk3Yzg3Y2YiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-10-28 00:46:41','2025-12-22 00:46:41',NULL),(16,5,4,'Gripe','Hipertensión controlada','Antihipertensivos','eyJpdiI6ImdsZkxRSENoTUdZd3pVMGM2T1VSOXc9PSIsInZhbHVlIjoidmRsZm50ZTZrVjNBOU1sM1V1OHBWUGhWcXU3bHlRck1DMTRhTllsa05Xb0g5MnUxcEpuU05CbU5mZXpMUlpYZ1BHNFNWR0dzK2JxdkRYMUNQRXlSUkE9PSIsIm1hYyI6ImNkOWM1MTc5MjU4NzNhZjdmYTdhOTY0MGVlYjRmZTg2ZDJhNjAwNTFjMGRjNTBhOTVlMTlkOTQyN2NmMTQ2ZjkiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-02-27 00:46:41','2025-12-11 00:46:41',NULL),(17,5,3,'Dolor de cabeza','Lumbalgia','Antibióticos si es necesario','eyJpdiI6InJZQWVFbmN6QXNGQlpwMVQ2b0dGQlE9PSIsInZhbHVlIjoiWVJvd3JkUUoyc0prUUlYZ3Z1SlBtR1RLdDFiTHBHQ244MmdLcDVQL0NMTEJPTXVBZExsOGFRcXovZTR3WGpmbSIsIm1hYyI6ImY4YmYzZTIyYTE5ZTk0YTY2NTc5MzY4ZmVhOGVmMmZmZGQ4N2I0OWE4MjQxM2M1ZmI1MGFkN2ZhODE0Yzk4YmEiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-09-29 00:46:41','2026-01-08 00:46:41',NULL),(18,5,4,'Ansiedad','Trastorno de ansiedad generalizada','Dieta blanda','eyJpdiI6ImpZVnV3ZlZQcHMzdE5xTzd4Wi92SWc9PSIsInZhbHVlIjoiMGpiM3hEdnNQRXRYcE1xOVl5Sm9GNGw2eGFkcmVQZWYrOVRFd21TRkVnNkgrb00rWFR0bWM0eWRxenNGWTlZcHJ6Q3JRa01hUzg5ZVg1cFZGVkVtRkE9PSIsIm1hYyI6ImZhMTIyYjRmYjc4NWM0MzZiM2MyYjIxNmZiNzRlMzFlOWYyMWFjOTk1YjBkZWQ5NmFmMTk5ZmY3YTVkNjU3NzkiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-02-13 00:46:41','2025-10-28 00:46:41',NULL),(19,6,4,'Ansiedad','Trastorno de ansiedad generalizada','Reposo y analgésicos','eyJpdiI6IkFJNDJpNDhrWkNMbHlNeU5yb2hPNHc9PSIsInZhbHVlIjoia2JJRGJNM1hjODRyTTdRaGZDK2JNWktYbllyaVpDRS95dUJkN2lBOTFINHJCMmFxbVBSMDFTT24yNThHSFRvOCIsIm1hYyI6ImQwYjdhZWRhYjYxMTZmZmQxNDVhMTk0MDU3OTNiNTI4YWEzZTVlODI5MjRhMjk5YWZmY2MwMmM1YWQwN2QzMGUiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-11-02 00:46:41','2026-03-05 00:46:41',NULL),(20,6,4,'Gripe','Paciente estable','Seguimiento en 1 semana','eyJpdiI6Inlub3VEVG0rNUsrd2ZvQW5TZUF3S2c9PSIsInZhbHVlIjoiYktLeG85YWhRb2VFcE80cklmK1czSE1TaWpKNjRFVTRycXRxTGkrZzNYVWhlNk5EZmdibGRxOW15Yi9jWElCbCIsIm1hYyI6ImRkNGUyM2UyYzMyYjI5Yzk5YTczNmNjY2FmNTQ1ZTM1MTU4OWJjZmNiNTExNzQ4OWFlYWFlMjJmMjQ1NzBiYzgiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-01-11 00:46:41','2025-12-13 00:46:41',NULL),(21,6,3,'Dolor de cabeza','Paciente estable','Antibióticos si es necesario','eyJpdiI6Ik9PQ2YxeUEyVjNVVDQxZU9rQjJlcGc9PSIsInZhbHVlIjoid052VjhsVWloRW8zR2duZHAvMTk0ZGM1YlJJdkN0bzExdlRFelFYYWFEQ0NSbU5BYVd4UWk5SGphdk9pWSt4MiIsIm1hYyI6IjJmMjcxZTZlODBkMjM4MWE2ZGY0ZTljYTRiYThkNGE4MTkyM2M0NWIyMGYzYjllNTNkZjU4ZmFlZWFjM2VmNjUiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-12-30 00:46:41','2026-02-03 00:46:41',NULL),(22,7,4,'Tos persistente','Bronquitis leve','Antihipertensivos','eyJpdiI6IkRCOU9FMkNxS3RWU3ZGQ1JvNWhnWkE9PSIsInZhbHVlIjoicWkrUHhoL2FmOUR1WTBSZkJtMExtSHk5THU5N3I1TklLRTVSRHdoSXgyYWRlWW4vVGVpTFdab2hTV0pFYllaZCIsIm1hYyI6IjI0MDgyZjMzYTI2ZTU0ZDU1MWU0NmMzN2I3MzVmNzk0NWEyZGM4MzdmODI1ZTRkMmY0ODY0YzBkODY0Y2E3NjQiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-10-14 00:46:41','2026-02-15 00:46:41',NULL),(23,8,2,'Tos persistente','Gripe común','Seguimiento en 1 semana','eyJpdiI6ImtXVE1QQ2Vsdk1jU2JTUC8zS0dPQUE9PSIsInZhbHVlIjoidHlMZTd6d0RlcEJlL09OZE5SQmFLakgwVkxYQ1hOWjAvVWNiVk5tajRTZU5XZUFzL05YZ054QmxRNXBtWndnOEdKZkxna2kxTUdpUkc1c3N1SlRpb0E9PSIsIm1hYyI6IjUwNjE5MTY1MDhjOWJmMDhkZTVkY2RlYmJhYmVmOWI0NDY2YjZhN2ViYzc1N2YzZjU1MTYyZDg0NGExYmYwODAiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-12-09 00:46:41','2026-03-02 00:46:41',NULL),(24,9,4,'Chequeo general','Diabetes tipo 2 compensada','Seguimiento en 1 semana','eyJpdiI6IkFTdGtoUzJLeDhKT0U3QzBoVFp3THc9PSIsInZhbHVlIjoiMXM2RkJqeW5CSVRDZnVNajZBSEgyM3N0SjlUYXVkY1FpbnBIUUJ4V3RENTd5aEJIR2M1SUxwcEpUczZpdHVVdCIsIm1hYyI6IjdjZGU3ODI5N2FjNDIxODAyMDZhYTRhZjdmM2YwN2M0ZWVkYjg3ZTlmZWM5Zjg3MTllY2ZiZjFiMTkxZTg4ZTgiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-12-29 00:46:41','2025-12-23 00:46:41',NULL),(25,10,2,'Diabetes check-up','Gastritis','Psicoterapia y relajación','eyJpdiI6IjBlRnlPa2IyU2QzMkR4WkhRUi8yWHc9PSIsInZhbHVlIjoidnFTTk9LaUpZbXNpYVFQUGkvSFVKc2U2SUZHSC9GdDRtdGZiNTZzRGxseTFTMHZ6ZTRtUUtpYzdEOTNIUXBMaiIsIm1hYyI6IjRlOWMyODRjZTVhY2E1YmY3NGM1MTA4YTAxYTc0NmEzZDhkMWM2ZmI2NmJhMTkyNGI5ZDgyNmFjYmY0YjhlODQiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-02-25 00:46:41','2025-10-21 00:46:41',NULL),(26,10,2,'Ansiedad','Diabetes tipo 2 compensada','Antiinflamatorios','eyJpdiI6IlBTNGZsaVJ1QnZCVkhIMnk5alpIUUE9PSIsInZhbHVlIjoiMXF6T04vOGwzd2pmTVEyekgrZFdPSjFkZlVyTVlNbFhXWG9xVHRDREdQNDFGYnNJZ2JJTnB4bkduZ2ZXRXc0YyIsIm1hYyI6ImU5YjIwNmNjZTYzNzAyZGViNjYzNThhOTAwMmVmOTM3MWRlODdjYzk1NGJkMzhlZGY2NDdkZGY2MzZmNWFjNjQiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-02-12 00:46:41','2025-11-15 00:46:41',NULL),(27,10,3,'Control de presión arterial','Insomnio primario','Higiene del sueño','eyJpdiI6Ik1PNXREczZkdFpEV1FDY2x5SjlGNlE9PSIsInZhbHVlIjoiZW1sU3pDVUI5MkxHRTZCTjMwUXROVWs0N2FCTThQbjhreEZ2ZkR1R0lzdUc1eGo2QVgxZ3laZEp5WGxTams4bSIsIm1hYyI6ImI1NTlkNDNjYzdjOTcxNmE2ZmNmYTZmYWM4ODI0MWQwZjNlMGE4MWExODIzNjVmY2ZlZGQxZmQ5OWVhODZkMTMiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-01-18 00:46:41','2025-09-16 00:46:41',NULL),(28,10,2,'Insomnio','Migraña tensional','Higiene del sueño','eyJpdiI6IlZQSEZCK0pUV2tJZWlUN0JudXVYdkE9PSIsInZhbHVlIjoib29VSTlQc2NVYnZnTllWNXV1RmxITXdPeEhrVUNNMk53RFFRaGRmaVVvR2JYdWdJQ0UxSnYySGVib25Na09vcFJOcS9BTkJWVENyWW1zSE9SRXE1bkE9PSIsIm1hYyI6Ijk4OWRjN2I4MjVjMWI3YzNiZmI5ZGZkNThiOTQ2YzQxNzdkNzc4OTU3YzI5MjIwYzVlNzcyYTU1ZGQ3NTM3YzgiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-12-14 00:46:41','2026-02-19 00:46:41',NULL),(29,11,2,'Chequeo general','Hipertensión controlada','Antihipertensivos','eyJpdiI6IklyaXh5MDRPWkIveHNyOGpxWDM0WVE9PSIsInZhbHVlIjoiaVliSzVaaFF3Mno3VC9lcm5VMWdyS25iMmJwcGhpa3dwV1o5MTUxbnNOcHlDYlBuZXNXSk8wMzQvRGIycmdpYTF1azVUMDRVeDJ4U2VUU2RrMzF2MkE9PSIsIm1hYyI6ImM0ZDYwNDU0NDUwNTdmZDQ0MmFmMWM2Yjg1MTA1ZmU5NjMzOTQyOGRlMzIzNDYyZmNmZTY0YzUzOTA1NDFmNDYiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-12-12 00:46:41','2025-12-13 00:46:41',NULL),(30,11,2,'Ansiedad','Bronquitis leve','Antibióticos si es necesario','eyJpdiI6IkJSSmxhSjRIVGR1U24wR293NHdtSGc9PSIsInZhbHVlIjoiMTRBUlA2R2tpS0E4cGxqQlRQaVhMVjdiUTArZnZUTDZhUWtxVjE4eS9FdEZRQVNLZVNiWHBEV2thWXpVNnVyOFJYdllUazFOa2RBejZ0RTJOMGhaVFE9PSIsIm1hYyI6IjljMzk1NDIxNjcyZWVlODZjYWMyMWM0N2E5NDdlYmMyN2QwOWNjMTUwNDE1YzY2ZWI1ZjBjYzA5ZWY3MmY2Y2UiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-10-14 00:46:41','2026-02-12 00:46:41',NULL),(31,11,2,'Gripe','Insomnio primario','Antihipertensivos','eyJpdiI6Im95QWgycjhrYWt5ZHB6RGJia0lTeHc9PSIsInZhbHVlIjoiZk1ZT2k5Tmpzb3BvMFZRNk1kcmd5WHV4TkI2aDFVOERSbDZSTitoRmRpYndPTGVYV1AyNjB2Sy9Va2d4MjdBcSIsIm1hYyI6ImEzN2IwZDUyZjQ3MWIxZTAwMTk5MTgzM2FhYWZkMGY1Mjc0MjhiZGQ4MWY2MTRjNzA1N2ExMmYxYTI1MmQ3OGEiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-12-09 00:46:41','2025-11-11 00:46:41',NULL),(32,11,2,'Tos persistente','Hipertensión controlada','Antitusígenos','eyJpdiI6IjFjQmJUVEpHQ1gyMFB3SFJuTXc1bVE9PSIsInZhbHVlIjoiUmJRUHZWOGVNVkRRL252K3Z5ajk0ejg4M2U1MzVpWFFOV0x3SEtCRXhFUTdXMTl5SEQ0SlJKcnpOb3V6VmY4di9DZFJoYXF0YmRUa05lWEtHQUpBa2c9PSIsIm1hYyI6IjZlMzI2ZDBkYzllOGUxZjY3YThjZmVlZGQzMjNmZTkzYTJlZmEwYjgwMzIxN2RmMjZhNGM1ZGE4OGJhMDQ4YTAiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-12-24 00:46:41','2025-10-31 00:46:41',NULL),(33,12,4,'Dolor de cabeza','Lumbalgia','Reposo y analgésicos','eyJpdiI6IjRXdzBIY0c0Zm4wcE1PRlBpTENTcXc9PSIsInZhbHVlIjoiL0p3Mkxvd1k1Z0xqenI1enNJZVlNaVZoWERhWmxaVnhrMmNibGRiaXBmb3FleGkvd1VNRVp2REg4Z1RzcTdkQ0JIbFl5QWJnTlUwTDhlemYxOGdaekE9PSIsIm1hYyI6ImQ5Y2FhN2QxZWJlNDMzZmY0OTI3NmJlZTM4ODc5ZmQ2N2UyMGNmY2JlODIzOWVlOGM0NThjMTA5OTk3ZTc3YjMiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-10-14 00:46:41','2025-12-04 00:46:41',NULL),(34,13,2,'Ansiedad','Gastritis','Higiene del sueño','eyJpdiI6IlhqK0lXZ09pZGtqcDZpOGVJOGxlZkE9PSIsInZhbHVlIjoiUUo5ZU05ZUdMVmp1bjdhL253YmtpVlMwWnN4aGxTN09CZmpWS1FTbkpPSXNGT002QThUcWxEZmdLYWpkeUtjOVkzNmQ5a2xnTk5yNEZ0amtnV1pIaGc9PSIsIm1hYyI6IjgzNjNmYjg2ZmQzZWVkZWViZWZkOWMwMmNlODA5ZjZiZmFlOTE3YTA1ODlhN2M1NDQyZjY4MTY4NTNkMWU2OGQiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-10-12 00:46:41','2025-12-27 00:46:41',NULL),(35,14,2,'Ansiedad','Lumbalgia','Reposo y analgésicos','eyJpdiI6IjhPVzVvbHh0UGM2TEhyNC9vU2lYNFE9PSIsInZhbHVlIjoiYi9JbWZaYmVSSUs2anFjSjFEMHg4QVdoMGFENXIwM3g3TVR2bTJFRzczd0hjamNieWk1R3JDWE1XNFNiWHZDbm90Uld6MjlVRkNtcTBPcy9lNDBLc0E9PSIsIm1hYyI6IjE5M2ZjZmU4OGRmMmY3MDE1MzRjMDg3MDIzNjExNmFmNmEzOTlkOGQ0MzAzZWNkZDE2YzQ5NWY3MjVhMzgwYmEiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-03-05 00:46:41','2026-01-13 00:46:41',NULL),(36,14,3,'Insomnio','Lumbalgia','Higiene del sueño','eyJpdiI6IlJMU090TTVVU3NsNTBqR2VEeEo5QVE9PSIsInZhbHVlIjoiNW02VVhKWmJxL3F5WEw1Zk5vM09VZGRWSFd0aU1zVGtDTmRRdjhiNnNxT2x5cW00Y3AxNG1RcExLVXlGVTU0VCIsIm1hYyI6IjNkNTI2OWIxNjVkMDQ4MWVhMzBkZDE0Yzg0MTU3MTc4YjMxZTM1OGYwZDU4YTQ0MzQ3OGE3YjZmYjZjNzM3ZmUiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-02-10 00:46:41','2025-12-20 00:46:41',NULL),(37,14,4,'Control de presión arterial','Lumbalgia','Antitusígenos','eyJpdiI6ImdWL2hBL0FYQll4dXRCRjFoK0ZHVmc9PSIsInZhbHVlIjoiOWpOSU54a2RiTXJlT01QUFRJTWg2ZTVBeW5oK3ptM0F3Nm1xd0FmNTdreXhtZlYzbzhqNG4xR3lMbUJqWVJWYW1sbmVNSGUzQUdrOVVaK1UxeGE0VEE9PSIsIm1hYyI6ImViZTIyN2UyNDIyNWQ2MTZjYmQwY2YxYjhiYzdhZTY1MjQzNzA0NTQ3OWJhZTY4MmJkMWFkNmE3NGUwMDZhNmUiLCJ0YWciOiIifQ==',0,NULL,NULL,'2025-10-26 00:46:41','2025-09-16 00:46:41',NULL),(38,14,3,'Control de presión arterial','Trastorno de ansiedad generalizada','Antibióticos si es necesario','eyJpdiI6Ik93c1pJOW9uYUloVDN2T3A1d0lueHc9PSIsInZhbHVlIjoicWRtTjdoc0hTckRBU0RZbVZzSng3aHRrM1dvNC9JNjNFSk5ROWFhcXA5N0J2ZXdDSGxBdkJJN1dnRHlIeGNWMm43MnlNR3V4UzRtZk85SFhjUkZBblE9PSIsIm1hYyI6IjM0ZmNjZDMwMGE2YTgyMzdmOGIzOTE4MjE2ZDIxOTc2OWViNGZjZjNkNWE0M2RiMjE2M2Y0YTU3OGE1MTg0ZTAiLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-01-02 00:46:41','2025-11-20 00:46:41',NULL),(39,15,4,'Problemas digestivos','Trastorno de ansiedad generalizada','Antibióticos si es necesario','eyJpdiI6Im9Jb2dlSUJCUkJLUGVzWHJBeGJZRnc9PSIsInZhbHVlIjoiYkRSM2ZqSXRoWGVxYkt3WS9mRWlIOFpRcGZhSzl0bzdXbndzR2o1TVROejBOeldyZHNXUHozeExXaGRrVkU4MSIsIm1hYyI6ImJiNjM0NWEyOThmNTc0YTQyNmMwNTI0N2YwYzAxMDAzNzk1MjZiOTI2ZjExNjFiZDJhMjkzYmE2MDk5MGJmZTciLCJ0YWciOiIifQ==',0,NULL,NULL,'2026-01-25 00:46:41','2025-10-24 00:46:41',NULL);
/*!40000 ALTER TABLE `medical_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_02_12_144145_create_patients_table',1),(5,'2026_02_12_144626_create_appointments_table',1),(6,'2026_02_12_144703_add_role_and_specialty_to_users_table',1),(7,'2026_02_12_150058_create_medical_records_table',1),(8,'2026_02_12_160000_create_audits_table',1),(9,'2026_02_12_173150_add_appointment_id_to_medical_records_table',1),(10,'2026_02_13_000000_update_appointments_status_enum',1),(11,'2026_02_14_000001_create_doctor_schedules_table',1),(12,'2026_02_14_000002_create_doctor_exceptions_table',1),(13,'2026_02_14_000003_add_appointment_enhancements',1),(14,'2026_02_14_000004_add_patient_enhancements',1),(15,'2026_02_14_000005_create_health_insurances_table',1),(16,'2026_02_14_000006_create_patient_insurance_table',1),(17,'2026_02_14_000007_create_invoices_table',1),(18,'2026_02_14_000008_create_invoice_items_table',1),(19,'2026_02_14_000009_create_payments_table',1),(20,'2026_02_22_214403_create_prescriptions_table',1),(21,'2026_02_22_215849_add_professional_info_to_users',1),(22,'2026_02_22_215937_add_diagnosis_to_prescriptions',1),(23,'2026_02_22_222732_add_dni_to_users',1),(24,'2026_02_22_230001_add_cuil_to_patients_table',1),(25,'2026_02_24_000155_add_medical_record_id_to_prescriptions_table',1),(26,'2026_02_28_014328_add_renapdis_fields_to_prescriptions_table',1),(27,'2026_02_28_014444_add_matricula_fields_to_users_table',1),(28,'2026_02_28_020403_create_audit_logs_table',1),(29,'2026_02_28_020633_add_soft_deletes_to_prescriptions_table',1),(30,'2026_03_02_120000_add_first_consultation_fields_to_medical_records_table',1),(31,'2026_03_02_130000_add_coseguro_and_insurance_to_appointments',1),(32,'2026_03_07_000001_add_pharmacy_role_to_users',1),(33,'2026_03_07_000002_create_pharmacy_items_table',1),(34,'2026_03_07_000003_create_pharmacy_requests_table',1),(35,'2026_03_07_000004_create_pharmacy_request_items_table',1),(36,'2026_03_07_000005_create_pharmacy_stock_movements_table',1),(37,'2026_03_07_000006_add_operating_room_manager_role_to_users',1),(38,'2026_03_07_000007_create_operation_rooms_table',1),(39,'2026_03_07_000008_create_operations_table',1),(40,'2026_03_07_000009_create_operation_pharmacy_items_table',1),(41,'2026_03_07_000010_create_pre_admissions_table',1),(42,'2026_03_07_000011_create_required_documents_table',1),(43,'2026_03_07_000012_create_pre_admission_documents_table',1),(44,'2026_03_07_000012_create_rooms_table',1),(45,'2026_03_07_000013_create_beds_table',1),(46,'2026_03_07_000014_create_hospitalizations_table',1),(47,'2026_03_07_000015_create_bed_cleaning_logs_table',1),(48,'2026_03_07_000016_add_nurse_role_to_users',1),(49,'2026_03_07_000017_add_emergency_and_accountant_roles_to_users',1),(50,'2026_03_07_000018_create_emergency_admissions_table',1),(51,'2026_03_07_000019_create_emergency_evolutions_table',1),(52,'2026_03_07_000020_create_patient_accounts_table',1),(53,'2026_03_07_000021_create_account_transactions_table',1),(54,'2026_03_07_000022_add_maintenance_and_paramedic_roles_to_users',2),(55,'2026_03_07_000023_create_medical_equipments_table',2),(56,'2026_03_07_000024_create_maintenance_orders_table',2),(57,'2026_03_07_000025_create_ambulances_table',2),(58,'2026_03_07_000026_create_emergency_transfers_table',2),(59,'2026_03_08_212518_add_allowed_modules_to_users_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operation_pharmacy_items`
--

DROP TABLE IF EXISTS `operation_pharmacy_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operation_pharmacy_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `operation_id` bigint unsigned NOT NULL,
  `pharmacy_item_id` bigint unsigned DEFAULT NULL,
  `requested_item_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity_required` int unsigned NOT NULL DEFAULT '1',
  `unit_measurement` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `picked_up` tinyint(1) NOT NULL DEFAULT '0',
  `picked_up_at` timestamp NULL DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `operation_pharmacy_items_pharmacy_item_id_foreign` (`pharmacy_item_id`),
  KEY `operation_pharmacy_items_operation_id_picked_up_index` (`operation_id`,`picked_up`),
  CONSTRAINT `operation_pharmacy_items_operation_id_foreign` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `operation_pharmacy_items_pharmacy_item_id_foreign` FOREIGN KEY (`pharmacy_item_id`) REFERENCES `pharmacy_items` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operation_pharmacy_items`
--

LOCK TABLES `operation_pharmacy_items` WRITE;
/*!40000 ALTER TABLE `operation_pharmacy_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `operation_pharmacy_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operation_rooms`
--

DROP TABLE IF EXISTS `operation_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operation_rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_order` int unsigned NOT NULL DEFAULT '1',
  `status` enum('active','maintenance','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `operation_rooms_code_unique` (`code`),
  KEY `operation_rooms_status_display_order_index` (`status`,`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operation_rooms`
--

LOCK TABLES `operation_rooms` WRITE;
/*!40000 ALTER TABLE `operation_rooms` DISABLE KEYS */;
/*!40000 ALTER TABLE `operation_rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `operations`
--

DROP TABLE IF EXISTS `operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `operation_room_id` bigint unsigned NOT NULL,
  `doctor_id` bigint unsigned NOT NULL,
  `patient_id` bigint unsigned DEFAULT NULL,
  `operation_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_start` datetime NOT NULL,
  `scheduled_end` datetime NOT NULL,
  `estimated_duration_minutes` int unsigned NOT NULL,
  `cleaning_margin_minutes` int unsigned NOT NULL DEFAULT '15',
  `urgency` enum('scheduled','urgent','emergency') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `status` enum('scheduled','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
  `clinical_notes` text COLLATE utf8mb4_unicode_ci,
  `pharmacy_notes` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `cancelled_by` bigint unsigned DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `operations_patient_id_foreign` (`patient_id`),
  KEY `operations_created_by_foreign` (`created_by`),
  KEY `operations_updated_by_foreign` (`updated_by`),
  KEY `operations_cancelled_by_foreign` (`cancelled_by`),
  KEY `operations_operation_room_id_scheduled_start_index` (`operation_room_id`,`scheduled_start`),
  KEY `operations_doctor_id_scheduled_start_index` (`doctor_id`,`scheduled_start`),
  KEY `operations_status_urgency_index` (`status`,`urgency`),
  CONSTRAINT `operations_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `operations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `operations_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `operations_operation_room_id_foreign` FOREIGN KEY (`operation_room_id`) REFERENCES `operation_rooms` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `operations_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `operations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `operations`
--

LOCK TABLES `operations` WRITE;
/*!40000 ALTER TABLE `operations` DISABLE KEYS */;
/*!40000 ALTER TABLE `operations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_accounts`
--

DROP TABLE IF EXISTS `patient_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_charged` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_paid` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_credits` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','suspended','blocked') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `payment_status` enum('current','overdue','in_arrears') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'current',
  `last_payment_date` date DEFAULT NULL,
  `days_overdue` int NOT NULL DEFAULT '0',
  `accrued_interest` decimal(12,2) NOT NULL DEFAULT '0.00',
  `interest_rate` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_accounts_patient_id_unique` (`patient_id`),
  KEY `patient_accounts_patient_id_index` (`patient_id`),
  KEY `patient_accounts_status_index` (`status`),
  KEY `patient_accounts_payment_status_index` (`payment_status`),
  CONSTRAINT `patient_accounts_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_accounts`
--

LOCK TABLES `patient_accounts` WRITE;
/*!40000 ALTER TABLE `patient_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `patient_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_insurance`
--

DROP TABLE IF EXISTS `patient_insurance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient_insurance` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `health_insurance_id` bigint unsigned NOT NULL,
  `member_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_insurance_health_insurance_id_foreign` (`health_insurance_id`),
  KEY `patient_insurance_patient_id_is_primary_index` (`patient_id`,`is_primary`),
  CONSTRAINT `patient_insurance_health_insurance_id_foreign` FOREIGN KEY (`health_insurance_id`) REFERENCES `health_insurances` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_insurance_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_insurance`
--

LOCK TABLES `patient_insurance` WRITE;
/*!40000 ALTER TABLE `patient_insurance` DISABLE KEYS */;
/*!40000 ALTER TABLE `patient_insurance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allergies` text COLLATE utf8mb4_unicode_ci COMMENT 'Alergias separadas por coma',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notas generales del paciente',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_dni_unique` (`dni`),
  UNIQUE KEY `patients_cuil_unique` (`cuil`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES (1,'Leora','Bechtelar','70441228',NULL,'1974-08-28',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(2,'Francis','Weissnat','67622886',NULL,'1998-12-08',NULL,'+1.512.908.7322','johnston.shanelle@example.org',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(3,'Sadie','Schmeler','45281895',NULL,'1976-06-18',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(4,'Duane','Thiel','99924046',NULL,'1974-11-26',NULL,NULL,'dayna.halvorson@example.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(5,'Bryce','Kuhlman','10202733',NULL,'1977-04-06',NULL,'(907) 215-8656','garett35@example.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(6,'Wendell','Carter','84452844',NULL,'1986-05-06',NULL,'+1-475-389-7360',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(7,'Owen','Leuschke','13202485',NULL,'1988-07-27',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(8,'Gage','O\'Conner','38964395',NULL,'1988-09-09',NULL,'(270) 948-8143',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(9,'Verla','Cummerata','92305884',NULL,'1990-07-25',NULL,NULL,'ora07@example.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(10,'Alize','Rau','13007881',NULL,'1975-05-04',NULL,'803-971-5911',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(11,'Micheal','Flatley','90828627',NULL,'2004-08-18',NULL,NULL,'metz.easter@example.net',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(12,'Nikita','Jenkins','74618592',NULL,'1980-10-10',NULL,'1-445-810-2287',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(13,'Benedict','Jast','88870268',NULL,'2004-10-16',NULL,'619.914.1024','ratke.berta@example.net',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(14,'Corene','Trantow','38676309',NULL,'1984-05-10',NULL,'1-936-265-3453',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(15,'Dewitt','Hintz','88808752',NULL,'1974-12-14',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','2026-03-08 00:46:41');
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','transfer','check','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `received_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_received_by_foreign` (`received_by`),
  KEY `payments_invoice_id_payment_date_index` (`invoice_id`,`payment_date`),
  CONSTRAINT `payments_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_items`
--

DROP TABLE IF EXISTS `pharmacy_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pharmacy_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('medication','instrument','supply') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `laboratory` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `current_stock` int NOT NULL DEFAULT '0',
  `minimum_stock` int NOT NULL DEFAULT '10',
  `reorder_point` int NOT NULL DEFAULT '20',
  `unit_measurement` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unidad',
  `expiration_date` date DEFAULT NULL,
  `batch_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requires_sterilization` tinyint(1) NOT NULL DEFAULT '0',
  `last_sterilization_date` date DEFAULT NULL,
  `next_sterilization_date` date DEFAULT NULL,
  `status` enum('active','inactive','discontinued') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pharmacy_items_code_unique` (`code`),
  KEY `pharmacy_items_type_index` (`type`),
  KEY `pharmacy_items_status_index` (`status`),
  KEY `pharmacy_items_expiration_date_index` (`expiration_date`),
  KEY `pharmacy_items_current_stock_index` (`current_stock`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_items`
--

LOCK TABLES `pharmacy_items` WRITE;
/*!40000 ALTER TABLE `pharmacy_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `pharmacy_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_request_items`
--

DROP TABLE IF EXISTS `pharmacy_request_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pharmacy_request_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pharmacy_request_id` bigint unsigned NOT NULL,
  `pharmacy_item_id` bigint unsigned NOT NULL,
  `quantity_requested` int NOT NULL,
  `quantity_delivered` int NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pharmacy_request_items_pharmacy_request_id_foreign` (`pharmacy_request_id`),
  KEY `pharmacy_request_items_pharmacy_item_id_foreign` (`pharmacy_item_id`),
  CONSTRAINT `pharmacy_request_items_pharmacy_item_id_foreign` FOREIGN KEY (`pharmacy_item_id`) REFERENCES `pharmacy_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pharmacy_request_items_pharmacy_request_id_foreign` FOREIGN KEY (`pharmacy_request_id`) REFERENCES `pharmacy_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_request_items`
--

LOCK TABLES `pharmacy_request_items` WRITE;
/*!40000 ALTER TABLE `pharmacy_request_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `pharmacy_request_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_requests`
--

DROP TABLE IF EXISTS `pharmacy_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pharmacy_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `requested_by` bigint unsigned NOT NULL,
  `patient_id` bigint unsigned DEFAULT NULL,
  `appointment_id` bigint unsigned DEFAULT NULL,
  `processed_by` bigint unsigned DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `status` enum('pending','processing','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `pharmacy_notes` text COLLATE utf8mb4_unicode_ci,
  `requested_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `processed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pharmacy_requests_requested_by_foreign` (`requested_by`),
  KEY `pharmacy_requests_patient_id_foreign` (`patient_id`),
  KEY `pharmacy_requests_appointment_id_foreign` (`appointment_id`),
  KEY `pharmacy_requests_processed_by_foreign` (`processed_by`),
  KEY `pharmacy_requests_status_index` (`status`),
  KEY `pharmacy_requests_priority_index` (`priority`),
  KEY `pharmacy_requests_requested_at_index` (`requested_at`),
  CONSTRAINT `pharmacy_requests_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pharmacy_requests_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pharmacy_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pharmacy_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_requests`
--

LOCK TABLES `pharmacy_requests` WRITE;
/*!40000 ALTER TABLE `pharmacy_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `pharmacy_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy_stock_movements`
--

DROP TABLE IF EXISTS `pharmacy_stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pharmacy_stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pharmacy_item_id` bigint unsigned NOT NULL,
  `movement_type` enum('entry','exit','adjustment','return','expired','damaged') COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `stock_before` int NOT NULL,
  `stock_after` int NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `pharmacy_request_id` bigint unsigned DEFAULT NULL,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pharmacy_stock_movements_pharmacy_item_id_foreign` (`pharmacy_item_id`),
  KEY `pharmacy_stock_movements_user_id_foreign` (`user_id`),
  KEY `pharmacy_stock_movements_pharmacy_request_id_foreign` (`pharmacy_request_id`),
  KEY `pharmacy_stock_movements_movement_type_index` (`movement_type`),
  KEY `pharmacy_stock_movements_created_at_index` (`created_at`),
  CONSTRAINT `pharmacy_stock_movements_pharmacy_item_id_foreign` FOREIGN KEY (`pharmacy_item_id`) REFERENCES `pharmacy_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pharmacy_stock_movements_pharmacy_request_id_foreign` FOREIGN KEY (`pharmacy_request_id`) REFERENCES `pharmacy_requests` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pharmacy_stock_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy_stock_movements`
--

LOCK TABLES `pharmacy_stock_movements` WRITE;
/*!40000 ALTER TABLE `pharmacy_stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `pharmacy_stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pre_admission_documents`
--

DROP TABLE IF EXISTS `pre_admission_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pre_admission_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pre_admission_id` bigint unsigned NOT NULL,
  `required_document_id` bigint unsigned NOT NULL,
  `status` enum('pending','uploaded','verified','rejected','not_applicable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta del archivo subido',
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint unsigned DEFAULT NULL COMMENT 'Tamaño en bytes',
  `uploaded_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verification_notes` text COLLATE utf8mb4_unicode_ci,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pad_pre_req_unique` (`pre_admission_id`,`required_document_id`),
  KEY `pre_admission_documents_required_document_id_foreign` (`required_document_id`),
  KEY `pre_admission_documents_pre_admission_id_index` (`pre_admission_id`),
  KEY `pre_admission_documents_status_index` (`status`),
  CONSTRAINT `pre_admission_documents_pre_admission_id_foreign` FOREIGN KEY (`pre_admission_id`) REFERENCES `pre_admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pre_admission_documents_required_document_id_foreign` FOREIGN KEY (`required_document_id`) REFERENCES `required_documents` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pre_admission_documents`
--

LOCK TABLES `pre_admission_documents` WRITE;
/*!40000 ALTER TABLE `pre_admission_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `pre_admission_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pre_admissions`
--

DROP TABLE IF EXISTS `pre_admissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pre_admissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `operation_id` bigint unsigned NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `secretary_id` bigint unsigned DEFAULT NULL,
  `status` enum('pending_assignment','data_pending','documents_pending','ready_for_surgery','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_assignment',
  `urgent_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de urgencia/historia',
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medical_history_verified` enum('yes','no','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `patient_observations` text COLLATE utf8mb4_unicode_ci,
  `data_verified_at` timestamp NULL DEFAULT NULL,
  `documentation_verified_at` timestamp NULL DEFAULT NULL,
  `ready_for_surgery_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pre_admissions_operation_id_index` (`operation_id`),
  KEY `pre_admissions_patient_id_index` (`patient_id`),
  KEY `pre_admissions_secretary_id_index` (`secretary_id`),
  KEY `pre_admissions_status_index` (`status`),
  CONSTRAINT `pre_admissions_operation_id_foreign` FOREIGN KEY (`operation_id`) REFERENCES `operations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pre_admissions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pre_admissions_secretary_id_foreign` FOREIGN KEY (`secretary_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pre_admissions`
--

LOCK TABLES `pre_admissions` WRITE;
/*!40000 ALTER TABLE `pre_admissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `pre_admissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prescriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cuir` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `paciente_cuil` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paciente_nombre_completo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paciente_fecha_nacimiento` date DEFAULT NULL,
  `obra_social` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_afiliado` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `doctor_id` bigint unsigned NOT NULL,
  `matricula_profesional` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricula_tipo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profesional_nombre_completo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profesional_especialidad` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consultorio_direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appointment_id` bigint unsigned DEFAULT NULL,
  `medical_record_id` bigint unsigned DEFAULT NULL,
  `medications` json NOT NULL,
  `medicamentos_genericos` json DEFAULT NULL,
  `instructions` json NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `firma_electronica_hash` text COLLATE utf8mb4_unicode_ci,
  `firma_metodo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_timestamp` timestamp NULL DEFAULT NULL,
  `firma_ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_data` text COLLATE utf8mb4_unicode_ci,
  `validado_refeps` tinyint(1) NOT NULL DEFAULT '0',
  `validado_renaper` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_validacion_externa` timestamp NULL DEFAULT NULL,
  `log_modificaciones` json DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `estado_dispensacion` enum('pendiente','dispensada','anulada','vencida') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `fecha_dispensacion` timestamp NULL DEFAULT NULL,
  `farmacia_dispensadora` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `fecha_emision` timestamp NULL DEFAULT NULL,
  `fecha_vencimiento` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `diagnosis` text COLLATE utf8mb4_unicode_ci COMMENT 'Diagnóstico médico',
  `cie10_codigo` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cie10_descripcion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prescriptions_cuir_unique` (`cuir`),
  KEY `prescriptions_patient_id_foreign` (`patient_id`),
  KEY `prescriptions_doctor_id_foreign` (`doctor_id`),
  KEY `prescriptions_appointment_id_foreign` (`appointment_id`),
  KEY `prescriptions_medical_record_id_foreign` (`medical_record_id`),
  CONSTRAINT `prescriptions_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_doctor_id_foreign` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_medical_record_id_foreign` FOREIGN KEY (`medical_record_id`) REFERENCES `medical_records` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescriptions`
--

LOCK TABLES `prescriptions` WRITE;
/*!40000 ALTER TABLE `prescriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `prescriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `required_documents`
--

DROP TABLE IF EXISTS `required_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `required_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ej: Cédula de Identidad, Consentimiento Informado',
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Código único: DNI, FORM_CONSENT, etc',
  `description` text COLLATE utf8mb4_unicode_ci,
  `applicability` enum('all_surgeries','by_operation_type','by_insurance','custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all_surgeries',
  `is_mandatory` tinyint(1) NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `requires_upload` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Si necesita archivo adjunto',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `required_documents_code_unique` (`code`),
  KEY `required_documents_code_index` (`code`),
  KEY `required_documents_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `required_documents`
--

LOCK TABLES `required_documents` WRITE;
/*!40000 ALTER TABLE `required_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `required_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_type` enum('standard','intensive_care','isolation','pediatric','psychiatric','recovery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `floor` int DEFAULT NULL,
  `wing` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `max_beds` int NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rooms_code_unique` (`code`),
  KEY `rooms_room_type_index` (`room_type`),
  KEY `rooms_floor_wing_index` (`floor`,`wing`),
  KEY `rooms_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,'Habitacion 201','H201','standard',2,'Norte',2,'Internacion general',1,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(2,'Habitacion 202','H202','standard',2,'Norte',2,'Internacion general',1,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(3,'UCI 301','UCI301','intensive_care',3,'Sur',1,'Unidad de cuidados intensivos',1,'2026-03-08 00:46:41','2026-03-08 00:46:41'),(4,'Aislamiento 401','A401','isolation',4,'Oeste',1,'Sala de aislamiento',1,'2026-03-08 00:46:41','2026-03-08 00:46:41');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuil` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validado_refeps` tinyint(1) NOT NULL DEFAULT '0',
  `fecha_validacion_refeps` timestamp NULL DEFAULT NULL,
  `firma_electronica_habilitada` tinyint(1) NOT NULL DEFAULT '0',
  `firma_electronica_metodo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firma_digital_certificado` text COLLATE utf8mb4_unicode_ci,
  `role` enum('admin','doctor','secretary','pharmacy','operating_room_manager','nurse','emergency','accountant','maintenance','paramedic') COLLATE utf8mb4_unicode_ci DEFAULT 'doctor',
  `allowed_modules` json DEFAULT NULL,
  `specialty` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricula_nacional` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricula_provincial` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provincia_matricula` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consultorio_direccion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consultorio_telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `license_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de matrícula/licencia profesional',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Teléfono del consultorio',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dirección del consultorio',
  `professional_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Número de colegio o afiliación profesional',
  `dni` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'DNI del doctor',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_dni_unique` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','admin@clinica.com',NULL,0,NULL,0,NULL,NULL,'admin',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','$2y$12$8xMgFrPIQJSFTxY5uqBJ7Oc0q61BRvbEdo7LZ0hqanpq6CrOKIBO2','GbYcv6jzuS','2026-03-08 00:46:41','2026-03-08 00:46:41',NULL,NULL,NULL,NULL,NULL),(2,'Dr. Juan García','juan.garcia@clinica.com',NULL,0,NULL,0,NULL,NULL,'doctor',NULL,'Cardiología',NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','$2y$12$8xMgFrPIQJSFTxY5uqBJ7Oc0q61BRvbEdo7LZ0hqanpq6CrOKIBO2','Be4m0Kcfut','2026-03-08 00:46:41','2026-03-08 00:46:41',NULL,NULL,NULL,NULL,NULL),(3,'Dra. María López','maria.lopez@clinica.com',NULL,0,NULL,0,NULL,NULL,'doctor',NULL,'Pediatría',NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','$2y$12$8xMgFrPIQJSFTxY5uqBJ7Oc0q61BRvbEdo7LZ0hqanpq6CrOKIBO2','EdLuzMWobz','2026-03-08 00:46:41','2026-03-08 00:46:41',NULL,NULL,NULL,NULL,NULL),(4,'Dr. Carlos Martínez','carlos.martinez@clinica.com',NULL,0,NULL,0,NULL,NULL,'doctor',NULL,'Neurología',NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','$2y$12$8xMgFrPIQJSFTxY5uqBJ7Oc0q61BRvbEdo7LZ0hqanpq6CrOKIBO2','6qR4A2Zx6v','2026-03-08 00:46:41','2026-03-08 00:46:41',NULL,NULL,NULL,NULL,NULL),(5,'Secretaria Principal','secretaria@clinica.com',NULL,0,NULL,0,NULL,NULL,'secretary',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-08 00:46:41','$2y$12$8xMgFrPIQJSFTxY5uqBJ7Oc0q61BRvbEdo7LZ0hqanpq6CrOKIBO2','2NEBPZNYlG','2026-03-08 00:46:41','2026-03-08 00:46:41',NULL,NULL,NULL,NULL,NULL),(6,'Enfermera Jefe','enfermeria@clinica.com',NULL,0,NULL,0,NULL,NULL,'nurse',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-10 10:42:03','$2y$12$d3q8a/PeTvMeQ1qYWJ7fg.M/CHSkU0p5QPp7Lor/r7by8rJYhVaRa',NULL,'2026-03-08 00:46:42','2026-03-10 10:42:03',NULL,'+54 381 555-9001',NULL,NULL,NULL),(7,'Enfermero Turno Noche','enfermeria.noche@clinica.com',NULL,0,NULL,0,NULL,NULL,'nurse',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-03-10 10:42:04','$2y$12$vrPkCFPdUscVkdezt4z05OnTJIrrpgAChps/Qcclm83aCUdxJthvO',NULL,'2026-03-08 00:46:42','2026-03-10 10:42:04',NULL,'+54 381 555-9002',NULL,NULL,NULL),(8,'Tecnico de Mantenimiento','mantenimiento@clinica.com',NULL,0,NULL,0,NULL,NULL,'maintenance',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$HXv2Fyjyq9r5pCmqp9hOEORAqcvJUXghm4IKrJSefI0nbxAIuZ6nq',NULL,'2026-03-08 01:29:27','2026-03-08 01:29:27',NULL,NULL,NULL,NULL,NULL),(9,'Paramedico de Guardia','paramedico@clinica.com',NULL,0,NULL,0,NULL,NULL,'paramedic',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$a8t5iUnuiPiOFasVCLYLYuSOPaZgyUvrA3e7rE07MSIykZ/./EIL.',NULL,'2026-03-08 01:29:28','2026-03-08 01:29:28',NULL,NULL,NULL,NULL,NULL),(10,'Administrador Sistema','admin@gestor.com',NULL,0,NULL,0,NULL,NULL,'admin',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$kYr3xntAZoY9DIgkfY7zce/8uZy0O6jvJ5.f26UQuqdqXhqQ8QTrW','78T2iVJLobK5gyft7X2xDZdfaKznUemIE8S3un7HYxKrPljHWM0OwLEXkbJP','2026-03-09 00:16:03','2026-03-10 10:40:46',NULL,NULL,NULL,NULL,'12345678'),(11,'Dr. Juan Pérez','doctor@gestor.com',NULL,0,NULL,0,NULL,NULL,'doctor',NULL,'Medicina General','MN123456','MP789012','Mendoza',NULL,NULL,NULL,'$2y$12$Qc3lPhEDi1WYPE5SAVVXruFMcLNvL1vvwJRlrkPzBZx8QxuaQImjy','v3EYiQ0X4JtOAo1iWnArO2KLzekZoLbnQ16RZATqo7aki5oVOjWQZON2AgPn','2026-03-09 00:16:04','2026-03-10 10:40:47',NULL,'261-7654321',NULL,NULL,'34567890'),(12,'María González','secretaria@gestor.com',NULL,0,NULL,0,NULL,NULL,'secretary',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$q1FUF1KjVKrypleTJ8Nk6.eP4pkMfDxExErBqyNGW95oyWko5E0Ni',NULL,'2026-03-09 00:16:04','2026-03-10 10:40:47',NULL,'261-4567890',NULL,NULL,'23456789'),(13,'Ana Martínez','farmacia@gestor.com',NULL,0,NULL,0,NULL,NULL,'pharmacy',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$aOdDdWchIiqZsPl4u/5gwedPC7CbFg3lUlTvvv6OrA9WQk6vmChki',NULL,'2026-03-09 00:16:04','2026-03-10 10:40:47',NULL,'261-5551234',NULL,NULL,'45678901'),(14,'Carlos Ruiz','quirofano@gestor.com',NULL,0,NULL,0,NULL,NULL,'operating_room_manager',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$Nvo6DGENIAicHAl9ExJcnuMiR7nUO/lwgqNJtdahkwCK4.VmeF07m',NULL,'2026-03-09 00:16:04','2026-03-10 10:40:47',NULL,'261-5552345',NULL,NULL,'56789012'),(15,'Laura Fernández','enfermera@gestor.com',NULL,0,NULL,0,NULL,NULL,'nurse',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$frsha5/Wz1dG3fydQmYIkeT96m7XkUo50VYnMjEMPeYJxovlXHUaq',NULL,'2026-03-09 00:16:05','2026-03-10 10:40:47',NULL,'261-5553456',NULL,NULL,'67890123'),(16,'Dr. Roberto Sánchez','emergencia@gestor.com',NULL,0,NULL,0,NULL,NULL,'emergency',NULL,'Emergencias',NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$KtA/9e.nc7kxFzxcbuQDw.jR.9OmMYJuYAMaFSk8icPwcFk.Y5/be',NULL,'2026-03-09 00:16:05','2026-03-10 10:40:47',NULL,'261-5554567',NULL,NULL,'78901234'),(17,'Patricia López','contador@gestor.com',NULL,0,NULL,0,NULL,NULL,'accountant',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$UsxBjtwwcth6NmUksUNEtOVYU/BW3CuSK/OVWwTvakYhW.Rjvmr0.',NULL,'2026-03-09 00:16:05','2026-03-10 10:40:48',NULL,'261-5555678',NULL,NULL,'89012345'),(18,'Miguel Torres','mantenimiento@gestor.com',NULL,0,NULL,0,NULL,NULL,'maintenance',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$CwTmrw2ZSwgqNC5paD.SOOYO04lIlhLhe7x73q9niY8y1oQmvyv.2',NULL,'2026-03-09 00:16:05','2026-03-10 10:40:48',NULL,'261-5556789',NULL,NULL,'90123456'),(19,'Jorge Ramírez','paramedico@gestor.com',NULL,0,NULL,0,NULL,NULL,'paramedic',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'$2y$12$GXZh1QBVK9zY74v.4T55Ku3c0gmV8AlyogR2e4ak9imcRKegxwAEK',NULL,'2026-03-09 00:16:05','2026-03-10 10:40:48',NULL,'261-5557890',NULL,NULL,'01234567');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'gestor_clinico'
--

--
-- Dumping routines for database 'gestor_clinico'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-10  7:42:30
