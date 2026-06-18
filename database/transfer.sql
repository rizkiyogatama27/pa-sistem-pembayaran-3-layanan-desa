-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sistem_pembayaran_desa_clean
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
-- Table structure for table `admin_activity_logs`
--

DROP TABLE IF EXISTS `admin_activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `module` varchar(60) NOT NULL,
  `action` varchar(80) NOT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_activity_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `admin_activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_activity_logs`
--

LOCK TABLES `admin_activity_logs` WRITE;
/*!40000 ALTER TABLE `admin_activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
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
-- Table structure for table `event_donasi_kontribusis`
--

DROP TABLE IF EXISTS `event_donasi_kontribusis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_donasi_kontribusis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `event_donasi_id` bigint(20) unsigned NOT NULL,
  `warga_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal_donasi` date NOT NULL,
  `nominal` bigint(20) NOT NULL,
  `metode` varchar(255) DEFAULT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'paid',
  `catatan` text DEFAULT NULL,
  `invoice` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_donasi_kontribusis_event_donasi_id_foreign` (`event_donasi_id`),
  KEY `event_donasi_kontribusis_warga_id_foreign` (`warga_id`),
  CONSTRAINT `event_donasi_kontribusis_event_donasi_id_foreign` FOREIGN KEY (`event_donasi_id`) REFERENCES `event_donasis` (`id`) ON DELETE CASCADE,
  CONSTRAINT `event_donasi_kontribusis_warga_id_foreign` FOREIGN KEY (`warga_id`) REFERENCES `wargas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_donasi_kontribusis`
--

