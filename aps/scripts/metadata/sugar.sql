-- MySQL dump 10.13  Distrib 5.1.44, for apple-darwin8.11.1 (i386)
--
-- Host: localhost    Database: sugarcrm
-- ------------------------------------------------------
-- Server version	5.1.44

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
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts` (
  `id` char(36) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `account_type` varchar(50) DEFAULT NULL,
  `industry` varchar(50) DEFAULT NULL,
  `annual_revenue` varchar(25) DEFAULT NULL,
  `phone_fax` varchar(25) DEFAULT NULL,
  `billing_address_street` varchar(150) DEFAULT NULL,
  `billing_address_city` varchar(100) DEFAULT NULL,
  `billing_address_state` varchar(100) DEFAULT NULL,
  `billing_address_postalcode` varchar(20) DEFAULT NULL,
  `billing_address_country` varchar(255) DEFAULT NULL,
  `rating` varchar(25) DEFAULT NULL,
  `phone_office` varchar(25) DEFAULT NULL,
  `phone_alternate` varchar(25) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `ownership` varchar(100) DEFAULT NULL,
  `employees` varchar(10) DEFAULT NULL,
  `ticker_symbol` varchar(10) DEFAULT NULL,
  `shipping_address_street` varchar(150) DEFAULT NULL,
  `shipping_address_city` varchar(100) DEFAULT NULL,
  `shipping_address_state` varchar(100) DEFAULT NULL,
  `shipping_address_postalcode` varchar(20) DEFAULT NULL,
  `shipping_address_country` varchar(255) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `sic_code` varchar(10) DEFAULT NULL,
  `campaign_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_accounts_tmst_id` (`team_set_id`),
  KEY `idx_accnt_id_del` (`id`,`deleted`),
  KEY `idx_accnt_name_del` (`name`,`deleted`),
  KEY `idx_accnt_assigned_del` (`deleted`,`assigned_user_id`),
  KEY `idx_accnt_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts_audit`
--

DROP TABLE IF EXISTS `accounts_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_accounts_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_audit`
--

LOCK TABLES `accounts_audit` WRITE;
/*!40000 ALTER TABLE `accounts_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts_bugs`
--

DROP TABLE IF EXISTS `accounts_bugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts_bugs` (
  `id` varchar(36) NOT NULL,
  `account_id` varchar(36) DEFAULT NULL,
  `bug_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_acc_bug_acc` (`account_id`),
  KEY `idx_acc_bug_bug` (`bug_id`),
  KEY `idx_account_bug` (`account_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_bugs`
--

LOCK TABLES `accounts_bugs` WRITE;
/*!40000 ALTER TABLE `accounts_bugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_bugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts_cases`
--

DROP TABLE IF EXISTS `accounts_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts_cases` (
  `id` varchar(36) NOT NULL,
  `account_id` varchar(36) DEFAULT NULL,
  `case_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_acc_case_acc` (`account_id`),
  KEY `idx_acc_acc_case` (`case_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_cases`
--

LOCK TABLES `accounts_cases` WRITE;
/*!40000 ALTER TABLE `accounts_cases` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_cases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts_contacts`
--

