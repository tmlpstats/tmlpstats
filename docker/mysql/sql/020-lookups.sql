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
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES ('2014_10_12_000000_create_users_table',1),('2014_10_12_100000_create_password_resets_table',1),('2015_02_07_031314_create_quarters_table',1),('2015_02_07_032226_create_centers_table',1),('2015_02_10_062608_create_roles_table',1),('2015_02_11_073126_create_role_user_table',1),('2015_02_12_053616_create_accountabilities_table',1),('2015_02_13_032016_create_center_stats_data_table',1),('2015_02_13_032017_create_center_stats_table',1),('2015_02_14_030420_create_team_members_table',1),('2015_02_14_030631_create_team_members_data_table',1),('2015_02_14_045018_create_courses_table',1),('2015_02_14_045031_create_courses_data_table',1),('2015_02_14_045056_create_tmlp_games_table',1),('2015_02_14_045107_create_tmlp_games_data_table',1),('2015_02_14_053314_create_program_team_members_table',1),('2015_02_14_053407_create_tmlp_registrations_table',1),('2015_02_14_053421_create_tmlp_registrations_data_table',1),('2015_02_15_030626_create_statsreports_table',1),('2015_02_28_062637_add_user_id_to_stats_report_table',2),('2015_04_18_062637_add_time_zone_to_centers_table',3),('2015_05_02_051413_add_region_to_quarters_table',4),('2015_05_23_073126_create_center_user_table',4),('2015_06_29_034336_add_locked_to_stats_reports_table',5),('2015_06_29_035805_create_global_reports_table',5),('2015_06_29_035819_create_global_report_stats_report_table',5),('2015_07_14_060743_add_submitted_at_to_stats_reports_table',6),('2015_08_24_224743_add_submit_comment_to_stats_reports_table',7),('2015_08_30_000000_convert_roles_table',8),('2015_08_30_000010_convert_accountabilities_table',8),('2015_08_30_000015_create_withdraw_codes_table',8),('2015_08_30_000020_create_regions_table',8),('2015_08_30_000030_convert_centers_table',8),('2015_08_30_000040_create_people_table',8),('2015_08_30_000045_convert_users_table',8),('2015_08_30_000060_create_region_quarter_details_table',8),('2015_08_30_000065_convert_quarters_table',8),('2015_08_30_000070_create_accountability_person_table',8),('2015_08_30_000080_convert_stats_reports_table',8),('2015_08_30_000120_convert_global_reports_table',8),('2015_08_30_000140_convert_center_stats_data_table',8),('2015_08_30_000150_convert_courses_table',8),('2015_08_30_000160_convert_courses_data_table',8),('2015_08_30_000170_convert_team_members_table',8),('2015_08_30_000180_convert_team_members_data_table',8),('2015_08_30_000200_convert_tmlp_games_data_table',8),('2015_08_30_000210_convert_tmlp_registrations_table',8),('2015_08_30_000220_convert_tmlp_registrations_data_table',8),('2015_08_30_000300_convert_program_team_members_table',8),('2015_08_30_000400_cleanup',8),('2015_10_18_224047_create_settings_table',9),('2015_10_23_042041_add_location_to_courses_table',10),('2015_11_07_053637_create_report_tokens_table',11),('2015_11_08_043510_create_sessions_table',12),('2015_11_21_311070_create_tracker_paths_table',13),('2015_11_21_311071_create_tracker_queries_table',13),('2015_11_21_311072_create_tracker_queries_arguments_table',13),('2015_11_21_311073_create_tracker_routes_table',13),('2015_11_21_311074_create_tracker_routes_paths_table',13),('2015_11_21_311075_create_tracker_route_path_parameters_table',13),('2015_11_21_311076_create_tracker_agents_table',13),('2015_11_21_311077_create_tracker_cookies_table',13),('2015_11_21_311078_create_tracker_devices_table',13),('2015_11_21_311079_create_tracker_domains_table',13),('2015_11_21_311080_create_tracker_referers_table',13),('2015_11_21_311081_create_tracker_geoip_table',13),('2015_11_21_311082_create_tracker_sessions_table',13),('2015_11_21_311083_create_tracker_errors_table',13),('2015_11_21_311084_create_tracker_system_classes_table',13),('2015_11_21_311085_create_tracker_log_table',13),('2015_11_21_311086_create_tracker_events_table',13),('2015_11_21_311087_create_tracker_events_log_table',13),('2015_11_21_311088_create_tracker_sql_queries_table',13),('2015_11_21_311089_create_tracker_sql_query_bindings_table',13),('2015_11_21_311090_create_tracker_sql_query_bindings_parameters_table',13),('2015_11_21_311091_create_tracker_sql_queries_log_table',13),('2015_11_21_311092_create_tracker_connections_table',13),('2015_11_21_311093_create_tracker_tables_relations',13),('2015_11_21_311094_create_tracker_referer_search_term_table',13),('2015_11_21_311095_add_tracker_referer_columns',13),('2015_11_22_232205_add_starts_ends_to_accountability_person_table',14),('2015_11_28_214352_add_guest_game_to_courses_data_table',15),('2015_12_09_061721_remove_accountability_id_from_team_members_data_table',16),('2015_12_22_053158_add_managed_to_users_table',17),('2015_12_22_053220_add_unsubscribed_to_people_table',17),('2016_01_13_060549_create_invites_table',18),('2016_02_02_092207_add_quarter_to_settings_table',19),('2016_03_24_003938_create_temp_scoreboard_table',20),('2016_05_30_092207_add_region_to_reporttokens_table',21),('2016_06_03_023509_create_submission_data_table',22),('2017_03_02_014933_add_validation_messages_to_stats_reports_table',23);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `accountabilities`
--