LOCK TABLES `event_donasi_kontribusis` WRITE;
/*!40000 ALTER TABLE `event_donasi_kontribusis` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_donasi_kontribusis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_donasis`
--

DROP TABLE IF EXISTS `event_donasis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_donasis` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_event` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `tujuan` text NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `cover_image_url` varchar(255) DEFAULT NULL,
  `target_dana` bigint(20) NOT NULL DEFAULT 0,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('draft','aktif','selesai') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `event_donasis_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_donasis`
--

LOCK TABLES `event_donasis` WRITE;
/*!40000 ALTER TABLE `event_donasis` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_donasis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
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
-- Table structure for table `jenis_pembayarans`
--

DROP TABLE IF EXISTS `jenis_pembayarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_pembayarans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `nominal` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_pembayarans`
--

LOCK TABLES `jenis_pembayarans` WRITE;
/*!40000 ALTER TABLE `jenis_pembayarans` DISABLE KEYS */;
/*!40000 ALTER TABLE `jenis_pembayarans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
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
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
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
-- Table structure for table `keluargas`
--

DROP TABLE IF EXISTS `keluargas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keluargas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_kk` varchar(32) NOT NULL,
  `nama_keluarga` varchar(255) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keluargas_no_kk_unique` (`no_kk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keluargas`
--

LOCK TABLES `keluargas` WRITE;
/*!40000 ALTER TABLE `keluargas` DISABLE KEYS */;
/*!40000 ALTER TABLE `keluargas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_02_25_135932_create_wargas_table',1),(5,'2026_03_04_124402_create_jenis_pembayarans_table',1),(6,'2026_03_04_125800_create_pembayarans_table',1),(7,'2026_03_15_192311_add_invoice_to_pembayarans_table',1),(8,'2026_03_27_175249_add_role_to_users_table',1),(9,'2026_04_01_143606_add_role_to_users_table',1),(10,'2026_04_02_000000_add_status_to_pembayarans_table',1),(11,'2026_04_10_000000_add_water_billing_fields_to_pembayarans_table',1),(12,'2026_04_10_000002_add_no_hp_to_wargas_table',1),(13,'2026_04_10_000003_add_last_whatsapp_reminder_at_to_pembayarans_table',1),(14,'2026_04_10_000004_add_cash_payment_fields_to_pembayarans_table',1),(15,'2026_04_10_000005_add_google_fields_to_users_table',1),(16,'2026_04_11_000001_create_admin_activity_logs_table',1),(17,'2026_04_11_000002_create_whatsapp_reminder_logs_table',1),(18,'2026_04_11_000003_create_system_settings_table',1),(19,'2026_04_13_000000_add_password_set_at_to_users_table',1),(20,'2026_04_13_220000_add_unique_period_key_to_pembayarans_table',1),(21,'2026_04_14_000000_create_keluargas_table',1),(22,'2026_04_14_000100_add_keluarga_id_to_wargas_and_users_table',1),(23,'2026_04_14_010000_create_event_donasis_table',1),(24,'2026_04_14_010100_create_event_donasi_kontribusis_table',1),(25,'2026_04_14_020000_add_cover_image_url_to_event_donasis_table',1),(26,'2026_04_15_000001_add_status_verifikasi_to_users_table',1),(27,'2026_04_16_000001_add_deleted_at_to_pembayarans_table',1),(28,'2026_04_16_000001_add_nik_kk_to_users_table',1),(29,'2026_04_17_000001_add_status_to_kontribusis_table',2),(30,'2026_04_23_000001_add_cover_image_to_event_donasis_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
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
-- Table structure for table `pembayarans`
--

DROP TABLE IF EXISTS `pembayarans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pembayarans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `warga_id` bigint(20) unsigned NOT NULL,
  `jenis_pembayaran_id` bigint(20) unsigned NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `periode` varchar(255) DEFAULT NULL,
  `meter_awal` int(11) DEFAULT NULL,
  `meter_akhir` int(11) DEFAULT NULL,
  `pemakaian_air` int(11) DEFAULT NULL,
  `tarif_per_meter` int(11) NOT NULL DEFAULT 1500,
  `biaya_tetap` int(11) NOT NULL DEFAULT 5000,
  `denda` int(11) NOT NULL DEFAULT 0,
  `jatuh_tempo` date DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) DEFAULT NULL,
  `cash_received_amount` bigint(20) unsigned DEFAULT NULL,
  `cash_change_amount` bigint(20) unsigned DEFAULT NULL,
  `paid_by_user_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `invoice` varchar(255) DEFAULT NULL,
  `last_whatsapp_reminder_at` date DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pembayarans_warga_jenis_periode_unique` (`warga_id`,`jenis_pembayaran_id`,`periode`),
  KEY `pembayarans_jenis_pembayaran_id_foreign` (`jenis_pembayaran_id`),
  KEY `pembayarans_paid_by_user_id_foreign` (`paid_by_user_id`),
  CONSTRAINT `pembayarans_jenis_pembayaran_id_foreign` FOREIGN KEY (`jenis_pembayaran_id`) REFERENCES `jenis_pembayarans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pembayarans_paid_by_user_id_foreign` FOREIGN KEY (`paid_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pembayarans_warga_id_foreign` FOREIGN KEY (`warga_id`) REFERENCES `wargas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pembayarans`
--

LOCK TABLES `pembayarans` WRITE;
/*!40000 ALTER TABLE `pembayarans` DISABLE KEYS */;
/*!40000 ALTER TABLE `pembayarans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
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
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `system_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `google_avatar` varchar(255) DEFAULT NULL,
  `nik` varchar(16) DEFAULT NULL,
  `kk` varchar(16) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `status_verifikasi` varchar(255) NOT NULL DEFAULT 'pending',
  `remember_token` varchar(100) DEFAULT NULL,
  `password_set_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `warga_id` bigint(20) unsigned DEFAULT NULL,
  `keluarga_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  KEY `users_warga_id_foreign` (`warga_id`),
  KEY `users_keluarga_id_foreign` (`keluarga_id`),
  CONSTRAINT `users_keluarga_id_foreign` FOREIGN KEY (`keluarga_id`) REFERENCES `keluargas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_warga_id_foreign` FOREIGN KEY (`warga_id`) REFERENCES `wargas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Test User','test@example.com',NULL,NULL,NULL,NULL,'2026-05-11 09:38:16','$2y$12$o9kTOdZ4T05KfNHdvp7uX.uum5KiZTuwI.to/5vLQjWyKYjcrwIhe','pending','VBJKsZ7rgv',NULL,'2026-05-11 09:38:16','2026-05-11 09:38:16','user',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wargas`
--

DROP TABLE IF EXISTS `wargas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wargas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `keluarga_id` bigint(20) unsigned DEFAULT NULL,
  `nama` varchar(255) NOT NULL,
  `nik` varchar(255) NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `no_hp` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wargas_nik_unique` (`nik`),
  KEY `wargas_keluarga_id_foreign` (`keluarga_id`),
  CONSTRAINT `wargas_keluarga_id_foreign` FOREIGN KEY (`keluarga_id`) REFERENCES `keluargas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wargas`
--

LOCK TABLES `wargas` WRITE;
/*!40000 ALTER TABLE `wargas` DISABLE KEYS */;
/*!40000 ALTER TABLE `wargas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_reminder_logs`
--

DROP TABLE IF EXISTS `whatsapp_reminder_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_reminder_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pembayaran_id` bigint(20) unsigned DEFAULT NULL,
  `warga_id` bigint(20) unsigned DEFAULT NULL,
  `recipient` varchar(30) DEFAULT NULL,
  `status` enum('sent','failed','skipped') NOT NULL DEFAULT 'sent',
  `message` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_reminder_logs_pembayaran_id_foreign` (`pembayaran_id`),
  KEY `whatsapp_reminder_logs_warga_id_foreign` (`warga_id`),
  CONSTRAINT `whatsapp_reminder_logs_pembayaran_id_foreign` FOREIGN KEY (`pembayaran_id`) REFERENCES `pembayarans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `whatsapp_reminder_logs_warga_id_foreign` FOREIGN KEY (`warga_id`) REFERENCES `wargas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_reminder_logs`
--

LOCK TABLES `whatsapp_reminder_logs` WRITE;
/*!40000 ALTER TABLE `whatsapp_reminder_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_reminder_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-11 23:55:29