DROP TABLE IF EXISTS `accounts_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts_contacts` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `account_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_account_contact` (`account_id`,`contact_id`),
  KEY `idx_contid_del_accid` (`contact_id`,`deleted`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_contacts`
--

LOCK TABLES `accounts_contacts` WRITE;
/*!40000 ALTER TABLE `accounts_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `accounts_opportunities`
--

DROP TABLE IF EXISTS `accounts_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounts_opportunities` (
  `id` varchar(36) NOT NULL,
  `opportunity_id` varchar(36) DEFAULT NULL,
  `account_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_account_opportunity` (`account_id`,`opportunity_id`),
  KEY `idx_oppid_del_accid` (`opportunity_id`,`deleted`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts_opportunities`
--

LOCK TABLES `accounts_opportunities` WRITE;
/*!40000 ALTER TABLE `accounts_opportunities` DISABLE KEYS */;
/*!40000 ALTER TABLE `accounts_opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_actions`
--

DROP TABLE IF EXISTS `acl_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_actions` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `acltype` varchar(100) DEFAULT NULL,
  `aclaccess` int(3) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_aclaction_id_del` (`id`,`deleted`),
  KEY `idx_category_name` (`category`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_actions`
--

LOCK TABLES `acl_actions` WRITE;
/*!40000 ALTER TABLE `acl_actions` DISABLE KEYS */;
INSERT INTO `acl_actions` VALUES ('99216c29-21b2-cb42-9aa4-4c218dafacb7','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'admin','Leads','module',1,0),('9b2b9dcc-d6a5-d2af-3e03-4c218dbe059f','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'access','Leads','module',89,0),('9bc9f865-5e83-0ee2-c68a-4c218d8d1c22','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'view','Leads','module',90,0),('9c2d5109-4a7d-5ea8-bb2c-4c218d1d5442','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'list','Leads','module',90,0),('9c88eca2-e28b-6f4c-fde3-4c218d9c8b7f','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'edit','Leads','module',90,0),('9d08541f-7a91-8dd8-51a8-4c218dc36beb','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'delete','Leads','module',90,0),('9d528a8b-5b2d-18d8-7a89-4c218de9f188','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'import','Leads','module',90,0),('9d93fcd6-0b8e-ebfb-3f45-4c218d8d5c83','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'export','Leads','module',90,0),('d87178a2-844b-eee2-3df3-4c218d2f69b0','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'admin','Cases','module',1,0),('d8e3bcb6-3159-ff38-1edc-4c218de22db2','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'access','Cases','module',89,0),('d94fc604-2537-7083-9fe2-4c218dbd8130','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'view','Cases','module',90,0),('d9bccad4-c7bc-7568-f24b-4c218d567b90','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'list','Cases','module',90,0),('da739ff7-aa02-7ac1-ed5e-4c218d3fd3d0','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'edit','Cases','module',90,0),('daec3cfa-bab5-2b1b-3183-4c218dcb84c4','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'delete','Cases','module',90,0),('db4dadfe-a78f-f1be-9035-4c218de197c0','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'import','Cases','module',90,0),('dba186a4-d81d-a977-938d-4c218db6d642','2010-06-23 04:30:10','2010-06-23 04:30:10','1',NULL,'export','Cases','module',90,0),('238c1ff7-d57b-cb7a-4d79-4c218d544ec0','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'admin','Bugs','module',1,0),('2401a6dc-8fe8-8797-2772-4c218d8052ec','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'access','Bugs','module',89,0),('246d6523-42c0-34de-76bb-4c218d960240','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'view','Bugs','module',90,0),('24d91eee-eb6b-41a3-f2bd-4c218dd85c02','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'list','Bugs','module',90,0),('259470c0-e25f-625f-31f7-4c218d6537df','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'edit','Bugs','module',90,0),('260888ab-9863-fe76-ad4f-4c218de6202b','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'delete','Bugs','module',90,0),('266246f7-44fa-86ee-8f57-4c218dd68aed','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'import','Bugs','module',90,0),('26a75b96-6cfe-ce89-feea-4c218ddc1985','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'export','Bugs','module',90,0),('62552269-0272-c080-9ba3-4c218dce4f5b','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'admin','Users','module',1,0),('62ccf893-2e75-fb06-a4d2-4c218d165b91','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'access','Users','module',89,0),('633be956-883c-e76c-5a59-4c218d0cb41b','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'view','Users','module',90,0),('63a7f7ac-9e9b-c5d5-d5eb-4c218dc42165','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'list','Users','module',90,0),('641c625f-55c4-a809-428e-4c218d2ceb34','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'edit','Users','module',90,0),('64de476d-0e9a-ad9f-b9f1-4c218d5e5dca','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'delete','Users','module',90,0),('653de411-841f-ed24-0daa-4c218d7d95bf','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'import','Users','module',90,0),('65945ee1-181e-bf21-a74e-4c218d465fd2','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'export','Users','module',90,0),('a4676240-994a-3f4a-77f8-4c218df80a8c','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'admin','Project','module',1,0),('a4dbe544-d650-c7b5-789f-4c218d35c7b9','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'access','Project','module',89,0),('a549154f-4926-94ac-8d17-4c218ded6860','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'view','Project','module',90,0),('a5be9078-49c5-ada9-2c07-4c218d4ce11d','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'list','Project','module',90,0),('a6a13088-fb8b-fea1-f6e4-4c218d0a4826','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'edit','Project','module',90,0),('a70ff078-0be0-37d5-6340-4c218d1c701a','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'delete','Project','module',90,0),('a76b4099-b4f5-0650-28b4-4c218d2ba72f','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'import','Project','module',90,0),('a7ba40c0-2ad3-8c60-ab0b-4c218d4b8edf','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'export','Project','module',90,0),('c5c5efed-1245-692b-c85b-4c218ddaec60','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'admin','ProjectTask','module',1,0),('c63cb55e-e9e9-9a37-df9a-4c218ddcc461','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'access','ProjectTask','module',89,0),('c6ae3472-bea6-9f2d-c48f-4c218dd2d4e6','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'view','ProjectTask','module',90,0),('c71db82a-d14c-f13a-194a-4c218de002fe','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'list','ProjectTask','module',90,0),('c7f7de3f-a92a-2cc2-435f-4c218de655a8','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'edit','ProjectTask','module',90,0),('c86c0c69-99a8-3120-3b5e-4c218de12a61','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'delete','ProjectTask','module',90,0),('c8c37d14-94a7-c015-180f-4c218d51ff5e','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'import','ProjectTask','module',90,0),('c92919c2-9068-ec27-e83d-4c218d33c8b1','2010-06-23 04:30:11','2010-06-23 04:30:11','1',NULL,'export','ProjectTask','module',90,0),('b1c708ec-5a1c-8172-0dd6-4c218d6a86ee','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'admin','Campaigns','module',1,0),('b6f80063-45e4-5f94-fc4c-4c218d0c199d','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'access','Campaigns','module',89,0),('bbaa0eda-1260-e9c6-fb49-4c218df4fe63','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'view','Campaigns','module',90,0),('c75008f5-2efb-d350-9643-4c218d6e817b','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'list','Campaigns','module',90,0),('cc040454-f847-1d94-4494-4c218dcc212e','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'edit','Campaigns','module',90,0),('d2d30903-8bb0-9450-a93c-4c218d55f2a4','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'delete','Campaigns','module',90,0),('d8af0f82-448b-5aa7-e0ed-4c218d681076','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'import','Campaigns','module',90,0),('dd570584-ec11-5166-0aa3-4c218dca0d10','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'export','Campaigns','module',90,0),('4a8875f8-43a3-1d82-d77c-4c218d8e22b6','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'admin','ProspectLists','module',1,0),('4aff6944-ce29-97e6-48d7-4c218dea5802','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'access','ProspectLists','module',89,0),('4b72c1d8-fffb-22d0-7172-4c218db3f4b5','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'view','ProspectLists','module',90,0),('4be7a578-085b-6dd0-662d-4c218d2fc63b','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'list','ProspectLists','module',90,0),('4cbe2999-07ce-a7db-e9ac-4c218d33ae15','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'edit','ProspectLists','module',90,0),('4d217fc1-e361-cfb0-70fd-4c218db37b54','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'delete','ProspectLists','module',90,0),('4d9e35d5-5afc-de56-d266-4c218d7f5347','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'import','ProspectLists','module',90,0),('4de8a475-f8eb-3937-de0a-4c218da835c7','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'export','ProspectLists','module',90,0),('68f7dc2c-6343-54ff-dd94-4c218d4067da','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'admin','Prospects','module',1,0),('6972c071-34f4-b58f-5858-4c218d8c67f3','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'access','Prospects','module',89,0),('69e85c55-4ad6-20c5-7068-4c218d07f51b','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'view','Prospects','module',90,0),('6a5a30b9-c3b8-a773-a208-4c218d8e1cab','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'list','Prospects','module',90,0),('6b1cd73e-01c7-ad47-9ac0-4c218d67ea54','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'edit','Prospects','module',90,0),('6b91223f-6df1-5263-c88a-4c218dab9379','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'delete','Prospects','module',90,0),('6bf5daf6-30c9-c7fd-25ea-4c218d5b3f3b','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'import','Prospects','module',90,0),('6c4fc0a7-b039-188e-4645-4c218d508b70','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'export','Prospects','module',90,0),('89e3bd48-904d-89f2-0585-4c218d7604ad','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'admin','EmailMarketing','module',1,0),('8a6121c8-2e15-4480-7d22-4c218d699bc1','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'access','EmailMarketing','module',89,0),('8ad53f33-51a8-7ccb-cc34-4c218d70f641','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'view','EmailMarketing','module',90,0),('8b44e14c-d282-c4dc-6262-4c218db932f0','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'list','EmailMarketing','module',90,0),('8c1b9093-0da8-893c-d84d-4c218d155bfd','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'edit','EmailMarketing','module',90,0),('8c95592c-e6b0-55b3-fd0e-4c218dddd524','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'delete','EmailMarketing','module',90,0),('8cfa1d24-adc1-a9f3-ee55-4c218d3d55cc','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'import','EmailMarketing','module',90,0),('8d4ca900-34cc-3f51-781d-4c218dc5b15c','2010-06-23 04:30:12','2010-06-23 04:30:12','1',NULL,'export','EmailMarketing','module',90,0),('5ce3c5f1-da2e-ba2e-cb2f-4c218d1b6336','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'admin','Contacts','module',1,0),('5d654fcc-e18f-2709-8597-4c218d11377e','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'access','Contacts','module',89,0),('5df40a85-690f-9efc-2b6c-4c218d496c25','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'view','Contacts','module',90,0),('5ea45f03-9934-c21c-a815-4c218dde9622','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'list','Contacts','module',90,0),('5f160b75-242b-1df3-55c8-4c218da523a8','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'edit','Contacts','module',90,0),('5f9a2036-a33c-69fa-e90c-4c218dde1cd0','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'delete','Contacts','module',90,0),('5fdf4c9b-57e5-de07-73de-4c218d28413c','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'import','Contacts','module',90,0),('6022a072-e8ae-c31c-6f0e-4c218d13aa04','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'export','Contacts','module',90,0),('9bf3cce7-15d8-68c8-2c59-4c218db446e6','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'admin','Accounts','module',1,0),('9c67f0f4-93c7-7365-c163-4c218d890c65','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'access','Accounts','module',89,0),('9cd63e0d-41f8-af2a-db91-4c218d81caf6','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'view','Accounts','module',90,0),('9d453da0-3153-e81c-a1ef-4c218d9d5960','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'list','Accounts','module',90,0),('9dfef2c0-76f6-3be0-8f93-4c218dd53cef','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'edit','Accounts','module',90,0),('9ea7d05b-0026-18c1-39eb-4c218da20b60','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'delete','Accounts','module',90,0),('9f037722-f5ce-4af7-6b70-4c218dd3b80b','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'import','Accounts','module',90,0),('9f5b97d2-6986-e397-c033-4c218d859a3c','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'export','Accounts','module',90,0),('d8716449-3ef1-2da6-12f8-4c218de7efc3','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'admin','Opportunities','module',1,0),('d8ebe30d-70ba-07f5-6641-4c218d68c84d','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'access','Opportunities','module',89,0),('d95d1b7f-b192-8019-e72d-4c218de22c15','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'view','Opportunities','module',90,0),('d9c2f828-7ef0-9760-c6a6-4c218daf5403','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'list','Opportunities','module',90,0),('da59c3b1-d32e-7af4-fa4c-4c218db7a0e1','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'edit','Opportunities','module',90,0),('dad586de-c8e1-7252-a2d8-4c218dc9121b','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'delete','Opportunities','module',90,0),('db458460-a5a2-62ba-b535-4c218d823c99','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'import','Opportunities','module',90,0),('db8fbab7-4282-f57b-796b-4c218df1bb05','2010-06-23 04:30:13','2010-06-23 04:30:13','1',NULL,'export','Opportunities','module',90,0),('23ac2f22-21b3-a1a9-b049-4c218d9f52ce','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'admin','EmailTemplates','module',1,0),('24244916-7f3b-f4c8-ec5d-4c218de168a9','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'access','EmailTemplates','module',89,0),('249500e6-9e0b-b014-2d2b-4c218dea8890','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'view','EmailTemplates','module',90,0),('2506281d-5891-6dd4-dcc8-4c218d50ba7c','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'list','EmailTemplates','module',90,0),('2607bd4e-9893-4c05-79c1-4c218d671d8e','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'edit','EmailTemplates','module',90,0),('267bf854-fdc8-9190-cdd5-4c218d1e8800','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'delete','EmailTemplates','module',90,0),('26dde97f-8b43-a56f-9c25-4c218d05d8dd','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'import','EmailTemplates','module',90,0),('2771520b-0651-4289-73c2-4c218d3b0239','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'export','EmailTemplates','module',90,0),('4a5f3bb8-5d0b-efe0-949b-4c218dfa4d0f','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'admin','Notes','module',1,0),('4adb4dd9-15dd-2c4d-db1d-4c218db75b79','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'access','Notes','module',89,0),('4b518b6d-0f6b-a89f-7519-4c218d2de1a4','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'view','Notes','module',90,0),('4bc2f448-3a02-c412-c9ee-4c218d423abb','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'list','Notes','module',90,0),('4c371951-0b23-e530-0097-4c218d635978','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'edit','Notes','module',90,0),('4ca17edb-2423-fcf0-cc6e-4c218d7648ff','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'delete','Notes','module',90,0),('4cfaa75f-0713-5637-be92-4c218dfb41e2','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'import','Notes','module',90,0),('4d5d44c9-a91f-e227-e48c-4c218d7c5915','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'export','Notes','module',90,0),('6bc89606-d9e2-2d0a-a31c-4c218d28a306','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'admin','Calls','module',1,0),('6c40a599-3bd2-4dfb-babc-4c218dc4cb88','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'access','Calls','module',89,0),('6cb57d5c-c8ea-b82e-922f-4c218d09743c','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'view','Calls','module',90,0),('6d1f824e-fccb-9bda-b9c4-4c218dfff826','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'list','Calls','module',90,0),('7486079b-e8d8-3c6d-51c8-4c218dcee39e','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'edit','Calls','module',90,0),('74cca501-6a83-5108-1849-4c218d2d8d4d','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'delete','Calls','module',90,0),('75155b47-bde4-4653-99bc-4c218dd6353f','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'import','Calls','module',90,0),('755bca03-6e1c-57be-31ba-4c218d3a6da6','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'export','Calls','module',90,0),('b57854be-2dd2-045b-620f-4c218d250aa7','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'admin','Emails','module',1,0),('b5f22af0-52ce-9119-a360-4c218d217f55','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'access','Emails','module',89,0),('b6640164-e28b-8bc2-c760-4c218de6088b','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'view','Emails','module',90,0),('b6d84cf9-ba07-83c1-87e6-4c218d702e47','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'list','Emails','module',90,0),('b7dae6d4-c3b0-e9dc-7c76-4c218d323bc9','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'edit','Emails','module',90,0),('b844bfb7-a3e7-ea55-4cf6-4c218d39c474','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'delete','Emails','module',90,0),('b89e27da-257a-617c-c824-4c218d082683','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'import','Emails','module',90,0),('b8f6e04a-1f04-789a-46ba-4c218ddb92f2','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'export','Emails','module',90,0),('d34daaaa-44ff-a071-97ab-4c218d0f1d72','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'admin','Meetings','module',1,0),('d47a7cf3-0268-3b9d-e15f-4c218d3289fa','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'access','Meetings','module',89,0),('d4d4ca4e-b4db-20a4-add9-4c218da6ae7e','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'view','Meetings','module',90,0),('d582fc4c-ad66-f2f0-eb51-4c218dac5d77','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'list','Meetings','module',90,0),('d5e1ff08-327d-ac0a-ad05-4c218d5c3d1f','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'edit','Meetings','module',90,0),('d62b1cde-8659-40eb-40a8-4c218da794e7','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'delete','Meetings','module',90,0),('d6713298-8af0-0ed2-d0f9-4c218d879109','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'import','Meetings','module',90,0),('d6b72d7b-5201-edd0-93d9-4c218d43217e','2010-06-23 04:30:14','2010-06-23 04:30:14','1',NULL,'export','Meetings','module',90,0),('35950b79-f110-dbfc-a36a-4c218d22db8a','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'admin','Tasks','module',1,0),('3e790210-1649-b46d-1080-4c218d68961f','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'access','Tasks','module',89,0),('46780149-418e-88d5-23c6-4c218dfd9e2e','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'view','Tasks','module',90,0),('4edb0b87-cd60-ca11-6d58-4c218d5563b1','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'list','Tasks','module',90,0),('5abd06d2-17d3-f41d-6f7c-4c218d309efe','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'edit','Tasks','module',90,0),('807d09c1-8244-cee2-855a-4c218d73a9fb','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'delete','Tasks','module',90,0),('85ec0217-1930-9503-28ef-4c218d994215','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'import','Tasks','module',90,0),('8a990c71-dbed-5701-7bb1-4c218defabeb','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'export','Tasks','module',90,0),('29e802a3-5108-d71a-dd86-4c218d5a0f9c','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'admin','TrackerSessions','TrackerSession',-99,0),('2a4539b8-63ec-fb77-5274-4c218d263c2e','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'access','TrackerSessions','TrackerSession',-99,0),('2a8e37a5-830b-febf-4606-4c218d5039ff','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'view','TrackerSessions','TrackerSession',-99,0),('2ad65813-b67c-d038-00f0-4c218d41c296','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'list','TrackerSessions','TrackerSession',-99,0),('2b1dad1a-758d-1f0c-5d17-4c218da0722f','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'edit','TrackerSessions','TrackerSession',-99,0),('2bb2deb7-62c3-4f48-c48b-4c218d7b9e17','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'delete','TrackerSessions','TrackerSession',-99,0),('2c01e975-d207-6469-f0ea-4c218d85ca5b','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'import','TrackerSessions','TrackerSession',-99,0),('2c4843f2-bfba-f1ed-88e9-4c218da276de','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'export','TrackerSessions','TrackerSession',-99,0),('2ca46f8c-d1ee-4c26-c38b-4c218d564773','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'admin','TrackerPerfs','TrackerPerf',-99,0),('2cec7fda-3b04-a37e-fcf6-4c218df8fa2d','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'access','TrackerPerfs','TrackerPerf',-99,0),('2d3f8842-a67f-c6f7-9d84-4c218dcb2678','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'view','TrackerPerfs','TrackerPerf',-99,0),('2d871bd9-c572-e4a9-5ee0-4c218dfa1ddf','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'list','TrackerPerfs','TrackerPerf',-99,0),('2dce0bfd-06e9-2f3e-0b08-4c218d4a7f6c','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'edit','TrackerPerfs','TrackerPerf',-99,0),('2e16ed57-c188-13df-734d-4c218d93c3ff','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'delete','TrackerPerfs','TrackerPerf',-99,0),('2e5e1363-e8f3-7b64-338d-4c218d38a036','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'import','TrackerPerfs','TrackerPerf',-99,0),('2ec5300e-3a0a-5fbc-27f6-4c218d7a20ce','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'export','TrackerPerfs','TrackerPerf',-99,0),('2f29f394-0171-c73b-66db-4c218db5848d','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'admin','TrackerQueries','TrackerQuery',-99,0),('2f891c2b-a7fb-9310-5c4d-4c218d173b8f','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'access','TrackerQueries','TrackerQuery',-99,0),('2fe5f7e7-d658-6d34-44ad-4c218d0f9a50','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'view','TrackerQueries','TrackerQuery',-99,0),('305849f9-2beb-21ef-c99b-4c218d0b4551','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'list','TrackerQueries','TrackerQuery',-99,0),('30aefbd6-dc76-bbf7-cf09-4c218dec731f','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'edit','TrackerQueries','TrackerQuery',-99,0),('310ce6cf-7ae2-2cb5-2e6f-4c218dfc7438','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'delete','TrackerQueries','TrackerQuery',-99,0),('315dab74-2045-1155-473e-4c218d918035','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'import','TrackerQueries','TrackerQuery',-99,0),('31a5e390-115c-716f-306d-4c218db0b20c','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'export','TrackerQueries','TrackerQuery',-99,0),('45aee06e-81d8-a2ea-43fc-4c218dd1501d','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'admin','Trackers','Tracker',-99,0),('46331499-c2c2-5149-65a3-4c218d87300b','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'access','Trackers','Tracker',-99,0),('46b2c34b-b669-6bfa-fd37-4c218dd6105e','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'view','Trackers','Tracker',-99,0),('4741b9db-daf1-a782-046f-4c218d432704','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'list','Trackers','Tracker',-99,0),('47cee9db-035d-2a04-1011-4c218d7c7b32','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'edit','Trackers','Tracker',-99,0),('484d9ec4-f24f-68f2-4e8c-4c218d83801c','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'delete','Trackers','Tracker',-99,0),('48b2cbf7-e4c2-8f29-8721-4c218d9227d1','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'import','Trackers','Tracker',-99,0),('491393b1-cdd2-095c-9d36-4c218d9ce663','2010-06-23 04:30:15','2010-06-23 04:30:15','1',NULL,'export','Trackers','Tracker',-99,0),('5863e0fb-d084-e730-d745-4c218d071515','2010-06-23 04:30:16','2010-06-23 04:30:16','1',NULL,'admin','Documents','module',1,0),('58de0fe8-a527-776a-a7e7-4c218db5c061','2010-06-23 04:30:16','2010-06-23 04:30:16','1',NULL,'access','Documents','module',89,0),('594fa971-d93e-8a67-e407-4c218dc45025','2010-06-23 04:30:16','2010-06-23 04:30:16','1',NULL,'view','Documents','module',90,0),('59c2f65c-5933-26cb-58c2-4c218ddfeef6','2010-06-23 04:30:16','2010-06-23 04:30:16','1',NULL,'list','Documents','module',90,0),('5ac15d84-fb4c-b420-009a-4c218dc215dc','2010-06-23 04:30:16','2010-06-23 04:30:16','1',NULL,'edit','Documents','module',90,0),('5b423336-4b4e-7e63-b818-4c218db41013','2010-06-23 04:30:16','2010-06-23 04:30:16','1',NULL,'delete','Documents','module',90,0),('5b9dd192-2439-4cc6-2c49-4c218d726835','2010-06-23 04:30:16','2010-06-23 04:30:16','1',NULL,'import','Documents','module',90,0),('5bf93b12-fb13-7cfc-b4d7-4c218d5fd86e','2010-06-23 04:30:16','2010-06-23 04:30:16','1',NULL,'export','Documents','module',90,0),('501e5caa-a282-e9ee-8a1c-4c218d15047f','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'admin','Reports','module',1,0),('50907341-478e-c1dd-9308-4c218de741dd','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'access','Reports','module',89,0),('50f586db-5b67-f423-43b3-4c218d15902e','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'view','Reports','module',90,0),('5159173b-abac-d894-d163-4c218d9edd7d','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'list','Reports','module',90,0),('51de6de7-7d96-6996-51df-4c218d6fabc0','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'edit','Reports','module',90,0),('5250d83b-1e00-3bbb-2ca7-4c218d236e57','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'delete','Reports','module',90,0),('52a1265d-ed96-8793-a137-4c218d8b7364','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'import','Reports','module',90,0),('52ea50c9-9684-713b-396b-4c218d6de6b6','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'export','Reports','module',90,0),('9010727a-92b7-c40c-2360-4c218db69d19','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'admin','Contracts','module',1,0),('9091669f-24b0-db05-0691-4c218de6ef85','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'access','Contracts','module',89,0),('910e30ae-4e56-7c17-d4e5-4c218d96303a','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'view','Contracts','module',90,0),('919e73dd-800a-6a43-def4-4c218d09fd55','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'list','Contracts','module',90,0),('92a8c426-1112-4a5a-6e33-4c218d26a8db','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'edit','Contracts','module',90,0),('930b48cb-8870-b8e0-3e3e-4c218d099c15','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'delete','Contracts','module',90,0),('93686ab7-f2ae-8198-90d5-4c218d12ee27','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'import','Contracts','module',90,0),('93acf557-250c-5fe1-49ab-4c218d9aeefa','2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,'export','Contracts','module',90,0),('1b19aea8-37ae-5870-667d-4c218d1953f9','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'admin','Quotes','module',1,0),('1b9e4ecd-52f2-74eb-d6c2-4c218d9ede9c','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'access','Quotes','module',89,0),('1c1bb603-0b36-ed76-3749-4c218d844815','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'view','Quotes','module',90,0),('1c981e39-fa62-7e4e-3fd7-4c218d90e98e','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'list','Quotes','module',90,0),('1d7468dc-30a7-f279-74f8-4c218dc4ee2e','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'edit','Quotes','module',90,0),('1de526af-3a9e-afa2-ce4a-4c218d9ee9c3','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'delete','Quotes','module',90,0),('1e476cd5-e532-ba4d-f032-4c218d9b2662','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'import','Quotes','module',90,0),('1e9353a6-26af-8876-f5c6-4c218db5fa37','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'export','Quotes','module',90,0),('762f43a1-8398-922a-4ce2-4c218d69358b','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'admin','Products','module',1,0),('76b25baa-6a85-e580-7ec0-4c218de097a2','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'access','Products','module',89,0),('77298cf7-c77a-4495-1ae9-4c218d50aab5','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'view','Products','module',90,0),('779f67db-4b5e-6746-a82d-4c218d0c3780','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'list','Products','module',90,0),('789957d2-8f05-0b18-cbbd-4c218dca5857','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'edit','Products','module',90,0),('790428d9-9c10-5914-e25f-4c218d706e07','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'delete','Products','module',90,0),('7966217f-69c9-6752-f2d0-4c218da70cac','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'import','Products','module',90,0),('79d54e4e-40f9-1555-bb10-4c218d63a0f4','2010-06-23 04:30:19','2010-06-23 04:30:19','1',NULL,'export','Products','module',90,0),('4332d619-47a3-a5a8-66ee-4c218de0da61','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'admin','Forecasts','module',1,0),('43aeed3f-11d5-0b2d-d42d-4c218d20305f','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'access','Forecasts','module',89,0),('44264fd1-9f76-4ed9-5337-4c218d239e8e','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'view','Forecasts','module',90,0),('449d163e-ea75-962c-d960-4c218d8cca4d','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'list','Forecasts','module',90,0),('4532438f-6a09-87a3-ef0a-4c218dfc7ae3','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'edit','Forecasts','module',90,0),('45a93728-0628-f3e2-4c58-4c218d496dda','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'delete','Forecasts','module',90,0),('4611397f-b075-da70-0849-4c218d9ebc4c','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'import','Forecasts','module',90,0),('467563cc-20b2-bfb7-1b46-4c218d993a50','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'export','Forecasts','module',90,0),('642fdf33-84d9-b747-622c-4c218d2b9ea7','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'admin','ForecastSchedule','module',1,0),('64ae0a58-6dca-e3db-ac8d-4c218da028c3','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'access','ForecastSchedule','module',89,0),('653facdc-8113-19e2-bbc5-4c218d4b7e08','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'view','ForecastSchedule','module',90,0),('65ca7223-35da-7bc8-866a-4c218db63479','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'list','ForecastSchedule','module',90,0),('6670edad-b123-ceb5-10c8-4c218d7b273c','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'edit','ForecastSchedule','module',90,0),('66dc34fe-0816-a5d0-04fc-4c218daa80b7','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'delete','ForecastSchedule','module',90,0),('674779bb-6aff-0f11-e56b-4c218d371fbb','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'import','ForecastSchedule','module',90,0),('67a881a4-f2ab-a0cc-dd1d-4c218d74d38f','2010-06-23 04:30:20','2010-06-23 04:30:20','1',NULL,'export','ForecastSchedule','module',90,0),('e7e5c9ed-e105-2be4-0b0e-4c218df1e57b','2010-06-23 04:30:21','2010-06-23 04:30:21','1',NULL,'admin','KBDocuments','module',1,0),('e8669f83-3923-2ce7-6c90-4c218dd77012','2010-06-23 04:30:21','2010-06-23 04:30:21','1',NULL,'access','KBDocuments','module',89,0),('e8ddb0bc-0005-51ab-935b-4c218d64758a','2010-06-23 04:30:21','2010-06-23 04:30:21','1',NULL,'view','KBDocuments','module',90,0),('e9a514d6-0f96-9d7e-261f-4c218db200c2','2010-06-23 04:30:21','2010-06-23 04:30:21','1',NULL,'list','KBDocuments','module',90,0),('ea6bba81-1061-ab11-4420-4c218d7a3948','2010-06-23 04:30:21','2010-06-23 04:30:21','1',NULL,'edit','KBDocuments','module',90,0),('eace8acd-0384-f0d1-67fe-4c218da1f601','2010-06-23 04:30:21','2010-06-23 04:30:21','1',NULL,'delete','KBDocuments','module',90,0),('eb1b4b3c-2309-e151-353c-4c218dc135c4','2010-06-23 04:30:21','2010-06-23 04:30:21','1',NULL,'import','KBDocuments','module',90,0),('eb63a7a4-499a-5874-3d07-4c218dc84895','2010-06-23 04:30:21','2010-06-23 04:30:21','1',NULL,'export','KBDocuments','module',90,0);
/*!40000 ALTER TABLE `acl_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_fields`
--

DROP TABLE IF EXISTS `acl_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_fields` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `aclaccess` int(3) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `role_id` char(36) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_aclfield_role_del` (`role_id`,`category`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_fields`
--

LOCK TABLES `acl_fields` WRITE;
/*!40000 ALTER TABLE `acl_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `acl_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_roles`
--

DROP TABLE IF EXISTS `acl_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_roles` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_aclrole_id_del` (`id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_roles`
--

LOCK TABLES `acl_roles` WRITE;
/*!40000 ALTER TABLE `acl_roles` DISABLE KEYS */;
INSERT INTO `acl_roles` VALUES ('28ad06ec-c146-9131-a584-4c218d728e1a','2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'Tracker','Tracker Role',0),('31609981-af28-cf91-8111-4c218d1eae08','2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'Sales Administrator','Sales Administrator Role',0),('34ac9726-a22f-397a-75fb-4c218d88cf52','2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'Marketing Administrator','Marketing Administrator Role',0),('37897a07-76b2-e2ef-cd86-4c218de41da3','2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'Customer Support Administrator','Customer Support Administrator Role',0);
/*!40000 ALTER TABLE `acl_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_roles_actions`
--

DROP TABLE IF EXISTS `acl_roles_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_roles_actions` (
  `id` varchar(36) NOT NULL,
  `role_id` varchar(36) DEFAULT NULL,
  `action_id` varchar(36) DEFAULT NULL,
  `access_override` int(3) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_acl_role_id` (`role_id`),
  KEY `idx_acl_action_id` (`action_id`),
  KEY `idx_aclrole_action` (`role_id`,`action_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_roles_actions`
--

LOCK TABLES `acl_roles_actions` WRITE;
/*!40000 ALTER TABLE `acl_roles_actions` DISABLE KEYS */;
INSERT INTO `acl_roles_actions` VALUES ('292b4573-61ff-7b13-6578-4c218d399527','28ad06ec-c146-9131-a584-4c218d728e1a','45aee06e-81d8-a2ea-43fc-4c218dd1501d',1,'2010-06-23 04:30:31',0),('29775de2-5cc1-225b-4c27-4c218d3b1786','28ad06ec-c146-9131-a584-4c218d728e1a','46331499-c2c2-5149-65a3-4c218d87300b',89,'2010-06-23 04:30:31',0),('29b655bd-a051-b231-414e-4c218db9daf9','28ad06ec-c146-9131-a584-4c218d728e1a','46b2c34b-b669-6bfa-fd37-4c218dd6105e',90,'2010-06-23 04:30:31',0),('29f4eabd-8f15-06a0-5cea-4c218d3d6a91','28ad06ec-c146-9131-a584-4c218d728e1a','4741b9db-daf1-a782-046f-4c218d432704',90,'2010-06-23 04:30:31',0),('2a38a002-1667-63de-7a0d-4c218dc40f29','28ad06ec-c146-9131-a584-4c218d728e1a','47cee9db-035d-2a04-1011-4c218d7c7b32',90,'2010-06-23 04:30:31',0),('2a73a80e-3124-bcac-a653-4c218de53c64','28ad06ec-c146-9131-a584-4c218d728e1a','484d9ec4-f24f-68f2-4e8c-4c218d83801c',90,'2010-06-23 04:30:31',0),('2aab8bb2-4e29-cd72-a93d-4c218d35db42','28ad06ec-c146-9131-a584-4c218d728e1a','48b2cbf7-e4c2-8f29-8721-4c218d9227d1',90,'2010-06-23 04:30:31',0),('2af67376-49bb-d8a9-8c49-4c218d815533','28ad06ec-c146-9131-a584-4c218d728e1a','491393b1-cdd2-095c-9d36-4c218d9ce663',90,'2010-06-23 04:30:31',0),('2b2f0d58-30d0-82ce-6d60-4c218da116bf','28ad06ec-c146-9131-a584-4c218d728e1a','2f29f394-0171-c73b-66db-4c218db5848d',1,'2010-06-23 04:30:31',0),('2b6696c9-83fd-2409-83b4-4c218ddb3b28','28ad06ec-c146-9131-a584-4c218d728e1a','2f891c2b-a7fb-9310-5c4d-4c218d173b8f',89,'2010-06-23 04:30:31',0),('2b9cdbc9-467e-4d27-6492-4c218dbafe94','28ad06ec-c146-9131-a584-4c218d728e1a','2fe5f7e7-d658-6d34-44ad-4c218d0f9a50',90,'2010-06-23 04:30:31',0),('2bd1fcf3-eb49-efa4-2f15-4c218d30b5d3','28ad06ec-c146-9131-a584-4c218d728e1a','305849f9-2beb-21ef-c99b-4c218d0b4551',90,'2010-06-23 04:30:31',0),('2c08e4d3-5757-1f73-7822-4c218d26f352','28ad06ec-c146-9131-a584-4c218d728e1a','30aefbd6-dc76-bbf7-cf09-4c218dec731f',90,'2010-06-23 04:30:31',0),('2c3e2203-e52b-d5c1-6ceb-4c218d18cd14','28ad06ec-c146-9131-a584-4c218d728e1a','310ce6cf-7ae2-2cb5-2e6f-4c218dfc7438',90,'2010-06-23 04:30:31',0),('2c73a5c1-5b88-a35b-d973-4c218d74230a','28ad06ec-c146-9131-a584-4c218d728e1a','315dab74-2045-1155-473e-4c218d918035',90,'2010-06-23 04:30:31',0),('2cadd247-e53e-0c39-c9ce-4c218ded55a9','28ad06ec-c146-9131-a584-4c218d728e1a','31a5e390-115c-716f-306d-4c218db0b20c',90,'2010-06-23 04:30:31',0),('2ce2de2f-f6ea-1c2a-6f25-4c218d68019d','28ad06ec-c146-9131-a584-4c218d728e1a','2ca46f8c-d1ee-4c26-c38b-4c218d564773',1,'2010-06-23 04:30:31',0),('2d178c40-8046-73de-ec30-4c218dbc8dee','28ad06ec-c146-9131-a584-4c218d728e1a','2cec7fda-3b04-a37e-fcf6-4c218df8fa2d',89,'2010-06-23 04:30:31',0),('2d4cff46-f044-91ad-9e86-4c218ddc389e','28ad06ec-c146-9131-a584-4c218d728e1a','2d3f8842-a67f-c6f7-9d84-4c218dcb2678',90,'2010-06-23 04:30:31',0),('2d8393a5-c8d5-ece0-a85d-4c218d32ecaf','28ad06ec-c146-9131-a584-4c218d728e1a','2d871bd9-c572-e4a9-5ee0-4c218dfa1ddf',90,'2010-06-23 04:30:31',0),('2dc19d9a-dc7a-d6c5-437e-4c218d49440c','28ad06ec-c146-9131-a584-4c218d728e1a','2dce0bfd-06e9-2f3e-0b08-4c218d4a7f6c',90,'2010-06-23 04:30:31',0),('2df85831-e0fc-4fd8-b90a-4c218dc17327','28ad06ec-c146-9131-a584-4c218d728e1a','2e16ed57-c188-13df-734d-4c218d93c3ff',90,'2010-06-23 04:30:31',0),('2e2f1d46-10bc-f75b-2a37-4c218dc18acf','28ad06ec-c146-9131-a584-4c218d728e1a','2e5e1363-e8f3-7b64-338d-4c218d38a036',90,'2010-06-23 04:30:31',0),('2e681514-92a9-3c22-06ae-4c218d8f7f3f','28ad06ec-c146-9131-a584-4c218d728e1a','2ec5300e-3a0a-5fbc-27f6-4c218d7a20ce',90,'2010-06-23 04:30:31',0),('2ea0a758-05b7-818c-9d3b-4c218d4bbabc','28ad06ec-c146-9131-a584-4c218d728e1a','29e802a3-5108-d71a-dd86-4c218d5a0f9c',1,'2010-06-23 04:30:31',0),('2ed94a0d-cf88-f452-e624-4c218d835e65','28ad06ec-c146-9131-a584-4c218d728e1a','2a4539b8-63ec-fb77-5274-4c218d263c2e',89,'2010-06-23 04:30:31',0),('2f1184b5-2867-c981-55da-4c218d9e1643','28ad06ec-c146-9131-a584-4c218d728e1a','2a8e37a5-830b-febf-4606-4c218d5039ff',90,'2010-06-23 04:30:31',0),('2f491f58-c55a-8857-0772-4c218de582b7','28ad06ec-c146-9131-a584-4c218d728e1a','2ad65813-b67c-d038-00f0-4c218d41c296',90,'2010-06-23 04:30:31',0),('2f813149-40f7-57d7-d426-4c218d3cf428','28ad06ec-c146-9131-a584-4c218d728e1a','2b1dad1a-758d-1f0c-5d17-4c218da0722f',90,'2010-06-23 04:30:31',0),('2fb8e972-a970-1791-ba00-4c218d7ac172','28ad06ec-c146-9131-a584-4c218d728e1a','2bb2deb7-62c3-4f48-c48b-4c218d7b9e17',90,'2010-06-23 04:30:31',0),('2ff110d6-2812-9f52-7e93-4c218d380469','28ad06ec-c146-9131-a584-4c218d728e1a','2c01e975-d207-6469-f0ea-4c218d85ca5b',90,'2010-06-23 04:30:31',0),('3029a97f-c43d-5076-affd-4c218de4a6d2','28ad06ec-c146-9131-a584-4c218d728e1a','2c4843f2-bfba-f1ed-88e9-4c218da276de',90,'2010-06-23 04:30:31',0),('31bab72c-a619-1258-e6cd-4c218d9d960b','31609981-af28-cf91-8111-4c218d1eae08','9bf3cce7-15d8-68c8-2c59-4c218db446e6',100,'2010-06-23 04:30:31',0),('31f2033a-07dc-bfd8-e09d-4c218dd571a9','31609981-af28-cf91-8111-4c218d1eae08','9c67f0f4-93c7-7365-c163-4c218d890c65',89,'2010-06-23 04:30:31',0),('3226d4a0-5f5e-d246-59e6-4c218d696261','31609981-af28-cf91-8111-4c218d1eae08','5ce3c5f1-da2e-ba2e-cb2f-4c218d1b6336',100,'2010-06-23 04:30:31',0),('325b2a89-a46d-b41b-c981-4c218d1b3055','31609981-af28-cf91-8111-4c218d1eae08','5d654fcc-e18f-2709-8597-4c218d11377e',89,'2010-06-23 04:30:31',0),('3290d2b8-b3ea-69f1-e1d6-4c218d15a52a','31609981-af28-cf91-8111-4c218d1eae08','4332d619-47a3-a5a8-66ee-4c218de0da61',100,'2010-06-23 04:30:31',0),('32c68bfa-35f9-4259-463f-4c218da7e9a1','31609981-af28-cf91-8111-4c218d1eae08','43aeed3f-11d5-0b2d-d42d-4c218d20305f',89,'2010-06-23 04:30:31',0),('32fdf3fc-2309-8d5e-f910-4c218dbc5921','31609981-af28-cf91-8111-4c218d1eae08','642fdf33-84d9-b747-622c-4c218d2b9ea7',100,'2010-06-23 04:30:31',0),('33352a57-345b-0c81-12c1-4c218d18e419','31609981-af28-cf91-8111-4c218d1eae08','64ae0a58-6dca-e3db-ac8d-4c218da028c3',89,'2010-06-23 04:30:31',0),('336dd509-b420-7470-01a8-4c218d10dc6c','31609981-af28-cf91-8111-4c218d1eae08','99216c29-21b2-cb42-9aa4-4c218dafacb7',100,'2010-06-23 04:30:31',0),('33a5fa62-85fd-edc4-60d7-4c218d7e664c','31609981-af28-cf91-8111-4c218d1eae08','9b2b9dcc-d6a5-d2af-3e03-4c218dbe059f',89,'2010-06-23 04:30:31',0),('33df4f87-685d-f86d-3fa3-4c218d31bff0','31609981-af28-cf91-8111-4c218d1eae08','1b19aea8-37ae-5870-667d-4c218d1953f9',100,'2010-06-23 04:30:31',0),('34181b36-8475-ee69-f0ad-4c218d5a87f9','31609981-af28-cf91-8111-4c218d1eae08','1b9e4ecd-52f2-74eb-d6c2-4c218d9ede9c',89,'2010-06-23 04:30:31',0),('3450d0e6-bd33-7c8c-1522-4c218d79ceb2','31609981-af28-cf91-8111-4c218d1eae08','d8716449-3ef1-2da6-12f8-4c218de7efc3',100,'2010-06-23 04:30:31',0),('348ad825-5cd3-376d-a4cf-4c218db9edfd','31609981-af28-cf91-8111-4c218d1eae08','d8ebe30d-70ba-07f5-6641-4c218d68c84d',89,'2010-06-23 04:30:31',0),('34f6dcb2-16aa-71c4-367f-4c218dc91fc4','34ac9726-a22f-397a-75fb-4c218d88cf52','9bf3cce7-15d8-68c8-2c59-4c218db446e6',100,'2010-06-23 04:30:31',0),('352be3a4-de44-7502-fd34-4c218d1e0f37','34ac9726-a22f-397a-75fb-4c218d88cf52','9c67f0f4-93c7-7365-c163-4c218d890c65',89,'2010-06-23 04:30:31',0),('35619184-915c-1b36-3e5a-4c218d8de8e1','34ac9726-a22f-397a-75fb-4c218d88cf52','5ce3c5f1-da2e-ba2e-cb2f-4c218d1b6336',100,'2010-06-23 04:30:31',0),('35998156-ec9a-58ff-c7b6-4c218df400c9','34ac9726-a22f-397a-75fb-4c218d88cf52','5d654fcc-e18f-2709-8597-4c218d11377e',89,'2010-06-23 04:30:31',0),('35cfac6e-5cd8-c593-803b-4c218dc4e3e3','34ac9726-a22f-397a-75fb-4c218d88cf52','b1c708ec-5a1c-8172-0dd6-4c218d6a86ee',100,'2010-06-23 04:30:31',0),('3609c6cf-05ca-58b6-3aa2-4c218d6ab3e5','34ac9726-a22f-397a-75fb-4c218d88cf52','b6f80063-45e4-5f94-fc4c-4c218d0c199d',89,'2010-06-23 04:30:31',0),('3644ee4d-9889-c39f-32c7-4c218d5827a9','34ac9726-a22f-397a-75fb-4c218d88cf52','4a8875f8-43a3-1d82-d77c-4c218d8e22b6',100,'2010-06-23 04:30:31',0),('367f4448-dae2-1955-ec02-4c218d68838f','34ac9726-a22f-397a-75fb-4c218d88cf52','4aff6944-ce29-97e6-48d7-4c218dea5802',89,'2010-06-23 04:30:31',0),('36ba4f42-c665-9ded-9c51-4c218d18c06d','34ac9726-a22f-397a-75fb-4c218d88cf52','99216c29-21b2-cb42-9aa4-4c218dafacb7',100,'2010-06-23 04:30:31',0),('36f3a34a-e6ba-e767-c623-4c218daf1aad','34ac9726-a22f-397a-75fb-4c218d88cf52','9b2b9dcc-d6a5-d2af-3e03-4c218dbe059f',89,'2010-06-23 04:30:31',0),('372dd2df-00b0-9bdd-4cf0-4c218def9d15','34ac9726-a22f-397a-75fb-4c218d88cf52','68f7dc2c-6343-54ff-dd94-4c218d4067da',100,'2010-06-23 04:30:31',0),('3767b671-1899-5604-ed37-4c218d30c244','34ac9726-a22f-397a-75fb-4c218d88cf52','6972c071-34f4-b58f-5858-4c218d8c67f3',89,'2010-06-23 04:30:31',0),('37cdab73-fe4b-e96e-867f-4c218d854a09','37897a07-76b2-e2ef-cd86-4c218de41da3','9bf3cce7-15d8-68c8-2c59-4c218db446e6',100,'2010-06-23 04:30:31',0),('3803408b-3227-df4e-1043-4c218d4b2e92','37897a07-76b2-e2ef-cd86-4c218de41da3','9c67f0f4-93c7-7365-c163-4c218d890c65',89,'2010-06-23 04:30:31',0),('38379b44-a0ae-a94d-b0fa-4c218da973db','37897a07-76b2-e2ef-cd86-4c218de41da3','5ce3c5f1-da2e-ba2e-cb2f-4c218d1b6336',100,'2010-06-23 04:30:31',0),('386cf428-17fa-f5e5-b409-4c218da11c44','37897a07-76b2-e2ef-cd86-4c218de41da3','5d654fcc-e18f-2709-8597-4c218d11377e',89,'2010-06-23 04:30:31',0),('389ea523-b4d2-ba0d-5713-4c218dda5772','37897a07-76b2-e2ef-cd86-4c218de41da3','238c1ff7-d57b-cb7a-4d79-4c218d544ec0',100,'2010-06-23 04:30:31',0),('38d8e35a-0b47-e98b-a960-4c218da24997','37897a07-76b2-e2ef-cd86-4c218de41da3','2401a6dc-8fe8-8797-2772-4c218d8052ec',89,'2010-06-23 04:30:31',0),('390c9ee4-649f-f462-fcbc-4c218dec15c8','37897a07-76b2-e2ef-cd86-4c218de41da3','d87178a2-844b-eee2-3df3-4c218d2f69b0',100,'2010-06-23 04:30:31',0),('39418a71-ab58-c41f-a248-4c218d20ca89','37897a07-76b2-e2ef-cd86-4c218de41da3','d8e3bcb6-3159-ff38-1edc-4c218de22db2',89,'2010-06-23 04:30:31',0),('3976c851-8ba6-32e2-0f9b-4c218d4bd579','37897a07-76b2-e2ef-cd86-4c218de41da3','e7e5c9ed-e105-2be4-0b0e-4c218df1e57b',100,'2010-06-23 04:30:31',0),('39ab2173-bc1c-69a5-40d4-4c218de9c26a','37897a07-76b2-e2ef-cd86-4c218de41da3','e8669f83-3923-2ce7-6c90-4c218dd77012',89,'2010-06-23 04:30:31',0);
/*!40000 ALTER TABLE `acl_roles_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acl_roles_users`
--

DROP TABLE IF EXISTS `acl_roles_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acl_roles_users` (
  `id` varchar(36) NOT NULL,
  `role_id` varchar(36) DEFAULT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_aclrole_id` (`role_id`),
  KEY `idx_acluser_id` (`user_id`),
  KEY `idx_aclrole_user` (`role_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acl_roles_users`
--

LOCK TABLES `acl_roles_users` WRITE;
/*!40000 ALTER TABLE `acl_roles_users` DISABLE KEYS */;
INSERT INTO `acl_roles_users` VALUES ('30672785-6aac-eaba-7f5e-4c218d8e1fc1','28ad06ec-c146-9131-a584-4c218d728e1a','1','2010-06-23 04:30:31',0);
/*!40000 ALTER TABLE `acl_roles_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `address_book`
--

DROP TABLE IF EXISTS `address_book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address_book` (
  `assigned_user_id` char(36) NOT NULL,
  `bean` varchar(50) DEFAULT NULL,
  `bean_id` char(36) NOT NULL,
  KEY `ab_user_bean_idx` (`assigned_user_id`,`bean`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `address_book`
--

LOCK TABLES `address_book` WRITE;
/*!40000 ALTER TABLE `address_book` DISABLE KEYS */;
/*!40000 ALTER TABLE `address_book` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `address_book_list_items`
--

DROP TABLE IF EXISTS `address_book_list_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address_book_list_items` (
  `list_id` char(36) NOT NULL,
  `bean_id` char(36) NOT NULL,
  KEY `abli_list_id_idx` (`list_id`),
  KEY `abli_list_id_bean_idx` (`list_id`,`bean_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `address_book_list_items`
--

LOCK TABLES `address_book_list_items` WRITE;
/*!40000 ALTER TABLE `address_book_list_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `address_book_list_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `address_book_lists`
--

DROP TABLE IF EXISTS `address_book_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address_book_lists` (
  `id` char(36) NOT NULL,
  `assigned_user_id` char(36) NOT NULL,
  `list_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `abml_user_bean_idx` (`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `address_book_lists`
--

LOCK TABLES `address_book_lists` WRITE;
/*!40000 ALTER TABLE `address_book_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `address_book_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bugs`
--

DROP TABLE IF EXISTS `bugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bugs` (
  `id` char(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `bug_number` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `priority` varchar(25) DEFAULT NULL,
  `resolution` varchar(255) DEFAULT NULL,
  `system_id` int(11) DEFAULT NULL,
  `work_log` text,
  `found_in_release` varchar(255) DEFAULT NULL,
  `fixed_in_release` varchar(255) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `product_category` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bugsnumk` (`bug_number`),
  UNIQUE KEY `bug_number` (`bug_number`,`system_id`),
  KEY `idx_bugs_tmst_id` (`team_set_id`),
  KEY `idx_bug_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bugs`
--

LOCK TABLES `bugs` WRITE;
/*!40000 ALTER TABLE `bugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `bugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bugs_audit`
--

DROP TABLE IF EXISTS `bugs_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bugs_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_bugs_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bugs_audit`
--

LOCK TABLES `bugs_audit` WRITE;
/*!40000 ALTER TABLE `bugs_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `bugs_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calls`
--

DROP TABLE IF EXISTS `calls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calls` (
  `id` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `duration_hours` int(2) DEFAULT NULL,
  `duration_minutes` int(2) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `parent_type` varchar(255) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `direction` varchar(25) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `reminder_time` int(4) DEFAULT '-1',
  `outlook_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_calls_tmst_id` (`team_set_id`),
  KEY `idx_call_name` (`name`),
  KEY `idx_status` (`status`),
  KEY `idx_calls_date_start` (`date_start`),
  KEY `idx_calls_par_del` (`parent_id`,`parent_type`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calls`
--

LOCK TABLES `calls` WRITE;
/*!40000 ALTER TABLE `calls` DISABLE KEYS */;
/*!40000 ALTER TABLE `calls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calls_contacts`
--

DROP TABLE IF EXISTS `calls_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calls_contacts` (
  `id` varchar(36) NOT NULL,
  `call_id` varchar(36) DEFAULT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `required` varchar(1) DEFAULT '1',
  `accept_status` varchar(25) DEFAULT 'none',
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_con_call_call` (`call_id`),
  KEY `idx_con_call_con` (`contact_id`),
  KEY `idx_call_contact` (`call_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calls_contacts`
--

LOCK TABLES `calls_contacts` WRITE;
/*!40000 ALTER TABLE `calls_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `calls_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calls_leads`
--

DROP TABLE IF EXISTS `calls_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calls_leads` (
  `id` varchar(36) NOT NULL,
  `call_id` varchar(36) DEFAULT NULL,
  `lead_id` varchar(36) DEFAULT NULL,
  `required` varchar(1) DEFAULT '1',
  `accept_status` varchar(25) DEFAULT 'none',
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_lead_call_call` (`call_id`),
  KEY `idx_lead_call_lead` (`lead_id`),
  KEY `idx_call_lead` (`call_id`,`lead_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calls_leads`
--

LOCK TABLES `calls_leads` WRITE;
/*!40000 ALTER TABLE `calls_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `calls_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `calls_users`
--

DROP TABLE IF EXISTS `calls_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `calls_users` (
  `id` varchar(36) NOT NULL,
  `call_id` varchar(36) DEFAULT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `required` varchar(1) DEFAULT '1',
  `accept_status` varchar(25) DEFAULT 'none',
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_usr_call_call` (`call_id`),
  KEY `idx_usr_call_usr` (`user_id`),
  KEY `idx_call_users` (`call_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `calls_users`
--

LOCK TABLES `calls_users` WRITE;
/*!40000 ALTER TABLE `calls_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `calls_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_log`
--

DROP TABLE IF EXISTS `campaign_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_log` (
  `id` char(36) NOT NULL,
  `campaign_id` char(36) DEFAULT NULL,
  `target_tracker_key` varchar(36) DEFAULT NULL,
  `target_id` varchar(36) DEFAULT NULL,
  `target_type` varchar(25) DEFAULT NULL,
  `activity_type` varchar(25) DEFAULT NULL,
  `activity_date` datetime DEFAULT NULL,
  `related_id` varchar(36) DEFAULT NULL,
  `related_type` varchar(25) DEFAULT NULL,
  `archived` tinyint(1) DEFAULT '0',
  `hits` int(11) DEFAULT '0',
  `list_id` char(36) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_modified` datetime DEFAULT NULL,
  `more_information` varchar(100) DEFAULT NULL,
  `marketing_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_camp_tracker` (`target_tracker_key`),
  KEY `idx_camp_campaign_id` (`campaign_id`),
  KEY `idx_camp_more_info` (`more_information`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_log`
--

LOCK TABLES `campaign_log` WRITE;
/*!40000 ALTER TABLE `campaign_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaign_trkrs`
--

DROP TABLE IF EXISTS `campaign_trkrs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaign_trkrs` (
  `id` char(36) NOT NULL,
  `tracker_name` varchar(30) DEFAULT NULL,
  `tracker_url` varchar(255) DEFAULT 'http://',
  `tracker_key` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` char(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `is_optout` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `campaign_tracker_key_idx` (`tracker_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaign_trkrs`
--

LOCK TABLES `campaign_trkrs` WRITE;
/*!40000 ALTER TABLE `campaign_trkrs` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaign_trkrs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns`
--

DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaigns` (
  `id` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `tracker_key` int(11) NOT NULL AUTO_INCREMENT,
  `tracker_count` int(11) DEFAULT '0',
  `refer_url` varchar(255) DEFAULT 'http://',
  `tracker_text` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `impressions` int(11) DEFAULT '0',
  `currency_id` char(36) DEFAULT NULL,
  `budget` double DEFAULT NULL,
  `expected_cost` double DEFAULT NULL,
  `actual_cost` double DEFAULT NULL,
  `expected_revenue` double DEFAULT NULL,
  `campaign_type` varchar(25) DEFAULT NULL,
  `objective` text,
  `content` text,
  `frequency` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_campaigns_tmst_id` (`team_set_id`),
  KEY `camp_auto_tracker_key` (`tracker_key`),
  KEY `idx_campaign_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns`
--

LOCK TABLES `campaigns` WRITE;
/*!40000 ALTER TABLE `campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `campaigns_audit`
--

DROP TABLE IF EXISTS `campaigns_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campaigns_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_campaigns_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campaigns_audit`
--

LOCK TABLES `campaigns_audit` WRITE;
/*!40000 ALTER TABLE `campaigns_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `campaigns_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cases`
--

DROP TABLE IF EXISTS `cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cases` (
  `id` char(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `case_number` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `priority` varchar(25) DEFAULT NULL,
  `resolution` text,
  `system_id` int(11) DEFAULT NULL,
  `work_log` text,
  `account_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `casesnumk` (`case_number`),
  UNIQUE KEY `case_number` (`case_number`,`system_id`),
  KEY `idx_cases_tmst_id` (`team_set_id`),
  KEY `idx_case_name` (`name`),
  KEY `idx_account_id` (`account_id`),
  KEY `idx_cases_stat_del` (`assigned_user_id`,`status`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cases`
--

LOCK TABLES `cases` WRITE;
/*!40000 ALTER TABLE `cases` DISABLE KEYS */;
/*!40000 ALTER TABLE `cases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cases_audit`
--

DROP TABLE IF EXISTS `cases_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cases_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_cases_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cases_audit`
--

LOCK TABLES `cases_audit` WRITE;
/*!40000 ALTER TABLE `cases_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `cases_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cases_bugs`
--

DROP TABLE IF EXISTS `cases_bugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cases_bugs` (
  `id` varchar(36) NOT NULL,
  `case_id` varchar(36) DEFAULT NULL,
  `bug_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_cas_bug_cas` (`case_id`),
  KEY `idx_cas_bug_bug` (`bug_id`),
  KEY `idx_case_bug` (`case_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cases_bugs`
--

LOCK TABLES `cases_bugs` WRITE;
/*!40000 ALTER TABLE `cases_bugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `cases_bugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category_tree`
--

DROP TABLE IF EXISTS `category_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category_tree` (
  `self_id` varchar(36) DEFAULT NULL,
  `node_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_node_id` int(11) DEFAULT '0',
  `type` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`node_id`),
  KEY `idx_categorytree` (`self_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category_tree`
--

LOCK TABLES `category_tree` WRITE;
/*!40000 ALTER TABLE `category_tree` DISABLE KEYS */;
/*!40000 ALTER TABLE `category_tree` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `config` (
  `category` varchar(32) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `value` text,
  KEY `idx_config_cat` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config`
--

LOCK TABLES `config` WRITE;
/*!40000 ALTER TABLE `config` DISABLE KEYS */;
INSERT INTO `config` VALUES ('notify','fromaddress','do_not_reply@example.com'),('notify','fromname','SugarCRM'),('notify','send_by_default','1'),('notify','on','1'),('notify','send_from_assigning_user','0'),('info','sugar_version','6.0.0RC2'),('MySettings','tab','YToxMzp7aTowO3M6NDoiSG9tZSI7aToxO3M6ODoiQWNjb3VudHMiO2k6MjtzOjg6IkNvbnRhY3RzIjtpOjM7czoxMzoiT3Bwb3J0dW5pdGllcyI7aTo0O3M6NToiTGVhZHMiO2k6NTtzOjEwOiJBY3Rpdml0aWVzIjtpOjY7czo3OiJSZXBvcnRzIjtpOjc7czo5OiJEb2N1bWVudHMiO2k6ODtzOjY6IkVtYWlscyI7aTo5O3M6OToiQ2FtcGFpZ25zIjtpOjEwO3M6NjoiUXVvdGVzIjtpOjExO3M6OToiRm9yZWNhc3RzIjtpOjEyO3M6NToiQ2FzZXMiO30='),('portal','on','0'),('license','users','0'),('license','expire_date',''),('license','key','internal sugar user 20100224'),('tracker','Tracker','1'),('tracker','tracker_perf','1'),('tracker','tracker_sessions','1'),('tracker','tracker_queries','1'),('system','system_id','1'),('system','skypeout_on','1'),('license','num_lic_oc','0'),('sugarfeed','enabled','1'),('sugarfeed','module_UserFeed','1'),('sugarfeed','module_Cases','1'),('sugarfeed','module_Contacts','1'),('sugarfeed','module_Leads','1'),('sugarfeed','module_Opportunities','1'),('password','System-generated password email','64e44098-6b51-5039-38df-4c218d1b6bfc'),('password','Forgot Password email','65b4ab02-17b8-e3bc-e44c-4c218db3a63e'),('Update','CheckUpdates','automatic'),('system','name','SugarCRM');
/*!40000 ALTER TABLE `config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `salutation` varchar(5) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `do_not_call` tinyint(1) DEFAULT '0',
  `phone_home` varchar(25) DEFAULT NULL,
  `phone_mobile` varchar(25) DEFAULT NULL,
  `phone_work` varchar(25) DEFAULT NULL,
  `phone_other` varchar(25) DEFAULT NULL,
  `phone_fax` varchar(25) DEFAULT NULL,
  `primary_address_street` varchar(150) DEFAULT NULL,
  `primary_address_city` varchar(100) DEFAULT NULL,
  `primary_address_state` varchar(100) DEFAULT NULL,
  `primary_address_postalcode` varchar(20) DEFAULT NULL,
  `primary_address_country` varchar(255) DEFAULT NULL,
  `alt_address_street` varchar(150) DEFAULT NULL,
  `alt_address_city` varchar(100) DEFAULT NULL,
  `alt_address_state` varchar(100) DEFAULT NULL,
  `alt_address_postalcode` varchar(20) DEFAULT NULL,
  `alt_address_country` varchar(255) DEFAULT NULL,
  `assistant` varchar(75) DEFAULT NULL,
  `assistant_phone` varchar(25) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `lead_source` varchar(100) DEFAULT NULL,
  `reports_to_id` char(36) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `campaign_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_contacts_tmst_id` (`team_set_id`),
  KEY `idx_cont_last_first` (`last_name`,`first_name`,`deleted`),
  KEY `idx_contacts_del_last` (`deleted`,`last_name`),
  KEY `idx_cont_del_reports` (`deleted`,`reports_to_id`,`last_name`),
  KEY `idx_reports_to_id` (`reports_to_id`),
  KEY `idx_del_id_user` (`deleted`,`id`,`assigned_user_id`),
  KEY `idx_cont_assigned` (`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--

LOCK TABLES `contacts` WRITE;
/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts_audit`
--

DROP TABLE IF EXISTS `contacts_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_contacts_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts_audit`
--

LOCK TABLES `contacts_audit` WRITE;
/*!40000 ALTER TABLE `contacts_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts_bugs`
--

DROP TABLE IF EXISTS `contacts_bugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts_bugs` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `bug_id` varchar(36) DEFAULT NULL,
  `contact_role` varchar(50) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_con_bug_con` (`contact_id`),
  KEY `idx_con_bug_bug` (`bug_id`),
  KEY `idx_contact_bug` (`contact_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts_bugs`
--

LOCK TABLES `contacts_bugs` WRITE;
/*!40000 ALTER TABLE `contacts_bugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts_bugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts_cases`
--

DROP TABLE IF EXISTS `contacts_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts_cases` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `case_id` varchar(36) DEFAULT NULL,
  `contact_role` varchar(50) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_con_case_con` (`contact_id`),
  KEY `idx_con_case_case` (`case_id`),
  KEY `idx_contacts_cases` (`contact_id`,`case_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts_cases`
--

LOCK TABLES `contacts_cases` WRITE;
/*!40000 ALTER TABLE `contacts_cases` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts_cases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contacts_users`
--

DROP TABLE IF EXISTS `contacts_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts_users` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_con_users_con` (`contact_id`),
  KEY `idx_con_users_user` (`user_id`),
  KEY `idx_contacts_users` (`contact_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts_users`
--

LOCK TABLES `contacts_users` WRITE;
/*!40000 ALTER TABLE `contacts_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `contacts_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contract_types`
--

DROP TABLE IF EXISTS `contract_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contract_types` (
  `id` char(36) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `list_order` int(4) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contract_types`
--

LOCK TABLES `contract_types` WRITE;
/*!40000 ALTER TABLE `contract_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `contract_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts`
--

DROP TABLE IF EXISTS `contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts` (
  `id` char(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `reference_code` varchar(255) DEFAULT NULL,
  `account_id` char(36) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `currency_id` char(36) DEFAULT NULL,
  `total_contract_value` decimal(26,6) DEFAULT NULL,
  `total_contract_value_usdollar` decimal(26,6) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `customer_signed_date` date DEFAULT NULL,
  `company_signed_date` date DEFAULT NULL,
  `expiration_notice` datetime DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_contracts_tmst_id` (`team_set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts`
--

LOCK TABLES `contracts` WRITE;
/*!40000 ALTER TABLE `contracts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts_audit`
--

DROP TABLE IF EXISTS `contracts_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_contracts_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts_audit`
--

LOCK TABLES `contracts_audit` WRITE;
/*!40000 ALTER TABLE `contracts_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `contracts_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts_contacts`
--

DROP TABLE IF EXISTS `contracts_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts_contacts` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `contract_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contracts_contacts_alt` (`contact_id`,`contract_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts_contacts`
--

LOCK TABLES `contracts_contacts` WRITE;
/*!40000 ALTER TABLE `contracts_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `contracts_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts_opportunities`
--

DROP TABLE IF EXISTS `contracts_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts_opportunities` (
  `id` varchar(36) NOT NULL,
  `opportunity_id` varchar(36) DEFAULT NULL,
  `contract_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contracts_opp_alt` (`contract_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts_opportunities`
--

LOCK TABLES `contracts_opportunities` WRITE;
/*!40000 ALTER TABLE `contracts_opportunities` DISABLE KEYS */;
/*!40000 ALTER TABLE `contracts_opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts_products`
--

DROP TABLE IF EXISTS `contracts_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts_products` (
  `id` varchar(36) NOT NULL,
  `product_id` varchar(36) DEFAULT NULL,
  `contract_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contracts_prod_alt` (`contract_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts_products`
--

LOCK TABLES `contracts_products` WRITE;
/*!40000 ALTER TABLE `contracts_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `contracts_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts_quotes`
--

DROP TABLE IF EXISTS `contracts_quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contracts_quotes` (
  `id` varchar(36) NOT NULL,
  `quote_id` varchar(36) DEFAULT NULL,
  `contract_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `contracts_quot_alt` (`contract_id`,`quote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts_quotes`
--

LOCK TABLES `contracts_quotes` WRITE;
/*!40000 ALTER TABLE `contracts_quotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `contracts_quotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `currencies` (
  `id` char(36) NOT NULL,
  `name` varchar(36) DEFAULT NULL,
  `symbol` varchar(36) DEFAULT NULL,
  `iso4217` varchar(3) DEFAULT NULL,
  `conversion_rate` double DEFAULT '0',
  `status` varchar(25) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `created_by` char(36) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_currency_name` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_fields`
--

DROP TABLE IF EXISTS `custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `custom_fields` (
  `bean_id` varchar(36) DEFAULT NULL,
  `set_num` int(11) DEFAULT '0',
  `field0` varchar(255) DEFAULT NULL,
  `field1` varchar(255) DEFAULT NULL,
  `field2` varchar(255) DEFAULT NULL,
  `field3` varchar(255) DEFAULT NULL,
  `field4` varchar(255) DEFAULT NULL,
  `field5` varchar(255) DEFAULT NULL,
  `field6` varchar(255) DEFAULT NULL,
  `field7` varchar(255) DEFAULT NULL,
  `field8` varchar(255) DEFAULT NULL,
  `field9` varchar(255) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  KEY `idx_beanid_set_num` (`bean_id`,`set_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_fields`
--

LOCK TABLES `custom_fields` WRITE;
/*!40000 ALTER TABLE `custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `document_revisions`
--

DROP TABLE IF EXISTS `document_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `document_revisions` (
  `id` varchar(36) NOT NULL,
  `change_log` varchar(255) DEFAULT NULL,
  `document_id` varchar(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `file_ext` varchar(25) DEFAULT NULL,
  `file_mime_type` varchar(100) DEFAULT NULL,
  `revision` varchar(25) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `document_revisions`
--

LOCK TABLES `document_revisions` WRITE;
/*!40000 ALTER TABLE `document_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `document_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `document_name` varchar(255) DEFAULT NULL,
  `active_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `category_id` varchar(25) DEFAULT NULL,
  `subcategory_id` varchar(25) DEFAULT NULL,
  `status_id` varchar(25) DEFAULT NULL,
  `document_revision_id` varchar(36) DEFAULT NULL,
  `related_doc_id` char(36) DEFAULT NULL,
  `related_doc_rev_id` char(36) DEFAULT NULL,
  `is_template` tinyint(1) DEFAULT '0',
  `template_type` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_documents_tmst_id` (`team_set_id`),
  KEY `idx_doc_cat` (`category_id`,`subcategory_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_addr_bean_rel`
--

DROP TABLE IF EXISTS `email_addr_bean_rel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_addr_bean_rel` (
  `id` char(36) NOT NULL,
  `email_address_id` char(36) NOT NULL,
  `bean_id` char(36) NOT NULL,
  `bean_module` varchar(25) DEFAULT NULL,
  `primary_address` tinyint(1) DEFAULT '0',
  `reply_to_address` tinyint(1) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_email_address_id` (`email_address_id`),
  KEY `idx_bean_id` (`bean_id`,`bean_module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_addr_bean_rel`
--

LOCK TABLES `email_addr_bean_rel` WRITE;
/*!40000 ALTER TABLE `email_addr_bean_rel` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_addr_bean_rel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_addresses`
--

DROP TABLE IF EXISTS `email_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_addresses` (
  `id` char(36) NOT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `email_address_caps` varchar(255) DEFAULT NULL,
  `invalid_email` tinyint(1) DEFAULT '0',
  `opt_out` tinyint(1) DEFAULT '0',
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_ea_caps_opt_out_invalid` (`email_address_caps`,`opt_out`,`invalid_email`),
  KEY `idx_ea_opt_out_invalid` (`email_address`,`opt_out`,`invalid_email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_addresses`
--

LOCK TABLES `email_addresses` WRITE;
/*!40000 ALTER TABLE `email_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_cache`
--

DROP TABLE IF EXISTS `email_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_cache` (
  `ie_id` char(36) NOT NULL,
  `mbox` varchar(60) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `fromaddr` varchar(100) DEFAULT NULL,
  `toaddr` varchar(255) DEFAULT NULL,
  `senddate` datetime DEFAULT NULL,
  `message_id` varchar(255) DEFAULT NULL,
  `mailsize` int(10) unsigned DEFAULT NULL,
  `imap_uid` int(10) unsigned DEFAULT NULL,
  `msgno` int(10) unsigned DEFAULT NULL,
  `recent` tinyint(4) DEFAULT NULL,
  `flagged` tinyint(4) DEFAULT NULL,
  `answered` tinyint(4) DEFAULT NULL,
  `deleted` tinyint(4) DEFAULT NULL,
  `seen` tinyint(4) DEFAULT NULL,
  `draft` tinyint(4) DEFAULT NULL,
  KEY `idx_ie_id` (`ie_id`),
  KEY `idx_mail_date` (`ie_id`,`mbox`,`senddate`),
  KEY `idx_mail_from` (`ie_id`,`mbox`,`fromaddr`),
  KEY `idx_mail_subj` (`subject`),
  KEY `idx_mail_to` (`toaddr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_cache`
--

LOCK TABLES `email_cache` WRITE;
/*!40000 ALTER TABLE `email_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_marketing`
--

DROP TABLE IF EXISTS `email_marketing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_marketing` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `from_name` varchar(100) DEFAULT NULL,
  `from_addr` varchar(100) DEFAULT NULL,
  `reply_to_name` varchar(100) DEFAULT NULL,
  `reply_to_addr` varchar(100) DEFAULT NULL,
  `inbound_email_id` varchar(36) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `template_id` char(36) NOT NULL,
  `status` varchar(25) DEFAULT NULL,
  `campaign_id` char(36) DEFAULT NULL,
  `all_prospect_lists` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_emmkt_name` (`name`),
  KEY `idx_emmkit_del` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_marketing`
--

LOCK TABLES `email_marketing` WRITE;
/*!40000 ALTER TABLE `email_marketing` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_marketing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_marketing_prospect_lists`
--

DROP TABLE IF EXISTS `email_marketing_prospect_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_marketing_prospect_lists` (
  `id` varchar(36) NOT NULL,
  `prospect_list_id` varchar(36) DEFAULT NULL,
  `email_marketing_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `email_mp_prospects` (`email_marketing_id`,`prospect_list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_marketing_prospect_lists`
--

LOCK TABLES `email_marketing_prospect_lists` WRITE;
/*!40000 ALTER TABLE `email_marketing_prospect_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_marketing_prospect_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_templates` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `published` varchar(3) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `subject` varchar(255) DEFAULT NULL,
  `body` text,
  `body_html` text,
  `deleted` tinyint(1) DEFAULT '0',
  `base_module` varchar(50) DEFAULT NULL,
  `from_name` varchar(255) DEFAULT NULL,
  `from_address` varchar(255) DEFAULT NULL,
  `text_only` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_email_templates_tmst_id` (`team_set_id`),
  KEY `idx_email_template_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_templates`
--

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;
INSERT INTO `email_templates` VALUES ('5f840460-9a76-a49f-ced9-4c218de0dca9','5f840460-9a76-a49f-ced9-4c218de0dca9','64e44098-6b51-5039-38df-4c218d1b6bfc','2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'off','System-generated password email','This template is used when the System Administrator sends a new password to a user.','New account information','\nHere is your account username and temporary password:\nUsername : $contact_user_user_name\nPassword : $contact_user_user_hash\n\nhttp://localhost/600RC2_pro/index.php\n\nAfter you log in using the above password, you may be required to reset the password to one of your own choice.','<div><table border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" width=\"550\" align=\\\"\\\"\\\"center\\\"\\\"\\\"><tbody><tr><td colspan=\\\"2\\\"><p>Here is your account username and temporary password:</p><p>Username : $contact_user_user_name </p><p>Password : $contact_user_user_hash </p><br><p>http://localhost/600RC2_pro/index.php</p><br><p>After you log in using the above password, you may be required to reset the password to one of your own choice.</p>   </td>         </tr><tr><td colspan=\\\"2\\\"></td>         </tr> </tbody></table> </div>',0,NULL,NULL,NULL,0),('5f840460-9a76-a49f-ced9-4c218de0dca9','5f840460-9a76-a49f-ced9-4c218de0dca9','65b4ab02-17b8-e3bc-e44c-4c218db3a63e','2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'off','Forgot Password email','This template is used to send a user a link to click to reset the user\'s account password.','Reset your account password','\nYou recently requested on $contact_user_pwd_last_changed to be able to reset your account password.\n\nClick on the link below to reset your password:\n\n$contact_user_link_guid','<div><table border=\\\"0\\\" cellspacing=\\\"0\\\" cellpadding=\\\"0\\\" width=\"550\" align=\\\"\\\"\\\"center\\\"\\\"\\\"><tbody><tr><td colspan=\\\"2\\\"><p>You recently requested on $contact_user_pwd_last_changed to be able to reset your account password. </p><p>Click on the link below to reset your password:</p><p> $contact_user_link_guid </p>  </td>         </tr><tr><td colspan=\\\"2\\\"></td>         </tr> </tbody></table> </div>',0,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emailman`
--

DROP TABLE IF EXISTS `emailman`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emailman` (
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` char(36) DEFAULT NULL,
  `marketing_id` char(36) DEFAULT NULL,
  `list_id` char(36) DEFAULT NULL,
  `send_date_time` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `in_queue` tinyint(1) DEFAULT '0',
  `in_queue_date` datetime DEFAULT NULL,
  `send_attempts` int(11) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `related_id` char(36) DEFAULT NULL,
  `related_type` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_eman_list` (`list_id`,`user_id`,`deleted`),
  KEY `idx_eman_campaign_id` (`campaign_id`),
  KEY `idx_eman_relid_reltype_id` (`related_id`,`related_type`,`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emailman`
--

LOCK TABLES `emailman` WRITE;
/*!40000 ALTER TABLE `emailman` DISABLE KEYS */;
/*!40000 ALTER TABLE `emailman` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails`
--

DROP TABLE IF EXISTS `emails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_sent` datetime DEFAULT NULL,
  `message_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(25) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `flagged` tinyint(1) DEFAULT '0',
  `reply_to_status` tinyint(1) DEFAULT '0',
  `intent` varchar(25) DEFAULT 'pick',
  `mailbox_id` char(36) DEFAULT NULL,
  `parent_type` varchar(25) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_emails_tmst_id` (`team_set_id`),
  KEY `idx_email_name` (`name`),
  KEY `idx_message_id` (`message_id`),
  KEY `idx_email_parent_id` (`parent_id`),
  KEY `idx_email_assigned` (`assigned_user_id`,`type`,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails`
--

LOCK TABLES `emails` WRITE;
/*!40000 ALTER TABLE `emails` DISABLE KEYS */;
/*!40000 ALTER TABLE `emails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails_beans`
--

DROP TABLE IF EXISTS `emails_beans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails_beans` (
  `id` char(36) NOT NULL,
  `email_id` char(36) DEFAULT NULL,
  `bean_id` char(36) DEFAULT NULL,
  `bean_module` varchar(36) DEFAULT NULL,
  `campaign_data` text,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_emails_beans_bean_id` (`bean_id`),
  KEY `idx_emails_beans_email_bean` (`email_id`,`bean_id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails_beans`
--

LOCK TABLES `emails_beans` WRITE;
/*!40000 ALTER TABLE `emails_beans` DISABLE KEYS */;
/*!40000 ALTER TABLE `emails_beans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails_email_addr_rel`
--

DROP TABLE IF EXISTS `emails_email_addr_rel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails_email_addr_rel` (
  `id` char(36) NOT NULL,
  `email_id` char(36) NOT NULL,
  `address_type` varchar(4) DEFAULT NULL,
  `email_address_id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_eearl_email_id` (`email_id`,`address_type`),
  KEY `idx_eearl_address_id` (`email_address_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails_email_addr_rel`
--

LOCK TABLES `emails_email_addr_rel` WRITE;
/*!40000 ALTER TABLE `emails_email_addr_rel` DISABLE KEYS */;
/*!40000 ALTER TABLE `emails_email_addr_rel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emails_text`
--

DROP TABLE IF EXISTS `emails_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emails_text` (
  `email_id` char(36) NOT NULL,
  `from_addr` varchar(255) DEFAULT NULL,
  `reply_to_addr` varchar(255) DEFAULT NULL,
  `to_addrs` text,
  `cc_addrs` text,
  `bcc_addrs` text,
  `description` longtext,
  `description_html` longtext,
  `raw_source` longtext,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`email_id`),
  KEY `emails_textfromaddr` (`from_addr`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emails_text`
--

LOCK TABLES `emails_text` WRITE;
/*!40000 ALTER TABLE `emails_text` DISABLE KEYS */;
/*!40000 ALTER TABLE `emails_text` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expressions`
--

DROP TABLE IF EXISTS `expressions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expressions` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `lhs_type` varchar(15) DEFAULT NULL,
  `lhs_field` varchar(50) DEFAULT NULL,
  `lhs_module` varchar(50) DEFAULT NULL,
  `lhs_value` varchar(100) DEFAULT NULL,
  `lhs_group_type` varchar(10) DEFAULT NULL,
  `operator` varchar(15) DEFAULT NULL,
  `rhs_group_type` varchar(10) DEFAULT NULL,
  `rhs_type` varchar(15) DEFAULT NULL,
  `rhs_field` varchar(50) DEFAULT NULL,
  `rhs_module` varchar(50) DEFAULT NULL,
  `rhs_value` varchar(255) DEFAULT NULL,
  `parent_id` char(36) NOT NULL,
  `exp_type` varchar(25) DEFAULT NULL,
  `exp_order` int(4) DEFAULT NULL,
  `parent_type` varchar(255) DEFAULT NULL,
  `parent_exp_id` char(36) DEFAULT NULL,
  `parent_exp_side` int(8) DEFAULT NULL,
  `ext1` varchar(50) DEFAULT NULL,
  `ext2` varchar(50) DEFAULT NULL,
  `ext3` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_exp` (`parent_id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expressions`
--

LOCK TABLES `expressions` WRITE;
/*!40000 ALTER TABLE `expressions` DISABLE KEYS */;
/*!40000 ALTER TABLE `expressions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fields_meta_data`
--

DROP TABLE IF EXISTS `fields_meta_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fields_meta_data` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `vname` varchar(255) DEFAULT NULL,
  `comments` varchar(255) DEFAULT NULL,
  `help` varchar(255) DEFAULT NULL,
  `custom_module` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `len` int(11) DEFAULT NULL,
  `required` tinyint(1) DEFAULT '0',
  `default_value` varchar(255) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `audited` tinyint(1) DEFAULT '0',
  `massupdate` tinyint(1) DEFAULT '0',
  `duplicate_merge` smallint(6) DEFAULT '0',
  `reportable` tinyint(1) DEFAULT '1',
  `importable` varchar(255) DEFAULT NULL,
  `ext1` varchar(255) DEFAULT NULL,
  `ext2` varchar(255) DEFAULT NULL,
  `ext3` varchar(255) DEFAULT NULL,
  `ext4` text,
  PRIMARY KEY (`id`),
  KEY `idx_meta_id_del` (`id`,`deleted`),
  KEY `idx_meta_cm_del` (`custom_module`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fields_meta_data`
--

LOCK TABLES `fields_meta_data` WRITE;
/*!40000 ALTER TABLE `fields_meta_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `fields_meta_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `folders`
--

DROP TABLE IF EXISTS `folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `folders` (
  `id` char(36) NOT NULL,
  `name` varchar(25) DEFAULT NULL,
  `folder_type` varchar(25) DEFAULT NULL,
  `parent_folder` char(36) DEFAULT NULL,
  `has_child` tinyint(1) DEFAULT '0',
  `is_group` tinyint(1) DEFAULT '0',
  `is_dynamic` tinyint(1) DEFAULT '0',
  `dynamic_query` text,
  `assign_to_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `created_by` char(36) NOT NULL,
  `modified_by` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_parent_folder` (`parent_folder`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `folders`
--

LOCK TABLES `folders` WRITE;
/*!40000 ALTER TABLE `folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `folders_rel`
--

DROP TABLE IF EXISTS `folders_rel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `folders_rel` (
  `id` char(36) NOT NULL,
  `folder_id` char(36) NOT NULL,
  `polymorphic_module` varchar(25) DEFAULT NULL,
  `polymorphic_id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_poly_module_poly_id` (`polymorphic_module`,`polymorphic_id`),
  KEY `idx_folders_rel_folder_id` (`folder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `folders_rel`
--

LOCK TABLES `folders_rel` WRITE;
/*!40000 ALTER TABLE `folders_rel` DISABLE KEYS */;
/*!40000 ALTER TABLE `folders_rel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `folders_subscriptions`
--

DROP TABLE IF EXISTS `folders_subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `folders_subscriptions` (
  `id` char(36) NOT NULL,
  `folder_id` char(36) NOT NULL,
  `assigned_user_id` char(36) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_folder_id_assigned_user_id` (`folder_id`,`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `folders_subscriptions`
--

LOCK TABLES `folders_subscriptions` WRITE;
/*!40000 ALTER TABLE `folders_subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `folders_subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forecast_schedule`
--

DROP TABLE IF EXISTS `forecast_schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forecast_schedule` (
  `id` char(36) NOT NULL,
  `timeperiod_id` char(36) DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `cascade_hierarchy` tinyint(1) DEFAULT '0',
  `forecast_start_date` date DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forecast_schedule`
--

LOCK TABLES `forecast_schedule` WRITE;
/*!40000 ALTER TABLE `forecast_schedule` DISABLE KEYS */;
/*!40000 ALTER TABLE `forecast_schedule` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forecasts`
--

DROP TABLE IF EXISTS `forecasts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `forecasts` (
  `id` char(36) NOT NULL,
  `timeperiod_id` char(36) DEFAULT NULL,
  `forecast_type` varchar(25) DEFAULT NULL,
  `opp_count` int(5) DEFAULT NULL,
  `opp_weigh_value` int(11) DEFAULT NULL,
  `best_case` int(11) DEFAULT NULL,
  `likely_case` int(11) DEFAULT NULL,
  `worst_case` int(11) DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forecasts`
--

LOCK TABLES `forecasts` WRITE;
/*!40000 ALTER TABLE `forecasts` DISABLE KEYS */;
/*!40000 ALTER TABLE `forecasts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `holidays` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `holiday_date` date DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `person_id` char(36) DEFAULT NULL,
  `person_type` varchar(255) DEFAULT NULL,
  `related_module` varchar(255) DEFAULT NULL,
  `related_module_id` char(36) DEFAULT NULL,
  `resource_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_holiday_id_del` (`id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `import_maps`
--

DROP TABLE IF EXISTS `import_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `import_maps` (
  `id` char(36) NOT NULL,
  `name` varchar(254) DEFAULT NULL,
  `source` varchar(36) DEFAULT NULL,
  `enclosure` varchar(1) DEFAULT ' ',
  `delimiter` varchar(1) DEFAULT ',',
  `module` varchar(36) DEFAULT NULL,
  `content` text,
  `default_values` text,
  `has_header` tinyint(1) DEFAULT '1',
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `is_published` varchar(3) DEFAULT 'no',
  PRIMARY KEY (`id`),
  KEY `idx_owner_module_name` (`assigned_user_id`,`module`,`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `import_maps`
--

LOCK TABLES `import_maps` WRITE;
/*!40000 ALTER TABLE `import_maps` DISABLE KEYS */;
/*!40000 ALTER TABLE `import_maps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inbound_email`
--

DROP TABLE IF EXISTS `inbound_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inbound_email` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` varchar(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `status` varchar(25) DEFAULT 'Active',
  `server_url` varchar(100) DEFAULT NULL,
  `email_user` varchar(100) DEFAULT NULL,
  `email_password` varchar(100) DEFAULT NULL,
  `port` int(5) DEFAULT NULL,
  `service` varchar(50) DEFAULT NULL,
  `mailbox` text,
  `delete_seen` tinyint(1) DEFAULT '0',
  `mailbox_type` varchar(10) DEFAULT NULL,
  `template_id` char(36) DEFAULT NULL,
  `stored_options` text,
  `group_id` char(36) DEFAULT NULL,
  `is_personal` tinyint(1) DEFAULT '0',
  `groupfolder_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_inbound_email_tmst_id` (`team_set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inbound_email`
--

LOCK TABLES `inbound_email` WRITE;
/*!40000 ALTER TABLE `inbound_email` DISABLE KEYS */;
/*!40000 ALTER TABLE `inbound_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inbound_email_autoreply`
--

DROP TABLE IF EXISTS `inbound_email_autoreply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inbound_email_autoreply` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `autoreplied_to` varchar(100) DEFAULT NULL,
  `ie_id` char(36) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ie_autoreplied_to` (`autoreplied_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inbound_email_autoreply`
--

LOCK TABLES `inbound_email_autoreply` WRITE;
/*!40000 ALTER TABLE `inbound_email_autoreply` DISABLE KEYS */;
/*!40000 ALTER TABLE `inbound_email_autoreply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inbound_email_cache_ts`
--

DROP TABLE IF EXISTS `inbound_email_cache_ts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inbound_email_cache_ts` (
  `id` varchar(255) NOT NULL,
  `ie_timestamp` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inbound_email_cache_ts`
--

LOCK TABLES `inbound_email_cache_ts` WRITE;
/*!40000 ALTER TABLE `inbound_email_cache_ts` DISABLE KEYS */;
/*!40000 ALTER TABLE `inbound_email_cache_ts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kbcontents`
--

DROP TABLE IF EXISTS `kbcontents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbcontents` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `kbdocument_body` longtext,
  `document_revision_id` char(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `modified_user_id` char(36) DEFAULT NULL,
  `kb_index` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fts_unique_idx` (`kb_index`),
  KEY `idx_kbcontents_tmst_id` (`team_set_id`),
  FULLTEXT KEY `kbdocument_body` (`kbdocument_body`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kbcontents`
--

LOCK TABLES `kbcontents` WRITE;
/*!40000 ALTER TABLE `kbcontents` DISABLE KEYS */;
/*!40000 ALTER TABLE `kbcontents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kbcontents_audit`
--

DROP TABLE IF EXISTS `kbcontents_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbcontents_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_kbcontents_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kbcontents_audit`
--

LOCK TABLES `kbcontents_audit` WRITE;
/*!40000 ALTER TABLE `kbcontents_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `kbcontents_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kbdocument_revisions`
--

DROP TABLE IF EXISTS `kbdocument_revisions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbdocument_revisions` (
  `id` varchar(36) NOT NULL,
  `change_log` varchar(255) DEFAULT NULL,
  `kbdocument_id` varchar(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `file_ext` varchar(25) DEFAULT NULL,
  `file_mime_type` varchar(100) DEFAULT NULL,
  `revision` varchar(25) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `latest` tinyint(1) DEFAULT '0',
  `kbcontent_id` varchar(36) DEFAULT NULL,
  `document_revision_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_del_latest_kbcontent_id` (`deleted`,`latest`,`kbcontent_id`),
  KEY `idx_cont_id_doc_id` (`kbcontent_id`,`kbdocument_id`),
  KEY `idx_name_rev_id_del` (`document_revision_id`,`kbdocument_id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kbdocument_revisions`
--

LOCK TABLES `kbdocument_revisions` WRITE;
/*!40000 ALTER TABLE `kbdocument_revisions` DISABLE KEYS */;
/*!40000 ALTER TABLE `kbdocument_revisions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kbdocuments`
--

DROP TABLE IF EXISTS `kbdocuments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbdocuments` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` varchar(36) NOT NULL,
  `kbdocument_name` varchar(255) DEFAULT NULL,
  `active_date` date DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `status_id` varchar(25) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `is_external_article` tinyint(1) DEFAULT '0',
  `description` text,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `kbdocument_revision_id` varchar(36) DEFAULT NULL,
  `kbdocument_revision_number` varchar(25) DEFAULT NULL,
  `mail_merge_document` varchar(3) DEFAULT 'off',
  `related_doc_id` char(36) DEFAULT NULL,
  `related_doc_rev_id` char(36) DEFAULT NULL,
  `is_template` tinyint(1) DEFAULT '0',
  `template_type` varchar(25) DEFAULT NULL,
  `kbdoc_approver_id` char(36) DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `parent_type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kbdocuments_tmst_id` (`team_set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kbdocuments`
--

LOCK TABLES `kbdocuments` WRITE;
/*!40000 ALTER TABLE `kbdocuments` DISABLE KEYS */;
/*!40000 ALTER TABLE `kbdocuments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kbdocuments_kbtags`
--

DROP TABLE IF EXISTS `kbdocuments_kbtags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbdocuments_kbtags` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` varchar(36) NOT NULL,
  `kbdocument_id` varchar(36) DEFAULT NULL,
  `kbtag_id` varchar(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `revision` varchar(25) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kbdocuments_kbtags_tmst_id` (`team_set_id`),
  KEY `idx_doc_id_tag_id` (`kbdocument_id`,`kbtag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kbdocuments_kbtags`
--

LOCK TABLES `kbdocuments_kbtags` WRITE;
/*!40000 ALTER TABLE `kbdocuments_kbtags` DISABLE KEYS */;
/*!40000 ALTER TABLE `kbdocuments_kbtags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kbdocuments_views_ratings`
--

DROP TABLE IF EXISTS `kbdocuments_views_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbdocuments_views_ratings` (
  `id` varchar(36) NOT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `kbdocument_id` varchar(36) DEFAULT NULL,
  `views_number` int(11) DEFAULT '0',
  `ratings_number` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_kbvr_kbdoc` (`kbdocument_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kbdocuments_views_ratings`
--

LOCK TABLES `kbdocuments_views_ratings` WRITE;
/*!40000 ALTER TABLE `kbdocuments_views_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `kbdocuments_views_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kbtags`
--

DROP TABLE IF EXISTS `kbtags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kbtags` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` varchar(36) NOT NULL,
  `parent_tag_id` varchar(36) DEFAULT NULL,
  `tag_name` varchar(255) DEFAULT NULL,
  `root_tag` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `revision` varchar(25) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kbtags_tmst_id` (`team_set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kbtags`
--

LOCK TABLES `kbtags` WRITE;
/*!40000 ALTER TABLE `kbtags` DISABLE KEYS */;
INSERT INTO `kbtags` VALUES (NULL,NULL,'FAQs',NULL,'FAQs',0,NULL,NULL,NULL,0,NULL);
/*!40000 ALTER TABLE `kbtags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads`
--

DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `salutation` varchar(5) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `do_not_call` tinyint(1) DEFAULT '0',
  `phone_home` varchar(25) DEFAULT NULL,
  `phone_mobile` varchar(25) DEFAULT NULL,
  `phone_work` varchar(25) DEFAULT NULL,
  `phone_other` varchar(25) DEFAULT NULL,
  `phone_fax` varchar(25) DEFAULT NULL,
  `primary_address_street` varchar(150) DEFAULT NULL,
  `primary_address_city` varchar(100) DEFAULT NULL,
  `primary_address_state` varchar(100) DEFAULT NULL,
  `primary_address_postalcode` varchar(20) DEFAULT NULL,
  `primary_address_country` varchar(255) DEFAULT NULL,
  `alt_address_street` varchar(150) DEFAULT NULL,
  `alt_address_city` varchar(100) DEFAULT NULL,
  `alt_address_state` varchar(100) DEFAULT NULL,
  `alt_address_postalcode` varchar(20) DEFAULT NULL,
  `alt_address_country` varchar(255) DEFAULT NULL,
  `assistant` varchar(75) DEFAULT NULL,
  `assistant_phone` varchar(25) DEFAULT NULL,
  `converted` tinyint(1) DEFAULT '0',
  `refered_by` varchar(100) DEFAULT NULL,
  `lead_source` varchar(100) DEFAULT NULL,
  `lead_source_description` text,
  `status` varchar(100) DEFAULT NULL,
  `status_description` text,
  `reports_to_id` char(36) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_description` text,
  `contact_id` char(36) DEFAULT NULL,
  `account_id` char(36) DEFAULT NULL,
  `opportunity_id` char(36) DEFAULT NULL,
  `opportunity_name` varchar(255) DEFAULT NULL,
  `opportunity_amount` varchar(50) DEFAULT NULL,
  `campaign_id` char(36) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `portal_name` varchar(255) DEFAULT NULL,
  `portal_app` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_leads_tmst_id` (`team_set_id`),
  KEY `idx_lead_acct_name_first` (`account_name`,`deleted`),
  KEY `idx_lead_last_first` (`last_name`,`first_name`,`deleted`),
  KEY `idx_lead_del_stat` (`last_name`,`status`,`deleted`,`first_name`),
  KEY `idx_lead_opp_del` (`opportunity_id`,`deleted`),
  KEY `idx_leads_acct_del` (`account_id`,`deleted`),
  KEY `idx_del_user` (`deleted`,`assigned_user_id`),
  KEY `idx_lead_assigned` (`assigned_user_id`),
  KEY `idx_lead_contact` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads`
--

LOCK TABLES `leads` WRITE;
/*!40000 ALTER TABLE `leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leads_audit`
--

DROP TABLE IF EXISTS `leads_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leads_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_leads_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leads_audit`
--

LOCK TABLES `leads_audit` WRITE;
/*!40000 ALTER TABLE `leads_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `leads_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `linked_documents`
--

DROP TABLE IF EXISTS `linked_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `linked_documents` (
  `id` varchar(36) NOT NULL,
  `parent_id` varchar(36) DEFAULT NULL,
  `parent_type` varchar(25) DEFAULT NULL,
  `document_id` varchar(36) DEFAULT NULL,
  `document_revision_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_parent_document` (`parent_type`,`parent_id`,`document_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `linked_documents`
--

LOCK TABLES `linked_documents` WRITE;
/*!40000 ALTER TABLE `linked_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `linked_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `manufacturers`
--

DROP TABLE IF EXISTS `manufacturers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `manufacturers` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `list_order` int(4) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_manufacturers` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `manufacturers`
--

LOCK TABLES `manufacturers` WRITE;
/*!40000 ALTER TABLE `manufacturers` DISABLE KEYS */;
/*!40000 ALTER TABLE `manufacturers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meetings`
--

DROP TABLE IF EXISTS `meetings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meetings` (
  `id` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `duration_hours` int(2) DEFAULT NULL,
  `duration_minutes` int(2) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `parent_type` varchar(25) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `reminder_time` int(11) DEFAULT '-1',
  `outlook_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_meetings_tmst_id` (`team_set_id`),
  KEY `idx_mtg_name` (`name`),
  KEY `idx_meet_par_del` (`parent_id`,`parent_type`,`deleted`),
  KEY `idx_meet_stat_del` (`assigned_user_id`,`status`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meetings`
--

LOCK TABLES `meetings` WRITE;
/*!40000 ALTER TABLE `meetings` DISABLE KEYS */;
/*!40000 ALTER TABLE `meetings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meetings_contacts`
--

DROP TABLE IF EXISTS `meetings_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meetings_contacts` (
  `id` varchar(36) NOT NULL,
  `meeting_id` varchar(36) DEFAULT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `required` varchar(1) DEFAULT '1',
  `accept_status` varchar(25) DEFAULT 'none',
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_con_mtg_mtg` (`meeting_id`),
  KEY `idx_con_mtg_con` (`contact_id`),
  KEY `idx_meeting_contact` (`meeting_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meetings_contacts`
--

LOCK TABLES `meetings_contacts` WRITE;
/*!40000 ALTER TABLE `meetings_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `meetings_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meetings_leads`
--

DROP TABLE IF EXISTS `meetings_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meetings_leads` (
  `id` varchar(36) NOT NULL,
  `meeting_id` varchar(36) DEFAULT NULL,
  `lead_id` varchar(36) DEFAULT NULL,
  `required` varchar(1) DEFAULT '1',
  `accept_status` varchar(25) DEFAULT 'none',
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_lead_meeting_meeting` (`meeting_id`),
  KEY `idx_lead_meeting_lead` (`lead_id`),
  KEY `idx_meeting_lead` (`meeting_id`,`lead_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meetings_leads`
--

LOCK TABLES `meetings_leads` WRITE;
/*!40000 ALTER TABLE `meetings_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `meetings_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `meetings_users`
--

DROP TABLE IF EXISTS `meetings_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meetings_users` (
  `id` varchar(36) NOT NULL,
  `meeting_id` varchar(36) DEFAULT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `required` varchar(1) DEFAULT '1',
  `accept_status` varchar(25) DEFAULT 'none',
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_usr_mtg_mtg` (`meeting_id`),
  KEY `idx_usr_mtg_usr` (`user_id`),
  KEY `idx_meeting_users` (`meeting_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `meetings_users`
--

LOCK TABLES `meetings_users` WRITE;
/*!40000 ALTER TABLE `meetings_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `meetings_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `file_mime_type` varchar(100) DEFAULT NULL,
  `parent_type` varchar(255) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `portal_flag` tinyint(1) DEFAULT '0',
  `embed_flag` tinyint(1) DEFAULT '0',
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_notes_tmst_id` (`team_set_id`),
  KEY `idx_note_name` (`name`),
  KEY `idx_notes_parent` (`parent_id`,`parent_type`),
  KEY `idx_note_contact` (`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunities`
--

DROP TABLE IF EXISTS `opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunities` (
  `id` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `opportunity_type` varchar(255) DEFAULT NULL,
  `campaign_id` char(36) DEFAULT NULL,
  `lead_source` varchar(50) DEFAULT NULL,
  `amount` double DEFAULT NULL,
  `amount_usdollar` double DEFAULT NULL,
  `currency_id` char(36) DEFAULT NULL,
  `date_closed` date DEFAULT NULL,
  `next_step` varchar(100) DEFAULT NULL,
  `sales_stage` varchar(255) DEFAULT NULL,
  `probability` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_opportunities_tmst_id` (`team_set_id`),
  KEY `idx_opp_name` (`name`),
  KEY `idx_opp_assigned` (`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunities`
--

LOCK TABLES `opportunities` WRITE;
/*!40000 ALTER TABLE `opportunities` DISABLE KEYS */;
/*!40000 ALTER TABLE `opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunities_audit`
--

DROP TABLE IF EXISTS `opportunities_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunities_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_opportunities_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunities_audit`
--

LOCK TABLES `opportunities_audit` WRITE;
/*!40000 ALTER TABLE `opportunities_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `opportunities_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `opportunities_contacts`
--

DROP TABLE IF EXISTS `opportunities_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `opportunities_contacts` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `opportunity_id` varchar(36) DEFAULT NULL,
  `contact_role` varchar(50) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_con_opp_con` (`contact_id`),
  KEY `idx_con_opp_opp` (`opportunity_id`),
  KEY `idx_opportunities_contacts` (`opportunity_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opportunities_contacts`
--

LOCK TABLES `opportunities_contacts` WRITE;
/*!40000 ALTER TABLE `opportunities_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `opportunities_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `outbound_email`
--

DROP TABLE IF EXISTS `outbound_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `outbound_email` (
  `id` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `type` varchar(15) DEFAULT 'user',
  `user_id` char(36) NOT NULL,
  `mail_sendtype` varchar(8) DEFAULT 'smtp',
  `mail_smtptype` varchar(20) DEFAULT 'other',
  `mail_smtpserver` varchar(100) DEFAULT NULL,
  `mail_smtpport` int(5) DEFAULT '0',
  `mail_smtpuser` varchar(100) DEFAULT NULL,
  `mail_smtppass` varchar(100) DEFAULT NULL,
  `mail_smtpauth_req` tinyint(1) DEFAULT '0',
  `mail_smtpssl` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `oe_user_id_idx` (`id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `outbound_email`
--

LOCK TABLES `outbound_email` WRITE;
/*!40000 ALTER TABLE `outbound_email` DISABLE KEYS */;
INSERT INTO `outbound_email` VALUES ('e4046b4a-1677-93a8-a1c3-4c218de5a0e2','system','system','1','SMTP','other','',25,'','',1,0);
/*!40000 ALTER TABLE `outbound_email` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_bundle_note`
--

DROP TABLE IF EXISTS `product_bundle_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_bundle_note` (
  `id` varchar(36) NOT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `bundle_id` varchar(36) DEFAULT NULL,
  `note_id` varchar(36) DEFAULT NULL,
  `note_index` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pbn_bundle` (`bundle_id`),
  KEY `idx_pbn_note` (`note_id`),
  KEY `idx_pbn_pb_nb` (`note_id`,`bundle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_bundle_note`
--

LOCK TABLES `product_bundle_note` WRITE;
/*!40000 ALTER TABLE `product_bundle_note` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_bundle_note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_bundle_notes`
--

DROP TABLE IF EXISTS `product_bundle_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_bundle_notes` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_bundle_notes`
--

LOCK TABLES `product_bundle_notes` WRITE;
/*!40000 ALTER TABLE `product_bundle_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_bundle_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_bundle_product`
--

DROP TABLE IF EXISTS `product_bundle_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_bundle_product` (
  `id` varchar(36) NOT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `bundle_id` varchar(36) DEFAULT NULL,
  `product_id` varchar(36) DEFAULT NULL,
  `product_index` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pbp_bundle` (`bundle_id`),
  KEY `idx_pbp_quote` (`product_id`),
  KEY `idx_pbp_bq` (`product_id`,`bundle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_bundle_product`
--

LOCK TABLES `product_bundle_product` WRITE;
/*!40000 ALTER TABLE `product_bundle_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_bundle_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_bundle_quote`
--

DROP TABLE IF EXISTS `product_bundle_quote`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_bundle_quote` (
  `id` varchar(36) NOT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `bundle_id` varchar(36) DEFAULT NULL,
  `quote_id` varchar(36) DEFAULT NULL,
  `bundle_index` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pbq_bundle` (`bundle_id`),
  KEY `idx_pbq_quote` (`quote_id`),
  KEY `idx_pbq_bq` (`quote_id`,`bundle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_bundle_quote`
--

LOCK TABLES `product_bundle_quote` WRITE;
/*!40000 ALTER TABLE `product_bundle_quote` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_bundle_quote` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_bundles`
--

DROP TABLE IF EXISTS `product_bundles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_bundles` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `bundle_stage` varchar(255) DEFAULT NULL,
  `description` text,
  `tax` decimal(26,6) DEFAULT NULL,
  `tax_usdollar` decimal(26,6) DEFAULT NULL,
  `total` decimal(26,6) DEFAULT NULL,
  `total_usdollar` decimal(26,6) DEFAULT NULL,
  `subtotal_usdollar` decimal(26,6) DEFAULT NULL,
  `shipping_usdollar` decimal(26,6) DEFAULT NULL,
  `deal_tot` decimal(26,2) DEFAULT NULL,
  `deal_tot_usdollar` decimal(26,2) DEFAULT NULL,
  `new_sub` decimal(26,6) DEFAULT NULL,
  `new_sub_usdollar` decimal(26,6) DEFAULT NULL,
  `subtotal` decimal(26,6) DEFAULT NULL,
  `shipping` decimal(26,6) DEFAULT NULL,
  `currency_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_bundles_tmst_id` (`team_set_id`),
  KEY `idx_products_bundles` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_bundles`
--

LOCK TABLES `product_bundles` WRITE;
/*!40000 ALTER TABLE `product_bundles` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_bundles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_categories` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `list_order` int(4) DEFAULT NULL,
  `description` text,
  `parent_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_productcategories` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_product`
--

DROP TABLE IF EXISTS `product_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_product` (
  `id` varchar(36) NOT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `parent_id` varchar(36) DEFAULT NULL,
  `child_id` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pp_parent` (`parent_id`),
  KEY `idx_pp_child` (`child_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_product`
--

LOCK TABLES `product_product` WRITE;
/*!40000 ALTER TABLE `product_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_templates`
--

DROP TABLE IF EXISTS `product_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_templates` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `type_id` char(36) DEFAULT NULL,
  `manufacturer_id` char(36) DEFAULT NULL,
  `category_id` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `mft_part_num` varchar(50) DEFAULT NULL,
  `vendor_part_num` varchar(50) DEFAULT NULL,
  `date_cost_price` date DEFAULT NULL,
  `cost_price` decimal(26,6) DEFAULT NULL,
  `discount_price` decimal(26,6) DEFAULT NULL,
  `list_price` decimal(26,6) DEFAULT NULL,
  `cost_usdollar` decimal(26,6) DEFAULT NULL,
  `discount_usdollar` decimal(26,6) DEFAULT NULL,
  `list_usdollar` decimal(26,6) DEFAULT NULL,
  `currency_id` char(36) DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `tax_class` varchar(25) DEFAULT NULL,
  `date_available` date DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `weight` decimal(12,2) DEFAULT NULL,
  `qty_in_stock` int(5) DEFAULT NULL,
  `description` text,
  `support_name` varchar(50) DEFAULT NULL,
  `support_description` varchar(255) DEFAULT NULL,
  `support_contact` varchar(50) DEFAULT NULL,
  `support_term` varchar(25) DEFAULT NULL,
  `pricing_formula` varchar(25) DEFAULT NULL,
  `pricing_factor` decimal(8,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_template` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_templates`
--

LOCK TABLES `product_templates` WRITE;
/*!40000 ALTER TABLE `product_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_types`
--

DROP TABLE IF EXISTS `product_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_types` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `list_order` int(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_producttypes` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_types`
--

LOCK TABLES `product_types` WRITE;
/*!40000 ALTER TABLE `product_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `product_template_id` char(36) DEFAULT NULL,
  `account_id` char(36) DEFAULT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `type_id` char(36) DEFAULT NULL,
  `quote_id` char(36) DEFAULT NULL,
  `manufacturer_id` char(36) DEFAULT NULL,
  `category_id` char(36) DEFAULT NULL,
  `mft_part_num` varchar(50) DEFAULT NULL,
  `vendor_part_num` varchar(50) DEFAULT NULL,
  `date_purchased` date DEFAULT NULL,
  `cost_price` decimal(26,6) DEFAULT NULL,
  `discount_price` decimal(26,6) DEFAULT NULL,
  `discount_amount` decimal(26,6) DEFAULT NULL,
  `discount_amount_usdollar` decimal(26,6) DEFAULT NULL,
  `discount_select` tinyint(1) DEFAULT '0',
  `deal_calc` decimal(26,6) DEFAULT NULL,
  `deal_calc_usdollar` decimal(26,6) DEFAULT NULL,
  `list_price` decimal(26,6) DEFAULT NULL,
  `cost_usdollar` decimal(26,6) DEFAULT NULL,
  `discount_usdollar` decimal(26,6) DEFAULT NULL,
  `list_usdollar` decimal(26,6) DEFAULT NULL,
  `currency_id` char(36) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `tax_class` varchar(25) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `weight` decimal(12,2) DEFAULT NULL,
  `quantity` int(5) DEFAULT NULL,
  `support_name` varchar(50) DEFAULT NULL,
  `support_description` varchar(255) DEFAULT NULL,
  `support_contact` varchar(50) DEFAULT NULL,
  `support_term` varchar(25) DEFAULT NULL,
  `date_support_expires` date DEFAULT NULL,
  `date_support_starts` date DEFAULT NULL,
  `pricing_formula` varchar(25) DEFAULT NULL,
  `pricing_factor` int(4) DEFAULT NULL,
  `serial_number` varchar(50) DEFAULT NULL,
  `asset_number` varchar(50) DEFAULT NULL,
  `book_value` decimal(26,6) DEFAULT NULL,
  `book_value_usdollar` decimal(26,6) DEFAULT NULL,
  `book_value_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_products_tmst_id` (`team_set_id`),
  KEY `idx_products` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products_audit`
--

DROP TABLE IF EXISTS `products_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_products_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products_audit`
--

LOCK TABLES `products_audit` WRITE;
/*!40000 ALTER TABLE `products_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `products_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project`
--

DROP TABLE IF EXISTS `project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `estimated_start_date` date DEFAULT NULL,
  `estimated_end_date` date DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `priority` varchar(255) DEFAULT NULL,
  `is_template` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_project_tmst_id` (`team_set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project`
--

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;
/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_resources`
--

DROP TABLE IF EXISTS `project_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_resources` (
  `id` char(36) NOT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `project_id` char(36) DEFAULT NULL,
  `resource_id` char(36) DEFAULT NULL,
  `resource_type` varchar(20) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_resources`
--

LOCK TABLES `project_resources` WRITE;
/*!40000 ALTER TABLE `project_resources` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_task`
--

DROP TABLE IF EXISTS `project_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_task` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `project_id` char(36) NOT NULL,
  `project_task_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `description` text,
  `resource_id` text,
  `predecessors` text,
  `date_start` date DEFAULT NULL,
  `time_start` int(11) DEFAULT NULL,
  `time_finish` int(11) DEFAULT NULL,
  `date_finish` date DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `duration_unit` text,
  `actual_duration` int(11) DEFAULT NULL,
  `percent_complete` int(11) DEFAULT NULL,
  `parent_task_id` int(11) DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `priority` varchar(255) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `milestone_flag` tinyint(1) DEFAULT '0',
  `order_number` int(11) DEFAULT '1',
  `task_number` int(11) DEFAULT NULL,
  `estimated_effort` int(11) DEFAULT NULL,
  `actual_effort` int(11) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `utilization` int(11) DEFAULT '100',
  PRIMARY KEY (`id`),
  KEY `idx_project_task_tmst_id` (`team_set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_task`
--

LOCK TABLES `project_task` WRITE;
/*!40000 ALTER TABLE `project_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_task_audit`
--

DROP TABLE IF EXISTS `project_task_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `project_task_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_project_task_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_task_audit`
--

LOCK TABLES `project_task_audit` WRITE;
/*!40000 ALTER TABLE `project_task_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_task_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects_accounts`
--

DROP TABLE IF EXISTS `projects_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects_accounts` (
  `id` varchar(36) NOT NULL,
  `account_id` varchar(36) DEFAULT NULL,
  `project_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_proj_acct_proj` (`project_id`),
  KEY `idx_proj_acct_acct` (`account_id`),
  KEY `projects_accounts_alt` (`project_id`,`account_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects_accounts`
--

LOCK TABLES `projects_accounts` WRITE;
/*!40000 ALTER TABLE `projects_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects_bugs`
--

DROP TABLE IF EXISTS `projects_bugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects_bugs` (
  `id` varchar(36) NOT NULL,
  `bug_id` varchar(36) DEFAULT NULL,
  `project_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_proj_bug_proj` (`project_id`),
  KEY `idx_proj_bug_bug` (`bug_id`),
  KEY `projects_bugs_alt` (`project_id`,`bug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects_bugs`
--

LOCK TABLES `projects_bugs` WRITE;
/*!40000 ALTER TABLE `projects_bugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects_bugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects_cases`
--

DROP TABLE IF EXISTS `projects_cases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects_cases` (
  `id` varchar(36) NOT NULL,
  `case_id` varchar(36) DEFAULT NULL,
  `project_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_proj_case_proj` (`project_id`),
  KEY `idx_proj_case_case` (`case_id`),
  KEY `projects_cases_alt` (`project_id`,`case_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects_cases`
--

LOCK TABLES `projects_cases` WRITE;
/*!40000 ALTER TABLE `projects_cases` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects_cases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects_contacts`
--

DROP TABLE IF EXISTS `projects_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects_contacts` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `project_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_proj_con_proj` (`project_id`),
  KEY `idx_proj_con_con` (`contact_id`),
  KEY `projects_contacts_alt` (`project_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects_contacts`
--

LOCK TABLES `projects_contacts` WRITE;
/*!40000 ALTER TABLE `projects_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects_opportunities`
--

DROP TABLE IF EXISTS `projects_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects_opportunities` (
  `id` varchar(36) NOT NULL,
  `opportunity_id` varchar(36) DEFAULT NULL,
  `project_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_proj_opp_proj` (`project_id`),
  KEY `idx_proj_opp_opp` (`opportunity_id`),
  KEY `projects_opportunities_alt` (`project_id`,`opportunity_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects_opportunities`
--

LOCK TABLES `projects_opportunities` WRITE;
/*!40000 ALTER TABLE `projects_opportunities` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects_opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects_products`
--

DROP TABLE IF EXISTS `projects_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects_products` (
  `id` varchar(36) NOT NULL,
  `product_id` varchar(36) DEFAULT NULL,
  `project_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_proj_prod_project` (`project_id`),
  KEY `idx_proj_prod_product` (`product_id`),
  KEY `projects_products_alt` (`project_id`,`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects_products`
--

LOCK TABLES `projects_products` WRITE;
/*!40000 ALTER TABLE `projects_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects_quotes`
--

DROP TABLE IF EXISTS `projects_quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `projects_quotes` (
  `id` varchar(36) NOT NULL,
  `quote_id` varchar(36) DEFAULT NULL,
  `project_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_proj_quote_proj` (`project_id`),
  KEY `idx_proj_quote_quote` (`quote_id`),
  KEY `projects_quotes_alt` (`project_id`,`quote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects_quotes`
--

LOCK TABLES `projects_quotes` WRITE;
/*!40000 ALTER TABLE `projects_quotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `projects_quotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prospect_list_campaigns`
--

DROP TABLE IF EXISTS `prospect_list_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prospect_list_campaigns` (
  `id` varchar(36) NOT NULL,
  `prospect_list_id` varchar(36) DEFAULT NULL,
  `campaign_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pro_id` (`prospect_list_id`),
  KEY `idx_cam_id` (`campaign_id`),
  KEY `idx_prospect_list_campaigns` (`prospect_list_id`,`campaign_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prospect_list_campaigns`
--

LOCK TABLES `prospect_list_campaigns` WRITE;
/*!40000 ALTER TABLE `prospect_list_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `prospect_list_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prospect_lists`
--

DROP TABLE IF EXISTS `prospect_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prospect_lists` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `list_type` varchar(25) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `description` text,
  `domain_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_prospect_lists_tmst_id` (`team_set_id`),
  KEY `idx_prospect_list_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prospect_lists`
--

LOCK TABLES `prospect_lists` WRITE;
/*!40000 ALTER TABLE `prospect_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `prospect_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prospect_lists_prospects`
--

DROP TABLE IF EXISTS `prospect_lists_prospects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prospect_lists_prospects` (
  `id` varchar(36) NOT NULL,
  `prospect_list_id` varchar(36) DEFAULT NULL,
  `related_id` varchar(36) DEFAULT NULL,
  `related_type` varchar(25) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_plp_pro_id` (`prospect_list_id`),
  KEY `idx_plp_rel_id` (`related_id`,`related_type`,`prospect_list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prospect_lists_prospects`
--

LOCK TABLES `prospect_lists_prospects` WRITE;
/*!40000 ALTER TABLE `prospect_lists_prospects` DISABLE KEYS */;
/*!40000 ALTER TABLE `prospect_lists_prospects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prospects`
--

DROP TABLE IF EXISTS `prospects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prospects` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `salutation` varchar(5) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `do_not_call` tinyint(1) DEFAULT '0',
  `phone_home` varchar(25) DEFAULT NULL,
  `phone_mobile` varchar(25) DEFAULT NULL,
  `phone_work` varchar(25) DEFAULT NULL,
  `phone_other` varchar(25) DEFAULT NULL,
  `phone_fax` varchar(25) DEFAULT NULL,
  `primary_address_street` varchar(150) DEFAULT NULL,
  `primary_address_city` varchar(100) DEFAULT NULL,
  `primary_address_state` varchar(100) DEFAULT NULL,
  `primary_address_postalcode` varchar(20) DEFAULT NULL,
  `primary_address_country` varchar(255) DEFAULT NULL,
  `alt_address_street` varchar(150) DEFAULT NULL,
  `alt_address_city` varchar(100) DEFAULT NULL,
  `alt_address_state` varchar(100) DEFAULT NULL,
  `alt_address_postalcode` varchar(20) DEFAULT NULL,
  `alt_address_country` varchar(255) DEFAULT NULL,
  `assistant` varchar(75) DEFAULT NULL,
  `assistant_phone` varchar(25) DEFAULT NULL,
  `tracker_key` int(11) NOT NULL AUTO_INCREMENT,
  `birthdate` date DEFAULT NULL,
  `lead_id` char(36) DEFAULT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `campaign_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_prospects_tmst_id` (`team_set_id`),
  KEY `prospect_auto_tracker_key` (`tracker_key`),
  KEY `idx_prospects_last_first` (`last_name`,`first_name`,`deleted`),
  KEY `idx_prospecs_del_last` (`last_name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prospects`
--

LOCK TABLES `prospects` WRITE;
/*!40000 ALTER TABLE `prospects` DISABLE KEYS */;
/*!40000 ALTER TABLE `prospects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotas`
--

DROP TABLE IF EXISTS `quotas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotas` (
  `id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `timeperiod_id` char(36) NOT NULL,
  `quota_type` varchar(25) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `amount_base_currency` int(11) DEFAULT NULL,
  `currency_id` char(36) NOT NULL,
  `committed` tinyint(1) DEFAULT '0',
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotas`
--

LOCK TABLES `quotas` WRITE;
/*!40000 ALTER TABLE `quotas` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotes`
--

DROP TABLE IF EXISTS `quotes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotes` (
  `id` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `shipper_id` char(36) DEFAULT NULL,
  `currency_id` char(36) DEFAULT NULL,
  `taxrate_id` char(36) DEFAULT NULL,
  `show_line_nums` tinyint(1) DEFAULT '1',
  `calc_grand_total` tinyint(1) DEFAULT '1',
  `quote_type` varchar(25) DEFAULT NULL,
  `date_quote_expected_closed` date DEFAULT NULL,
  `original_po_date` date DEFAULT NULL,
  `payment_terms` varchar(128) DEFAULT NULL,
  `date_quote_closed` date DEFAULT NULL,
  `date_order_shipped` date DEFAULT NULL,
  `order_stage` varchar(25) DEFAULT NULL,
  `quote_stage` varchar(25) DEFAULT NULL,
  `purchase_order_num` varchar(50) DEFAULT NULL,
  `quote_num` int(11) NOT NULL AUTO_INCREMENT,
  `subtotal` decimal(26,6) DEFAULT NULL,
  `subtotal_usdollar` decimal(26,6) DEFAULT NULL,
  `shipping` decimal(26,6) DEFAULT NULL,
  `shipping_usdollar` decimal(26,6) DEFAULT NULL,
  `discount` decimal(26,6) DEFAULT NULL,
  `deal_tot` decimal(26,2) DEFAULT NULL,
  `deal_tot_usdollar` decimal(26,2) DEFAULT NULL,
  `new_sub` decimal(26,6) DEFAULT NULL,
  `new_sub_usdollar` decimal(26,6) DEFAULT NULL,
  `tax` decimal(26,6) DEFAULT NULL,
  `tax_usdollar` decimal(26,6) DEFAULT NULL,
  `total` decimal(26,6) DEFAULT NULL,
  `total_usdollar` decimal(26,6) DEFAULT NULL,
  `billing_address_street` varchar(150) DEFAULT NULL,
  `billing_address_city` varchar(100) DEFAULT NULL,
  `billing_address_state` varchar(100) DEFAULT NULL,
  `billing_address_postalcode` varchar(20) DEFAULT NULL,
  `billing_address_country` varchar(100) DEFAULT NULL,
  `shipping_address_street` varchar(150) DEFAULT NULL,
  `shipping_address_city` varchar(100) DEFAULT NULL,
  `shipping_address_state` varchar(100) DEFAULT NULL,
  `shipping_address_postalcode` varchar(20) DEFAULT NULL,
  `shipping_address_country` varchar(100) DEFAULT NULL,
  `system_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quote_num` (`quote_num`,`system_id`),
  KEY `idx_quotes_tmst_id` (`team_set_id`),
  KEY `idx_qte_name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotes`
--

LOCK TABLES `quotes` WRITE;
/*!40000 ALTER TABLE `quotes` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotes_accounts`
--

DROP TABLE IF EXISTS `quotes_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotes_accounts` (
  `id` varchar(36) NOT NULL,
  `quote_id` varchar(36) DEFAULT NULL,
  `account_id` varchar(36) DEFAULT NULL,
  `account_role` varchar(20) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_acc_qte_acc` (`account_id`),
  KEY `idx_acc_qte_opp` (`quote_id`),
  KEY `idx_quote_account_role` (`quote_id`,`account_role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotes_accounts`
--

LOCK TABLES `quotes_accounts` WRITE;
/*!40000 ALTER TABLE `quotes_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotes_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotes_audit`
--

DROP TABLE IF EXISTS `quotes_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotes_audit` (
  `id` char(36) NOT NULL,
  `parent_id` char(36) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `data_type` varchar(100) DEFAULT NULL,
  `before_value_string` varchar(255) DEFAULT NULL,
  `after_value_string` varchar(255) DEFAULT NULL,
  `before_value_text` text,
  `after_value_text` text,
  KEY `idx_quotes_parent_id` (`parent_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotes_audit`
--

LOCK TABLES `quotes_audit` WRITE;
/*!40000 ALTER TABLE `quotes_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotes_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotes_contacts`
--

DROP TABLE IF EXISTS `quotes_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotes_contacts` (
  `id` varchar(36) NOT NULL,
  `contact_id` varchar(36) DEFAULT NULL,
  `quote_id` varchar(36) DEFAULT NULL,
  `contact_role` varchar(20) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_con_qte_con` (`contact_id`),
  KEY `idx_con_qte_opp` (`quote_id`),
  KEY `idx_quote_contact_role` (`quote_id`,`contact_role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotes_contacts`
--

LOCK TABLES `quotes_contacts` WRITE;
/*!40000 ALTER TABLE `quotes_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotes_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotes_opportunities`
--

DROP TABLE IF EXISTS `quotes_opportunities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `quotes_opportunities` (
  `id` varchar(36) NOT NULL,
  `opportunity_id` varchar(36) DEFAULT NULL,
  `quote_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_opp_qte_opp` (`opportunity_id`),
  KEY `idx_quote_oportunities` (`quote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotes_opportunities`
--

LOCK TABLES `quotes_opportunities` WRITE;
/*!40000 ALTER TABLE `quotes_opportunities` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotes_opportunities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `relationships`
--

DROP TABLE IF EXISTS `relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `relationships` (
  `id` char(36) NOT NULL,
  `relationship_name` varchar(150) DEFAULT NULL,
  `lhs_module` varchar(100) DEFAULT NULL,
  `lhs_table` varchar(64) DEFAULT NULL,
  `lhs_key` varchar(64) DEFAULT NULL,
  `rhs_module` varchar(100) DEFAULT NULL,
  `rhs_table` varchar(64) DEFAULT NULL,
  `rhs_key` varchar(64) DEFAULT NULL,
  `join_table` varchar(64) DEFAULT NULL,
  `join_key_lhs` varchar(64) DEFAULT NULL,
  `join_key_rhs` varchar(64) DEFAULT NULL,
  `relationship_type` varchar(64) DEFAULT NULL,
  `relationship_role_column` varchar(64) DEFAULT NULL,
  `relationship_role_column_value` varchar(50) DEFAULT NULL,
  `reverse` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_rel_name` (`relationship_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `relationships`
--

LOCK TABLES `relationships` WRITE;
/*!40000 ALTER TABLE `relationships` DISABLE KEYS */;
INSERT INTO `relationships` VALUES ('b75da1c9-54ca-2fe7-e7af-4c218d6f776c','leads_modified_user','Users','users','id','Leads','leads','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b791c69c-642d-7605-f8a6-4c218d643054','leads_created_by','Users','users','id','Leads','leads','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b7c1514f-da2f-f3ef-fc79-4c218dab5296','leads_assigned_user','Users','users','id','Leads','leads','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b7efa415-74e9-d2be-d4a5-4c218dc3a746','leads_team_count_relationship','Teams','team_sets','id','Leads','leads','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b81dc4c8-7dc7-a83f-da58-4c218d5c5abb','leads_teams','Leads','leads','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('b84c909e-a563-cd5b-c3bf-4c218d65a78c','leads_team','Teams','teams','id','Leads','leads','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b87a7299-165d-1a04-b4c4-4c218d1ffd57','leads_email_addresses','Leads','leads','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','bean_module','Leads',0,0),('b93da903-990b-65cd-6175-4c218d3bd450','leads_email_addresses_primary','Leads','leads','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','primary_address','1',0,0),('b968d2ca-c909-1cdb-e955-4c218d13aa48','lead_direct_reports','Leads','leads','id','Leads','leads','reports_to_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b9901ba8-a1c1-0ab5-8f76-4c218dce930e','lead_tasks','Leads','leads','id','Tasks','tasks','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Leads',0,0),('b9c06c7e-8b91-52b5-f7e6-4c218d4c8e63','lead_notes','Leads','leads','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Leads',0,0),('b9f99e91-155a-bd1c-1c67-4c218d58b71d','lead_meetings','Leads','leads','id','Meetings','meetings','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Leads',0,0),('ba1ce74b-c7fd-ceb2-925b-4c218d4b2475','lead_calls','Leads','leads','id','Calls','calls','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Leads',0,0),('ba3a76b4-e4d5-929c-4155-4c218dc9aff8','lead_emails','Leads','leads','id','Emails','emails','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Leads',0,0),('ba591f29-05ab-7a88-63c7-4c218d2a797c','lead_campaign_log','Leads','leads','id','CampaignLog','campaign_log','target_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('29670019-daf6-fff8-0666-4c218d9881e4','cases_modified_user','Users','users','id','Cases','cases','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2c8d0064-3c37-5205-3c7d-4c218d219fa5','cases_created_by','Users','users','id','Cases','cases','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2f7400be-3e5b-c4f9-edfc-4c218d94fc2c','cases_assigned_user','Users','users','id','Cases','cases','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('32ae0c60-1c08-0f0a-a175-4c218d3283fc','cases_team_count_relationship','Teams','team_sets','id','Cases','cases','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('35d00c3e-2ff1-a23b-5081-4c218da132c0','cases_teams','Cases','cases','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('38d105e9-3ce0-4a4b-dceb-4c218d5ab2cc','cases_team','Teams','teams','id','Cases','cases','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('3bc20905-42d3-9a28-c4e9-4c218d8e94ce','case_calls','Cases','cases','id','Calls','calls','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Cases',0,0),('3eba0116-4ffc-46f6-5ba8-4c218da6caca','case_tasks','Cases','cases','id','Tasks','tasks','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Cases',0,0),('49260c01-cffc-ecbb-1255-4c218d223152','case_notes','Cases','cases','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Cases',0,0),('4ddf0090-fbca-5b5e-f5f8-4c218dc19d39','case_meetings','Cases','cases','id','Meetings','meetings','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Cases',0,0),('50ea0b8b-177a-5cf1-f1d8-4c218d062a4d','case_emails','Cases','cases','id','Emails','emails','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Cases',0,0),('417db586-e541-2b7a-0555-4c218d0331f0','bugs_modified_user','Users','users','id','Bugs','bugs','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('41b07754-ef69-d076-1d6a-4c218d137fef','bugs_created_by','Users','users','id','Bugs','bugs','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('41df61b4-1e9b-0b09-e849-4c218d0aa539','bugs_assigned_user','Users','users','id','Bugs','bugs','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('420e1821-f17f-1880-27a8-4c218db6da13','bugs_team_count_relationship','Teams','team_sets','id','Bugs','bugs','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('423c9d14-fea6-8420-3404-4c218d77794c','bugs_teams','Bugs','bugs','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('426d37d5-c32d-08a0-ad63-4c218d5a01b3','bugs_team','Teams','teams','id','Bugs','bugs','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('42a00bda-7aca-83d9-e424-4c218d96b259','bug_tasks','Bugs','bugs','id','Tasks','tasks','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Bugs',0,0),('42cac5ac-0525-d160-d66f-4c218d453ab6','bug_meetings','Bugs','bugs','id','Meetings','meetings','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Bugs',0,0),('43120e5d-7599-f6df-08a7-4c218df3d2c0','bug_calls','Bugs','bugs','id','Calls','calls','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Bugs',0,0),('4383f1ae-823e-190b-8b5e-4c218d9e113f','bug_emails','Bugs','bugs','id','Emails','emails','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Bugs',0,0),('43adbbb7-491f-b1dc-8fd3-4c218db4f8e3','bug_notes','Bugs','bugs','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Bugs',0,0),('43d666d3-e783-8a7f-ec47-4c218d879fa3','bugs_release','Releases','releases','id','Bugs','bugs','found_in_release',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('43fbf3cf-e34a-11aa-2b58-4c218d3ecce7','bugs_fixed_in_release','Releases','releases','id','Bugs','bugs','fixed_in_release',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('6627da5e-ae51-870e-dc4a-4c218dc5df10','user_direct_reports','Users','users','id','Users','users','reports_to_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('665148a8-ce5c-32cf-ab53-4c218d1f99b8','users_email_addresses','Users','users','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','bean_module','Users',0,0),('6677bce8-4c38-8918-a25b-4c218d01e67d','users_email_addresses_primary','Users','users','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','primary_address','1',0,0),('6698549a-b429-f8d8-d852-4c218d69ff67','users_team_count_relationship','Teams','team_sets','id','users','users','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('66b6c179-65f8-8b6d-5c7f-4c218d7f39ba','users_teams','users','users','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('66d56ed8-b783-d76d-153e-4c218d8d445c','users_team','Teams','teams','id','users','users','default_team',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('8398d4bf-6af0-1a59-d9f7-4c218d404530','campaignlog_contact','CampaignLog','campaign_log','related_id','Contacts','contacts','id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('83cc7f3b-c506-375b-6c43-4c218db9943d','campaignlog_lead','CampaignLog','campaign_log','related_id','Leads','leads','id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('a8354582-4949-51ae-07fd-4c218d0bda1f','project_team_count_relationship','Teams','team_sets','id','Project','project','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('a854d142-7244-28fa-29c3-4c218da78b13','project_teams','Project','project','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('a872ecec-2fa7-56b4-4fb0-4c218d94c804','project_team','Teams','teams','id','Project','project','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('a893e464-097e-da8a-f98d-4c218d690f79','projects_notes','Project','project','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Project',0,0),('a8b28bb7-48e7-74d4-ed79-4c218d34339a','projects_tasks','Project','project','id','Tasks','tasks','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Project',0,0),('a8d061f1-ff94-a682-eb09-4c218d520038','projects_meetings','Project','project','id','Meetings','meetings','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Project',0,0),('a8ee2669-8082-e673-d436-4c218dd52b2a','projects_calls','Project','project','id','Calls','calls','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Project',0,0),('a90c138d-cd35-eb23-c87e-4c218df955be','projects_emails','Project','project','id','Emails','emails','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Project',0,0),('a92a0576-2738-c6be-fd1b-4c218da767e5','projects_project_tasks','Project','project','id','ProjectTask','project_task','project_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('a9470dd0-edb9-6f76-e573-4c218d9765e3','projects_assigned_user','Users','users','id','Project','project','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('a9646278-d793-7cc7-792f-4c218d63c079','projects_modified_user','Users','users','id','Project','project','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('a9857084-4a9b-bdca-5798-4c218d3f3928','projects_created_by','Users','users','id','Project','project','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('a9c0ad9e-383f-4c00-1acf-4c218de0c59f','projects_users_resources','Project','project','id','Users','users','id','project_resources','project_id','resource_id','many-to-many','resource_type','Users',0,0),('aa05752b-d7d8-e6fd-6303-4c218d2567ab','projects_contacts_resources','Project','project','id','Contacts','contacts','id','project_resources','project_id','resource_id','many-to-many','resource_type','Contacts',0,0),('aa36336e-d19b-ea2a-5bdc-4c218d42037e','projects_holidays','Project','project','id','Holidays','holidays','related_module_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('dec0b604-d134-5974-c3d8-4c218d2b39da','projecttask_team_count_relationship','Teams','team_sets','id','ProjectTask','project_task','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('df2827e7-c8b0-6f37-de63-4c218d4c0b47','projecttask_teams','ProjectTask','project_task','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('df5de963-d7dc-b66f-f53b-4c218d6613f6','projecttask_team','Teams','teams','id','ProjectTask','project_task','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('df98875e-b84a-94a2-b861-4c218df3015a','project_tasks_notes','ProjectTask','project_task','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','ProjectTask',0,0),('dfe24c68-ec63-e3fc-1c72-4c218d15398b','project_tasks_tasks','ProjectTask','project_task','id','Tasks','tasks','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','ProjectTask',0,0),('e01704e2-fec5-5c5a-502c-4c218d055a72','project_tasks_meetings','ProjectTask','project_task','id','Meetings','meetings','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','ProjectTask',0,0),('e0a167d5-04bf-f5c1-e73f-4c218db7a890','project_tasks_calls','ProjectTask','project_task','id','Calls','calls','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','ProjectTask',0,0),('e0f1945a-a20e-2aa4-d760-4c218dc1797f','project_tasks_emails','ProjectTask','project_task','id','Emails','emails','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','ProjectTask',0,0),('e17a1dd0-5bbf-aa40-36b2-4c218dee4e31','project_tasks_assigned_user','Users','users','id','ProjectTask','project_task','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('e1abd584-9e53-1a2a-2d29-4c218d818584','project_tasks_modified_user','Users','users','id','ProjectTask','project_task','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('e1db2e98-abea-4b41-49df-4c218dc5a966','project_tasks_created_by','Users','users','id','ProjectTask','project_task','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('29b1e913-5e4a-3b8a-4e8f-4c218d254134','campaigns_modified_user','Users','users','id','Campaigns','campaigns','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('29eaeaff-c520-06f9-4e85-4c218dcdc17a','campaigns_created_by','Users','users','id','Campaigns','campaigns','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2a23a5cb-7d4e-ac2e-b566-4c218d071f5d','campaigns_assigned_user','Users','users','id','Campaigns','campaigns','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2a57a285-d1e0-d11a-b05a-4c218da22a07','campaigns_team_count_relationship','Teams','team_sets','id','Campaigns','campaigns','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2a840538-31b9-14db-a5ad-4c218dd5180a','campaigns_teams','Campaigns','campaigns','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('2ab09d14-c444-c275-8853-4c218d987659','campaigns_team','Teams','teams','id','Campaigns','campaigns','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2adc5385-3d83-2712-c812-4c218de6575d','campaign_accounts','Campaigns','campaigns','id','Accounts','accounts','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2b06c260-7f2c-7c14-db44-4c218d828599','campaign_contacts','Campaigns','campaigns','id','Contacts','contacts','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2b81be5d-90cb-34df-358e-4c218d87e7ec','campaign_leads','Campaigns','campaigns','id','Leads','leads','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2baa0dd0-cafd-b8ec-b095-4c218dd4c33f','campaign_prospects','Campaigns','campaigns','id','Prospects','prospects','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2bcad2e0-a8d6-ba9f-333f-4c218d880099','campaign_opportunities','Campaigns','campaigns','id','Opportunities','opportunities','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2be6113f-5816-98c2-9d7b-4c218d67e22d','campaign_email_marketing','Campaigns','campaigns','id','EmailMarketing','email_marketing','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2c00f84a-4552-1213-15dc-4c218d620cd1','campaign_emailman','Campaigns','campaigns','id','EmailMan','emailman','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2c201685-6e39-fef4-9132-4c218d1e53e1','campaign_campaignlog','Campaigns','campaigns','id','CampaignLog','campaign_log','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2c40140e-3997-bd50-336d-4c218d652eb5','campaign_assigned_user','Users','users','id','Campaigns','campaigns','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2c661ff4-c7cb-2768-778d-4c218dc4b10c','campaign_modified_user','Users','users','id','Campaigns','campaigns','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('4e7577bd-b6cd-8a3f-3789-4c218deb729d','prospectlists_team_count_relationship','Teams','team_sets','id','ProspectLists','prospect_lists','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('4e972996-b20c-92d8-b63e-4c218d453bde','prospectlists_teams','ProspectLists','prospect_lists','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('4eb6851b-c1e2-62e9-afa1-4c218d6ebecc','prospectlists_team','Teams','teams','id','ProspectLists','prospect_lists','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('6cdb7bb5-3cb1-2d33-8840-4c218dfe2234','prospects_modified_user','Users','users','id','Prospects','prospects','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('6cfc1b5c-fd6c-92ff-91e7-4c218d7a6f60','prospects_created_by','Users','users','id','Prospects','prospects','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('6d1a0947-aaee-299a-05ec-4c218d908a8f','prospects_assigned_user','Users','users','id','Prospects','prospects','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('6d37d749-c88d-851f-a5a9-4c218d366e1c','prospects_team_count_relationship','Teams','team_sets','id','Prospects','prospects','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('6d5656e7-c7f5-d0e9-bd2e-4c218dd98816','prospects_teams','Prospects','prospects','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('6d75015e-b3c6-b5e3-dc2b-4c218d65dea2','prospects_team','Teams','teams','id','Prospects','prospects','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('6d955abb-469a-937d-4ff2-4c218de9cf4b','prospects_email_addresses','Prospects','prospects','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','bean_module','Prospects',0,0),('6db29e2b-f140-44e0-5b1b-4c218de821bc','prospects_email_addresses_primary','Prospects','prospects','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','primary_address','1',0,0),('6e6e9c74-acb0-888d-a004-4c218da23540','prospect_tasks','Prospects','prospects','id','Tasks','tasks','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Prospects',0,0),('6eaa654f-6e4f-a8e3-e0bf-4c218d455b6d','prospect_notes','Prospects','prospects','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Prospects',0,0),('6ed1bd80-9138-fc04-c51a-4c218d620c23','prospect_meetings','Prospects','prospects','id','Meetings','meetings','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Prospects',0,0),('6eff5499-11a4-c1f7-46d5-4c218dab08dd','prospect_calls','Prospects','prospects','id','Calls','calls','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Prospects',0,0),('6f30bc26-f9c4-72ca-f4fb-4c218d3eb0cb','prospect_emails','Prospects','prospects','id','Emails','emails','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Prospects',0,0),('6f6113cb-d14d-0138-3fe0-4c218d58e8e4','prospect_campaign_log','Prospects','prospects','id','CampaignLog','campaign_log','target_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('8daac106-8544-63c8-3d6d-4c218d45f8e4','email_template_email_marketings','EmailTemplates','email_templates','id','EmailMarketing','email_marketing','template_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ab88ca79-d641-9d88-6f71-4c218d643369','campaign_campaigntrakers','Campaigns','campaigns','id','CampaignTrackers','campaign_trkrs','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('1fd7be8e-da8e-e479-be4c-4c218d2c7e12','schedulers_created_by_rel','Users','users','id','Schedulers','schedulers','created_by',NULL,NULL,NULL,'one-to-one',NULL,NULL,0,0),('200fb126-d265-625a-adc7-4c218d3dbaf5','schedulers_modified_user_id_rel','Users','users','id','Schedulers','schedulers','modified_user_id',NULL,NULL,NULL,'one-to-one',NULL,NULL,0,0),('204246db-9d80-2356-7927-4c218d5c4a82','schedulers_jobs_rel','Schedulers','schedulers','id','SchedulersJobs','schedulers_times','scheduler_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7b3fbab0-6dcb-9b38-8e97-4c218d9f73ea','contacts_modified_user','Users','users','id','Contacts','contacts','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7b77ec62-40ae-67d0-f9d3-4c218d3082cf','contacts_created_by','Users','users','id','Contacts','contacts','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7bab187a-44a6-6055-f93a-4c218dde0b15','contacts_assigned_user','Users','users','id','Contacts','contacts','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7bd80b4d-1385-cb1a-44d7-4c218d794ad7','contacts_team_count_relationship','Teams','team_sets','id','Contacts','contacts','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7c04a7d8-d7bd-329f-9455-4c218d2ccb76','contacts_teams','Contacts','contacts','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('7c31f2af-9119-52ac-023d-4c218d59ec12','contacts_team','Teams','teams','id','Contacts','contacts','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7c5e68ae-e428-9407-5b99-4c218d3734fa','contacts_email_addresses','Contacts','contacts','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','bean_module','Contacts',0,0),('7c8f040b-7dd9-40a4-e0f6-4c218dec0e59','contacts_email_addresses_primary','Contacts','contacts','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','primary_address','1',0,0),('7d049f5d-43eb-857b-ff21-4c218de67894','contact_direct_reports','Contacts','contacts','id','Contacts','contacts','reports_to_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7d4978c3-1785-5b32-b58d-4c218de7d793','contact_leads','Contacts','contacts','id','Leads','leads','contact_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7d770ba8-47ae-cfb8-2cb3-4c218d6eddd9','contact_notes','Contacts','contacts','id','Notes','notes','contact_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7d9c5afe-3d70-6c3d-fa0e-4c218d0807e4','contact_tasks','Contacts','contacts','id','Tasks','tasks','contact_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7dbfea9f-cbd7-4c10-3c09-4c218d61f33c','contact_products','Contacts','contacts','id','Products','products','contact_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7ddf7d1a-e6c6-5933-e9b5-4c218d8f6ec1','contact_campaign_log','Contacts','contacts','id','CampaignLog','campaign_log','target_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b79f0e6a-d884-8f85-c3b2-4c218d39e145','accounts_modified_user','Users','users','id','Accounts','accounts','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b7d2f5b8-d0a0-4011-7ae2-4c218dec9f7c','accounts_created_by','Users','users','id','Accounts','accounts','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b8031423-bcd4-d724-3699-4c218d56a610','accounts_assigned_user','Users','users','id','Accounts','accounts','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b83656d9-f171-2bc6-43a6-4c218d87af67','accounts_team_count_relationship','Teams','team_sets','id','Accounts','accounts','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b866dffa-1c05-756b-78a9-4c218dbaa7dc','accounts_teams','Accounts','accounts','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('b897cf0a-33d4-3acb-a532-4c218df3e94d','accounts_team','Teams','teams','id','Accounts','accounts','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b8c78af7-24b0-bda9-a5f6-4c218d3327d3','accounts_email_addresses','Accounts','accounts','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','bean_module','Accounts',0,0),('b94a957c-4fc8-01f0-4b03-4c218d9cf544','accounts_email_addresses_primary','Accounts','accounts','id','EmailAddresses','email_addresses','id','email_addr_bean_rel','bean_id','email_address_id','many-to-many','primary_address','1',0,0),('ba2504f5-c5ea-f34d-7824-4c218dc2891c','member_accounts','Accounts','accounts','id','Accounts','accounts','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ba61d65c-5e93-6349-f16f-4c218dbfc0d9','account_cases','Accounts','accounts','id','Cases','cases','account_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ba8a4e97-820f-43c6-a5d0-4c218d49c667','account_tasks','Accounts','accounts','id','Tasks','tasks','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Accounts',0,0),('bab22a9d-6157-6da3-f3d4-4c218d0ce683','account_notes','Accounts','accounts','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Accounts',0,0),('bad95178-b37f-08c5-d369-4c218d54c297','account_meetings','Accounts','accounts','id','Meetings','meetings','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Accounts',0,0),('bb006b0c-6f7c-8187-3f1f-4c218d8a7ef6','account_calls','Accounts','accounts','id','Calls','calls','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Accounts',0,0),('bb27a8e0-c220-02a2-acc2-4c218d112945','account_emails','Accounts','accounts','id','Emails','emails','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Accounts',0,0),('bb4b08b9-612a-7c32-d1b5-4c218df92332','account_leads','Accounts','accounts','id','Leads','leads','account_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b90008a4-a45c-0a6a-b3a9-4c218d405c0a','opportunities_modified_user','Users','users','id','Opportunities','opportunities','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('42e00739-5f17-43b3-46b8-4c218dd1154c','opportunities_created_by','Users','users','id','Opportunities','opportunities','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('744003f4-257d-ee67-9314-4c218da16f0c','opportunities_assigned_user','Users','users','id','Opportunities','opportunities','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('a5300705-8b0e-155f-79ac-4c218d526a1a','opportunities_team_count_relationship','Teams','team_sets','id','Opportunities','opportunities','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('d5f0052b-ae71-9d5e-28a1-4c218ddf1e4c','opportunities_teams','Opportunities','opportunities','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('111c0636-c4fb-b878-92fe-4c218d339b5c','opportunities_team','Teams','teams','id','Opportunities','opportunities','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('145f07b0-983f-e8d8-7143-4c218d4f3565','opportunity_calls','Opportunities','opportunities','id','Calls','calls','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Opportunities',0,0),('1b9f0c09-7716-b040-4adf-4c218dd794d8','opportunity_meetings','Opportunities','opportunities','id','Meetings','meetings','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Opportunities',0,0),('21fc02d9-91a0-f59f-e8bb-4c218d891a7a','opportunity_tasks','Opportunities','opportunities','id','Tasks','tasks','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Opportunities',0,0),('252108f3-6b49-3122-7220-4c218ddefe79','opportunity_notes','Opportunities','opportunities','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Opportunities',0,0),('282801ac-8703-8c4a-4b92-4c218d60cb2a','opportunity_emails','Opportunities','opportunities','id','Emails','emails','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Opportunities',0,0),('2bd40ff5-93a2-7109-8acd-4c218de3a493','opportunity_leads','Opportunities','opportunities','id','Leads','leads','opportunity_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2e9909c3-bb7f-0af4-a87d-4c218d433a53','opportunity_currencies','Opportunities','opportunities','currency_id','Currencies','currencies','id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('310e0cd5-9b02-3210-054b-4c218d3ffdc3','opportunities_campaign','campaigns','campaigns','id','Opportunities','opportunities','campaign_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('280e4a92-0d5f-8abf-29cf-4c218d5f2aeb','emailtemplates_team_count_relationship','Teams','team_sets','id','EmailTemplates','email_templates','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2872dc42-7193-04f6-2fcb-4c218d5a582c','emailtemplates_teams','EmailTemplates','email_templates','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('28b4818f-e426-c82d-18ba-4c218d1733b5','emailtemplates_team','Teams','teams','id','EmailTemplates','email_templates','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('4e03d72f-9717-83f7-13c8-4c218d591c28','notes_team_count_relationship','Teams','team_sets','id','Notes','notes','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('4e25650d-0050-ab1f-9b12-4c218d40125d','notes_teams','Notes','notes','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('4e43a917-a7fb-21b8-bce5-4c218db4bf3e','notes_team','Teams','teams','id','Notes','notes','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('4e60e4a0-359a-63f8-0a1e-4c218d7296f0','notes_modified_user','Users','users','id','Notes','notes','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('4e7e19bd-8a75-9906-5a85-4c218d0ad717','notes_created_by','Users','users','id','Notes','notes','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7c47b444-1140-ef69-9b6a-4c218d5163dc','calls_modified_user','Users','users','id','Calls','calls','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7c88a883-76d3-e1dc-cfa1-4c218d0811b8','calls_created_by','Users','users','id','Calls','calls','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7ccae812-d77a-92fb-d8be-4c218dc2ce85','calls_assigned_user','Users','users','id','Calls','calls','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7d052cca-1657-6727-d492-4c218d25d867','calls_team_count_relationship','Teams','team_sets','id','Calls','calls','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7d3d4363-91eb-032d-6acd-4c218db3b05e','calls_teams','Calls','calls','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('7d765346-bf05-c8cd-98e5-4c218df5651e','calls_team','Teams','teams','id','Calls','calls','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7da887c3-4975-a70b-21e7-4c218d68d83b','calls_notes','Calls','calls','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b993ad30-c264-3ce0-37e5-4c218d0a67e2','emails_team_count_relationship','Teams','team_sets','id','Emails','emails','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b9b42514-8a7c-4869-a01e-4c218d8261bd','emails_teams','Emails','emails','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('b9d321ad-6b40-3cd6-8857-4c218d71f4db','emails_team','Teams','teams','id','Emails','emails','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b9f19e25-223d-4663-512e-4c218d6f301c','emails_assigned_user','Users','users','id','Emails','emails','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ba0fd192-02f4-3f7f-6f4f-4c218de75d8c','emails_modified_user','Users','users','id','Emails','emails','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ba2eaf60-fde8-af7a-7d42-4c218d7bc433','emails_created_by','Users','users','id','Emails','emails','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ba4f95da-75c7-4bc0-6881-4c218d1f3ef9','emails_notes_rel','Emails','emails','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('d73ffcef-c7e7-948a-c9ab-4c218d616684','meetings_modified_user','Users','users','id','Meetings','meetings','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('d75feaba-3218-2d33-2d8f-4c218dd15c5d','meetings_created_by','Users','users','id','Meetings','meetings','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('d786f39f-265e-57e4-30af-4c218dc2d09b','meetings_assigned_user','Users','users','id','Meetings','meetings','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('d7a5e086-9047-b42f-b57f-4c218daff982','meetings_team_count_relationship','Teams','team_sets','id','Meetings','meetings','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('d7c6f040-160b-af98-2de3-4c218d3b2529','meetings_teams','Meetings','meetings','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('d7e5ca28-c272-4d3b-fb0e-4c218d872796','meetings_team','Teams','teams','id','Meetings','meetings','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('d803e027-43d1-d6aa-40e8-4c218d3d8770','meetings_notes','Meetings','meetings','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Meetings',0,0),('92c70ac4-e890-e3ff-1115-4c218da60005','tasks_modified_user','Users','users','id','Tasks','tasks','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('94da0d6b-514e-157b-560d-4c218dfae889','tasks_created_by','Users','users','id','Tasks','tasks','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('96c10c86-1d71-84ee-1b4b-4c218d017d58','tasks_assigned_user','Users','users','id','Tasks','tasks','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('98a9020d-b9c1-ff21-aaa9-4c218dd3025b','tasks_team_count_relationship','Teams','team_sets','id','Tasks','tasks','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b50508a2-1d50-e60d-8f23-4c218d13d22a','tasks_teams','Tasks','tasks','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('b9da0706-4545-7258-b3fc-4c218dc03cfd','tasks_team','Teams','teams','id','Tasks','tasks','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('4997038f-603c-89e0-8f8a-4c218d6eaacf','tracker_monitor_id','TrackerPerfs','tracker_perf','monitor_id','Trackers','tracker','monitor_id',NULL,NULL,NULL,'one-to-one',NULL,NULL,0,0),('5c9ac704-f150-408c-6c91-4c218dd7ea1f','documents_modified_user','Users','users','id','Documents','documents','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('5cbb3a5c-58ce-0e46-2c00-4c218d779af0','documents_created_by','Users','users','id','Documents','documents','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('5cd9fbfe-1e00-80ab-ab4d-4c218dc0bf86','documents_team_count_relationship','Teams','team_sets','id','Documents','documents','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('5cf8c87f-aa11-c064-ba92-4c218df88508','documents_teams','Documents','documents','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('5d17c446-01df-900a-c8e6-4c218dfa454a','documents_team','Teams','teams','id','Documents','documents','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('5d35fa9c-61d7-8057-865f-4c218d0ebba2','document_revisions','Documents','documents','id','Documents','document_revisions','document_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('7b302168-f493-cfcc-dc9c-4c218d249cf8','revisions_created_by','Users','users','id','DocumentRevisions','document_revisions','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('bb727bb7-08f7-55bf-32ef-4c218dd88982','inboundemail_team_count_relationship','Teams','team_sets','id','InboundEmail','inbound_email','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('bbb499d9-a3f3-fd70-d8d7-4c218d0fdb69','inboundemail_teams','InboundEmail','inbound_email','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('bbf03756-5e23-5b11-bdd2-4c218dfcc359','inboundemail_team','Teams','teams','id','InboundEmail','inbound_email','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('bc3a4a65-3e76-4bfc-01ed-4c218d332d57','inbound_email_created_by','Users','users','id','InboundEmail','inbound_email','created_by',NULL,NULL,NULL,'one-to-one',NULL,NULL,0,0),('bcad5675-3255-e203-ae09-4c218d8e1361','inbound_email_modified_user_id','Users','users','id','InboundEmail','inbound_email','modified_user_id',NULL,NULL,NULL,'one-to-one',NULL,NULL,0,0),('e1efe5be-7a6a-9f4a-9133-4c218d90e93b','savedsearch_team_count_relationship','Teams','team_sets','id','SavedSearch','saved_search','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('e2397f21-f4d5-06a7-e438-4c218d9bb820','savedsearch_teams','SavedSearch','saved_search','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('e2899537-9a09-3861-a585-4c218db4482f','savedsearch_team','Teams','teams','id','SavedSearch','saved_search','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('e2c456a2-238f-c2ae-4176-4c218dc43095','saved_search_assigned_user','Users','users','id','SavedSearch','saved_search','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('5375e677-df4c-2b72-9a79-4c218d238f7b','reports_team_count_relationship','Teams','team_sets','id','Reports','saved_reports','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('539652cb-ab32-2bbe-c622-4c218db7ea23','reports_teams','Reports','saved_reports','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('53b86daf-e059-8cf6-60b3-4c218d40d27a','reports_team','Teams','teams','id','Reports','saved_reports','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('53d763d4-9f8f-3ed6-2777-4c218d7ba525','report_assigned_user','Users','users','id','Reports','saved_reports','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ae4f75a5-0eec-d7b5-e0a3-4c218dff21ca','contracts_modified_user','Users','users','id','Contracts','contracts','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ae8645cb-a2dd-cc55-b39c-4c218d0d62eb','contracts_created_by','Users','users','id','Contracts','contracts','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('aeb8bccd-ebf5-4a00-0f6f-4c218d3705a6','contracts_assigned_user','Users','users','id','Contracts','contracts','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('aeeb1784-8580-d81b-47f5-4c218dd360f0','contracts_team_count_relationship','Teams','team_sets','id','Contracts','contracts','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('af1d7364-4a17-a045-5e93-4c218d291c8d','contracts_teams','Contracts','contracts','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('af523c3c-225f-c939-1ce1-4c218d81a4f4','contracts_team','Teams','teams','id','Contracts','contracts','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('af841c1f-e11d-545a-7b02-4c218dde30a7','contracts_contract_types','Contracts','contracts','type','ContractTypes','contract_types','id',NULL,NULL,NULL,'one-to-many','type','Contracts',0,0),('b024a990-7f47-feba-18b8-4c218d88c7c2','contract_notes','Contracts','contracts','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Contracts',0,0),('b05e1136-ed24-7d49-c9c3-4c218d006151','account_contracts','Accounts','accounts','id','Contracts','contracts','account_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('f0406454-db6b-4ccc-5223-4c218db5b95f','team_memberships','Teams','teams','id','Users','users','id','team_memberships','team_id','user_id','many-to-many',NULL,NULL,0,0),('72aa088c-84f1-8430-5332-4c218dda3a18','teamnotices_team_count_relationship','Teams','team_sets','id','TeamNotices','team_notices','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('72e785ff-96bc-1cc3-0968-4c218d27c4ed','teamnotices_teams','TeamNotices','team_notices','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('732abeec-89c9-0e1c-61a6-4c218db12bea','teamnotices_team','Teams','teams','id','TeamNotices','team_notices','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('90b57f85-cd7c-cbee-f32b-4c218db43bf6','product_templates_product_categories','ProductCategories','product_categories','id','ProductTemplates','product_templates','category_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('90ee3e5e-19ce-64a5-6f77-4c218d80f17b','product_templates_product_types','ProductTypes','product_types','id','ProductTemplates','product_templates','type_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('9124ad9a-025f-2bb6-4d0b-4c218d552f2f','product_templates_modified_user','Users','users','id','ProductTemplates','product_templates','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('9157adc6-dece-a9ad-4d70-4c218dc45d1d','product_templates_created_by','Users','users','id','ProductTemplates','product_templates','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('cd06d829-262d-5438-d404-4c218d61c544','member_categories','ProductCategories','product_categories','id','ProductCategories','product_categories','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('395c5439-e76b-c2ea-dbe7-4c218d8c5316','quotes_modified_user','Users','users','id','Quotes','quotes','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('397e9f35-4dc4-e304-127b-4c218d5c6e5e','quotes_created_by','Users','users','id','Quotes','quotes','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('399d570f-d865-dcc4-928d-4c218dd09b04','quotes_assigned_user','Users','users','id','Quotes','quotes','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('39bbbbe2-dab3-aed7-011c-4c218d94ffa8','quotes_team_count_relationship','Teams','team_sets','id','Quotes','quotes','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('39da2f24-971b-a4e3-9580-4c218d339058','quotes_teams','Quotes','quotes','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('39fa6f96-7831-d49a-e63d-4c218d217fb8','quotes_team','Teams','teams','id','Quotes','quotes','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('3a18b7d4-41d4-65bd-bb93-4c218d6052a1','quote_tasks','Quotes','quotes','id','Tasks','tasks','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Quotes',0,0),('3a378283-7cf3-a309-892e-4c218dd0cd92','quote_notes','Quotes','quotes','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Quotes',0,0),('3a56c2f3-3ee9-dbb5-e66f-4c218db13748','quote_meetings','Quotes','quotes','id','Meetings','meetings','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Quotes',0,0),('3a765f34-1c31-457a-9f89-4c218d0b21aa','quote_calls','Quotes','quotes','id','Calls','calls','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Quotes',0,0),('3a950d63-dc5d-c414-5ff6-4c218dbec71d','quote_emails','Quotes','quotes','id','Emails','emails','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Quotes',0,0),('3ab3fa29-7289-c695-2728-4c218d772f41','quote_products','Quotes','quotes','id','Products','products','quote_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('947a7e2c-8bb2-58c7-92d9-4c218d3005f8','products_modified_user','Users','users','id','Products','products','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('94b05d24-8923-20f2-bc99-4c218d2215e2','products_created_by','Users','users','id','Products','products','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('94e9798a-2063-1cb1-0ec9-4c218d9db852','products_team_count_relationship','Teams','team_sets','id','Products','products','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('9515d2b1-815b-54b6-cb76-4c218d65a3b3','products_teams','Products','products','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('953f55de-a5b2-d8a2-09aa-4c218d986979','products_team','Teams','teams','id','Products','products','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('95691afe-11dd-e8dd-b7dc-4c218dfce132','product_notes','Products','products','id','Notes','notes','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Products',0,0),('9591ad12-f77c-ec35-f716-4c218d1b45be','products_accounts','Accounts','accounts','id','Products','products','account_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('95bf0063-2ad2-cfda-bf51-4c218db7f4db','product_categories','ProductCategories','product_categories','id','Products','products','category_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('960a1ec0-8a31-a2cf-b2d7-4c218d9ee87d','product_types','ProductTypes','product_types','id','Products','products','type_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b328affe-94c3-9cbf-aadc-4c218d8bc09b','productbundles_team_count_relationship','Teams','team_sets','id','ProductBundles','product_bundles','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('b360de77-ec4b-3020-fb02-4c218d83f80d','productbundles_teams','ProductBundles','product_bundles','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('b394a699-032d-ea58-7181-4c218de2999a','productbundles_team','Teams','teams','id','ProductBundles','product_bundles','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('22270072-b2df-4b11-2daa-4c218d6f33f6','timeperiod_forecast_schedules','TimePeriods','timeperiods','id','Forecasts','forecast_schedule','timeperiod_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2249b22d-d19b-896b-7070-4c218d7b99a6','related_timeperiods','TimePeriods','timeperiods','id','TimePeriods','timeperiods','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('47065799-0e74-27e9-7ca6-4c218d603799','forecasts_created_by','Users','users','id','Forecasts','forecasts','user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('c20f294a-3c74-9494-adc2-4c218d333e11','workflow_triggers','WorkFlow','workflow','id','WorkFlowTriggerShells','workflow_triggershells','parent_id',NULL,NULL,NULL,'one-to-many','frame_type','Primary',0,0),('c248c75b-4f38-e7be-f08b-4c218d763b0f','workflow_trigger_filters','WorkFlow','workflow','id','WorkFlowTriggerShells','workflow_triggershells','parent_id',NULL,NULL,NULL,'one-to-many','frame_type','Secondary',0,0),('c27e85a1-dd54-f991-28e4-4c218d98e0ba','workflow_alerts','WorkFlow','workflow','id','WorkFlowAlertShells','workflow_alertshells','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('c2b9fa93-1bfe-369d-3927-4c218d3d9408','workflow_actions','WorkFlow','workflow','id','WorkFlowActionShells','workflow_actionshells','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('e09764c7-8805-83eb-4700-4c218d10c46c','past_triggers','WorkFlowTriggerShells','workflow_triggershells','id','Expressions','expressions','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','past_trigger',0,0),('e0cffd87-e897-5820-0a82-4c218d598461','future_triggers','WorkFlowTriggerShells','workflow_triggershells','id','Expressions','expressions','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','future_trigger',0,0),('e10799f5-4ec3-54f5-5d75-4c218ded6ae4','trigger_expressions','WorkFlowTriggerShells','workflow_triggershells','id','Expressions','expressions','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','expression',0,0),('d6b70d09-1590-11e6-8c0a-4c218dd3e1af','alert_components','WorkFlowAlertShells','workflow_alertshells','id','WorkFlowAlerts','workflow_alerts','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('2bd6f607-5e5c-4b95-e041-4c218d383c68','expressions','WorkFlowAlerts','workflow_alerts','id','Expressions','expressions','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','filter',0,0),('2c02a25a-bf9f-50fe-8a05-4c218d9ce219','rel1_alert_fil','WorkFlowAlerts','workflow_alerts','id','Expressions','expressions','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','rel1_alert_fil',0,0),('2c4198f2-965d-b888-1d3a-4c218d2e7c40','rel2_alert_fil','WorkFlowAlerts','workflow_alerts','id','Expressions','expressions','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','rel2_alert_fil',0,0),('47327ebd-47e5-62a6-7e9d-4c218da2fadd','actions','WorkFlowActionShells','workflow_actionshells','id','WorkFlowActions','workflow_actions','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('475d240f-b7fe-1a5e-e86e-4c218d539378','action_bridge','WorkFlowActionShells','workflow_actionshells','id','WorkFlow','workflow','parent_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('4783a301-944f-1ecd-3fb6-4c218d8ca900','rel1_action_fil','WorkFlowActionShells','workflow_actionshells','id','Expressions','expressions','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','rel1_action_fil',0,0),('84fab298-f434-8c3e-cdca-4c218d8a89ca','member_expressions','Expressions','expressions','id','Expressions','expressions','parent_exp_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ebf78727-7b2f-bfcc-3d94-4c218d9ae1d2','kbdocuments_team_count_relationship','Teams','team_sets','id','KBDocuments','kbdocuments','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ec194d55-629a-5886-bdff-4c218dcf1171','kbdocuments_teams','KBDocuments','kbdocuments','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('ec3b2ffa-a4d1-dc96-6409-4c218d7274d7','kbdocuments_team','Teams','teams','id','KBDocuments','kbdocuments','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ec5adba5-5ea1-3a8f-58e9-4c218ddcb8ef','kbdocument_revisions','KBDocuments','kbdocuments','id','KBDocumentRevisions','kbdocument_revisions','kbdocument_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ec7a5bd9-7b17-4a14-9818-4c218dcfd59c','kbdocuments_modified_user','Users','users','id','KBDocuments','kbdocuments','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ec99aff6-d0cf-82ea-8b13-4c218d922a7f','kbdocuments_created_by','Users','users','id','KBDocuments','kbdocuments','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ecb845c5-d8f2-0112-c4d4-4c218de327f4','kb_assigned_user','Users','users','id','KBDocuments','kbdocuments','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ecd636bc-0009-2d24-6d2d-4c218dc697ba','kbdoc_approver_user','Users','users','id','KBDocuments','kbdocuments','kbdoc_approver_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('ecf3bb36-79e9-609e-c62a-4c218de5c718','case_kbdocuments','Cases','cases','id','KBDocuments','kbdocuments','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Cases',0,0),('ed11cf34-e510-79ed-7688-4c218d8224e3','email_kbdocuments','Emails','emails','id','KBDocuments','kbdocuments','parent_id',NULL,NULL,NULL,'one-to-many','parent_type','Emails',0,0),('179a8217-f3e8-be4c-875d-4c218d89ff42','kbrev_revisions_created_by','Users','users','id','KBDocumentRevisions','kbdocument_revisions','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('3659d30b-bd91-dfd0-4321-4c218dfd3cf1','kbtags_team_count_relationship','Teams','team_sets','id','KBTags','kbtags','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('368f7bf0-a276-afcb-3084-4c218dee6568','kbtags_teams','KBTags','kbtags','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('36c27bb7-7324-bd30-fab8-4c218d27c3ad','kbtags_team','Teams','teams','id','KBTags','kbtags','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('5723e7f1-bb63-e560-c976-4c218d7ddd2f','kbdocumentkbtags_team_count_relationship','Teams','team_sets','id','KBDocumentKBTags','kbdocuments_kbtags','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('5748d9c9-a8a0-46d1-7e16-4c218d49b125','kbdocumentkbtags_teams','KBDocumentKBTags','kbdocuments_kbtags','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('576a53d5-77a2-0af2-aea8-4c218de49c0a','kbdocumentkbtags_team','Teams','teams','id','KBDocumentKBTags','kbdocuments_kbtags','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('578a77e9-2013-409b-e68d-4c218db70066','kbrevisions_created_by','Users','users','id','DocumentRevisions','document_revisions','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('964ee379-2930-4469-645f-4c218d35c73d','kbcontents_team_count_relationship','Teams','team_sets','id','KBContents','kbcontents','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('9684a53a-fcc6-95ee-df2c-4c218db09178','kbcontents_teams','KBContents','kbcontents','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('96b3158f-9e54-17a5-7905-4c218dddfe87','kbcontents_team','Teams','teams','id','KBContents','kbcontents','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('142e7849-dc75-a262-3795-4c218db71cc3','sugarfeed_modified_user','Users','users','id','SugarFeed','sugarfeed','modified_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('1463619b-9538-fbb4-d3d5-4c218d8a92a0','sugarfeed_created_by','Users','users','id','SugarFeed','sugarfeed','created_by',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('14965131-fd94-cce1-9c93-4c218d686dac','sugarfeed_team_count_relationship','Teams','team_sets','id','SugarFeed','sugarfeed','team_set_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('14ce4027-cce7-0cb4-1597-4c218d425a50','sugarfeed_teams','SugarFeed','sugarfeed','team_set_id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('150b7bbd-cf67-2aff-ec1a-4c218d8ec8a9','sugarfeed_team','Teams','teams','id','SugarFeed','sugarfeed','team_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('15484b88-cd6d-51aa-b06e-4c218d3da797','sugarfeed_assigned_user','Users','users','id','SugarFeed','sugarfeed','assigned_user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('1f69e5da-9052-b347-0479-4c218df7d259','accounts_bugs','Accounts','accounts','id','Bugs','bugs','id','accounts_bugs','account_id','bug_id','many-to-many',NULL,NULL,0,0),('50e57ea5-7ae5-7256-5876-4c218d535bfe','accounts_contacts','Accounts','accounts','id','Contacts','contacts','id','accounts_contacts','account_id','contact_id','many-to-many',NULL,NULL,0,0),('66eeb0e9-6a10-00be-8ca2-4c218d9cabac','accounts_opportunities','Accounts','accounts','id','Opportunities','opportunities','id','accounts_opportunities','account_id','opportunity_id','many-to-many',NULL,NULL,0,0),('7cdc0a52-21b3-ee7c-3558-4c218df35595','acl_roles_actions','ACLRoles','acl_roles','id','ACLActions','acl_actions','id','acl_roles_actions','role_id','action_id','many-to-many',NULL,NULL,0,0),('9574169f-ed4a-7a4e-9441-4c218d449990','acl_roles_users','ACLRoles','acl_roles','id','Users','users','id','acl_roles_users','role_id','user_id','many-to-many',NULL,NULL,0,0),('aeeebe67-8d67-36e6-cfc7-4c218dc9420a','calls_contacts','Calls','calls','id','Contacts','contacts','id','calls_contacts','call_id','contact_id','many-to-many',NULL,NULL,0,0),('d653c527-1bcf-85ff-47c7-4c218de478c2','calls_leads','Calls','calls','id','Leads','leads','id','calls_leads','call_id','lead_id','many-to-many',NULL,NULL,0,0),('4ae001b2-c54e-5d1f-15c2-4c218de6605d','calls_users','Calls','calls','id','Users','users','id','calls_users','call_id','user_id','many-to-many',NULL,NULL,0,0),('1d4fe23a-e17d-9742-38b1-4c218dcf50b2','cases_bugs','Cases','cases','id','Bugs','bugs','id','cases_bugs','case_id','bug_id','many-to-many',NULL,NULL,0,0),('3b9330c6-1303-90ae-3c9e-4c218d3b3388','contacts_bugs','Contacts','contacts','id','Bugs','bugs','id','contacts_bugs','contact_id','bug_id','many-to-many',NULL,NULL,0,0),('61c81c79-b728-312b-4506-4c218dd9c9c9','contacts_cases','Contacts','contacts','id','Cases','cases','id','contacts_cases','contact_id','case_id','many-to-many',NULL,NULL,0,0),('7d637b02-83a4-e17e-79ce-4c218d227b25','contacts_users','Contacts','contacts','id','Users','users','id','contacts_users','contact_id','user_id','many-to-many',NULL,NULL,0,0),('9b7afdb2-4a83-1eda-3e25-4c218d01100d','contracts_contacts','Contracts','contracts','id','Contacts','contacts','id','contracts_contacts','contract_id','contact_id','many-to-many',NULL,NULL,0,0),('b4af635b-bc96-417a-187e-4c218d16e68c','contracts_opportunities','Contracts','contracts','id','Opportunities','opportunities','id','contracts_opportunities','contract_id','opportunity_id','many-to-many',NULL,NULL,0,0),('cd9cd5a0-4b6d-7ae1-e3a8-4c218de7c44c','contracts_products','Contracts','contracts','id','Products','products','id','contracts_products','contract_id','product_id','many-to-many',NULL,NULL,0,0),('e6ac8a96-cac7-33ba-ae81-4c218dae0eab','contracts_quotes','Contracts','contracts','id','Quotes','quotes','id','contracts_quotes','contract_id','quote_id','many-to-many',NULL,NULL,0,0),('730b47e0-3fa7-9ddc-3ad2-4c218d1501a5','email_marketing_prospect_lists','EmailMarketing','email_marketing','id','ProspectLists','prospect_lists','id','email_marketing_prospect_lists','email_marketing_id','prospect_list_id','many-to-many',NULL,NULL,0,0),('8f353a5d-0104-6e14-34db-4c218d897433','emails_accounts_rel','Emails','emails','id','Accounts','accounts','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Accounts',0,0),('8f6f6d44-c13e-1e18-6539-4c218d55b7c2','emails_bugs_rel','Emails','emails','id','Bugs','bugs','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Bugs',0,0),('8fa4af8b-2d48-c279-7ad7-4c218d0011cf','emails_cases_rel','Emails','emails','id','Cases','cases','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Cases',0,0),('8fd9eaa5-fe42-8c0b-d7cf-4c218dc79db4','emails_contacts_rel','Emails','emails','id','Contacts','contacts','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Contacts',0,0),('900d815e-01c0-ce44-2e90-4c218def3bdb','emails_leads_rel','Emails','emails','id','Leads','leads','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Leads',0,0),('90421dff-2a42-720b-50b5-4c218d48f76f','emails_opportunities_rel','Emails','emails','id','Opportunities','opportunities','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Opportunities',0,0),('90810e13-f4bb-fe07-cc91-4c218d018ec5','emails_tasks_rel','Emails','emails','id','Tasks','tasks','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Tasks',0,0),('90c9e91b-0ea7-4bd7-45e2-4c218db633f9','emails_users_rel','Emails','emails','id','Users','users','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Users',0,0),('9100e92e-e94b-840b-c6e0-4c218d33109e','emails_project_task_rel','Emails','emails','id','ProjectTask','project_task','id','emails_beans','email_id','bean_id','many-to-many','bean_module','ProjectTask',0,0),('916fd3f6-e246-d552-d39d-4c218d33e7c9','emails_projects_rel','Emails','emails','id','Project','project','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Project',0,0),('91a99f2b-2b0a-0c4e-689a-4c218d2997c9','emails_prospects_rel','Emails','emails','id','Prospects','prospects','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Prospects',0,0),('91fe2869-8b05-41a8-5a6f-4c218d944b41','emails_quotes','Emails','emails','id','Quotes','quotes','id','emails_beans','email_id','bean_id','many-to-many','bean_module','Quotes',0,0),('3fec294d-1d59-e227-f0e4-4c218d08c323','contracts_documents','Contracts','contracts','id','Documents','documents','id','linked_documents','parent_id','document_id','many-to-many','parent_type','Contracts',0,0),('40272cf9-680a-dd9e-24c3-4c218db46928','leads_documents','Leads','leads','id','Documents','documents','id','linked_documents','parent_id','document_id','many-to-many','parent_type','Leads',0,0),('405cfa06-140f-4314-8f1a-4c218d82bad3','contracttype_documents','ContractTypes','contract_types','id','Documents','documents','id','linked_documents','parent_id','document_id','many-to-many','parent_type','ContracTemplates',0,0),('59185fc2-1a88-0e36-036f-4c218dea5f4c','meetings_contacts','Meetings','meetings','id','Contacts','contacts','id','meetings_contacts','meeting_id','contact_id','many-to-many',NULL,NULL,0,0),('7485baf4-2de9-4e88-23fe-4c218d7c6871','meetings_leads','Meetings','meetings','id','Leads','leads','id','meetings_leads','meeting_id','lead_id','many-to-many',NULL,NULL,0,0),('8d2d0640-769b-2e84-7e5a-4c218d60d035','meetings_users','Meetings','meetings','id','Users','users','id','meetings_users','meeting_id','user_id','many-to-many',NULL,NULL,0,0),('a6090608-cbd9-cc3e-6f4a-4c218d2a2fd8','opportunities_contacts','Opportunities','opportunities','id','Contacts','contacts','id','opportunities_contacts','opportunity_id','contact_id','many-to-many',NULL,NULL,0,0),('c182aa45-942c-c962-f90b-4c218dfdfbbc','product_bundle_note','ProductBundles','product_bundles','id','ProductBundleNotes','product_bundle_note','id','product_bundle_note','bundle_id','note_id','many-to-many',NULL,NULL,0,0),('e5ef9f51-07ed-1b72-99c2-4c218d990bb7','product_bundle_product','ProductBundles','product_bundles','id','Products','products','id','product_bundle_product','bundle_id','product_id','many-to-many',NULL,NULL,0,0),('9f840493-6c83-b805-81d5-4c218db12a8f','product_bundle_quote','ProductBundles','product_bundles','id','Quotes','quotes','id','product_bundle_quote','bundle_id','quote_id','many-to-many',NULL,NULL,0,0),('25941597-1e7a-c6d2-eee8-4c218df757b2','product_product','Products','products','id','Products','products','id','product_product','parent_id','child_id','many-to-many',NULL,NULL,1,0),('5c9a6ff7-9d9f-37f4-5175-4c218d337e41','projects_accounts','Project','project','id','Accounts','accounts','id','projects_accounts','project_id','account_id','many-to-many',NULL,NULL,0,0),('757b899c-0d85-36bb-7455-4c218ddf0e74','projects_bugs','Project','project','id','Bugs','bugs','id','projects_bugs','project_id','bug_id','many-to-many',NULL,NULL,0,0),('9107c9b1-0c91-8c58-1138-4c218db797ee','projects_cases','Project','project','id','Cases','cases','id','projects_cases','project_id','case_id','many-to-many',NULL,NULL,0,0),('a70a604b-0ad4-8d15-7358-4c218d8845cb','projects_contacts','Project','project','id','Contacts','contacts','id','projects_contacts','project_id','contact_id','many-to-many',NULL,NULL,0,0),('c398e23c-7fed-e012-1d68-4c218dba92eb','projects_opportunities','Project','project','id','Opportunities','opportunities','id','projects_opportunities','project_id','opportunity_id','many-to-many',NULL,NULL,0,0),('db77faa3-c4c9-db49-ffdd-4c218d86f9c7','projects_products','Project','project','id','Products','products','id','projects_products','project_id','product_id','many-to-many',NULL,NULL,0,0),('94500416-e2ed-f299-c231-4c218d59e092','projects_quotes','Project','project','id','Quotes','quotes','id','projects_quotes','project_id','quote_id','many-to-many',NULL,NULL,0,0),('18dcceb2-290d-574c-6b78-4c218d3d48c5','prospect_list_campaigns','ProspectLists','prospect_lists','id','Campaigns','campaigns','id','prospect_list_campaigns','prospect_list_id','campaign_id','many-to-many',NULL,NULL,0,0),('319ad96c-2f7c-e6e3-66c0-4c218d323be7','prospect_list_contacts','ProspectLists','prospect_lists','id','Contacts','contacts','id','prospect_lists_prospects','prospect_list_id','related_id','many-to-many','related_type','Contacts',0,0),('31bf1051-fa1b-f0ea-ee19-4c218de83250','prospect_list_prospects','ProspectLists','prospect_lists','id','Prospects','prospects','id','prospect_lists_prospects','prospect_list_id','related_id','many-to-many','related_type','Prospects',0,0),('31df9119-ee0f-5246-580e-4c218d5b6da3','prospect_list_leads','ProspectLists','prospect_lists','id','Leads','leads','id','prospect_lists_prospects','prospect_list_id','related_id','many-to-many','related_type','Leads',0,0),('31ff990a-ebaa-e5dc-3483-4c218dd28597','prospect_list_users','ProspectLists','prospect_lists','id','Users','users','id','prospect_lists_prospects','prospect_list_id','related_id','many-to-many','related_type','Users',0,0),('321fcf62-9319-4224-5f43-4c218d4dc2af','prospect_list_accounts','ProspectLists','prospect_lists','id','Accounts','accounts','id','prospect_lists_prospects','prospect_list_id','related_id','many-to-many','related_type','Accounts',0,0),('4d8ae57b-0a3a-3792-2ee4-4c218d249488','quotes_billto_accounts','Quotes','quotes','id','Accounts','accounts','id','quotes_accounts','quote_id','account_id','many-to-many','account_role','Bill To',0,0),('4dde9268-1c36-a399-f8e4-4c218db4b2a8','quotes_shipto_accounts','Quotes','quotes','id','Accounts','accounts','id','quotes_accounts','quote_id','account_id','many-to-many','account_role','Ship To',0,0),('6874fda0-ec1f-a383-6200-4c218d2d54b8','quotes_contacts_shipto','Quotes','quotes','id','Contacts','contacts','id','quotes_contacts','quote_id','contact_id','many-to-many','contact_role','Ship To',0,0),('68a41658-b69a-2b7f-fd3a-4c218d2b1aac','quotes_contacts_billto','Quotes','quotes','id','Contacts','contacts','id','quotes_contacts','quote_id','contact_id','many-to-many','contact_role','Bill To',0,0),('7ea2ef5a-95db-ba4a-b801-4c218d92aa59','quotes_opportunities','Quotes','quotes','id','Opportunities','opportunities','id','quotes_opportunities','quote_id','opportunity_id','many-to-many',NULL,NULL,0,0),('ecdf1f96-8511-cbfd-2605-4c218d4bb197','roles_users','Roles','roles','id','Users','users','id','roles_users','role_id','user_id','many-to-many',NULL,NULL,0,0),('2a1de71f-df2c-69cb-635a-4c218d860f94','team_sets_teams','TeamSets','team_sets','id','Teams','teams','id','team_sets_teams','team_set_id','team_id','many-to-many',NULL,NULL,0,0),('74856d87-00b0-917b-7a65-4c218d438729','tracker_user_id','Users','users','id','TrackerSessions','tracker','user_id',NULL,NULL,NULL,'one-to-many',NULL,NULL,0,0),('92dd6501-5b56-8f4b-3738-4c218de29703','tracker_tracker_queries','Trackers','tracker','monitor_id','TrackerQueries','tracker_queries','query_id','tracker_tracker_queries','monitor_id','query_id','many-to-many',NULL,NULL,0,0),('c73878e4-a7e3-2cd9-4596-4c218d352344','users_holidays','Users','users','id','Holidays','holidays','person_id',NULL,NULL,NULL,'one-to-many','related_module','',0,0);
/*!40000 ALTER TABLE `relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `releases`
--

DROP TABLE IF EXISTS `releases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `releases` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `list_order` int(4) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_releases` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `releases`
--

LOCK TABLES `releases` WRITE;
/*!40000 ALTER TABLE `releases` DISABLE KEYS */;
/*!40000 ALTER TABLE `releases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_cache`
--

DROP TABLE IF EXISTS `report_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_cache` (
  `id` char(36) NOT NULL,
  `assigned_user_id` char(36) NOT NULL,
  `contents` text,
  `report_options` text,
  `deleted` varchar(1) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`,`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_cache`
--

LOCK TABLES `report_cache` WRITE;
/*!40000 ALTER TABLE `report_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report_schedules`
--

DROP TABLE IF EXISTS `report_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report_schedules` (
  `id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `report_id` char(36) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `next_run` datetime DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `time_interval` int(11) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `schedule_type` varchar(3) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report_schedules`
--

LOCK TABLES `report_schedules` WRITE;
/*!40000 ALTER TABLE `report_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `report_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text,
  `modules` text,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_role_id_del` (`id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_modules`
--

DROP TABLE IF EXISTS `roles_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_modules` (
  `id` varchar(36) NOT NULL,
  `role_id` varchar(36) DEFAULT NULL,
  `module_id` varchar(36) DEFAULT NULL,
  `allow` tinyint(1) DEFAULT '0',
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_module_id` (`module_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_modules`
--

LOCK TABLES `roles_modules` WRITE;
/*!40000 ALTER TABLE `roles_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles_users`
--

DROP TABLE IF EXISTS `roles_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles_users` (
  `id` varchar(36) NOT NULL,
  `role_id` varchar(36) DEFAULT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_ru_role_id` (`role_id`),
  KEY `idx_ru_user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles_users`
--

LOCK TABLES `roles_users` WRITE;
/*!40000 ALTER TABLE `roles_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_reports`
--

DROP TABLE IF EXISTS `saved_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saved_reports` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `module` varchar(36) DEFAULT NULL,
  `report_type` varchar(36) DEFAULT NULL,
  `content` longtext,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT '0',
  `chart_type` varchar(36) DEFAULT 'none',
  `schedule_type` varchar(3) DEFAULT 'pro',
  `favorite` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_saved_reports_tmst_id` (`team_set_id`),
  KEY `idx_rep_owner_module_name` (`assigned_user_id`,`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_reports`
--

LOCK TABLES `saved_reports` WRITE;
/*!40000 ALTER TABLE `saved_reports` DISABLE KEYS */;
INSERT INTO `saved_reports` VALUES ('1','1','bb7c062b-1bfe-8ec5-326d-4c218d212750','Current Quarter Forecast','Opportunities','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Name\",\"table_key\":\"Opportunities:accounts\"},{\"name\":\"amount_usdollar\",\"label\":\"Amount\",\"table_key\":\"self\"},{\"name\":\"date_closed\",\"label\":\"Expected Close Date\",\"table_key\":\"self\"},{\"name\":\"probability\",\"label\":\"Probability (%)\",\"table_key\":\"self\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Opportunities:assigned_user_link\"}],\"module\":\"Opportunities\",\"group_defs\":[],\"summary_columns\":[],\"report_name\":\"Current Quarter Forecast\",\"do_round\":1,\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\"},\"Opportunities:accounts\":{\"name\":\"Opportunities  >  Accounts\",\"parent\":\"self\",\"link_def\":{\"name\":\"accounts\",\"relationship_name\":\"accounts_opportunities\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Accounts\",\"table_key\":\"Opportunities:accounts\"},\"dependents\":[\"display_cols_row_2\"],\"module\":\"Accounts\",\"label\":\"Accounts\"},\"Opportunities:assigned_user_link\":{\"name\":\"Opportunities  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"opportunities_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Opportunities:assigned_user_link\"},\"dependents\":[\"display_cols_row_6\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"date_closed\",\"table_key\":\"self\",\"qualifier_name\":\"between_dates\",\"runtime\":1,\"input_name0\":\"2009-10-01\",\"input_name1\":\"2009-12-31\"}}},\"chart_type\":\"none\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','cb040a1f-f2bd-9905-a0de-4c218dc9638d','Detailed Forecast','Opportunities','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Account Name\",\"table_key\":\"accounts\"},{\"name\":\"amount_usdollar\",\"label\":\"Amount\",\"table_key\":\"self\"},{\"name\":\"description\",\"label\":\"Description\",\"table_key\":\"self\"},{\"name\":\"next_step\",\"label\":\"Next Step\",\"table_key\":\"self\"},{\"name\":\"date_closed\",\"label\":\"Expected Close Date\",\"table_key\":\"self\"},{\"name\":\"probability\",\"label\":\"Probability (%)\",\"table_key\":\"self\"}],\"summary_columns\":[],\"order_by\":[{\"name\":\"probability\",\"label\":\"Probability (%)\",\"table_key\":\"self\",\"sort_dir\":\"a\"}],\"filters_def\":[],\"group_defs\":[],\"links_def\":[\"accounts\",\"team_link\",\"created_by_link\",\"modified_user_link\",\"assigned_user_link\"],\"module\":\"Opportunities\",\"report_name\":\"Detailed Forecast\",\"report_type\":\"tabular\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','d26c0451-6ad0-ebc8-469a-4c218db526a5','Partner Account List','Accounts','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Account Name\",\"table_key\":\"self\"},{\"name\":\"phone_office\",\"label\":\"Phone Office\",\"table_key\":\"self\"},{\"name\":\"description\",\"label\":\"Description\",\"table_key\":\"self\"},{\"name\":\"annual_revenue\",\"label\":\"Annual Revenue\",\"table_key\":\"self\"},{\"name\":\"account_type\",\"label\":\"Type\",\"table_key\":\"self\"},{\"name\":\"full_name\",\"label\":\"Assigned to\",\"table_key\":\"assigned_user_link\"}],\"summary_columns\":[],\"filters_def\":[{\"name\":\"account_type\",\"table_key\":\"self\",\"qualifier_name\":\"is\",\"input_name0\":\"Partner\"}],\"group_defs\":[],\"links_def\":[\"member_of\",\"team_link\",\"created_by_link\",\"modified_user_link\",\"assigned_user_link\"],\"module\":\"Accounts\",\"report_name\":\"Partner Account List\",\"order_by\":[],\"report_type\":\"tabular\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','d9590d75-0def-e860-314d-4c218d9dc3d3','Customer Account List','Accounts','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Account Name\",\"table_key\":\"self\"},{\"name\":\"website\",\"label\":\"Website\",\"table_key\":\"self\"},{\"name\":\"phone_office\",\"label\":\"Phone Office\",\"table_key\":\"self\"},{\"name\":\"description\",\"label\":\"Description\",\"table_key\":\"self\"},{\"name\":\"account_type\",\"label\":\"Type\",\"table_key\":\"self\"},{\"name\":\"full_name\",\"label\":\"Assigned to\",\"table_key\":\"assigned_user_link\"}],\"summary_columns\":[],\"filters_def\":[{\"name\":\"account_type\",\"table_key\":\"self\",\"qualifier_name\":\"is\",\"input_name0\":\"Customer\"}],\"group_defs\":[],\"links_def\":[\"member_of\",\"team_link\",\"created_by_link\",\"modified_user_link\",\"assigned_user_link\"],\"module\":\"Accounts\",\"report_name\":\"Customer Account List\",\"order_by\":[],\"report_type\":\"tabular\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','e094045c-9ff8-e0be-25fb-4c218d8fc2c8','Call List By Last Date Contacted','Contacts','tabular','{\"display_columns\":[{\"name\":\"date_modified\",\"label\":\"Last Modified\",\"table_key\":\"self\"},{\"name\":\"full_name\",\"label\":\"Contact Name\",\"table_key\":\"self\"},{\"name\":\"phone_work\",\"label\":\"Office Phone\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Account Name\",\"table_key\":\"accounts\"},{\"name\":\"alt_address_country\",\"label\":\"Alternate Address Country\",\"table_key\":\"self\"},{\"name\":\"full_name\",\"label\":\"Assigned to\",\"table_key\":\"assigned_user_link\"}],\"summary_columns\":[],\"filters_def\":[{\"name\":\"do_not_call\",\"table_key\":\"self\",\"qualifier_name\":\"equals\",\"input_name0\":[\"no\"]}],\"group_defs\":[],\"links_def\":[\"accounts\",\"reports_to_link\",\"team_link\",\"created_by_link\",\"modified_user_link\",\"assigned_user_link\"],\"order_by\":[],\"module\":\"Contacts\",\"report_name\":\"Call list by last date modified\",\"report_type\":\"tabular\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','e73a0278-2a2c-ad25-6287-4c218d107d88','Opportunities By Lead Source','Opportunities','detailed_summary','{\"report_type\":\"summary\",\"display_columns\":[{\"name\":\"lead_source\",\"label\":\"Lead Source\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Account Name\",\"table_key\":\"accounts\"},{\"name\":\"amount_usdollar\",\"label\":\"Amount\",\"table_key\":\"self\"},{\"name\":\"sales_stage\",\"label\":\"Sales Stage\",\"table_key\":\"self\"},{\"name\":\"probability\",\"label\":\"Probability (%)\",\"table_key\":\"self\"},{\"name\":\"full_name\",\"label\":\"Assigned to\",\"table_key\":\"assigned_user_link\"}],\"summary_columns\":[{\"name\":\"lead_source\",\"label\":\"Opportunities: Lead Source\",\"table_key\":\"self\"},{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"}],\"order_by\":[{\"name\":\"amount_usdollar\",\"label\":\"Amount\",\"table_key\":\"self\",\"sort_dir\":\"d\"}],\"filters_def\":[],\"group_defs\":[{\"name\":\"lead_source\",\"label\":\"Lead Source\",\"table_key\":\"self\"}],\"links_def\":[\"accounts\",\"team_link\",\"created_by_link\",\"modified_user_link\",\"assigned_user_link\"],\"module\":\"Opportunities\",\"report_name\":\"Opportunities by Lead Source\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','ed4303ae-3f14-34e0-5679-4c218d90572d','Open Cases By User By Status','Cases','detailed_summary','{\"report_type\":\"summary\",\"display_columns\":[{\"name\":\"case_number\",\"label\":\"Cases: Number\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Cases: Subject\",\"table_key\":\"self\"},{\"name\":\"date_entered\",\"label\":\"Cases: Date Created\",\"table_key\":\"self\"}],\"summary_columns\":[{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"status\",\"label\":\"Cases: Status\",\"table_key\":\"self\",\"is_group_by\":\"visible\"},{\"name\":\"user_name\",\"label\":\"Assigned to User: User Name\",\"table_key\":\"self_link_0\",\"is_group_by\":\"visible\"}],\"filters_def\":[{\"name\":\"status\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"input_name0\":[\"New\",\"Assigned\",\"Pending Input\"]}],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"self_link_0\"},{\"name\":\"status\",\"label\":\"Status\",\"table_key\":\"self\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Cases\",\"module\":\"Cases\",\"label\":\"Cases\",\"children\":{\"self_link_0\":\"self_link_0\"}},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"assigned_user_link\",\"label\":\"Assigned to User\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"cases_assigned_user\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"self_link_0\"},\"module\":\"Users\"}},\"module\":\"Cases\",\"report_name\":\"Open Cases By User By Status\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','f3870516-3577-9f14-54bf-4c218d18a4cd','Open Cases By Month By User','Cases','detailed_summary','{\"report_type\":\"summary\",\"display_columns\":[{\"name\":\"case_number\",\"label\":\"Cases: Number\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Cases: Subject\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Account: Name\",\"table_key\":\"self_link_1\"}],\"summary_columns\":[{\"name\":\"date_entered\",\"label\":\"Date Created\",\"table_key\":\"self\",\"qualifier\":\"month\",\"is_group_by\":\"hidden\",\"column_function\":\"month\"},{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"date_entered\",\"label\":\"Cases: Month: Date Created\",\"column_function\":\"month\",\"table_key\":\"self\"},{\"name\":\"user_name\",\"label\":\"Assigned to User: User Name\",\"table_key\":\"self_link_0\",\"is_group_by\":\"visible\"}],\"filters_def\":[{\"name\":\"status\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"input_name0\":[\"New\",\"Assigned\",\"Pending Input\"]}],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"date_entered\",\"label\":\"Date Created\",\"table_key\":\"self\",\"qualifier\":\"month\",\"is_group_by\":\"hidden\",\"column_function\":\"month\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"self_link_0\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Cases\",\"module\":\"Cases\",\"label\":\"Cases\",\"children\":{\"self_link_0\":\"self_link_0\",\"self_link_1\":\"self_link_1\"}},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"assigned_user_link\",\"label\":\"Assigned to User\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"cases_assigned_user\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"self_link_0\"},\"module\":\"Users\"},\"self_link_1\":{\"parent\":\"self\",\"children\":[],\"value\":\"account\",\"label\":\"Account\",\"link_def\":{\"name\":\"account\",\"relationship_name\":\"account_cases\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Account\",\"table_key\":\"self_link_1\"},\"module\":\"Accounts\"}},\"module\":\"Cases\",\"report_name\":\"Open Cases By Month By User\",\"chart_type\":\"vBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'vBarF','pro',0),('1','1','f9c204ae-784d-8ef5-7a40-4c218de4a374','Open Cases By Priority By User','Cases','detailed_summary','{\"report_type\":\"summary\",\"display_columns\":[{\"name\":\"case_number\",\"label\":\"Cases: Number\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Cases: Subject\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Account: Name\",\"table_key\":\"self_link_1\"}],\"summary_columns\":[{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"priority\",\"label\":\"Cases: Priority\",\"table_key\":\"self\",\"is_group_by\":\"visible\"},{\"name\":\"user_name\",\"label\":\"Assigned to User: User Name\",\"table_key\":\"self_link_0\",\"is_group_by\":\"visible\"}],\"filters_def\":[{\"name\":\"status\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"input_name0\":[\"New\",\"Assigned\",\"Pending Input\"]}],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"self_link_0\"},{\"name\":\"priority\",\"label\":\"Priority\",\"table_key\":\"self\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Cases\",\"module\":\"Cases\",\"label\":\"Cases\",\"children\":{\"self_link_0\":\"self_link_0\",\"self_link_1\":\"self_link_1\"}},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"assigned_user_link\",\"label\":\"Assigned to User\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"cases_assigned_user\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"self_link_0\"},\"module\":\"Users\"},\"self_link_1\":{\"parent\":\"self\",\"children\":[],\"value\":\"account\",\"label\":\"Account\",\"link_def\":{\"name\":\"account\",\"relationship_name\":\"account_cases\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Account\",\"table_key\":\"self_link_1\"},\"module\":\"Accounts\"}},\"module\":\"Cases\",\"report_name\":\"Open Cases By Priority By User\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','ffde0e5b-22dc-7df9-ecca-4c218d4a6f38','New Cases By Month','Cases','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"date_entered\",\"label\":\"Date Created\",\"table_key\":\"self\",\"qualifier\":\"month\",\"is_group_by\":\"hidden\",\"column_function\":\"month\"},{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"date_entered\",\"label\":\"Cases: Month: Date Created\",\"column_function\":\"month\",\"table_key\":\"self\"}],\"filters_def\":[],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"date_entered\",\"label\":\"Date Created\",\"table_key\":\"self\",\"qualifier\":\"month\",\"is_group_by\":\"hidden\",\"column_function\":\"month\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Cases\",\"module\":\"Cases\",\"label\":\"Cases\",\"children\":[]}},\"module\":\"Cases\",\"report_name\":\"New Cases By Month\",\"chart_type\":\"vBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'vBarF','pro',0),('1','1','105d5453-f614-2e47-ecc7-4c218d5dd7aa','Pipeline By Type By Team','Opportunities','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"amount_usdollar\",\"label\":\"Opportunities: SUM: Amount\",\"group_function\":\"sum\",\"table_key\":\"self\"},{\"name\":\"opportunity_type\",\"label\":\"Opportunities: Type\",\"table_key\":\"self\",\"is_group_by\":\"visible\"},{\"name\":\"name\",\"label\":\"Team: Team Name\",\"table_key\":\"self_link_0\",\"is_group_by\":\"visible\"}],\"filters_def\":[{\"name\":\"sales_stage\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"input_name0\":[\"Prospecting\",\"Qualification\",\"Needs Analysis\",\"Value Proposition\",\"Id. Decision Makers\",\"Perception Analysis\",\"Proposal\\/Price Quote\",\"Negotiation\\/Review\"]}],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"name\",\"label\":\"Team Name\",\"table_key\":\"self_link_0\"},{\"name\":\"opportunity_type\",\"label\":\"Type\",\"table_key\":\"self\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\",\"children\":{\"self_link_0\":\"self_link_0\"}},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"team_link\",\"label\":\"Team\",\"link_def\":{\"name\":\"team_link\",\"relationship_name\":\"opportunities_team\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Team\",\"table_key\":\"self_link_0\"},\"module\":\"Teams\"}},\"module\":\"Opportunities\",\"report_name\":\"Pipeline By Type By Team\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"self:amount_usdollar:sum\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','10bd0ae5-bf0f-5c29-a4ce-4c218dcf21f9','Pipeline By Team By User','Opportunities','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"amount_usdollar\",\"label\":\"Opportunities: SUM: Amount\",\"group_function\":\"sum\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Team: Team Name\",\"table_key\":\"self_link_0\",\"is_group_by\":\"visible\"},{\"name\":\"user_name\",\"label\":\"Assigned to User: User Name\",\"table_key\":\"self_link_1\",\"is_group_by\":\"visible\"}],\"filters_def\":[{\"name\":\"sales_stage\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"input_name0\":[\"Prospecting\",\"Qualification\",\"Needs Analysis\",\"Value Proposition\",\"Id. Decision Makers\",\"Perception Analysis\",\"Proposal\\/Price Quote\",\"Negotiation\\/Review\"]}],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"name\",\"label\":\"Team Name\",\"table_key\":\"self_link_0\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"self_link_1\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\",\"children\":{\"self_link_0\":\"self_link_0\",\"self_link_1\":\"self_link_1\"}},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"team_link\",\"label\":\"Team\",\"link_def\":{\"name\":\"team_link\",\"relationship_name\":\"opportunities_team\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Team\",\"table_key\":\"self_link_0\"},\"module\":\"Teams\"},\"self_link_1\":{\"parent\":\"self\",\"children\":[],\"value\":\"assigned_user_link\",\"label\":\"Assigned to User\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"opportunities_assigned_user\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"self_link_1\"},\"module\":\"Users\"}},\"module\":\"Opportunities\",\"report_name\":\"Pipeline By Team By User\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"self:amount_usdollar:sum\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','111e2859-d320-9638-8be0-4c218da0aa6f','Opportunities Won By Lead Source','Opportunities','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"amount_usdollar\",\"label\":\"Opportunities: SUM: Amount\",\"group_function\":\"sum\",\"table_key\":\"self\"},{\"name\":\"lead_source\",\"label\":\"Opportunities: Lead Source\",\"table_key\":\"self\",\"is_group_by\":\"visible\"},{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"}],\"filters_def\":[{\"name\":\"sales_stage\",\"table_key\":\"self\",\"qualifier_name\":\"is\",\"input_name0\":[\"Closed Won\"]}],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"lead_source\",\"label\":\"Lead Source\",\"table_key\":\"self\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\",\"children\":[]}},\"module\":\"Opportunities\",\"report_name\":\"Opportunities Won By Lead Source\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"self:amount_usdollar:sum\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','117dc3de-6066-48ca-2108-4c218d6f015a','Tasks By Team By User','Tasks','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Team: Team Name\",\"table_key\":\"self_link_0\",\"is_group_by\":\"visible\"},{\"name\":\"user_name\",\"label\":\"Assigned to User: User Name\",\"table_key\":\"self_link_1\",\"is_group_by\":\"visible\"}],\"filters_def\":[],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"name\",\"label\":\"Team Name\",\"table_key\":\"self_link_0\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"self_link_1\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Tasks\",\"module\":\"Tasks\",\"label\":\"Tasks\",\"children\":{\"self_link_0\":\"self_link_0\",\"self_link_1\":\"self_link_1\"}},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"team_link\",\"label\":\"Team\",\"link_def\":{\"name\":\"team_link\",\"relationship_name\":\"tasks_team\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Team\",\"table_key\":\"self_link_0\"},\"module\":\"Teams\"},\"self_link_1\":{\"parent\":\"self\",\"children\":[],\"value\":\"assigned_user_link\",\"label\":\"Assigned to User\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tasks_assigned_user\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"self_link_1\"},\"module\":\"Users\"}},\"module\":\"Tasks\",\"report_name\":\"Tasks By Team By User\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','11dd5e1d-f963-d5a3-6996-4c218dc0c80f','Meetings By Team By User','Meetings','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Team: Team Name\",\"table_key\":\"self_link_0\",\"is_group_by\":\"visible\"},{\"name\":\"user_name\",\"label\":\"Assigned to User: User Name\",\"table_key\":\"self_link_1\",\"is_group_by\":\"visible\"}],\"filters_def\":[],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"name\",\"label\":\"Team Name\",\"table_key\":\"self_link_0\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"self_link_1\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Meetings\",\"module\":\"Meetings\",\"label\":\"Meetings\",\"children\":{\"self_link_0\":\"self_link_0\",\"self_link_1\":\"self_link_1\"}},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"team_link\",\"label\":\"Team\",\"link_def\":{\"name\":\"team_link\",\"relationship_name\":\"meetings_team\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Team\",\"table_key\":\"self_link_0\"},\"module\":\"Teams\"},\"self_link_1\":{\"parent\":\"self\",\"children\":[],\"value\":\"assigned_user_link\",\"label\":\"Assigned to User\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"meetings_assigned_user\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"self_link_1\"},\"module\":\"Users\"}},\"module\":\"Meetings\",\"report_name\":\"Calls By Team By User\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','123ce940-983f-8aee-71f7-4c218d4e0484','Calls By Team By User','Calls','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Team: Team Name\",\"table_key\":\"self_link_0\",\"is_group_by\":\"visible\"},{\"name\":\"user_name\",\"label\":\"Assigned to User: User Name\",\"table_key\":\"self_link_1\",\"is_group_by\":\"visible\"}],\"filters_def\":[],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"name\",\"label\":\"Team Name\",\"table_key\":\"self_link_0\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"self_link_1\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Calls\",\"module\":\"Calls\",\"label\":\"Calls\",\"children\":{\"self_link_0\":\"self_link_0\",\"self_link_1\":\"self_link_1\"}},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"team_link\",\"label\":\"Team\",\"link_def\":{\"name\":\"team_link\",\"relationship_name\":\"calls_team\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Team\",\"table_key\":\"self_link_0\"},\"module\":\"Teams\"},\"self_link_1\":{\"parent\":\"self\",\"children\":[],\"value\":\"assigned_user_link\",\"label\":\"Assigned to User\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"calls_assigned_user\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"self_link_1\"},\"module\":\"Users\"}},\"module\":\"Calls\",\"report_name\":\"Meetings By Team By User\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','129cb0ce-8bb0-7604-ca67-4c218d50dd4e','Accounts By Type By Industry','Accounts','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"account_type\",\"label\":\"Accounts: Type\",\"table_key\":\"self\",\"is_group_by\":\"visible\"},{\"name\":\"industry\",\"label\":\"Accounts: Industry\",\"table_key\":\"self\",\"is_group_by\":\"visible\"}],\"filters_def\":[],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"account_type\",\"label\":\"Type\",\"table_key\":\"self\"},{\"name\":\"industry\",\"label\":\"Industry\",\"table_key\":\"self\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Accounts\",\"module\":\"Accounts\",\"label\":\"Accounts\",\"children\":[]}},\"module\":\"Accounts\",\"report_name\":\"Accounts By Type By Industry\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','12fb7a5f-7ecf-64ed-8270-4c218d4469f7','Leads By Lead Source','Leads','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"count\",\"label\":\"Count\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"lead_source\",\"label\":\"Leads: Lead Source\",\"table_key\":\"self\",\"is_group_by\":\"visible\"}],\"filters_def\":[],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"lead_source\",\"label\":\"Lead Source\",\"table_key\":\"self\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Leads\",\"module\":\"Leads\",\"label\":\"Leads\",\"children\":{\"self_link_0\":\"self_link_0\"}},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"assigned_user_link\",\"label\":\"Assigned To User\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"leads_assigned_user\",\"bean_is_lhs\":\"\",\"link_type\":\"one\",\"label\":\"Assigned To User\",\"table_key\":\"self_link_0\"},\"module\":\"Users\"}},\"module\":\"Leads\",\"report_name\":\"Leads By Lead Source\",\"chart_type\":\"vBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'vBarF','pro',0),('1','1','1359f0f2-4c29-ca99-ab48-4c218d413ab7','Customer Account Owners','Accounts','summary','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Name\",\"table_key\":\"self\"},{\"name\":\"website\",\"label\":\"Website\",\"table_key\":\"self\"},{\"name\":\"phone_office\",\"label\":\"Phone Office\",\"table_key\":\"self\"},{\"name\":\"billing_address_city\",\"label\":\"Billing City\",\"table_key\":\"self\"},{\"name\":\"billing_address_country\",\"label\":\"Billing Country\",\"table_key\":\"self\"}],\"module\":\"Accounts\",\"group_defs\":[{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Accounts:assigned_user_link\",\"type\":\"user_name\"}],\"summary_columns\":[{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Accounts:assigned_user_link\"},{\"name\":\"count\",\"label\":\"Count\",\"field_type\":\"\",\"group_function\":\"count\",\"table_key\":\"self\"}],\"order_by\":[{\"name\":\"name\",\"type\":\"name\",\"dbType\":\"varchar\",\"vname\":\"Name\",\"len\":\"150\",\"unified_search\":\"1\",\"audited\":\"1\",\"required\":\"1\",\"importable\":\"required\",\"merge_filter\":\"selected\",\"table_key\":\"self\",\"sort_dir\":\"a\"}],\"report_name\":\"Customer Account Owners\",\"chart_type\":\"none\",\"do_round\":1,\"chart_description\":\"Customer Account Owners\",\"numerical_chart_column\":\"self:count\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Accounts\",\"module\":\"Accounts\",\"label\":\"Accounts\"},\"Accounts:assigned_user_link\":{\"name\":\"Accounts > Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"accounts_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Accounts:assigned_user_link\"},\"dependents\":[\"Filter.1_table_filter_row_1\",\"group_by_row_1\",\"display_summaries_row_group_by_row_1\",\"Filter.1_table_filter_row_1\",\"group_by_row_1\",\"display_summaries_row_group_by_row_1\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"user_name\",\"table_key\":\"Accounts:assigned_user_link\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Current User\"]}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','13c14aaf-4712-9738-7ec6-4c218dad347c','My New Customer Accounts','Accounts','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Name\",\"table_key\":\"self\"},{\"name\":\"website\",\"label\":\"Website\",\"table_key\":\"self\"},{\"name\":\"phone_office\",\"label\":\"Phone Office\",\"table_key\":\"self\"},{\"name\":\"billing_address_city\",\"label\":\"Billing City\",\"table_key\":\"self\"},{\"name\":\"billing_address_country\",\"label\":\"Billing Country\",\"table_key\":\"self\"}],\"module\":\"Accounts\",\"group_defs\":[],\"summary_columns\":[],\"report_name\":\"My New Customer Accounts\",\"do_round\":1,\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Accounts\",\"module\":\"Accounts\",\"label\":\"Accounts\"},\"Accounts:assigned_user_link\":{\"name\":\"Accounts  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"accounts_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Accounts:assigned_user_link\"},\"dependents\":[\"Filter.1_table_filter_row_2\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"account_type\",\"table_key\":\"self\",\"qualifier_name\":\"is\",\"input_name0\":[\"Customer\"]},\"1\":{\"name\":\"user_name\",\"table_key\":\"Accounts:assigned_user_link\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]},\"2\":{\"name\":\"date_entered\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"runtime\":1,\"input_name0\":\"undefined\",\"input_name1\":\"on\"}}},\"chart_type\":\"none\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1422e700-563f-3477-d164-4c218d082dfd','Opportunities By Sales Stage','Opportunities','summary','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"amount_usdollar\",\"label\":\"Amount\",\"table_key\":\"self\"},{\"name\":\"date_closed\",\"label\":\"Expected Close Date\",\"table_key\":\"self\"},{\"name\":\"description\",\"label\":\"Description\",\"table_key\":\"self\"},{\"name\":\"opportunity_type\",\"label\":\"Type\",\"table_key\":\"self\"},{\"name\":\"probability\",\"label\":\"Probability (%)\",\"table_key\":\"self\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Opportunities:assigned_user_link\"}],\"module\":\"Opportunities\",\"group_defs\":[{\"name\":\"sales_stage\",\"label\":\"Sales Stage\",\"table_key\":\"self\",\"type\":\"enum\"}],\"summary_columns\":[{\"name\":\"sales_stage\",\"label\":\"Sales Stage\",\"table_key\":\"self\"},{\"name\":\"count\",\"label\":\"Count\",\"field_type\":\"\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"amount_usdollar\",\"label\":\"AVG: Amount\",\"field_type\":\"currency\",\"group_function\":\"avg\",\"table_key\":\"self\"},{\"name\":\"amount_usdollar\",\"label\":\"SUM: Amount\",\"field_type\":\"currency\",\"group_function\":\"sum\",\"table_key\":\"self\"}],\"order_by\":[{\"name\":\"date_closed\",\"label\":\"Expected Close Date\",\"table_key\":\"self\",\"sort_dir\":\"a\"}],\"report_name\":\"Opportunities By Stage\",\"chart_type\":\"none\",\"do_round\":1,\"chart_description\":\"Opportunities By Stage\",\"numerical_chart_column\":\"self:count\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\"},\"Opportunities:assigned_user_link\":{\"name\":\"Opportunities  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"opportunities_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Opportunities:assigned_user_link\"},\"dependents\":[\"display_cols_row_11\",\"Filter.1_table_filter_row_2\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"sales_stage\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Prospecting\",\"Qualification\",\"Needs Analysis\",\"Value Proposition\",\"Id. Decision Makers\",\"Perception Analysis\",\"Proposal\\/Price Quote\",\"Negotiation\\/Review\",\"Closed Won\",\"Closed Lost\"],\"column_name\":\"self:sales_stage\",\"id\":\"rowid0\"},\"1\":{\"name\":\"user_name\",\"table_key\":\"Opportunities:assigned_user_link\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"1\",\"seed_chris_id\",\"seed_jim_id\",\"seed_max_id\",\"seed_sally_id\",\"seed_sarah_id\",\"seed_will_id\"],\"column_name\":\"Opportunities:assigned_user_link:user_name\",\"id\":\"rowid1\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1491c1cc-e671-19b2-d985-4c218d21ad16','Opportunities By Type','Opportunities','summary','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"amount_usdollar\",\"label\":\"Amount\",\"table_key\":\"self\"},{\"name\":\"date_closed\",\"label\":\"Expected Close Date\",\"table_key\":\"self\"},{\"name\":\"sales_stage\",\"label\":\"Sales Stage\",\"table_key\":\"self\"},{\"name\":\"description\",\"label\":\"Description\",\"table_key\":\"self\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Opportunities:assigned_user_link\"}],\"module\":\"Opportunities\",\"group_defs\":[{\"name\":\"opportunity_type\",\"label\":\"Type\",\"table_key\":\"self\",\"type\":\"enum\"},{\"name\":\"sales_stage\",\"label\":\"Sales Stage\",\"table_key\":\"self\",\"type\":\"enum\"}],\"summary_columns\":[{\"name\":\"opportunity_type\",\"label\":\"Type\",\"table_key\":\"self\"},{\"name\":\"sales_stage\",\"label\":\"Sales Stage\",\"table_key\":\"self\"},{\"name\":\"count\",\"label\":\"Count\",\"field_type\":\"\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"amount_usdollar\",\"label\":\"AVG: Amount\",\"field_type\":\"currency\",\"group_function\":\"avg\",\"table_key\":\"self\"},{\"name\":\"amount_usdollar\",\"label\":\"SUM: Amount\",\"field_type\":\"currency\",\"group_function\":\"sum\",\"table_key\":\"self\"}],\"report_name\":\"Opportunities By Type\",\"chart_type\":\"none\",\"do_round\":1,\"chart_description\":\"Opportunities By Type\",\"numerical_chart_column\":\"self:count\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\"},\"Opportunities:assigned_user_link\":{\"name\":\"Opportunities  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"opportunities_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Opportunities:assigned_user_link\"},\"dependents\":[\"Filter.1_table_filter_row_3\",\"display_cols_row_12\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"opportunity_type\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Existing Business\",\"New Business\"]},\"1\":{\"name\":\"sales_stage\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Prospecting\",\"Qualification\",\"Needs Analysis\",\"Value Proposition\",\"Id. Decision Makers\",\"Perception Analysis\",\"Proposal\\/Price Quote\",\"Negotiation\\/Review\",\"Closed Won\",\"Closed Lost\"]},\"2\":{\"name\":\"user_name\",\"table_key\":\"Opportunities:assigned_user_link\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Current User\",\"1\",\"seed_chris_id\",\"seed_jim_id\",\"seed_max_id\",\"seed_sally_id\",\"seed_sarah_id\",\"seed_will_id\"]}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','15079dc5-5410-3e70-bc39-4c218dafd401','Open Calls','Calls','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Subject\",\"table_key\":\"self\"},{\"name\":\"status\",\"label\":\"Status\",\"table_key\":\"self\"},{\"name\":\"date_entered\",\"label\":\"Date Created\",\"table_key\":\"self\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Calls:assigned_user_link\"}],\"module\":\"Calls\",\"group_defs\":[],\"summary_columns\":[],\"order_by\":[{\"name\":\"date_entered\",\"vname\":\"Date Created\",\"type\":\"datetime\",\"group\":\"created_by_name\",\"table_key\":\"self\",\"sort_dir\":\"a\"}],\"report_name\":\"Open Calls\",\"do_round\":1,\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Calls\",\"module\":\"Calls\",\"label\":\"Calls\"},\"Calls:assigned_user_link\":{\"name\":\"Calls  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"calls_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Calls:assigned_user_link\"},\"dependents\":[\"display_cols_row_4\",\"Filter.1_table_filter_row_3\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"status\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Planned\",\"Not Held\"]},\"1\":{\"name\":\"date_start\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"runtime\":1,\"input_name0\":\"undefined\",\"input_name1\":\"on\"},\"2\":{\"name\":\"user_name\",\"table_key\":\"Calls:assigned_user_link\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Current User\"]}}},\"chart_type\":\"none\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','156b5bb8-9fea-e35d-769c-4c218d2eebcd','Open Meetings','Meetings','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Subject\",\"table_key\":\"self\"},{\"name\":\"description\",\"label\":\"Description\",\"table_key\":\"self\"},{\"name\":\"date_entered\",\"label\":\"Date Created\",\"table_key\":\"self\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Meetings:assigned_user_link\"}],\"module\":\"Meetings\",\"group_defs\":[],\"summary_columns\":[],\"order_by\":[{\"name\":\"date_entered\",\"vname\":\"Date Created\",\"type\":\"datetime\",\"group\":\"created_by_name\",\"table_key\":\"self\",\"sort_dir\":\"a\"}],\"report_name\":\"Open Meetings\",\"do_round\":1,\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Meetings\",\"module\":\"Meetings\",\"label\":\"Meetings\"},\"Meetings:assigned_user_link\":{\"name\":\"Meetings  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"meetings_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Meetings:assigned_user_link\"},\"dependents\":[\"Filter.1_table_filter_row_3\",\"display_cols_row_4\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"status\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Planned\",\"Not Held\"]},\"1\":{\"name\":\"date_start\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"runtime\":1,\"input_name0\":\"undefined\",\"input_name1\":\"on\"},\"2\":{\"name\":\"user_name\",\"table_key\":\"Meetings:assigned_user_link\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Current User\"]}}},\"chart_type\":\"none\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','15cc7acf-0956-abbd-1ca7-4c218d1fde11','Open Tasks','Tasks','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Subject\",\"table_key\":\"self\"},{\"name\":\"priority\",\"label\":\"Priority\",\"table_key\":\"self\"},{\"name\":\"status\",\"label\":\"Status\",\"table_key\":\"self\"},{\"name\":\"date_entered\",\"label\":\"Date Created\",\"table_key\":\"self\"},{\"name\":\"date_due\",\"label\":\"Due Date\",\"table_key\":\"self\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Tasks:assigned_user_link\"}],\"module\":\"Tasks\",\"group_defs\":[],\"summary_columns\":[],\"order_by\":[{\"name\":\"date_due\",\"vname\":\"Due Date\",\"type\":\"datetime\",\"group\":\"date_due\",\"table_key\":\"self\",\"sort_dir\":\"d\"}],\"report_name\":\"Open Tasks\",\"do_round\":1,\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Tasks\",\"module\":\"Tasks\",\"label\":\"Tasks\"},\"Tasks:assigned_user_link\":{\"name\":\"Tasks  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tasks_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Tasks:assigned_user_link\"},\"dependents\":[\"Filter.1_table_filter_row_4\",\"display_cols_row_6\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"status\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Not Started\",\"In Progress\",\"Pending Input\"]},\"1\":{\"name\":\"date_entered\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"runtime\":1,\"input_name0\":\"undefined\",\"input_name1\":\"on\"},\"2\":{\"name\":\"user_name\",\"table_key\":\"Tasks:assigned_user_link\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Current User\"]}}},\"chart_type\":\"none\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1630ee49-6b53-b85b-b261-4c218d1d50e4','Opportunities Won By Account','Opportunities','detailed_summary','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"name\",\"label\":\"Account Name\",\"table_key\":\"Opportunities:accounts\"},{\"name\":\"date_closed\",\"label\":\"Expected Close Date\",\"table_key\":\"self\"}],\"module\":\"Opportunities\",\"group_defs\":[{\"name\":\"name\",\"label\":\"Account Name\",\"table_key\":\"Opportunities:accounts\",\"type\":\"name\"}],\"summary_columns\":[{\"name\":\"name\",\"label\":\"Account Name\",\"table_key\":\"Opportunities:accounts\"}],\"report_name\":\"Opportunities Won By Account\",\"chart_type\":\"none\",\"do_round\":1,\"chart_description\":\"Opportunities Won By Account\",\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"summary\",\"order_by\":[{\"name\":\"date_closed\",\"vname\":\"Expected Close Date\",\"type\":\"date\",\"audited\":\"1\",\"importable\":\"required\",\"table_key\":\"self\",\"sort_dir\":\"a\"}],\"full_table_list\":{\"self\":{\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\"},\"Opportunities:accounts\":{\"name\":\"Opportunities  >  Accounts\",\"parent\":\"self\",\"link_def\":{\"name\":\"accounts\",\"relationship_name\":\"accounts_opportunities\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Accounts\",\"table_key\":\"Opportunities:accounts\"},\"dependents\":[\"group_by_row_1\",\"display_summaries_row_group_by_row_1\",\"display_cols_row_3\",\"group_by_row_1\",\"display_summaries_row_group_by_row_1\",\"display_cols_row_3\",\"group_by_row_1\",\"display_summaries_row_group_by_row_1\",\"display_cols_row_3\",\"Filter.1_table_filter_row_4\",\"Filter.1_table_filter_row_2\",\"group_by_row_1\",\"display_summaries_row_group_by_row_1\",\"display_cols_row_3\"],\"module\":\"Accounts\",\"label\":\"Accounts\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"sales_stage\",\"table_key\":\"self\",\"qualifier_name\":\"is\",\"runtime\":1,\"input_name0\":[\"Closed Won\"]},\"1\":{\"name\":\"name\",\"table_key\":\"Opportunities:accounts\",\"qualifier_name\":\"not_empty\",\"runtime\":1,\"input_name0\":\"not_empty\",\"input_name1\":\"on\"},\"2\":{\"name\":\"date_closed\",\"table_key\":\"self\",\"qualifier_name\":\"not_empty\",\"runtime\":1,\"input_name0\":\"not_empty\",\"input_name1\":\"on\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','169473b6-faf7-05a6-1d15-4c218dde56ea','Opportunities Won By User','Opportunities','detailed_summary','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"date_closed\",\"label\":\"Expected Close Date\",\"table_key\":\"self\"}],\"module\":\"Opportunities\",\"group_defs\":[{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Opportunities:assigned_user_link\",\"type\":\"user_name\"}],\"summary_columns\":[{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Opportunities:assigned_user_link\"}],\"order_by\":[{\"name\":\"date_closed\",\"vname\":\"Expected Close Date\",\"type\":\"date\",\"audited\":\"1\",\"importable\":\"required\",\"table_key\":\"self\",\"sort_dir\":\"a\"}],\"report_name\":\"Opportunities Won By User\",\"chart_type\":\"none\",\"do_round\":1,\"chart_description\":\"\",\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\"},\"Opportunities:assigned_user_link\":{\"name\":\"Opportunities  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"opportunities_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Opportunities:assigned_user_link\"},\"dependents\":[\"Filter.1_table_filter_row_2\",\"group_by_row_1\",\"display_summaries_row_group_by_row_1\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"sales_stage\",\"table_key\":\"self\",\"qualifier_name\":\"is\",\"input_name0\":[\"Closed Won\"]},\"1\":{\"name\":\"user_name\",\"table_key\":\"Opportunities:assigned_user_link\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Current User\",\"1\",\"seed_chris_id\",\"seed_jim_id\",\"seed_max_id\",\"seed_sally_id\",\"seed_sarah_id\",\"seed_will_id\"]},\"2\":{\"name\":\"date_closed\",\"table_key\":\"self\",\"qualifier_name\":\"not_empty\",\"runtime\":1,\"input_name0\":\"undefined\",\"input_name1\":\"on\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','16f70870-71b4-3d1c-403a-4c218d8222cc','All Open Opportunities','Opportunities','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"opportunity_type\",\"label\":\"Type\",\"table_key\":\"self\"},{\"name\":\"sales_stage\",\"label\":\"Sales Stage\",\"table_key\":\"self\"},{\"name\":\"date_closed\",\"label\":\"Expected Close Date\",\"table_key\":\"self\"},{\"name\":\"amount_usdollar\",\"label\":\"Amount\",\"table_key\":\"self\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Opportunities:assigned_user_link\"}],\"module\":\"Opportunities\",\"group_defs\":[],\"summary_columns\":[],\"order_by\":[{\"name\":\"date_closed\",\"vname\":\"Expected Close Date\",\"type\":\"date\",\"audited\":\"1\",\"importable\":\"required\",\"table_key\":\"self\",\"sort_dir\":\"a\"}],\"report_name\":\"All Open Opportunities\",\"chart_type\":\"none\",\"do_round\":1,\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\"},\"Opportunities:assigned_user_link\":{\"name\":\"Opportunities  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"opportunities_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Opportunities:assigned_user_link\"},\"dependents\":[\"display_cols_row_6\",\"display_cols_row_6\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"sales_stage\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Prospecting\",\"Qualification\",\"Needs Analysis\",\"Value Proposition\",\"Id. Decision Makers\",\"Perception Analysis\",\"Proposal\\/Price Quote\",\"Negotiation\\/Review\"]},\"1\":{\"name\":\"date_closed\",\"table_key\":\"self\",\"qualifier_name\":\"not_empty\",\"runtime\":1,\"input_name0\":\"undefined\",\"input_name1\":\"on\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','175c9582-1f9e-580f-4431-4c218dec52aa','All Closed Opportunities','Opportunities','tabular','{\"display_columns\":[{\"name\":\"name\",\"label\":\"Opportunity Name\",\"table_key\":\"self\"},{\"name\":\"opportunity_type\",\"label\":\"Type\",\"table_key\":\"self\"},{\"name\":\"sales_stage\",\"label\":\"Sales Stage\",\"table_key\":\"self\"},{\"name\":\"date_closed\",\"label\":\"Expected Close Date\",\"table_key\":\"self\"},{\"name\":\"amount_usdollar\",\"label\":\"Amount\",\"table_key\":\"self\"},{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"Opportunities:assigned_user_link\"}],\"module\":\"Opportunities\",\"group_defs\":[],\"summary_columns\":[],\"order_by\":[{\"name\":\"date_closed\",\"vname\":\"Expected Close Date\",\"type\":\"date\",\"audited\":\"1\",\"importable\":\"required\",\"table_key\":\"self\",\"sort_dir\":\"a\"}],\"report_name\":\"All Closed Opportunities\",\"do_round\":1,\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Opportunities\",\"module\":\"Opportunities\",\"label\":\"Opportunities\"},\"Opportunities:assigned_user_link\":{\"name\":\"Opportunities  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"opportunities_assigned_user\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Opportunities:assigned_user_link\"},\"dependents\":[\"display_cols_row_8\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"sales_stage\",\"table_key\":\"self\",\"qualifier_name\":\"one_of\",\"runtime\":1,\"input_name0\":[\"Closed Won\",\"Closed Lost\"]},\"1\":{\"name\":\"date_closed\",\"table_key\":\"self\",\"qualifier_name\":\"not_empty\",\"runtime\":1,\"input_name0\":\"undefined\",\"input_name1\":\"on\"}}},\"chart_type\":\"none\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','17bba29d-2400-01a6-3c49-4c218d4d892b','My Usage Metrics (Today)','TrackerPerfs','summary','{\"display_columns\":[],\"module\":\"Trackers\",\"report_name\":\"My Usage Metrics (Today)\",\"group_defs\":[],\"summary_columns\":[{\"name\":\"server_response_time\",\"label\":\"Total Server Response Time (secs)\",\"group_function\":\"sum\",\"table_key\":\"Trackers:tracker_monitor_id\"},{\"name\":\"db_round_trips\",\"label\":\"Total Database Roundtrips\",\"group_function\":\"sum\",\"table_key\":\"Trackers:tracker_monitor_id\"},{\"name\":\"files_opened\",\"label\":\"Total Files Accessed\",\"group_function\":\"sum\",\"table_key\":\"Trackers:tracker_monitor_id\"}],\"chart_type\":\"none\",\"chart_description\":\"My Usage Metrics (Today)\",\"numerical_chart_column\":\"Trackers>tracker_monitor_id->server_response_time\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:tracker_user_id\":{\"name\":\"Trackers  >  Assigned to User\",\"label\":\"Users\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:tracker_user_id\"},\"dependents\":[\"Filter_1_table_filter_row_1\"],\"module\":\"Users\"},\"Trackers:tracker_monitor_id\":{\"name\":\"Trackers  >  Monitor Id\",\"label\":\"TrackerPerfs\",\"parent\":\"self\",\"link_def\":{\"name\":\"monitor_id_link\",\"relationship_name\":\"tracker_monitor_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Monitor Id\",\"table_key\":\"Trackers:tracker_monitor_id\"},\"dependents\":[\"display_summaries_row_1\",\"display_summaries_row_2\",\"display_summaries_row_3\"],\"module\":\"TrackerPerfs\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"user_name\",\"table_key\":\"Trackers:tracker_user_id\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]},\"1\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_today\",\"input_name0\":\"tp_today\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','181e0d31-ed9a-5038-09bf-4c218db018e5','My Usage Metrics (Last 7 Days)','TrackerPerfs','summary','{\"display_columns\":[],\"module\":\"Trackers\",\"report_name\":\"My Usage Metrics (Last 7 Days)\",\"group_defs\":[],\"summary_columns\":[{\"name\":\"server_response_time\",\"label\":\"Total Server Response Time (secs)\",\"group_function\":\"sum\",\"table_key\":\"Trackers:tracker_monitor_id\"},{\"name\":\"db_round_trips\",\"label\":\"Total Database Roundtrips\",\"group_function\":\"sum\",\"table_key\":\"Trackers:tracker_monitor_id\"},{\"name\":\"files_opened\",\"label\":\"Total Files Accessed\",\"group_function\":\"sum\",\"table_key\":\"Trackers:tracker_monitor_id\"}],\"chart_type\":\"none\",\"chart_description\":\"My Usage Metrics (Last 7 Days)\",\"numerical_chart_column\":\"Trackers>tracker_monitor_id->server_response_time\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:tracker_user_id\":{\"name\":\"Trackers  >  Assigned to User\",\"label\":\"Users\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:tracker_user_id\"},\"dependents\":[\"Filter_1_table_filter_row_1\"],\"module\":\"Users\"},\"Trackers:tracker_monitor_id\":{\"name\":\"Trackers  >  Monitor Id\",\"label\":\"TrackerPerfs\",\"parent\":\"self\",\"link_def\":{\"name\":\"monitor_id_link\",\"relationship_name\":\"tracker_monitor_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Monitor Id\",\"table_key\":\"Trackers:tracker_monitor_id\"},\"dependents\":[\"display_summaries_row_1\",\"display_summaries_row_2\",\"display_summaries_row_3\"],\"module\":\"TrackerPerfs\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"user_name\",\"table_key\":\"Trackers:tracker_user_id\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]},\"1\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"input_name0\":\"tp_last_7_days\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1881c31c-97b2-9576-d91a-4c218d0bd720','My Usage Metrics (Last 30 Days)','TrackerPerfs','summary','{\"display_columns\":[],\"module\":\"Trackers\",\"report_name\":\"My Usage Metrics (Last 30 Days)\",\"group_defs\":[],\"summary_columns\":[{\"name\":\"server_response_time\",\"label\":\"Total Server Response Time (secs)\",\"group_function\":\"sum\",\"table_key\":\"Trackers:tracker_monitor_id\"},{\"name\":\"db_round_trips\",\"label\":\"Total Database Roundtrips\",\"group_function\":\"sum\",\"table_key\":\"Trackers:tracker_monitor_id\"},{\"name\":\"files_opened\",\"label\":\"Total Files Accessed\",\"group_function\":\"sum\",\"table_key\":\"Trackers:tracker_monitor_id\"}],\"chart_type\":\"none\",\"chart_description\":\"My Usage Metrics (Last 30 Days)\",\"numerical_chart_column\":\"Trackers>tracker_monitor_id->server_response_time\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:tracker_user_id\":{\"name\":\"Trackers  >  Assigned to User\",\"label\":\"Users\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:tracker_user_id\"},\"dependents\":[\"Filter_1_table_filter_row_1\"],\"module\":\"Users\"},\"Trackers:tracker_monitor_id\":{\"name\":\"Trackers  >  Monitor Id\",\"label\":\"TrackerPerfs\",\"parent\":\"self\",\"link_def\":{\"name\":\"monitor_id_link\",\"relationship_name\":\"tracker_monitor_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Monitor Id\",\"table_key\":\"Trackers:tracker_monitor_id\"},\"dependents\":[\"display_summaries_row_1\",\"display_summaries_row_2\",\"display_summaries_row_3\"],\"module\":\"TrackerPerfs\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"user_name\",\"table_key\":\"Trackers:tracker_user_id\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]},\"1\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_30_days\",\"input_name0\":\"tp_last_30_days\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','18e50e6d-dd33-6cbd-8b48-4c218de4ff3d','My Module Usage (Today)','Trackers','summary','{\"display_columns\":[],\"module\":\"Trackers\",\"report_name\":\"My Module Usage (Today)\",\"group_defs\":[{\"name\":\"module_name\",\"label\":\"Module Name\",\"table_key\":\"self\"}],\"summary_columns\":[{\"name\":\"module_name\",\"label\":\"Trackers > Module Name\",\"table_key\":\"self\"},{\"name\":\"count\",\"label\":\"Trackers > Count\",\"group_function\":\"count\",\"table_key\":\"self\"}],\"chart_type\":\"hBarF\",\"chart_description\":\"My Module Usage (Today)\",\"numerical_chart_column\":\"count\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:tracker_user_id\":{\"name\":\"Trackers  >  Assigned to User\",\"label\":\"Users\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:tracker_user_id\"},\"dependents\":[\"Filter_1_table_filter_row_1\"],\"module\":\"Users\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"user_name\",\"table_key\":\"Trackers:tracker_user_id\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]},\"1\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_today\",\"input_name0\":\"tp_today\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','19447c34-7354-3091-9b9e-4c218d8b9c6f','My Module Usage (Last 7 Days)','Trackers','summary','{\"display_columns\":[],\"module\":\"Trackers\",\"report_name\":\"My Module Usage (Last 7 Days)\",\"group_defs\":[{\"name\":\"module_name\",\"label\":\"Module Name\",\"table_key\":\"self\"}],\"summary_columns\":[{\"name\":\"module_name\",\"label\":\"Trackers > Module Name\",\"table_key\":\"self\"},{\"name\":\"count\",\"label\":\"Trackers > Count\",\"group_function\":\"count\",\"table_key\":\"self\"}],\"chart_type\":\"hBarF\",\"chart_description\":\"My Module Usage (Last 7 Days)\",\"numerical_chart_column\":\"count\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:tracker_user_id\":{\"name\":\"Trackers  >  Assigned to User\",\"label\":\"Users\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:tracker_user_id\"},\"dependents\":[\"Filter_1_table_filter_row_1\"],\"module\":\"Users\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"user_name\",\"table_key\":\"Trackers:tracker_user_id\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]},\"1\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"input_name0\":\"tp_last_7_days\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','19a43881-f7fd-4427-f02c-4c218d349556','My Module Usage (Last 30 Days)','Trackers','summary','{\"display_columns\":[],\"module\":\"Trackers\",\"report_name\":\"My Module Usage (Last 30 Days)\",\"group_defs\":[{\"name\":\"module_name\",\"label\":\"Module Name\",\"table_key\":\"self\"}],\"summary_columns\":[{\"name\":\"module_name\",\"label\":\"Trackers > Module Name\",\"table_key\":\"self\"},{\"name\":\"count\",\"label\":\"Trackers > Count\",\"group_function\":\"count\",\"table_key\":\"self\"}],\"chart_type\":\"hBarF\",\"chart_description\":\"My Module Usage (Last 30 Days)\",\"numerical_chart_column\":\"count\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:tracker_user_id\":{\"name\":\"Trackers  >  Assigned to User\",\"label\":\"Users\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:tracker_user_id\"},\"dependents\":[\"Filter_1_table_filter_row_1\"],\"module\":\"Users\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"user_name\",\"table_key\":\"Trackers:tracker_user_id\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]},\"1\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_30_days\",\"input_name0\":\"tp_last_30_days\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','1a0555c1-7780-8727-5849-4c218ddb2865','Users Usage Metrics (Last 7 Days)','TrackerPerfs','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"user_id\",\"label\":\"User Id\",\"table_key\":\"self\",\"is_group_by\":\"visible\"},{\"name\":\"server_response_time\",\"label\":\"Total Server Response Time (secs)\",\"group_function\":\"sum\",\"table_key\":\"self_link_0\"},{\"name\":\"db_round_trips\",\"label\":\"Total Database Roundtrips\",\"group_function\":\"sum\",\"table_key\":\"self_link_0\"},{\"name\":\"files_opened\",\"label\":\"Total Files Accessed\",\"group_function\":\"sum\",\"table_key\":\"self_link_0\"}],\"filters_def\":[{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"input_name0\":\"tp_last_7_days\"}],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"user_id\",\"label\":\"User Id\",\"table_key\":\"self\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Tracker\",\"children\":[]},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"monitor_id_link\",\"label\":\"Monitor Id\",\"link_def\":{\"name\":\"monitor_id_link\",\"relationship_name\":\"tracker_monitor_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Monitor Id\",\"table_key\":\"self_link_0\"},\"module\":\"TrackerPerfs\"}},\"module\":\"Trackers\",\"report_name\":\"Users Usage Metrics (Last 7 Days)\",\"chart_type\":\"none\",\"chart_description\":\"Users Usage Metrics (Last 7 Days)\",\"numerical_chart_column\":\"self_link_0:server_response_time:sum\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1a65b15f-24be-337d-98ee-4c218d51d961','Users Usage Metrics (Last 30 Days)','TrackerPerfs','summary','{\"report_type\":\"summary\",\"display_columns\":[],\"summary_columns\":[{\"name\":\"user_id\",\"label\":\"User Id\",\"table_key\":\"self\",\"is_group_by\":\"visible\"},{\"name\":\"server_response_time\",\"label\":\"Total Server Response Time (secs)\",\"group_function\":\"sum\",\"table_key\":\"self_link_0\"},{\"name\":\"db_round_trips\",\"label\":\"Total Database Roundtrips\",\"group_function\":\"sum\",\"table_key\":\"self_link_0\"},{\"name\":\"files_opened\",\"label\":\"Total Files Accessed\",\"group_function\":\"sum\",\"table_key\":\"self_link_0\"}],\"filters_def\":[{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_30_days\",\"input_name0\":\"tp_last_30_days\"}],\"filters_combiner\":\"AND\",\"group_defs\":[{\"name\":\"user_id\",\"label\":\"User Id\",\"table_key\":\"self\"}],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Tracker\",\"children\":[]},\"self_link_0\":{\"parent\":\"self\",\"children\":[],\"value\":\"monitor_id_link\",\"label\":\"Monitor Id\",\"link_def\":{\"name\":\"monitor_id_link\",\"relationship_name\":\"tracker_monitor_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Monitor Id\",\"table_key\":\"self_link_0\"},\"module\":\"TrackerPerfs\"}},\"module\":\"Trackers\",\"report_name\":\"Users Usage Metrics (Last 30 Days)\",\"chart_type\":\"none\",\"chart_description\":\"Users Usage Metrics (Last 30 Days)\",\"numerical_chart_column\":\"self_link_0:server_response_time:sum\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1ac7c95b-b490-836c-afaa-4c218df4d69f','Modules Used By My Direct Reports (Last 30 Days)','Trackers','summary','{\"display_columns\":[],\"module\":\"Trackers\",\"group_defs\":[{\"name\":\"module_name\",\"label\":\"Module Name\",\"table_key\":\"self\",\"type\":\"varchar\"}],\"summary_columns\":[{\"name\":\"module_name\",\"label\":\"Module Name\",\"table_key\":\"self\"},{\"name\":\"count\",\"label\":\"Count\",\"field_type\":\"\",\"group_function\":\"count\",\"table_key\":\"self\"}],\"report_name\":\"Modules Used By My Direct Reports (Last 30 Days)\",\"chart_type\":\"hBarF\",\"chart_description\":\"\",\"numerical_chart_column\":\"self:count\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:assigned_user_link\":{\"name\":\"Trackers  >  Assigned to User \",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:assigned_user_link\"},\"dependents\":[\"Filter.1_table_filter_row_3\"],\"module\":\"Users\",\"label\":\"Assigned to User\"},\"Trackers:assigned_user_link:reports_to_link\":{\"name\":\"Trackers  >  Assigned to User  >  Reports To\",\"parent\":\"Trackers:assigned_user_link\",\"link_def\":{\"name\":\"reports_to_link\",\"relationship_name\":\"user_direct_reports\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Reports to\",\"table_key\":\"Trackers:assigned_user_link:reports_to_link\"},\"dependents\":[\"Filter.1_table_filter_row_3\"],\"module\":\"Users\",\"label\":\"Reports to\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"module_name\",\"table_key\":\"self\",\"qualifier_name\":\"not_equals_str\",\"input_name0\":\"UserPreferences\",\"input_name1\":\"on\"},\"1\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_30_days\",\"input_name0\":\"undefined\",\"input_name1\":\"on\"},\"2\":{\"name\":\"user_name\",\"table_key\":\"Trackers:assigned_user_link:reports_to_link\",\"qualifier_name\":\"one_of\",\"input_name0\":[\"Current User\"]}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'hBarF','pro',0),('1','1','1b27e155-73a9-766e-8135-4c218d089219','Slow Queries','TrackerQueries','tabular','{\"report_type\":\"tabular\",\"display_columns\":[{\"name\":\"text\",\"label\":\"SQL STATEMENT\",\"table_key\":\"self\"},{\"name\":\"sec_avg\",\"label\":\"Average Seconds\",\"table_key\":\"self\"},{\"name\":\"sec_total\",\"label\":\"Seconds Total\",\"table_key\":\"self\"},{\"name\":\"run_count\",\"label\":\"Execution Count\",\"table_key\":\"self\"}],\"summary_columns\":[],\"filters_def\":[],\"filters_combiner\":\"AND\",\"group_defs\":[],\"full_table_list\":{\"self\":{\"parent\":\"\",\"value\":\"TrackerQueries\",\"module\":\"TrackerQueries\",\"label\":\"Tracker Queries\",\"children\":[]}},\"module\":\"TrackerQueries\",\"report_name\":\"Slow Queries\",\"chart_type\":\"none\",\"chart_description\":\"\",\"numerical_chart_column\":\"count\",\"assigned_user_id\":\"1\"}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1b86e012-3039-fd4c-a140-4c218d446341','My Records Modified (Last 7 Days)','Trackers','tabular','{\"display_columns\":[{\"name\":\"item_id\",\"label\":\"Trackers > Item Id\",\"table_key\":\"self\"},{\"name\":\"item_summary\",\"label\":\"Trackers > Item Summary\",\"table_key\":\"self\"},{\"name\":\"module_name\",\"label\":\"Trackers > Module Name\",\"table_key\":\"self\"},{\"name\":\"action\",\"label\":\"Trackers > Action\",\"table_key\":\"self\"},{\"name\":\"date_modified\",\"label\":\"Trackers > Last Modified\",\"table_key\":\"self\"}],\"module\":\"Trackers\",\"report_name\":\"My Records Modified (Last 7 Days)\",\"group_defs\":[],\"summary_columns\":[],\"chart_type\":\"none\",\"numerical_chart_column\":\"\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:tracker_user_id\":{\"name\":\"Trackers  >  Assigned to User\",\"label\":\"Users\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:tracker_user_id\"},\"dependents\":[\"Filter_1_table_filter_row_1\"],\"module\":\"Users\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"user_name\",\"table_key\":\"Trackers:tracker_user_id\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]},\"1\":{\"name\":\"action\",\"table_key\":\"self\",\"qualifier_name\":\"equals\",\"input_name0\":\"save\",\"input_name1\":\"on\"},\"2\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"input_name0\":\"tp_last_7_days\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1be8c8c2-4117-c4a0-13d2-4c218dff6b41','My Recently Modified Records (Last 30 Days)','Trackers','tabular','{\"display_columns\":[{\"name\":\"item_id\",\"label\":\"Trackers > Item Id\",\"table_key\":\"self\"},{\"name\":\"item_summary\",\"label\":\"Trackers > Item Summary\",\"table_key\":\"self\"},{\"name\":\"module_name\",\"label\":\"Trackers > Module Name\",\"table_key\":\"self\"},{\"name\":\"action\",\"label\":\"Trackers > Action\",\"table_key\":\"self\"},{\"name\":\"date_modified\",\"label\":\"Trackers > Last Modified\",\"table_key\":\"self\"}],\"module\":\"Trackers\",\"report_name\":\"My Recently Modified Records (Last 30 Days)\",\"group_defs\":[],\"summary_columns\":[],\"chart_type\":\"none\",\"numerical_chart_column\":\"\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:tracker_user_id\":{\"name\":\"Trackers  >  Assigned to User\",\"label\":\"Users\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:tracker_user_id\"},\"dependents\":[\"Filter_1_table_filter_row_1\"],\"module\":\"Users\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"user_name\",\"table_key\":\"Trackers:tracker_user_id\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]},\"1\":{\"name\":\"action\",\"table_key\":\"self\",\"qualifier_name\":\"equals\",\"input_name0\":\"save\",\"input_name1\":\"on\"},\"2\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_30_days\",\"input_name0\":\"tp_last_30_days\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1c4afb3e-8c67-84da-5fee-4c218d0d8ede','Records Modified By My Direct Reports (Last 30 Days)','Trackers','tabular','{\"display_columns\":[{\"name\":\"user_id\",\"label\":\"User Id\",\"table_key\":\"self\"},{\"name\":\"item_id\",\"label\":\"Item Id\",\"table_key\":\"self\"},{\"name\":\"item_summary\",\"label\":\"Item Summary\",\"table_key\":\"self\"},{\"name\":\"module_name\",\"label\":\"Module Name\",\"table_key\":\"self\"},{\"name\":\"date_modified\",\"label\":\"Last Modified\",\"table_key\":\"self\"}],\"module\":\"Trackers\",\"group_defs\":[],\"summary_columns\":[],\"report_name\":\"Records Modified By My Direct Reports (Last 30 Days)\",\"chart_type\":\"none\",\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"Trackers\",\"module\":\"Trackers\",\"label\":\"Trackers\"},\"Trackers:assigned_user_link\":{\"name\":\"Trackers  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"Trackers:assigned_user_link\"},\"dependents\":[\"Filter.1_table_filter_row_4\",\"Filter.1_table_filter_row_5\",\"Filter.1_table_filter_row_4\",\"Filter.1_table_filter_row_4\"],\"module\":\"Users\",\"label\":\"Assigned to User\",\"optional\":false},\"Trackers:assigned_user_link:reports_to_link\":{\"name\":\"Trackers  >  Assigned to User  >  Reports To\",\"parent\":\"Trackers:assigned_user_link\",\"link_def\":{\"name\":\"reports_to_link\",\"relationship_name\":\"user_direct_reports\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Reports to\",\"table_key\":\"Trackers:assigned_user_link:reports_to_link\"},\"dependents\":[\"Filter.1_table_filter_row_5\",\"Filter.1_table_filter_row_4\",\"Filter.1_table_filter_row_4\"],\"module\":\"Users\",\"label\":\"Reports to\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"action\",\"table_key\":\"self\",\"qualifier_name\":\"equals\",\"input_name0\":\"save\",\"input_name1\":\"on\"},\"1\":{\"name\":\"date_modified\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_30_days\",\"input_name0\":\"tp_last_30_days\",\"input_name1\":\"on\"},\"2\":{\"name\":\"module_name\",\"table_key\":\"self\",\"qualifier_name\":\"not_equals_str\",\"input_name0\":\"UserPreferences\",\"input_name1\":\"on\"},\"3\":{\"name\":\"user_name\",\"table_key\":\"Trackers:assigned_user_link:reports_to_link\",\"qualifier_name\":\"is\",\"input_name0\":[\"Current User\"]}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1caf3fea-dbf1-7c32-20b0-4c218db6b4d1','Active User Sessions (Last 7 Days)','TrackerSessions','tabular','{\"display_columns\":[{\"name\":\"user_name\",\"label\":\"User Name\",\"table_key\":\"TrackerSessions:assigned_user_link\"},{\"name\":\"first_name\",\"label\":\"First Name\",\"table_key\":\"TrackerSessions:assigned_user_link\"},{\"name\":\"last_name\",\"label\":\"Last Name\",\"table_key\":\"TrackerSessions:assigned_user_link\"},{\"name\":\"round_trips\",\"label\":\"Session Roundtrips\",\"table_key\":\"self\"},{\"name\":\"seconds\",\"label\":\"Seconds\",\"table_key\":\"self\"}],\"module\":\"TrackerSessions\",\"group_defs\":[],\"summary_columns\":[],\"report_name\":\"Active User Sessions (Last 7 Days)\",\"chart_type\":\"none\",\"numerical_chart_column\":\"\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"tabular\",\"full_table_list\":{\"self\":{\"value\":\"TrackerSessions\",\"module\":\"TrackerSessions\",\"label\":\"TrackerSessions\"},\"TrackerSessions:assigned_user_link\":{\"name\":\"TrackerSessions  >  Assigned to User\",\"parent\":\"self\",\"link_def\":{\"name\":\"assigned_user_link\",\"relationship_name\":\"tracker_user_id\",\"bean_is_lhs\":false,\"link_type\":\"one\",\"label\":\"Assigned to User\",\"table_key\":\"TrackerSessions:assigned_user_link\"},\"dependents\":[\"display_cols_row_1\",\"display_cols_row_2\",\"display_cols_row_3\",\"display_cols_row_1\",\"display_cols_row_2\",\"display_cols_row_3\"],\"module\":\"Users\",\"label\":\"Assigned to User\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"date_end\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"input_name0\":\"tp_last_7_days\",\"input_name1\":\"on\"},\"1\":{\"name\":\"active\",\"table_key\":\"self\",\"qualifier_name\":\"equals\",\"input_name0\":[\"1\"]}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0),('1','1','1d11cce6-fb10-f55d-7c75-4c218d2b9ac7','User Sessions Summary (Last 7 Days)','TrackerSessions','summary','{\"display_columns\":[],\"module\":\"TrackerSessions\",\"group_defs\":[{\"name\":\"user_id\",\"label\":\"User Id\",\"table_key\":\"self\",\"type\":\"varchar\"}],\"summary_columns\":[{\"name\":\"user_id\",\"label\":\"User Id\",\"table_key\":\"self\"},{\"name\":\"count\",\"label\":\"Count\",\"field_type\":\"\",\"group_function\":\"count\",\"table_key\":\"self\"},{\"name\":\"round_trips\",\"label\":\"SUM: Session Roundtrips\",\"field_type\":\"int\",\"group_function\":\"sum\",\"table_key\":\"self\"},{\"name\":\"seconds\",\"label\":\"SUM: Seconds\",\"field_type\":\"int\",\"group_function\":\"sum\",\"table_key\":\"self\"}],\"report_name\":\"User Sessions Summary (Last 7 Days)\",\"chart_type\":\"none\",\"chart_description\":\"\",\"numerical_chart_column\":\"self:round_trips:sum\",\"numerical_chart_column_type\":\"\",\"assigned_user_id\":\"1\",\"report_type\":\"summary\",\"full_table_list\":{\"self\":{\"value\":\"TrackerSessions\",\"module\":\"TrackerSessions\",\"label\":\"TrackerSessions\"}},\"filters_def\":{\"Filter_1\":{\"operator\":\"AND\",\"0\":{\"name\":\"date_end\",\"table_key\":\"self\",\"qualifier_name\":\"tp_last_7_days\",\"input_name0\":\"tp_last_7_days\",\"input_name1\":\"on\"}}}}',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','1',NULL,1,'none','pro',0);
/*!40000 ALTER TABLE `saved_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `saved_search`
--

DROP TABLE IF EXISTS `saved_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `saved_search` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `search_module` varchar(150) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `contents` text,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `idx_saved_search_tmst_id` (`team_set_id`),
  KEY `idx_desc` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `saved_search`
--

LOCK TABLES `saved_search` WRITE;
/*!40000 ALTER TABLE `saved_search` DISABLE KEYS */;
/*!40000 ALTER TABLE `saved_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedulers`
--

DROP TABLE IF EXISTS `schedulers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedulers` (
  `id` varchar(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `job` varchar(255) DEFAULT NULL,
  `date_time_start` datetime DEFAULT NULL,
  `date_time_end` datetime DEFAULT NULL,
  `job_interval` varchar(100) DEFAULT NULL,
  `time_from` time DEFAULT NULL,
  `time_to` time DEFAULT NULL,
  `last_run` datetime DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `catch_up` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_schedule` (`date_time_start`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedulers`
--

LOCK TABLES `schedulers` WRITE;
/*!40000 ALTER TABLE `schedulers` DISABLE KEYS */;
INSERT INTO `schedulers` VALUES ('1f6e8164-94d3-d456-f4c8-4c218de76bda',0,'2010-06-23 04:30:31','2010-06-23 04:30:31',NULL,'1','Process Workflow Tasks','function::processWorkflow','2005-01-01 07:45:01','2020-12-31 23:59:59','*::*::*::*::*',NULL,NULL,NULL,'Active',0),('20992f9e-d2dc-4d53-7fa1-4c218db32566',0,'2010-06-23 04:30:31','2010-06-23 04:30:31',NULL,'1','Run Report Generation Scheduled Tasks','function::processQueue','2005-01-01 18:00:01','2020-12-31 23:59:59','0::6::*::*::*',NULL,NULL,NULL,'Inactive',1),('21c4691c-12d7-7d30-9bff-4c218d488c4c',0,'2010-06-23 04:30:31','2010-06-23 04:30:31',NULL,'1','Prune tracker tables','function::trimTracker','2005-01-01 07:45:01','2020-12-31 23:59:59','0::2::1::*::*',NULL,NULL,NULL,'Active',1),('22ea1a0b-8da5-ad08-69b0-4c218d1d5e35',0,'2010-06-23 04:30:31','2010-06-23 04:30:31',NULL,'1','Check Inbound Mailboxes','function::pollMonitoredInboxes','2005-01-01 15:45:01','2020-12-31 23:59:59','*::*::*::*::*',NULL,NULL,NULL,'Active',0),('240e1843-3db2-d3c8-10e0-4c218d044c6c',0,'2010-06-23 04:30:31','2010-06-23 04:30:31',NULL,'1','Run Nightly Process Bounced Campaign Emails','function::pollMonitoredInboxesForBouncedCampaignEmails','2005-01-01 11:15:01','2020-12-31 23:59:59','0::2-6::*::*::*',NULL,NULL,NULL,'Active',1),('2533434d-66c4-0691-f7b8-4c218dda0037',0,'2010-06-23 04:30:31','2010-06-23 04:30:31',NULL,'1','Run Nightly Mass Email Campaigns','function::runMassEmailCampaign','2005-01-01 12:30:01','2020-12-31 23:59:59','0::2-6::*::*::*',NULL,NULL,NULL,'Active',1),('26e46562-1eb4-bc50-39c7-4c218d3bca57',0,'2010-06-23 04:30:31','2010-06-23 04:30:31',NULL,'1','Prune Database on 1st of Month','function::pruneDatabase','2005-01-01 15:15:01','2020-12-31 23:59:59','0::4::1::*::*',NULL,NULL,NULL,'Inactive',0),('280b170e-4d21-9ea3-d7c3-4c218debe54b',0,'2010-06-23 04:30:31','2010-06-23 04:30:31',NULL,'1','Update tracker_sessions table','function::updateTrackerSessions','2005-01-01 16:45:01','2020-12-31 23:59:59','*::*::*::*::*',NULL,NULL,NULL,'Active',1);
/*!40000 ALTER TABLE `schedulers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedulers_times`
--

DROP TABLE IF EXISTS `schedulers_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedulers_times` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `scheduler_id` char(36) NOT NULL,
  `execute_time` datetime DEFAULT NULL,
  `status` varchar(25) DEFAULT 'ready',
  PRIMARY KEY (`id`),
  KEY `idx_scheduler_id` (`scheduler_id`,`execute_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedulers_times`
--

LOCK TABLES `schedulers_times` WRITE;
/*!40000 ALTER TABLE `schedulers_times` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedulers_times` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_active`
--

DROP TABLE IF EXISTS `session_active`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_active` (
  `id` char(36) NOT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `last_request_time` datetime DEFAULT NULL,
  `session_type` varchar(100) DEFAULT NULL,
  `is_violation` tinyint(1) DEFAULT '0',
  `num_active_sessions` int(11) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_session_id` (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_active`
--

LOCK TABLES `session_active` WRITE;
/*!40000 ALTER TABLE `session_active` DISABLE KEYS */;
/*!40000 ALTER TABLE `session_active` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_history`
--

DROP TABLE IF EXISTS `session_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_history` (
  `id` char(36) NOT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_request_time` datetime DEFAULT NULL,
  `session_type` varchar(100) DEFAULT NULL,
  `is_violation` tinyint(1) DEFAULT '0',
  `num_active_sessions` int(11) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_history`
--

LOCK TABLES `session_history` WRITE;
/*!40000 ALTER TABLE `session_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `session_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shippers`
--

DROP TABLE IF EXISTS `shippers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shippers` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `list_order` int(4) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_shippers` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shippers`
--

LOCK TABLES `shippers` WRITE;
/*!40000 ALTER TABLE `shippers` DISABLE KEYS */;
/*!40000 ALTER TABLE `shippers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sugarfeed`
--

DROP TABLE IF EXISTS `sugarfeed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sugarfeed` (
  `id` char(36) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `related_module` varchar(100) DEFAULT NULL,
  `related_id` char(36) DEFAULT NULL,
  `link_url` varchar(255) DEFAULT NULL,
  `link_type` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sugarfeed_tmst_id` (`team_set_id`),
  KEY `sgrfeed_date` (`date_entered`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sugarfeed`
--

LOCK TABLES `sugarfeed` WRITE;
/*!40000 ALTER TABLE `sugarfeed` DISABLE KEYS */;
/*!40000 ALTER TABLE `sugarfeed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `systems`
--

DROP TABLE IF EXISTS `systems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `systems` (
  `id` char(36) NOT NULL,
  `system_id` int(11) NOT NULL AUTO_INCREMENT,
  `system_key` varchar(36) DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `last_connect_date` datetime DEFAULT NULL,
  `status` varchar(255) DEFAULT 'Active',
  `num_syncs` int(11) DEFAULT '0',
  `system_name` varchar(100) DEFAULT NULL,
  `install_method` varchar(100) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `system_id` (`system_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `systems`
--

LOCK TABLES `systems` WRITE;
/*!40000 ALTER TABLE `systems` DISABLE KEYS */;
INSERT INTO `systems` VALUES ('e1c4d24d-da89-e477-4903-4c218d9bec0d',1,'8e54dc20ea6d9742ebea00a5ecf46310','1','1969-12-31 23:00:00','Active',0,NULL,'web','2010-06-23 04:30:30','2010-06-23 04:30:30',0);
/*!40000 ALTER TABLE `systems` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `id` char(36) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  `assigned_user_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `date_due_flag` tinyint(1) DEFAULT '1',
  `date_due` datetime DEFAULT NULL,
  `date_start_flag` tinyint(1) DEFAULT '1',
  `date_start` datetime DEFAULT NULL,
  `parent_type` varchar(255) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `priority` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tasks_tmst_id` (`team_set_id`),
  KEY `idx_tsk_name` (`name`),
  KEY `idx_task_con_del` (`contact_id`,`deleted`),
  KEY `idx_task_par_del` (`parent_id`,`parent_type`,`deleted`),
  KEY `idx_task_assigned` (`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxrates`
--

DROP TABLE IF EXISTS `taxrates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taxrates` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `value` decimal(7,5) DEFAULT NULL,
  `list_order` int(4) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_taxrates` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxrates`
--

LOCK TABLES `taxrates` WRITE;
/*!40000 ALTER TABLE `taxrates` DISABLE KEYS */;
/*!40000 ALTER TABLE `taxrates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_memberships`
--

DROP TABLE IF EXISTS `team_memberships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_memberships` (
  `id` char(36) NOT NULL,
  `team_id` char(36) DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `explicit_assign` tinyint(1) DEFAULT '0',
  `implicit_assign` tinyint(1) DEFAULT '0',
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_team_membership` (`user_id`,`team_id`),
  KEY `idx_teammemb_team_user` (`team_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_memberships`
--

LOCK TABLES `team_memberships` WRITE;
/*!40000 ALTER TABLE `team_memberships` DISABLE KEYS */;
INSERT INTO `team_memberships` VALUES ('49ae0b22-51aa-e07f-7436-4c218d6b5cac','1','1',1,0,'2010-06-23 04:30:31',0),('678f07f4-44b9-aeb4-bcee-4c218d1d14b1','5f840460-9a76-a49f-ced9-4c218de0dca9','1',1,0,'2010-06-23 04:30:31',0);
/*!40000 ALTER TABLE `team_memberships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_notices`
--

DROP TABLE IF EXISTS `team_notices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_notices` (
  `team_id` char(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text,
  `status` varchar(25) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `url_title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_team_notices_tmst_id` (`team_set_id`),
  KEY `idx_team_notice` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_notices`
--

LOCK TABLES `team_notices` WRITE;
/*!40000 ALTER TABLE `team_notices` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_notices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_sets`
--

DROP TABLE IF EXISTS `team_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_sets` (
  `id` char(36) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `team_md5` varchar(32) DEFAULT NULL,
  `team_count` int(11) DEFAULT '0',
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `created_by` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_team_sets_md5` (`team_md5`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_sets`
--

LOCK TABLES `team_sets` WRITE;
/*!40000 ALTER TABLE `team_sets` DISABLE KEYS */;
INSERT INTO `team_sets` VALUES ('f37b0ffa-7b43-f34a-7ee8-4c218d1adf30','d41d8cd98f00b204e9800998ecf8427e','d41d8cd98f00b204e9800998ecf8427e',0,'2010-06-23 04:30:30',0,NULL),('5f840460-9a76-a49f-ced9-4c218de0dca9','e694fe9e80e705a714efde93252c61df','e694fe9e80e705a714efde93252c61df',1,'2010-06-23 04:30:31',0,NULL),('1','c4ca4238a0b923820dcc509a6f75849b','c4ca4238a0b923820dcc509a6f75849b',1,'2010-06-23 04:30:31',0,NULL);
/*!40000 ALTER TABLE `team_sets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_sets_modules`
--

DROP TABLE IF EXISTS `team_sets_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_sets_modules` (
  `id` char(36) NOT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `module_table_name` varchar(128) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_team_sets_modules` (`team_set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_sets_modules`
--

LOCK TABLES `team_sets_modules` WRITE;
/*!40000 ALTER TABLE `team_sets_modules` DISABLE KEYS */;
/*!40000 ALTER TABLE `team_sets_modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_sets_teams`
--

DROP TABLE IF EXISTS `team_sets_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_sets_teams` (
  `id` char(36) NOT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `team_id` char(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  KEY `idx_ud_set_id` (`team_set_id`,`team_id`),
  KEY `idx_ud_team_id` (`team_id`),
  KEY `idx_ud_team_set_id` (`team_set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_sets_teams`
--

LOCK TABLES `team_sets_teams` WRITE;
/*!40000 ALTER TABLE `team_sets_teams` DISABLE KEYS */;
INSERT INTO `team_sets_teams` VALUES ('8d650856-eef7-68d0-d4b5-4c218d096a6a','5f840460-9a76-a49f-ced9-4c218de0dca9','5f840460-9a76-a49f-ced9-4c218de0dca9','2010-06-23 04:30:31',0),('c33c0f49-3c46-36af-d060-4c218d09124e','1','1','2010-06-23 04:30:31',0);
/*!40000 ALTER TABLE `team_sets_teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` char(36) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `name_2` varchar(128) DEFAULT NULL,
  `associated_user_id` char(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `private` tinyint(1) DEFAULT '0',
  `description` text,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_team_del` (`name`),
  KEY `idx_team_del_name` (`deleted`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES ('1','Global',NULL,NULL,'2010-06-23 04:30:17','2010-06-23 04:30:17','1',NULL,0,'Globally Visible',0),('5f840460-9a76-a49f-ced9-4c218de0dca9','Administrator',NULL,'1','2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,1,'Private team for admin',0);
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeperiods`
--

DROP TABLE IF EXISTS `timeperiods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeperiods` (
  `id` varchar(36) NOT NULL,
  `name` varchar(36) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_by` varchar(36) DEFAULT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `is_fiscal_year` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeperiods`
--

LOCK TABLES `timeperiods` WRITE;
/*!40000 ALTER TABLE `timeperiods` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeperiods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tracker`
--

DROP TABLE IF EXISTS `tracker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracker` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monitor_id` char(36) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `module_name` varchar(255) DEFAULT NULL,
  `item_id` varchar(36) DEFAULT NULL,
  `item_summary` varchar(255) DEFAULT NULL,
  `team_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `session_id` varchar(36) DEFAULT NULL,
  `visible` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_tracker_iid` (`item_id`),
  KEY `idx_tracker_userid_vis_id` (`user_id`,`visible`,`id`),
  KEY `idx_tracker_userid_itemid_vis` (`user_id`,`item_id`,`visible`),
  KEY `idx_tracker_monitor_id` (`monitor_id`),
  KEY `idx_tracker_date_modified` (`date_modified`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracker`
--

LOCK TABLES `tracker` WRITE;
/*!40000 ALTER TABLE `tracker` DISABLE KEYS */;
/*!40000 ALTER TABLE `tracker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tracker_perf`
--

DROP TABLE IF EXISTS `tracker_perf`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracker_perf` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monitor_id` char(36) NOT NULL,
  `server_response_time` double DEFAULT NULL,
  `db_round_trips` int(6) DEFAULT NULL,
  `files_opened` int(6) DEFAULT NULL,
  `memory_usage` int(12) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tracker_perf_mon_id` (`monitor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracker_perf`
--

LOCK TABLES `tracker_perf` WRITE;
/*!40000 ALTER TABLE `tracker_perf` DISABLE KEYS */;
/*!40000 ALTER TABLE `tracker_perf` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tracker_queries`
--

DROP TABLE IF EXISTS `tracker_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracker_queries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query_id` char(36) NOT NULL,
  `text` text,
  `query_hash` varchar(36) DEFAULT NULL,
  `sec_total` double DEFAULT NULL,
  `sec_avg` double DEFAULT NULL,
  `run_count` int(6) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tracker_queries_query_hash` (`query_hash`),
  KEY `idx_tracker_queries_query_id` (`query_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracker_queries`
--

LOCK TABLES `tracker_queries` WRITE;
/*!40000 ALTER TABLE `tracker_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `tracker_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tracker_sessions`
--

DROP TABLE IF EXISTS `tracker_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracker_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(36) DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  `seconds` int(9) DEFAULT '0',
  `client_ip` varchar(20) DEFAULT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `round_trips` int(5) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_tracker_sessions_s_id` (`session_id`),
  KEY `idx_tracker_sessions_uas_id` (`user_id`,`active`,`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracker_sessions`
--

LOCK TABLES `tracker_sessions` WRITE;
/*!40000 ALTER TABLE `tracker_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `tracker_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tracker_tracker_queries`
--

DROP TABLE IF EXISTS `tracker_tracker_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracker_tracker_queries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `monitor_id` varchar(36) DEFAULT NULL,
  `query_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tracker_tq_monitor` (`monitor_id`),
  KEY `idx_tracker_tq_query` (`query_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracker_tracker_queries`
--

LOCK TABLES `tracker_tracker_queries` WRITE;
/*!40000 ALTER TABLE `tracker_tracker_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `tracker_tracker_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `upgrade_history`
--

DROP TABLE IF EXISTS `upgrade_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `upgrade_history` (
  `id` char(36) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `md5sum` varchar(32) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `version` varchar(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `id_name` varchar(255) DEFAULT NULL,
  `manifest` longtext,
  `date_entered` datetime DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `upgrade_history_md5_uk` (`md5sum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `upgrade_history`
--

LOCK TABLES `upgrade_history` WRITE;
/*!40000 ALTER TABLE `upgrade_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `upgrade_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_preferences` (
  `id` char(36) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `contents` text,
  PRIMARY KEY (`id`),
  KEY `idx_userprefnamecat` (`assigned_user_id`,`category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_preferences`
--

LOCK TABLES `user_preferences` WRITE;
/*!40000 ALTER TABLE `user_preferences` DISABLE KEYS */;
INSERT INTO `user_preferences` VALUES ('9d4b06ce-f5a4-cecb-3a63-4c218d86b4af','global',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1','YTowOnt9');
/*!40000 ALTER TABLE `user_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `user_name` varchar(60) DEFAULT NULL,
  `user_hash` varchar(32) DEFAULT NULL,
  `system_generated_password` tinyint(1) DEFAULT '0',
  `pwd_last_changed` datetime DEFAULT NULL,
  `authenticate_id` varchar(100) DEFAULT NULL,
  `sugar_login` tinyint(1) DEFAULT '1',
  `picture` varchar(255) DEFAULT NULL,
  `first_name` varchar(30) DEFAULT NULL,
  `last_name` varchar(30) DEFAULT NULL,
  `reports_to_id` char(36) DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `external_auth_only` tinyint(1) DEFAULT '0',
  `receive_notifications` tinyint(1) DEFAULT '1',
  `description` text,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `department` varchar(50) DEFAULT NULL,
  `phone_home` varchar(50) DEFAULT NULL,
  `phone_mobile` varchar(50) DEFAULT NULL,
  `phone_work` varchar(50) DEFAULT NULL,
  `phone_other` varchar(50) DEFAULT NULL,
  `phone_fax` varchar(50) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `address_street` varchar(150) DEFAULT NULL,
  `address_city` varchar(100) DEFAULT NULL,
  `address_state` varchar(100) DEFAULT NULL,
  `address_country` varchar(25) DEFAULT NULL,
  `address_postalcode` varchar(9) DEFAULT NULL,
  `default_team` varchar(36) DEFAULT NULL,
  `team_set_id` char(36) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `portal_only` tinyint(1) DEFAULT '0',
  `employee_status` varchar(25) DEFAULT NULL,
  `messenger_id` varchar(25) DEFAULT NULL,
  `messenger_type` varchar(25) DEFAULT NULL,
  `is_group` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_user_name` (`user_name`,`is_group`,`status`,`last_name`,`first_name`,`id`),
  KEY `idx_users_tmst_id` (`team_set_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('1','@@ADMIN_NAME@@','@@ADMIN_PASSWORD_MD5@@',0,NULL,NULL,1,NULL,NULL,'Administrator',NULL,1,0,1,NULL,'2010-06-23 04:30:30','2010-06-23 04:30:31','1',NULL,'Administrator',NULL,NULL,NULL,NULL,NULL,NULL,'Active',NULL,NULL,NULL,NULL,NULL,'5f840460-9a76-a49f-ced9-4c218de0dca9','5f840460-9a76-a49f-ced9-4c218de0dca9',0,0,'Active',NULL,NULL,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_feeds`
--

DROP TABLE IF EXISTS `users_feeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_feeds` (
  `user_id` varchar(36) DEFAULT NULL,
  `feed_id` varchar(36) DEFAULT NULL,
  `rank` int(11) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  KEY `idx_ud_user_id` (`user_id`,`feed_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_feeds`
--

LOCK TABLES `users_feeds` WRITE;
/*!40000 ALTER TABLE `users_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_holidays`
--

DROP TABLE IF EXISTS `users_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_holidays` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) DEFAULT NULL,
  `holiday_id` varchar(36) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_user_holi_user` (`user_id`),
  KEY `idx_user_holi_holi` (`holiday_id`),
  KEY `users_quotes_alt` (`user_id`,`holiday_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_holidays`
--

LOCK TABLES `users_holidays` WRITE;
/*!40000 ALTER TABLE `users_holidays` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_last_import`
--

DROP TABLE IF EXISTS `users_last_import`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_last_import` (
  `id` char(36) NOT NULL,
  `assigned_user_id` char(36) DEFAULT NULL,
  `import_module` varchar(36) DEFAULT NULL,
  `bean_type` varchar(36) DEFAULT NULL,
  `bean_id` char(36) DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`assigned_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_last_import`
--

LOCK TABLES `users_last_import` WRITE;
/*!40000 ALTER TABLE `users_last_import` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_last_import` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_password_link`
--

DROP TABLE IF EXISTS `users_password_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_password_link` (
  `id` char(36) NOT NULL,
  `username` varchar(36) DEFAULT NULL,
  `date_generated` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_password_link`
--

LOCK TABLES `users_password_link` WRITE;
/*!40000 ALTER TABLE `users_password_link` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_password_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users_signatures`
--

DROP TABLE IF EXISTS `users_signatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_signatures` (
  `id` char(36) NOT NULL,
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `user_id` varchar(36) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `signature` text,
  `signature_html` text,
  PRIMARY KEY (`id`),
  KEY `idx_usersig_uid` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users_signatures`
--

LOCK TABLES `users_signatures` WRITE;
/*!40000 ALTER TABLE `users_signatures` DISABLE KEYS */;
/*!40000 ALTER TABLE `users_signatures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vcals`
--

DROP TABLE IF EXISTS `vcals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vcals` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `user_id` char(36) NOT NULL,
  `type` varchar(25) DEFAULT NULL,
  `source` varchar(25) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `idx_vcal` (`type`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vcals`
--

LOCK TABLES `vcals` WRITE;
/*!40000 ALTER TABLE `vcals` DISABLE KEYS */;
/*!40000 ALTER TABLE `vcals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `versions`
--

DROP TABLE IF EXISTS `versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `versions` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `file_version` varchar(255) DEFAULT NULL,
  `db_version` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_version` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `versions`
--

LOCK TABLES `versions` WRITE;
/*!40000 ALTER TABLE `versions` DISABLE KEYS */;
INSERT INTO `versions` VALUES ('43f66811-8992-d9a6-47f2-4c218dddf2fd',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'Chart Data Cache','3.5.1','3.5.1'),('4440bd8b-7aa6-d582-cf91-4c218dbdf71d',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'htaccess','3.5.1','3.5.1'),('4480d507-e99f-224e-3cae-4c218d62fb76',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'Rebuild Relationships','4.0.0','4.0.0'),('44be2250-86c3-c745-e867-4c218dcd30d6',0,'2010-06-23 04:30:31','2010-06-23 04:30:31','1',NULL,'Rebuild Extensions','4.0.0','4.0.0');
/*!40000 ALTER TABLE `versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow`
--

DROP TABLE IF EXISTS `workflow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `base_module` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `description` text,
  `type` varchar(25) DEFAULT NULL,
  `fire_order` varchar(25) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `record_type` varchar(25) DEFAULT NULL,
  `list_order_y` int(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_workflow` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow`
--

LOCK TABLES `workflow` WRITE;
/*!40000 ALTER TABLE `workflow` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_actions`
--

DROP TABLE IF EXISTS `workflow_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_actions` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `field` varchar(50) DEFAULT NULL,
  `value` text,
  `set_type` varchar(10) DEFAULT NULL,
  `adv_type` varchar(10) DEFAULT NULL,
  `parent_id` char(36) NOT NULL,
  `ext1` varchar(50) DEFAULT NULL,
  `ext2` varchar(50) DEFAULT NULL,
  `ext3` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_action` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_actions`
--

LOCK TABLES `workflow_actions` WRITE;
/*!40000 ALTER TABLE `workflow_actions` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_actions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_actionshells`
--

DROP TABLE IF EXISTS `workflow_actionshells`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_actionshells` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `action_type` varchar(25) DEFAULT NULL,
  `parent_id` char(36) NOT NULL,
  `parameters` varchar(255) DEFAULT NULL,
  `rel_module` varchar(50) DEFAULT NULL,
  `rel_module_type` varchar(10) DEFAULT NULL,
  `action_module` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_actionshell` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_actionshells`
--

LOCK TABLES `workflow_actionshells` WRITE;
/*!40000 ALTER TABLE `workflow_actionshells` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_actionshells` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_alerts`
--

DROP TABLE IF EXISTS `workflow_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_alerts` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `field_value` varchar(50) DEFAULT NULL,
  `rel_email_value` varchar(50) DEFAULT NULL,
  `rel_module1` varchar(50) DEFAULT NULL,
  `rel_module2` varchar(50) DEFAULT NULL,
  `rel_module1_type` varchar(10) DEFAULT NULL,
  `rel_module2_type` varchar(10) DEFAULT NULL,
  `where_filter` tinyint(1) DEFAULT '0',
  `user_type` varchar(25) DEFAULT NULL,
  `array_type` varchar(25) DEFAULT NULL,
  `relate_type` varchar(25) DEFAULT NULL,
  `address_type` varchar(25) DEFAULT NULL,
  `parent_id` char(36) NOT NULL,
  `user_display_type` varchar(25) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_workflowalerts` (`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_alerts`
--

LOCK TABLES `workflow_alerts` WRITE;
/*!40000 ALTER TABLE `workflow_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_alertshells`
--

DROP TABLE IF EXISTS `workflow_alertshells`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_alertshells` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `alert_text` text,
  `alert_type` varchar(25) DEFAULT NULL,
  `source_type` varchar(25) DEFAULT NULL,
  `parent_id` char(36) NOT NULL,
  `custom_template_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_workflowalertshell` (`name`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_alertshells`
--

LOCK TABLES `workflow_alertshells` WRITE;
/*!40000 ALTER TABLE `workflow_alertshells` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_alertshells` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_schedules`
--

DROP TABLE IF EXISTS `workflow_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_schedules` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `date_expired` datetime DEFAULT NULL,
  `workflow_id` char(36) DEFAULT NULL,
  `target_module` varchar(50) DEFAULT NULL,
  `bean_id` char(36) DEFAULT NULL,
  `parameters` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_wkfl_schedule` (`workflow_id`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_schedules`
--

LOCK TABLES `workflow_schedules` WRITE;
/*!40000 ALTER TABLE `workflow_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `workflow_triggershells`
--

DROP TABLE IF EXISTS `workflow_triggershells`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `workflow_triggershells` (
  `id` char(36) NOT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  `date_entered` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `field` varchar(50) DEFAULT NULL,
  `type` varchar(25) DEFAULT NULL,
  `frame_type` varchar(15) DEFAULT NULL,
  `eval` text,
  `parent_id` char(36) NOT NULL,
  `show_past` tinyint(1) DEFAULT '0',
  `rel_module` varchar(50) DEFAULT NULL,
  `rel_module_type` varchar(10) DEFAULT NULL,
  `parameters` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `workflow_triggershells`
--

LOCK TABLES `workflow_triggershells` WRITE;
/*!40000 ALTER TABLE `workflow_triggershells` DISABLE KEYS */;
/*!40000 ALTER TABLE `workflow_triggershells` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `worksheet`
--

DROP TABLE IF EXISTS `worksheet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `worksheet` (
  `id` char(36) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `timeperiod_id` char(36) DEFAULT NULL,
  `forecast_type` varchar(25) DEFAULT NULL,
  `related_id` char(36) DEFAULT NULL,
  `related_forecast_type` varchar(25) DEFAULT NULL,
  `best_case` int(11) DEFAULT NULL,
  `likely_case` int(11) DEFAULT NULL,
  `worst_case` int(11) DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `modified_user_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `worksheet`
--

LOCK TABLES `worksheet` WRITE;
/*!40000 ALTER TABLE `worksheet` DISABLE KEYS */;
/*!40000 ALTER TABLE `worksheet` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-06-22 21:31:00
