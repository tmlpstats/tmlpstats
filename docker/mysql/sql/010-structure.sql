-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: tmlpstats_dev
-- ------------------------------------------------------
-- Server version	5.7.17

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
-- Table structure for table `accountabilities`
--

DROP TABLE IF EXISTS `accountabilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountabilities` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `context` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `accountabilities_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accountability_person`
--

DROP TABLE IF EXISTS `accountability_person`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accountability_person` (
  `person_id` int(10) unsigned NOT NULL,
  `accountability_id` int(10) unsigned NOT NULL,
  `starts_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `accountability_person_person_id_index` (`person_id`),
  KEY `accountability_person_accountability_id_index` (`accountability_id`),
  CONSTRAINT `accountability_person_accountability_id_foreign` FOREIGN KEY (`accountability_id`) REFERENCES `accountabilities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `accountability_person_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `center_stats`
--

DROP TABLE IF EXISTS `center_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `center_stats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reporting_date` date NOT NULL,
  `promise_data_id` int(10) unsigned DEFAULT NULL,
  `revoked_promise_data_id` int(10) unsigned DEFAULT NULL,
  `actual_data_id` int(10) unsigned DEFAULT NULL,
  `center_id` int(10) unsigned NOT NULL,
  `quarter_id` int(10) unsigned NOT NULL,
  `stats_report_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `center_stats_center_id_foreign` (`center_id`),
  KEY `center_stats_quarter_id_foreign` (`quarter_id`),
  KEY `center_stats_promise_data_id_foreign` (`promise_data_id`),
  KEY `center_stats_revoked_promise_data_id_foreign` (`revoked_promise_data_id`),
  KEY `center_stats_actual_data_id_foreign` (`actual_data_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7815 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `center_stats_data`
--

DROP TABLE IF EXISTS `center_stats_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `center_stats_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reporting_date` date NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tdo` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cap` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cpc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t1x` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `t2x` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gitw` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lf` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `program_manager_attending_weekend` int(10) unsigned NOT NULL DEFAULT '0',
  `classroom_leader_attending_weekend` int(10) unsigned NOT NULL DEFAULT '0',
  `stats_report_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18462 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `center_user`
--

DROP TABLE IF EXISTS `center_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `center_user` (
  `center_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `center_user_center_id_index` (`center_id`),
  KEY `center_user_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `centers`
--

DROP TABLE IF EXISTS `centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `centers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `abbreviation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `team_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `region_id` int(10) unsigned NOT NULL,
  `stats_email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sheet_filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sheet_version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `timezone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `centers_abbreviation_unique` (`abbreviation`),
  KEY `centers_region_id_foreign` (`region_id`),
  CONSTRAINT `centers_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `courses`
--

DROP TABLE IF EXISTS `courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start_date` date NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `center_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `courses_center_id_foreign` (`center_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1342 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `courses_data`
--

DROP TABLE IF EXISTS `courses_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `courses_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int(10) unsigned NOT NULL,
  `quarter_start_ter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quarter_start_standard_starts` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `quarter_start_xfer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `current_ter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `current_standard_starts` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `current_xfer` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `completed_standard_starts` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `potentials` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `registrations` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `guests_promised` int(11) DEFAULT NULL,
  `guests_invited` int(11) DEFAULT NULL,
  `guests_confirmed` int(11) DEFAULT NULL,
  `guests_attended` int(11) DEFAULT NULL,
  `stats_report_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `courses_data_course_id_foreign` (`course_id`),
  KEY `courses_data_stats_report_id_index` (`stats_report_id`),
  CONSTRAINT `courses_data_stats_report_id_foreign` FOREIGN KEY (`stats_report_id`) REFERENCES `stats_reports` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19242 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `global_report_stats_report`
--

DROP TABLE IF EXISTS `global_report_stats_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_report_stats_report` (
  `stats_report_id` int(10) unsigned NOT NULL,
  `global_report_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `global_report_stats_report_stats_report_id_index` (`stats_report_id`),
  KEY `global_report_stats_report_global_report_id_index` (`global_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `global_reports`
--

DROP TABLE IF EXISTS `global_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `global_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reporting_date` date NOT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `global_reports_user_id_foreign` (`user_id`),
  KEY `global_reports_reporting_date_index` (`reporting_date`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `invites`
--

DROP TABLE IF EXISTS `invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invites` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `role_id` int(10) unsigned NOT NULL,
  `center_id` int(10) unsigned NOT NULL,
  `invited_by_user_id` int(10) unsigned NOT NULL,
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `token` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `invites_role_id_foreign` (`role_id`),
  KEY `invites_center_id_foreign` (`center_id`),
  KEY `invites_invited_by_user_id_foreign` (`invited_by_user_id`),
  CONSTRAINT `invites_center_id_foreign` FOREIGN KEY (`center_id`) REFERENCES `centers` (`id`),
  CONSTRAINT `invites_invited_by_user_id_foreign` FOREIGN KEY (`invited_by_user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `invites_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `password_resets_email_index` (`email`),
  KEY `password_resets_token_index` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `center_id` int(10) unsigned DEFAULT NULL,
  `identifier` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `unsubscribed` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `people_center_id_foreign` (`center_id`),
  CONSTRAINT `people_center_id_foreign` FOREIGN KEY (`center_id`) REFERENCES `centers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6993 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `program_team_members`
--

DROP TABLE IF EXISTS `program_team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `program_team_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_member_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `offset` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `accountability` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `center_id` int(10) unsigned NOT NULL,
  `quarter_id` int(10) unsigned NOT NULL,
  `stats_report_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `program_team_members_center_id_foreign` (`center_id`),
  KEY `program_team_members_quarter_id_foreign` (`quarter_id`),
  KEY `program_team_members_team_member_id_foreign` (`team_member_id`),
  KEY `program_team_members_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3660 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `quarters`
--

DROP TABLE IF EXISTS `quarters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quarters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `t1_distinction` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `t2_distinction` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quarter_number` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `quarters_quarter_number_year_unique` (`quarter_number`,`year`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `region_quarter_details`
--

DROP TABLE IF EXISTS `region_quarter_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `region_quarter_details` (
  `quarter_id` int(10) unsigned NOT NULL,
  `region_id` int(10) unsigned NOT NULL,
  `location` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `start_weekend_date` date DEFAULT NULL,
  `end_weekend_date` date DEFAULT NULL,
  `classroom1_date` date DEFAULT NULL,
  `classroom2_date` date DEFAULT NULL,
  `classroom3_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `region_quarter_start_weekend_date_unique` (`quarter_id`,`region_id`,`start_weekend_date`),
  KEY `region_quarter_details_quarter_id_index` (`quarter_id`),
  KEY `region_quarter_details_region_id_index` (`region_id`),
  CONSTRAINT `region_quarter_details_quarter_id_foreign` FOREIGN KEY (`quarter_id`) REFERENCES `quarters` (`id`),
  CONSTRAINT `region_quarter_details_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `abbreviation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `regions_abbreviation_unique` (`abbreviation`),
  KEY `regions_parent_id_foreign` (`parent_id`),
  CONSTRAINT `regions_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `regions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `report_tokens`
--

DROP TABLE IF EXISTS `report_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `report_id` int(10) unsigned NOT NULL,
  `report_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(10) unsigned DEFAULT NULL,
  `owner_type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `report_tokens_center_id_foreign` (`owner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3099 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_user` (
  `role_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  KEY `role_user_role_id_index` (`role_id`),
  KEY `role_user_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL,
  UNIQUE KEY `sessions_id_unique` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `center_id` int(10) unsigned DEFAULT NULL,
  `quarter_id` int(10) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(8192) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `settings_center_id_foreign` (`center_id`),
  KEY `settings_quarter_id_foreign` (`quarter_id`),
  CONSTRAINT `settings_center_id_foreign` FOREIGN KEY (`center_id`) REFERENCES `centers` (`id`),
  CONSTRAINT `settings_quarter_id_foreign` FOREIGN KEY (`quarter_id`) REFERENCES `quarters` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=388 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `stats_reports`
--

DROP TABLE IF EXISTS `stats_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stats_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reporting_date` date NOT NULL,
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `validated` tinyint(1) NOT NULL DEFAULT '0',
  `reporting_statistician_id` int(10) unsigned DEFAULT NULL,
  `center_id` int(10) unsigned NOT NULL,
  `quarter_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `locked` int(11) NOT NULL DEFAULT '0',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `submit_comment` varchar(8096) COLLATE utf8_unicode_ci DEFAULT NULL,
  `validation_messages` text COLLATE utf8_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `stats_reports_center_id_foreign` (`center_id`),
  KEY `stats_reports_quarter_id_foreign` (`quarter_id`),
  KEY `stats_reports_user_id_foreign` (`user_id`),
  KEY `stats_reports_reporting_date_index` (`reporting_date`)
) ENGINE=InnoDB AUTO_INCREMENT=8762 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `submission_data`
--

DROP TABLE IF EXISTS `submission_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submission_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `center_id` int(10) unsigned NOT NULL,
  `reporting_date` date NOT NULL,
  `stored_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `stored_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_faux_primary` (`center_id`,`reporting_date`,`stored_type`,`stored_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1023 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `submission_data_log`
--

DROP TABLE IF EXISTS `submission_data_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submission_data_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `center_id` int(10) unsigned NOT NULL,
  `reporting_date` date NOT NULL,
  `stored_type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `stored_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_center_date_type_id` (`center_id`,`reporting_date`,`stored_type`,`stored_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5233 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int(10) unsigned NOT NULL,
  `team_year` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `incoming_quarter_id` int(10) unsigned NOT NULL,
  `is_reviewer` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `team_members_completion_quarter_id_foreign` (`incoming_quarter_id`),
  KEY `team_members_person_id_index` (`person_id`),
  CONSTRAINT `team_members_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3802 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team_members_data`
--

DROP TABLE IF EXISTS `team_members_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_members_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_member_id` int(10) unsigned NOT NULL,
  `at_weekend` tinyint(1) NOT NULL DEFAULT '1',
  `xfer_out` tinyint(1) NOT NULL DEFAULT '0',
  `xfer_in` tinyint(1) NOT NULL DEFAULT '0',
  `ctw` tinyint(1) NOT NULL DEFAULT '0',
  `withdraw_code_id` int(10) unsigned DEFAULT NULL,
  `rereg` tinyint(1) NOT NULL DEFAULT '0',
  `excep` tinyint(1) NOT NULL DEFAULT '0',
  `travel` tinyint(1) NOT NULL DEFAULT '0',
  `room` tinyint(1) NOT NULL DEFAULT '0',
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gitw` tinyint(1) NOT NULL DEFAULT '0',
  `tdo` int(11) NOT NULL DEFAULT '0',
  `stats_report_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `team_members_data_tmp_withdraw_code_id_foreign` (`withdraw_code_id`),
  KEY `team_members_data_tmp_team_member_id_index` (`team_member_id`),
  KEY `team_members_data_tmp_stats_report_id_index` (`stats_report_id`),
  CONSTRAINT `team_members_data_tmp_stats_report_id_foreign` FOREIGN KEY (`stats_report_id`) REFERENCES `stats_reports` (`id`),
  CONSTRAINT `team_members_data_tmp_team_member_id_foreign` FOREIGN KEY (`team_member_id`) REFERENCES `team_members` (`id`),
  CONSTRAINT `team_members_data_tmp_withdraw_code_id_foreign` FOREIGN KEY (`withdraw_code_id`) REFERENCES `withdraw_codes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=141961 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_scoreboard`
--

DROP TABLE IF EXISTS `temp_scoreboard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_scoreboard` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `center_id` int(10) unsigned NOT NULL,
  `routing_key` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expires_at` datetime DEFAULT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `temp_scoreboard_center_id_routing_key_unique` (`center_id`,`routing_key`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `temp_scoreboard_log`
--

DROP TABLE IF EXISTS `temp_scoreboard_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `temp_scoreboard_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `center_id` int(10) unsigned NOT NULL,
  `quarter_id` int(10) unsigned DEFAULT NULL,
  `stats_report_id` int(10) unsigned DEFAULT NULL,
  `game` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `value` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `temp_scoreboard_log_center_id_quarter_id_index` (`center_id`,`quarter_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2521 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tmlp_games`
--

DROP TABLE IF EXISTS `tmlp_games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmlp_games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `center_id` int(10) unsigned NOT NULL,
  `stats_report_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `tmlp_games_center_id_foreign` (`center_id`)
) ENGINE=InnoDB AUTO_INCREMENT=653 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tmlp_games_data`
--

DROP TABLE IF EXISTS `tmlp_games_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmlp_games_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `quarter_start_registered` int(11) DEFAULT NULL,
  `quarter_start_approved` int(11) DEFAULT NULL,
  `stats_report_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `tmlp_games_data_stats_report_id_index` (`stats_report_id`),
  CONSTRAINT `tmlp_games_data_stats_report_id_foreign` FOREIGN KEY (`stats_report_id`) REFERENCES `stats_reports` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2737 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tmlp_registrations`
--

DROP TABLE IF EXISTS `tmlp_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmlp_registrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `person_id` int(10) unsigned NOT NULL,
  `team_year` int(11) NOT NULL,
  `reg_date` date DEFAULT NULL,
  `is_reviewer` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `tmlp_registrations_person_id_foreign` (`person_id`),
  CONSTRAINT `tmlp_registrations_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4416 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tmlp_registrations_data`
--

DROP TABLE IF EXISTS `tmlp_registrations_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmlp_registrations_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tmlp_registration_id` int(10) unsigned NOT NULL,
  `reg_date` date DEFAULT NULL,
  `app_out_date` date DEFAULT NULL,
  `app_in_date` date DEFAULT NULL,
  `appr_date` date DEFAULT NULL,
  `wd_date` date DEFAULT NULL,
  `withdraw_code_id` int(10) unsigned DEFAULT NULL,
  `committed_team_member_id` int(10) unsigned DEFAULT NULL,
  `incoming_quarter_id` int(10) unsigned DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `travel` tinyint(1) NOT NULL DEFAULT '0',
  `room` tinyint(1) NOT NULL DEFAULT '0',
  `stats_report_id` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `tmlp_registrations_data_tmlp_registration_id_foreign` (`tmlp_registration_id`),
  KEY `tmlp_registrations_data_stats_report_id_index` (`stats_report_id`),
  KEY `tmlp_registrations_data_withdraw_code_id_foreign` (`withdraw_code_id`),
  KEY `tmlp_registrations_data_committed_team_member_id_foreign` (`committed_team_member_id`),
  CONSTRAINT `tmlp_registrations_data_committed_team_member_id_foreign` FOREIGN KEY (`committed_team_member_id`) REFERENCES `team_members` (`id`),
  CONSTRAINT `tmlp_registrations_data_stats_report_id_foreign` FOREIGN KEY (`stats_report_id`) REFERENCES `stats_reports` (`id`),
  CONSTRAINT `tmlp_registrations_data_withdraw_code_id_foreign` FOREIGN KEY (`withdraw_code_id`) REFERENCES `withdraw_codes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=67546 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `require_password_reset` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `managed` tinyint(1) NOT NULL DEFAULT '0',
  `person_id` int(10) unsigned DEFAULT NULL,
  `role_id` int(10) unsigned DEFAULT NULL,
  `last_login_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `remember_token` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_person_id_index` (`person_id`),
  CONSTRAINT `users_person_id_foreign` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `withdraw_codes`
--

DROP TABLE IF EXISTS `withdraw_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `withdraw_codes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `withdraw_codes_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-03-11  7:49:17