LOCK TABLES `accountabilities` WRITE;
/*!40000 ALTER TABLE `accountabilities` DISABLE KEYS */;
INSERT INTO `accountabilities` VALUES (1,'regionalStatistician','program','Regional Statistician','2015-02-27 16:41:37','2015-10-14 03:56:40'),(2,'globalStatistician','program','Global Statistician','2015-02-27 16:41:37','2015-10-14 03:56:40'),(3,'globalLeader','program','Global Leader','2015-02-27 16:41:37','2015-10-14 03:56:40'),(4,'statistician','team','Statistician','2015-02-27 16:41:37','2015-11-27 08:13:19'),(5,'statisticianApprentice','team','Statistician Apprentice','2015-02-27 16:41:37','2015-11-27 08:13:19'),(6,'t1tl','team','Team 1 Team Leader','2015-02-27 16:41:37','2015-11-27 08:13:19'),(7,'t2tl','team','Team 2 Team Leader','2015-02-27 16:41:37','2015-11-27 08:13:19'),(8,'programManager','team','Program Manager','2015-02-27 16:41:37','2015-10-14 03:56:40'),(9,'classroomLeader','team','Classroom Leader','2015-02-27 16:41:37','2015-10-14 03:56:40'),(11,'cap','team','Access to Power','2015-11-22 23:52:06','2015-11-22 23:52:06'),(12,'cpc','team','Power to Create','2015-11-22 23:52:06','2015-11-22 23:52:06'),(13,'t1x','team','T1 Expansion','2015-11-22 23:52:41','2015-11-22 23:52:41'),(14,'t2x','team','T2 Expansion','2015-11-22 23:52:41','2015-11-22 23:52:41'),(15,'gitw','team','Game in the World','2015-11-22 23:52:41','2015-11-22 23:52:41'),(16,'lf','team','Landmark Forum','2015-11-22 23:52:41','2015-11-22 23:52:41'),(17,'logistics','team','Logistics','2015-11-22 23:57:43','2015-11-22 23:57:43');
/*!40000 ALTER TABLE `accountabilities` ENABLE KEYS */;
UNLOCK TABLES;

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

--
-- Dumping data for table `withdraw_codes`
--

LOCK TABLES `withdraw_codes` WRITE;
/*!40000 ALTER TABLE `withdraw_codes` DISABLE KEYS */;
INSERT INTO `withdraw_codes` VALUES (1,'AP','Chose another program',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40'),(2,'NW','Doesn\'t want the training',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40'),(3,'FIN','Financial',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40'),(4,'FW','Moved to a future weekend',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40'),(5,'MOA','Moved out of area',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40'),(6,'NA','Not approved',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40'),(7,'OOC','Out of communication',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40'),(8,'T','Time conversation',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40'),(9,'RE','Registration error',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40'),(10,'WB','Well-being',NULL,'2015-10-14 03:56:40','2015-10-14 03:56:40');
/*!40000 ALTER TABLE `withdraw_codes` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-03-11  7:49:17
