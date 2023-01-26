-- MySQL dump 10.13  Distrib 5.7.31, for Linux (x86_64)
--
-- Host: localhost    Database: prod_platinum
-- ------------------------------------------------------
-- Server version	5.7.31-0ubuntu0.18.04.1

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
-- Current Database: `prod_platinum`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `prod_platinum` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `prod_platinum`;

--
-- Table structure for table `achievements`
--

DROP TABLE IF EXISTS `achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achievements` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `achievement` int(11) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `User/Achievement` (`username`,`achievement`),
  KEY `achievement` (`achievement`,`rating`)
) ENGINE=InnoDB AUTO_INCREMENT=17190 DEFAULT CHARSET=utf8 COMMENT='Singleplayer Achievements';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `achievements`
--

LOCK TABLES `achievements` WRITE;
/*!40000 ALTER TABLE `achievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `achievements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addresses` (
  `username` varchar(64) NOT NULL,
  `address` varchar(64) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0',
  `lastHit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `firstHit` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user/addr` (`username`,`address`)
) ENGINE=InnoDB AUTO_INCREMENT=3984147 DEFAULT CHARSET=utf8 COMMENT='Super Sneaky IP Recording';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addresses`
--

LOCK TABLES `addresses` WRITE;
/*!40000 ALTER TABLE `addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bannedips`
--

DROP TABLE IF EXISTS `bannedips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bannedips` (
  `address` varchar(32) NOT NULL,
  `banner` varchar(64) NOT NULL,
  `reason` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `address` (`address`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8 COMMENT='IPs that are Banned';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bannedips`
--

LOCK TABLES `bannedips` WRITE;
/*!40000 ALTER TABLE `bannedips` DISABLE KEYS */;
/*!40000 ALTER TABLE `bannedips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bans`
--

DROP TABLE IF EXISTS `bans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bans` (
  `username` varchar(64) NOT NULL,
  `mute` int(11) NOT NULL COMMENT 'Prevent sending message',
  `deafen` int(11) NOT NULL COMMENT 'Prevent receiving messages',
  `block` int(11) NOT NULL COMMENT 'Prevent logout',
  `start` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sender` varchar(64) NOT NULL COMMENT 'Who banned them',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bans`
--

LOCK TABLES `bans` WRITE;
/*!40000 ALTER TABLE `bans` DISABLE KEYS */;
/*!40000 ALTER TABLE `bans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocks`
--

DROP TABLE IF EXISTS `blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocks` (
  `username` varchar(64) NOT NULL,
  `block` varchar(64) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocks`
--

LOCK TABLES `blocks` WRITE;
/*!40000 ALTER TABLE `blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cachievements`
--

DROP TABLE IF EXISTS `cachievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cachievements` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `achievement` int(11) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `User/Achievement` (`username`,`achievement`)
) ENGINE=MyISAM AUTO_INCREMENT=948 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cachievements`
--

LOCK TABLES `cachievements` WRITE;
/*!40000 ALTER TABLE `cachievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `cachievements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `name` varchar(64) CHARACTER SET latin1 NOT NULL,
  `display` varchar(64) CHARACTER SET latin1 NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='Custom Level Categories';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `challengedata`
--

DROP TABLE IF EXISTS `challengedata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `challengedata` (
  `username` varchar(64) NOT NULL,
  `opponent` varchar(64) NOT NULL,
  `winner` varchar(64) NOT NULL,
  `score` int(11) NOT NULL,
  `scores` text NOT NULL,
  `level` varchar(128) NOT NULL,
  `points` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `cid` int(11) NOT NULL COMMENT 'Challenge ID',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `Users/Level/Score` (`username`,`opponent`,`level`,`score`),
  KEY `WLS` (`winner`,`level`,`score`)
) ENGINE=InnoDB AUTO_INCREMENT=3771 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `challengedata`
--

LOCK TABLES `challengedata` WRITE;
/*!40000 ALTER TABLE `challengedata` DISABLE KEYS */;
/*!40000 ALTER TABLE `challengedata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `challenges`
--

DROP TABLE IF EXISTS `challenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `challenges` (
  `level` varchar(64) NOT NULL,
  `attempts` varchar(32) NOT NULL,
  `mode` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `player0` varchar(128) NOT NULL,
  `player1` varchar(128) NOT NULL,
  `player0status` int(11) NOT NULL,
  `player1status` int(11) NOT NULL,
  `player0time` int(11) NOT NULL,
  `player1time` int(11) NOT NULL,
  `player0bonus` int(11) NOT NULL,
  `player1bonus` int(11) NOT NULL,
  `player0scores` text NOT NULL,
  `player1scores` text NOT NULL,
  `player0gems` int(11) NOT NULL,
  `player1gems` int(11) NOT NULL,
  `player0attempts` int(11) NOT NULL,
  `player1attempts` int(11) NOT NULL,
  `player0best` int(11) NOT NULL,
  `player1best` int(11) NOT NULL,
  `winner` varchar(128) NOT NULL,
  `expires` int(11) NOT NULL,
  `starttime` int(11) NOT NULL,
  `timeout` int(11) NOT NULL DEFAULT '0',
  `timeouttime` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Players/Level` (`player0`,`player1`,`level`)
) ENGINE=MyISAM AUTO_INCREMENT=2362 DEFAULT CHARSET=utf8 COMMENT='Actual Running Challenges';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `challenges`
--

LOCK TABLES `challenges` WRITE;
/*!40000 ALTER TABLE `challenges` DISABLE KEYS */;
/*!40000 ALTER TABLE `challenges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chat`
--

DROP TABLE IF EXISTS `chat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `destination` varchar(128) CHARACTER SET latin1 NOT NULL,
  `message` varchar(1024) CHARACTER SET latin1 NOT NULL,
  `access` int(4) NOT NULL DEFAULT '0',
  `location` int(11) NOT NULL DEFAULT '0',
  `time` double unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2569081 DEFAULT CHARSET=utf8 COMMENT='Chat Log';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chat`
--

LOCK TABLES `chat` WRITE;
/*!40000 ALTER TABLE `chat` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatcolors`
--

DROP TABLE IF EXISTS `chatcolors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatcolors` (
  `ident` varchar(4) NOT NULL,
  `color` varchar(6) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatcolors`
--

LOCK TABLES `chatcolors` WRITE;
/*!40000 ALTER TABLE `chatcolors` DISABLE KEYS */;
/*!40000 ALTER TABLE `chatcolors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clevelscores`
--

DROP TABLE IF EXISTS `clevelscores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clevelscores` (
  `username` varchar(64) NOT NULL,
  `cid` int(11) NOT NULL,
  `level` varchar(64) NOT NULL,
  `score` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2110 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clevelscores`
--

LOCK TABLES `clevelscores` WRITE;
/*!40000 ALTER TABLE `clevelscores` DISABLE KEYS */;
/*!40000 ALTER TABLE `clevelscores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `easteregg`
--

DROP TABLE IF EXISTS `easteregg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `easteregg` (
  `username` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `level` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `gametype` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=16815 DEFAULT CHARSET=utf8 COMMENT='Easter Eggs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `easteregg`
--

LOCK TABLES `easteregg` WRITE;
/*!40000 ALTER TABLE `easteregg` DISABLE KEYS */;
/*!40000 ALTER TABLE `easteregg` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventcandy`
--

DROP TABLE IF EXISTS `eventcandy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventcandy` (
  `username` varchar(64) NOT NULL,
  `mission` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=387 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventcandy`
--

LOCK TABLES `eventcandy` WRITE;
/*!40000 ALTER TABLE `eventcandy` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventcandy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eventtriggers`
--

DROP TABLE IF EXISTS `eventtriggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `eventtriggers` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'UUID (PK)',
  `username` varchar(64) NOT NULL COMMENT 'Player''s username',
  `triggerID` int(11) NOT NULL COMMENT 'Event Achievement ID - provided by in-game trigger',
  `levelName` varchar(256) NOT NULL COMMENT 'Level the triggered event occurred on',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of event',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5957 DEFAULT CHARSET=utf8 COMMENT='Event trigger "achievements". ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eventtriggers`
--

LOCK TABLES `eventtriggers` WRITE;
/*!40000 ALTER TABLE `eventtriggers` DISABLE KEYS */;
/*!40000 ALTER TABLE `eventtriggers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expires`
--

DROP TABLE IF EXISTS `expires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expires` (
  `username` varchar(64) NOT NULL,
  `prev` int(11) NOT NULL,
  `rate` float NOT NULL,
  `base` int(11) NOT NULL,
  `new` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1891 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expires`
--

LOCK TABLES `expires` WRITE;
/*!40000 ALTER TABLE `expires` DISABLE KEYS */;
/*!40000 ALTER TABLE `expires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `friends` (
  `username` varchar(64) NOT NULL,
  `friendid` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4779 DEFAULT CHARSET=utf8 COMMENT='Friends';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friends`
--

LOCK TABLES `friends` WRITE;
/*!40000 ALTER TABLE `friends` DISABLE KEYS */;
/*!40000 ALTER TABLE `friends` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guitracking`
--

DROP TABLE IF EXISTS `guitracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guitracking` (
  `username` varchar(64) NOT NULL,
  `gui` varchar(64) NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `firstopen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastopen` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=415724 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guitracking`
--

LOCK TABLES `guitracking` WRITE;
/*!40000 ALTER TABLE `guitracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `guitracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imports`
--

DROP TABLE IF EXISTS `imports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imports` (
  `username` varchar(128) CHARACTER SET latin1 NOT NULL,
  `pusername` varchar(128) CHARACTER SET latin1 NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=75 DEFAULT CHARSET=utf8 COMMENT='Philsempire Account Imports';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imports`
--

LOCK TABLES `imports` WRITE;
/*!40000 ALTER TABLE `imports` DISABLE KEYS */;
/*!40000 ALTER TABLE `imports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jloggedin`
--

DROP TABLE IF EXISTS `jloggedin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jloggedin` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `display` varchar(64) CHARACTER SET latin1 NOT NULL,
  `access` int(4) NOT NULL,
  `location` int(4) NOT NULL,
  `game` varchar(32) CHARACTER SET latin1 NOT NULL,
  `time` double unsigned NOT NULL,
  `logintime` int(11) NOT NULL,
  `loginsess` varchar(64) CHARACTER SET latin1 NOT NULL,
  `chatkey` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `joomla` tinyint(1) NOT NULL DEFAULT '1',
  `address` varchar(32) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1658 DEFAULT CHARSET=utf8 COMMENT='Webchat Users Logged In';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jloggedin`
--

LOCK TABLES `jloggedin` WRITE;
/*!40000 ALTER TABLE `jloggedin` DISABLE KEYS */;
/*!40000 ALTER TABLE `jloggedin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jokes`
--

DROP TABLE IF EXISTS `jokes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jokes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `joke` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jokes`
--

LOCK TABLES `jokes` WRITE;
/*!40000 ALTER TABLE `jokes` DISABLE KEYS */;
/*!40000 ALTER TABLE `jokes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `levels`
--

DROP TABLE IF EXISTS `levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `levels` (
  `file` varchar(64) CHARACTER SET latin1 NOT NULL,
  `stripped` varchar(64) CHARACTER SET latin1 NOT NULL,
  `category` varchar(64) CHARACTER SET latin1 NOT NULL,
  `game` varchar(64) CHARACTER SET latin1 NOT NULL,
  `type` varchar(64) CHARACTER SET latin1 NOT NULL,
  `display` varchar(64) CHARACTER SET latin1 NOT NULL,
  `position` int(11) NOT NULL,
  `crc` int(11) NOT NULL DEFAULT '0',
  `notes` longtext CHARACTER SET latin1 NOT NULL,
  `qualify` float NOT NULL,
  `gold` float NOT NULL,
  `ultimate` float NOT NULL,
  `standardiser` float NOT NULL,
  `basescore` float NOT NULL,
  `basemultiplier` float NOT NULL,
  `goldbonus` float NOT NULL,
  `platinumbonus` float NOT NULL,
  `ultimatebonus` float NOT NULL,
  `difficulty` int(11) NOT NULL DEFAULT '1',
  `golddifficulty` int(11) NOT NULL DEFAULT '1' COMMENT 'Gold/Platinum',
  `ultimatedifficulty` int(11) NOT NULL DEFAULT '1',
  `easteregg` tinyint(1) NOT NULL DEFAULT '0',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `short` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Shawtay',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `file` (`file`),
  KEY `display` (`display`)
) ENGINE=MyISAM AUTO_INCREMENT=275 DEFAULT CHARSET=utf8 COMMENT='Custom Levels';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `levels`
--

LOCK TABLES `levels` WRITE;
/*!40000 ALTER TABLE `levels` DISABLE KEYS */;
/*!40000 ALTER TABLE `levels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `loggedin`
--

DROP TABLE IF EXISTS `loggedin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loggedin` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `display` varchar(64) CHARACTER SET latin1 NOT NULL,
  `access` int(4) NOT NULL,
  `location` int(4) NOT NULL,
  `game` varchar(32) CHARACTER SET latin1 NOT NULL,
  `time` double unsigned NOT NULL,
  `logintime` int(11) NOT NULL,
  `loginsess` varchar(64) CHARACTER SET latin1 NOT NULL,
  `chatkey` varchar(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `joomla` tinyint(1) NOT NULL DEFAULT '1',
  `address` varchar(32) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Statics` (`username`,`display`,`access`,`game`,`chatkey`,`joomla`,`address`)
) ENGINE=MyISAM AUTO_INCREMENT=10264 DEFAULT CHARSET=utf8 COMMENT='Ingame Users';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `loggedin`
--

LOCK TABLES `loggedin` WRITE;
/*!40000 ALTER TABLE `loggedin` DISABLE KEYS */;
/*!40000 ALTER TABLE `loggedin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `motd`
--

DROP TABLE IF EXISTS `motd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `motd` (
  `message` text NOT NULL,
  `submitter` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `motd`
--

LOCK TABLES `motd` WRITE;
/*!40000 ALTER TABLE `motd` DISABLE KEYS */;
/*!40000 ALTER TABLE `motd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpachievements`
--

DROP TABLE IF EXISTS `mpachievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpachievements` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `achievement` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mpachievements_username_achievement_uindex` (`username`,`achievement`)
) ENGINE=MyISAM AUTO_INCREMENT=4867 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpachievements`
--

LOCK TABLES `mpachievements` WRITE;
/*!40000 ALTER TABLE `mpachievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpachievements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mplevels`
--

DROP TABLE IF EXISTS `mplevels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mplevels` (
  `file` varchar(128) CHARACTER SET latin1 NOT NULL,
  `gamemode` varchar(32) CHARACTER SET latin1 NOT NULL,
  `type` varchar(32) CHARACTER SET latin1 NOT NULL,
  `display` varchar(64) CHARACTER SET latin1 NOT NULL,
  `game` varchar(32) CHARACTER SET latin1 NOT NULL,
  `platinumscore` int(11) NOT NULL DEFAULT '0',
  `ultimatescore` int(11) NOT NULL DEFAULT '0',
  `pplatinumscore` int(11) NOT NULL,
  `pultimatescore` int(11) NOT NULL,
  `crc` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `file` (`file`),
  KEY `display` (`display`)
) ENGINE=MyISAM AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mplevels`
--

LOCK TABLES `mplevels` WRITE;
/*!40000 ALTER TABLE `mplevels` DISABLE KEYS */;
/*!40000 ALTER TABLE `mplevels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpservers`
--

DROP TABLE IF EXISTS `mpservers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mpservers` (
  `address` varchar(16) NOT NULL,
  `port` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `level` varchar(128) NOT NULL,
  `mod` varchar(16) NOT NULL,
  `mode` varchar(8) NOT NULL,
  `handicap` int(11) NOT NULL,
  `host` varchar(64) NOT NULL,
  `submitting` tinyint(1) NOT NULL,
  `password` tinyint(1) NOT NULL,
  `minRating` int(11) NOT NULL,
  `maxPlayers` int(11) NOT NULL,
  `regionMask` int(11) NOT NULL,
  `version` int(11) NOT NULL,
  `filterFlag` int(11) NOT NULL,
  `botCount` int(11) NOT NULL,
  `CPUSpeed` int(11) NOT NULL,
  `players` int(11) NOT NULL,
  `receivedinfo` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `key` varchar(64) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=99823 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpservers`
--

LOCK TABLES `mpservers` WRITE;
/*!40000 ALTER TABLE `mpservers` DISABLE KEYS */;
/*!40000 ALTER TABLE `mpservers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notify`
--

DROP TABLE IF EXISTS `notify`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notify` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `type` varchar(32) CHARACTER SET latin1 NOT NULL,
  `message` varchar(128) CHARACTER SET latin1 NOT NULL,
  `access` int(4) NOT NULL,
  `time` double unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45487 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notify`
--

LOCK TABLES `notify` WRITE;
/*!40000 ALTER TABLE `notify` DISABLE KEYS */;
/*!40000 ALTER TABLE `notify` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `officiallevels`
--

DROP TABLE IF EXISTS `officiallevels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `officiallevels` (
  `file` varchar(64) NOT NULL,
  `stripped` varchar(64) NOT NULL,
  `game` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `display` varchar(64) NOT NULL,
  `position` int(11) NOT NULL,
  `qualify` float NOT NULL,
  `gold` float NOT NULL,
  `ultimate` float NOT NULL,
  `standardiser` float NOT NULL,
  `basescore` float NOT NULL,
  `basemultiplier` float NOT NULL,
  `goldbonus` float NOT NULL,
  `platinumbonus` float NOT NULL,
  `ultimatebonus` float NOT NULL,
  `difficulty` int(11) NOT NULL DEFAULT '1',
  `golddifficulty` int(11) NOT NULL DEFAULT '1' COMMENT 'Gold/Platinum',
  `ultimatedifficulty` int(11) NOT NULL DEFAULT '1',
  `easteregg` tinyint(1) NOT NULL DEFAULT '0',
  `crc` int(11) NOT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `notes` longtext NOT NULL,
  `number` int(11) NOT NULL,
  `short` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Shawtay',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Rating Factors` (`file`,`gold`,`ultimate`,`standardiser`,`basescore`,`basemultiplier`,`goldbonus`,`platinumbonus`,`ultimatebonus`),
  UNIQUE KEY `File/CRC` (`file`,`crc`),
  KEY `file` (`file`),
  KEY `display` (`display`)
) ENGINE=MyISAM AUTO_INCREMENT=403 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `officiallevels`
--

LOCK TABLES `officiallevels` WRITE;
/*!40000 ALTER TABLE `officiallevels` DISABLE KEYS */;
/*!40000 ALTER TABLE `officiallevels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packs`
--

DROP TABLE IF EXISTS `packs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packs` (
  `name` varchar(64) CHARACTER SET latin1 NOT NULL COMMENT 'internal name',
  `display` varchar(128) CHARACTER SET latin1 NOT NULL,
  `marbles` varchar(128) CHARACTER SET latin1 NOT NULL COMMENT 'space-separated skin names',
  `cost` float NOT NULL DEFAULT '5',
  `require` int(11) NOT NULL DEFAULT '0' COMMENT 'required donation to unlock',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packs`
--

LOCK TABLES `packs` WRITE;
/*!40000 ALTER TABLE `packs` DISABLE KEYS */;
/*!40000 ALTER TABLE `packs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packselects`
--

DROP TABLE IF EXISTS `packselects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `packselects` (
  `username` varchar(64) NOT NULL,
  `pack` int(11) NOT NULL COMMENT 'the id of the pack',
  `cost` float NOT NULL COMMENT 'how many credits did they pay',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packselects`
--

LOCK TABLES `packselects` WRITE;
/*!40000 ALTER TABLE `packselects` DISABLE KEYS */;
/*!40000 ALTER TABLE `packselects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pings`
--

DROP TABLE IF EXISTS `pings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `from_server` tinyint(1) NOT NULL DEFAULT '0',
  `time` float NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5013 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pings`
--

LOCK TABLES `pings` WRITE;
/*!40000 ALTER TABLE `pings` DISABLE KEYS */;
/*!40000 ALTER TABLE `pings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pqapril`
--

DROP TABLE IF EXISTS `pqapril`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pqapril` (
  `username` varchar(64) NOT NULL,
  `address` varchar(32) NOT NULL,
  `finished` tinyint(1) NOT NULL DEFAULT '0',
  `coins` int(11) NOT NULL DEFAULT '0',
  `oobs` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pqapril`
--

LOCK TABLES `pqapril` WRITE;
/*!40000 ALTER TABLE `pqapril` DISABLE KEYS */;
/*!40000 ALTER TABLE `pqapril` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qmutelevels`
--

DROP TABLE IF EXISTS `qmutelevels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qmutelevels` (
  `qmutes` int(11) NOT NULL,
  `action` enum('Warn','Mute','CBan','Ban') DEFAULT NULL,
  `message` text,
  `duration` int(11) DEFAULT NULL,
  PRIMARY KEY (`qmutes`),
  UNIQUE KEY `qmutelevels_qmutes_uindex` (`qmutes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qmutelevels`
--

LOCK TABLES `qmutelevels` WRITE;
/*!40000 ALTER TABLE `qmutelevels` DISABLE KEYS */;
/*!40000 ALTER TABLE `qmutelevels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `qotd`
--

DROP TABLE IF EXISTS `qotd`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qotd` (
  `text` text NOT NULL,
  `username` varchar(64) NOT NULL,
  `selected` tinyint(1) NOT NULL DEFAULT '0',
  `submitter` varchar(64) DEFAULT '??',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=331 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `qotd`
--

LOCK TABLES `qotd` WRITE;
/*!40000 ALTER TABLE `qotd` DISABLE KEYS */;
/*!40000 ALTER TABLE `qotd` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ratings` (
  `username` varchar(64) NOT NULL,
  `level` varchar(128) NOT NULL,
  `rating` int(11) NOT NULL COMMENT '-1 negative / 0 neutral / 1 positive',
  `positive` tinyint(1) NOT NULL,
  `neutral` tinyint(1) NOT NULL,
  `negative` tinyint(1) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13003 DEFAULT CHARSET=utf8 COMMENT='Level ratings from players';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ratings`
--

LOCK TABLES `ratings` WRITE;
/*!40000 ALTER TABLE `ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `report`
--

DROP TABLE IF EXISTS `report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `report` (
  `username` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `person` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `reason` text CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `report`
--

LOCK TABLES `report` WRITE;
/*!40000 ALTER TABLE `report` DISABLE KEYS */;
/*!40000 ALTER TABLE `report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `savedmessages`
--

DROP TABLE IF EXISTS `savedmessages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `savedmessages` (
  `sender` varchar(64) NOT NULL,
  `recipient` varchar(64) NOT NULL,
  `message` text NOT NULL,
  `received` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2731 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `savedmessages`
--

LOCK TABLES `savedmessages` WRITE;
/*!40000 ALTER TABLE `savedmessages` DISABLE KEYS */;
/*!40000 ALTER TABLE `savedmessages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scdata`
--

DROP TABLE IF EXISTS `scdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scdata` (
  `name` varchar(64) NOT NULL,
  `display` varchar(64) NOT NULL,
  `missions` varchar(256) NOT NULL,
  `platinumPercent` float NOT NULL DEFAULT '0.5',
  `ultimatePercent` float NOT NULL DEFAULT '0.8',
  `minTimeout` float NOT NULL DEFAULT '5',
  `maxTimeout` float NOT NULL DEFAULT '10',
  `points_2_win` int(11) NOT NULL DEFAULT '5',
  `points_2_tie` int(11) NOT NULL DEFAULT '3',
  `points_2_lose` int(11) NOT NULL DEFAULT '1',
  `points_2_forfeit` int(11) NOT NULL DEFAULT '-1',
  `points_2_plat` int(11) NOT NULL DEFAULT '1',
  `points_2_ult` int(11) NOT NULL DEFAULT '3',
  `points_3_win` int(11) NOT NULL DEFAULT '7',
  `points_3_tie1` int(11) NOT NULL DEFAULT '3',
  `points_3_tie2` int(11) NOT NULL DEFAULT '2',
  `points_3_tieall` int(11) NOT NULL DEFAULT '5',
  `points_3_lose2` int(11) NOT NULL DEFAULT '3',
  `points_3_lose3` int(11) NOT NULL DEFAULT '1',
  `points_3_forfeit` int(11) NOT NULL DEFAULT '-2',
  `points_3_plat` int(11) NOT NULL DEFAULT '2',
  `points_3_ult` int(11) NOT NULL DEFAULT '4',
  `points_4_win` int(11) NOT NULL DEFAULT '10',
  `points_4_tie1` int(11) NOT NULL DEFAULT '7',
  `points_4_tie2` int(11) NOT NULL DEFAULT '4',
  `points_4_tie3` int(11) NOT NULL DEFAULT '2',
  `points_4_tieall` int(11) NOT NULL DEFAULT '7',
  `points_4_lose2` int(11) NOT NULL DEFAULT '7',
  `points_4_lose3` int(11) NOT NULL DEFAULT '4',
  `points_4_lose4` int(11) NOT NULL DEFAULT '1',
  `points_4_forfeit` int(11) NOT NULL DEFAULT '-4',
  `points_4_plat` int(11) NOT NULL DEFAULT '3',
  `points_4_ult` int(11) NOT NULL DEFAULT '6',
  `bitmap` varchar(64) NOT NULL,
  `disabled` tinyint(2) NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scdata`
--

LOCK TABLES `scdata` WRITE;
/*!40000 ALTER TABLE `scdata` DISABLE KEYS */;
/*!40000 ALTER TABLE `scdata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sclevelscores`
--

DROP TABLE IF EXISTS `sclevelscores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sclevelscores` (
  `scid` int(11) NOT NULL,
  `username` varchar(64) NOT NULL,
  `level` varchar(128) NOT NULL,
  `levelnum` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `total` int(11) NOT NULL DEFAULT '0' COMMENT 'Cumulative time',
  `real` int(11) NOT NULL DEFAULT '0' COMMENT 'Total Real-time',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=62274 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sclevelscores`
--

LOCK TABLES `sclevelscores` WRITE;
/*!40000 ALTER TABLE `sclevelscores` DISABLE KEYS */;
/*!40000 ALTER TABLE `sclevelscores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scores`
--

DROP TABLE IF EXISTS `scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scores` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL COMMENT 'Player''s Username',
  `score` int(11) NOT NULL COMMENT 'Actual Time (ms)',
  `level` varchar(64) CHARACTER SET latin1 NOT NULL COMMENT 'Stripped level (lowercase, alphanumeric)',
  `type` varchar(16) CHARACTER SET latin1 NOT NULL COMMENT 'Difficulty',
  `gametype` varchar(16) CHARACTER SET latin1 NOT NULL COMMENT 'Game',
  `rating` int(11) NOT NULL COMMENT 'Rating Earned',
  `modifiers` int(6) NOT NULL DEFAULT '0' COMMENT 'Modifiers',
  `origin` int(4) NOT NULL DEFAULT '1' COMMENT 'Site Origin (0 - PhilsEmpire, 1 - Marbleblast.com)',
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Id (Auto-filled)',
  `time` int(10) unsigned NOT NULL COMMENT 'Server Time',
  PRIMARY KEY (`id`),
  KEY `Level/Score/Rating` (`level`,`score`,`rating`),
  KEY `Level/Types` (`level`,`type`,`gametype`,`modifiers`),
  KEY `username` (`username`,`score`,`level`,`modifiers`),
  KEY `User/Level/Score` (`username`,`level`,`score`)
) ENGINE=InnoDB AUTO_INCREMENT=570193 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scores`
--

LOCK TABLES `scores` WRITE;
/*!40000 ALTER TABLE `scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scores2`
--

DROP TABLE IF EXISTS `scores2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scores2` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `score` int(11) NOT NULL,
  `level` varchar(128) CHARACTER SET latin1 NOT NULL,
  `type` varchar(64) CHARACTER SET latin1 NOT NULL,
  `gametype` varchar(32) CHARACTER SET latin1 NOT NULL,
  `rating` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` double unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=150060 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scores2`
--

LOCK TABLES `scores2` WRITE;
/*!40000 ALTER TABLE `scores2` DISABLE KEYS */;
/*!40000 ALTER TABLE `scores2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scpractice`
--

DROP TABLE IF EXISTS `scpractice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scpractice` (
  `username` varchar(64) NOT NULL,
  `pack` varchar(32) NOT NULL,
  `score` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2432 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scpractice`
--

LOCK TABLES `scpractice` WRITE;
/*!40000 ALTER TABLE `scpractice` DISABLE KEYS */;
/*!40000 ALTER TABLE `scpractice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scscores`
--

DROP TABLE IF EXISTS `scscores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scscores` (
  `username` varchar(64) NOT NULL,
  `challenge` varchar(64) NOT NULL,
  `place` int(11) NOT NULL DEFAULT '0',
  `players` int(11) NOT NULL DEFAULT '2',
  `score` double NOT NULL DEFAULT '0',
  `percent` double NOT NULL DEFAULT '0',
  `points` double NOT NULL DEFAULT '0',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4229 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scscores`
--

LOCK TABLES `scscores` WRITE;
/*!40000 ALTER TABLE `scscores` DISABLE KEYS */;
/*!40000 ALTER TABLE `scscores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `serverplayers`
--

DROP TABLE IF EXISTS `serverplayers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `serverplayers` (
  `username` varchar(64) NOT NULL,
  `team` int(11) NOT NULL DEFAULT '-1',
  `host` tinyint(1) NOT NULL DEFAULT '0',
  `placed` int(11) NOT NULL,
  `score` int(11) NOT NULL DEFAULT '0',
  `gems1` int(11) NOT NULL DEFAULT '0',
  `gems2` int(11) NOT NULL DEFAULT '0',
  `gems5` int(11) NOT NULL DEFAULT '0',
  `marble` int(11) NOT NULL,
  `serverkey` varchar(64) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=307700 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `serverplayers`
--

LOCK TABLES `serverplayers` WRITE;
/*!40000 ALTER TABLE `serverplayers` DISABLE KEYS */;
/*!40000 ALTER TABLE `serverplayers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `servers` (
  `address` text CHARACTER SET latin1 NOT NULL,
  `port` int(11) NOT NULL DEFAULT '28000',
  `name` text CHARACTER SET latin1 NOT NULL,
  `level` text CHARACTER SET latin1 NOT NULL,
  `mode` int(11) NOT NULL,
  `players` int(11) NOT NULL,
  `maxPlayers` int(11) NOT NULL,
  `password` tinyint(1) NOT NULL,
  `submitting` tinyint(1) NOT NULL DEFAULT '0',
  `display` tinyint(1) NOT NULL DEFAULT '1',
  `lastHeartbeat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ping` int(11) NOT NULL,
  `key` text CHARACTER SET latin1 NOT NULL,
  `version` text CHARACTER SET latin1 NOT NULL,
  `dev` tinyint(1) NOT NULL,
  `dedicated` tinyint(1) NOT NULL,
  `mod` text CHARACTER SET latin1 NOT NULL,
  `os` text CHARACTER SET latin1 NOT NULL,
  `info` text CHARACTER SET latin1 NOT NULL,
  `handicap` int(11) NOT NULL DEFAULT '0',
  `host` text CHARACTER SET latin1 NOT NULL,
  `minrating` int(11) NOT NULL DEFAULT '0' COMMENT 'min rating to join server. default 0.',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Key` (`key`(64))
) ENGINE=MyISAM AUTO_INCREMENT=1507 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servers`
--

LOCK TABLES `servers` WRITE;
/*!40000 ALTER TABLE `servers` DISABLE KEYS */;
/*!40000 ALTER TABLE `servers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `serverscores`
--

DROP TABLE IF EXISTS `serverscores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `serverscores` (
  `username` varchar(64) NOT NULL,
  `place` int(11) NOT NULL DEFAULT '0',
  `score` int(11) NOT NULL DEFAULT '0',
  `handicap` int(11) NOT NULL DEFAULT '0',
  `server` varchar(64) NOT NULL,
  `key` varchar(64) NOT NULL,
  `host` tinyint(1) NOT NULL DEFAULT '0',
  `change` int(11) NOT NULL DEFAULT '0',
  `pre` int(11) NOT NULL DEFAULT '0',
  `post` int(11) NOT NULL DEFAULT '0',
  `current` int(11) NOT NULL DEFAULT '0',
  `players` int(11) NOT NULL DEFAULT '1',
  `guests` int(11) NOT NULL DEFAULT '0',
  `betterguests` int(11) NOT NULL DEFAULT '0',
  `team` int(11) NOT NULL DEFAULT '-1',
  `teammembers` int(11) NOT NULL DEFAULT '0',
  `teams` int(11) NOT NULL DEFAULT '0',
  `level` varchar(128) NOT NULL,
  `modes` varchar(64) NOT NULL,
  `gems1` int(11) NOT NULL DEFAULT '0',
  `gems2` int(11) NOT NULL DEFAULT '0',
  `gems5` int(11) NOT NULL DEFAULT '0',
  `marble` int(11) NOT NULL,
  `custom` tinyint(1) NOT NULL DEFAULT '0',
  `reset` int(11) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `User/place/players` (`username`,`place`,`players`),
  KEY `Key/Place/Player` (`username`,`place`,`key`)
) ENGINE=InnoDB AUTO_INCREMENT=128128 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `serverscores`
--

LOCK TABLES `serverscores` WRITE;
/*!40000 ALTER TABLE `serverscores` DISABLE KEYS */;
/*!40000 ALTER TABLE `serverscores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `serverteams`
--

DROP TABLE IF EXISTS `serverteams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `serverteams` (
  `username` varchar(64) NOT NULL,
  `scorekey` varchar(64) NOT NULL,
  `teamname` varchar(64) NOT NULL,
  `teamcolor` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1616 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `serverteams`
--

LOCK TABLES `serverteams` WRITE;
/*!40000 ALTER TABLE `serverteams` DISABLE KEYS */;
/*!40000 ALTER TABLE `serverteams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `key` varchar(64) CHARACTER SET latin1 NOT NULL,
  `value` text CHARACTER SET latin1 NOT NULL,
  `default` text CHARACTER SET latin1 NOT NULL,
  `displayname` varchar(64) CHARACTER SET latin1 NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `skins`
--

DROP TABLE IF EXISTS `skins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `skins` (
  `name` varchar(64) NOT NULL COMMENT 'internal name',
  `display` varchar(64) NOT NULL,
  `file` varchar(128) NOT NULL COMMENT 'relative to http root',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skins`
--

LOCK TABLES `skins` WRITE;
/*!40000 ALTER TABLE `skins` DISABLE KEYS */;
/*!40000 ALTER TABLE `skins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snowballs`
--

DROP TABLE IF EXISTS `snowballs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snowballs` (
  `username` varchar(64) NOT NULL,
  `count` int(11) NOT NULL,
  `hits` int(11) NOT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2289 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snowballs`
--

LOCK TABLES `snowballs` WRITE;
/*!40000 ALTER TABLE `snowballs` DISABLE KEYS */;
/*!40000 ALTER TABLE `snowballs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `snowglobes`
--

DROP TABLE IF EXISTS `snowglobes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `snowglobes` (
  `username` varchar(64) NOT NULL,
  `mission` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1229 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snowglobes`
--

LOCK TABLES `snowglobes` WRITE;
/*!40000 ALTER TABLE `snowglobes` DISABLE KEYS */;
/*!40000 ALTER TABLE `snowglobes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statuses`
--

DROP TABLE IF EXISTS `statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statuses` (
  `status` int(11) NOT NULL,
  `display` varchar(32) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statuses`
--

LOCK TABLES `statuses` WRITE;
/*!40000 ALTER TABLE `statuses` DISABLE KEYS */;
/*!40000 ALTER TABLE `statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `superchallenges`
--

DROP TABLE IF EXISTS `superchallenges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `superchallenges` (
  `pack` varchar(64) NOT NULL,
  `options` varchar(32) NOT NULL,
  `mode` int(11) NOT NULL,
  `max` int(11) NOT NULL,
  `player0` varchar(128) NOT NULL,
  `player1` varchar(128) NOT NULL,
  `player2` varchar(128) NOT NULL,
  `player3` varchar(128) NOT NULL,
  `player0status` varchar(128) NOT NULL,
  `player1status` varchar(128) NOT NULL,
  `player2status` varchar(128) NOT NULL,
  `player3status` varchar(128) NOT NULL,
  `player0time` int(11) NOT NULL DEFAULT '0',
  `player1time` int(11) NOT NULL DEFAULT '0',
  `player2time` int(11) NOT NULL DEFAULT '0',
  `player3time` int(11) NOT NULL DEFAULT '0',
  `player0progress` int(11) NOT NULL,
  `player1progress` int(11) NOT NULL,
  `player2progress` int(11) NOT NULL,
  `player3progress` int(11) NOT NULL,
  `player0percent` double NOT NULL DEFAULT '0',
  `player1percent` double NOT NULL DEFAULT '0',
  `player2percent` double NOT NULL DEFAULT '0',
  `player3percent` double NOT NULL DEFAULT '0',
  `player0finish` int(11) NOT NULL DEFAULT '0',
  `player1finish` int(11) NOT NULL DEFAULT '0',
  `player2finish` int(11) NOT NULL DEFAULT '0',
  `player3finish` int(11) NOT NULL DEFAULT '0',
  `player0started` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Did they get a START message?',
  `player1started` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Did they get a START message?',
  `player2started` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Did they get a START message?',
  `player3started` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Did they get a START message?',
  `winner` varchar(128) NOT NULL,
  `expires` int(11) NOT NULL,
  `starttime` int(11) NOT NULL DEFAULT '0',
  `timeouttime` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4212 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `superchallenges`
--

LOCK TABLES `superchallenges` WRITE;
/*!40000 ALTER TABLE `superchallenges` DISABLE KEYS */;
/*!40000 ALTER TABLE `superchallenges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `topscores`
--

DROP TABLE IF EXISTS `topscores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topscores` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `score` int(11) NOT NULL,
  `level` varchar(128) CHARACTER SET latin1 NOT NULL,
  `type` varchar(64) CHARACTER SET latin1 NOT NULL,
  `gametype` varchar(32) CHARACTER SET latin1 NOT NULL,
  `rating` int(11) NOT NULL,
  `modifiers` int(6) NOT NULL DEFAULT '0',
  `origin` int(4) NOT NULL DEFAULT '1',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` double unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `User/level` (`username`,`level`,`gametype`,`modifiers`),
  KEY `level` (`level`),
  KEY `score` (`score`)
) ENGINE=InnoDB AUTO_INCREMENT=23338 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `topscores`
--

LOCK TABLES `topscores` WRITE;
/*!40000 ALTER TABLE `topscores` DISABLE KEYS */;
/*!40000 ALTER TABLE `topscores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tracking`
--

DROP TABLE IF EXISTS `tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tracking` (
  `username` varchar(64) NOT NULL,
  `type` varchar(32) NOT NULL,
  `data` text NOT NULL,
  `count` int(11) NOT NULL DEFAULT '0',
  `lastUpdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `firstUpdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  KEY `username` (`username`),
  KEY `type` (`type`),
  KEY `username_2` (`username`,`type`,`data`(128))
) ENGINE=InnoDB AUTO_INCREMENT=9247873 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracking`
--

LOCK TABLES `tracking` WRITE;
/*!40000 ALTER TABLE `tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ultraachievements`
--

DROP TABLE IF EXISTS `ultraachievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ultraachievements` (
  `username` varchar(64) NOT NULL,
  `achievement` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `User/Achievement` (`username`,`achievement`)
) ENGINE=InnoDB AUTO_INCREMENT=1632 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ultraachievements`
--

LOCK TABLES `ultraachievements` WRITE;
/*!40000 ALTER TABLE `ultraachievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `ultraachievements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usedkeys`
--

DROP TABLE IF EXISTS `usedkeys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usedkeys` (
  `username` varchar(64) NOT NULL,
  `key` varchar(40) NOT NULL,
  `time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usedkeys`
--

LOCK TABLES `usedkeys` WRITE;
/*!40000 ALTER TABLE `usedkeys` DISABLE KEYS */;
/*!40000 ALTER TABLE `usedkeys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userchallengedata`
--

DROP TABLE IF EXISTS `userchallengedata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userchallengedata` (
  `username` varchar(128) NOT NULL,
  `points` int(11) NOT NULL,
  `winstreak` int(11) NOT NULL,
  `losestreak` int(11) NOT NULL,
  `totalgameswon` int(11) NOT NULL,
  `totalgameslost` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userchallengedata`
--

LOCK TABLES `userchallengedata` WRITE;
/*!40000 ALTER TABLE `userchallengedata` DISABLE KEYS */;
/*!40000 ALTER TABLE `userchallengedata` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usermarbles`
--

DROP TABLE IF EXISTS `usermarbles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usermarbles` (
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `marble` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usermarbles`
--

LOCK TABLES `usermarbles` WRITE;
/*!40000 ALTER TABLE `usermarbles` DISABLE KEYS */;
/*!40000 ALTER TABLE `usermarbles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `display` varchar(64) CHARACTER SET latin1 NOT NULL COMMENT 'deprecated-- see joomla db',
  `username` varchar(64) CHARACTER SET latin1 NOT NULL,
  `pass` varchar(64) CHARACTER SET latin1 NOT NULL,
  `salt` varchar(32) CHARACTER SET latin1 NOT NULL,
  `email` varchar(64) CHARACTER SET latin1 NOT NULL,
  `showemail` int(1) NOT NULL DEFAULT '0',
  `secretq` varchar(64) CHARACTER SET latin1 NOT NULL,
  `secreta` varchar(64) CHARACTER SET latin1 NOT NULL,
  `signature` text CHARACTER SET latin1 NOT NULL,
  `title` varchar(16) NOT NULL,
  `lastlevel` varchar(64) CHARACTER SET latin1 NOT NULL,
  `showscores` tinyint(1) NOT NULL DEFAULT '1',
  `access` int(4) NOT NULL DEFAULT '0',
  `rating` double NOT NULL DEFAULT '0',
  `rating_mbg` double NOT NULL DEFAULT '0',
  `rating_mbp` double NOT NULL DEFAULT '0',
  `rating_mbu` int(11) NOT NULL DEFAULT '0',
  `rating_mp` double NOT NULL DEFAULT '1500',
  `rating_mpgames` int(11) NOT NULL DEFAULT '0',
  `rating_mpteamgames` int(11) NOT NULL DEFAULT '0',
  `rating_custom` double NOT NULL DEFAULT '0',
  `rating_achievements` int(11) NOT NULL DEFAULT '0',
  `challengepoints` int(11) NOT NULL DEFAULT '0',
  `mpresets` int(11) NOT NULL DEFAULT '0',
  `mpwinstreak` int(11) NOT NULL DEFAULT '0',
  `challengewinstreak` int(11) NOT NULL DEFAULT '0',
  `challengeracewinstreak` int(11) NOT NULL DEFAULT '0',
  `challengeattemptswinstreak` int(11) NOT NULL DEFAULT '0',
  `gems1` int(11) NOT NULL DEFAULT '0',
  `gems2` int(11) NOT NULL DEFAULT '0',
  `gems5` int(11) NOT NULL DEFAULT '0',
  `mptotalgems` int(11) NOT NULL DEFAULT '0',
  `mptotalscore` int(11) NOT NULL DEFAULT '0',
  `forgotHash` varchar(64) CHARACTER SET latin1 NOT NULL,
  `disabled` tinyint(1) NOT NULL,
  `banned` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Ban status: 1 = perm mute, 2 = block entry, 3 = block all ips too',
  `banreason` text CHARACTER SET latin1 NOT NULL,
  `guest` tinyint(1) NOT NULL,
  `joomla` tinyint(1) NOT NULL DEFAULT '0',
  `chatkey` varchar(32) CHARACTER SET latin1 NOT NULL,
  `kicknext` tinyint(1) NOT NULL DEFAULT '0',
  `muteIndex` float NOT NULL DEFAULT '0',
  `muteMultiplier` float NOT NULL DEFAULT '1' COMMENT 'Mute idx * mult',
  `acceptedTos` tinyint(1) NOT NULL DEFAULT '0',
  `lastaction` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `joindate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `User/Show` (`username`,`showscores`),
  KEY `User/Ban` (`username`,`banned`),
  KEY `User/Access` (`username`,`access`)
) ENGINE=InnoDB AUTO_INCREMENT=33998 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `versions`
--

DROP TABLE IF EXISTS `versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `versions` (
  `version` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `desc` text NOT NULL,
  `url` varchar(256) NOT NULL,
  `submitter` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=188 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `versions`
--

LOCK TABLES `versions` WRITE;
/*!40000 ALTER TABLE `versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Current Database: `prod_joomla`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `prod_joomla` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `prod_joomla`;

--
-- Table structure for table `bv2xj_kunena_users`
--

DROP TABLE IF EXISTS `bv2xj_kunena_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bv2xj_kunena_users` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `status_text` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `view` varchar(8) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `signature` text CHARACTER SET utf8,
  `moderator` int(11) DEFAULT '0',
  `banned` datetime DEFAULT NULL,
  `ordering` int(11) DEFAULT '0',
  `posts` int(11) DEFAULT '0',
  `avatar` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `timestamp` int(11) DEFAULT NULL,
  `karma` int(11) DEFAULT '0',
  `karma_time` int(11) DEFAULT '0',
  `group_id` int(4) DEFAULT '1',
  `uhits` int(11) DEFAULT '0',
  `personalText` tinytext CHARACTER SET utf8,
  `gender` tinyint(4) NOT NULL DEFAULT '0',
  `birthdate` date NOT NULL DEFAULT '0001-01-01',
  `location` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `icq` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `yim` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `youtube` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ok` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `microsoft` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `telegram` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `vk` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `skype` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `twitter` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `facebook` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `google` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `myspace` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `linkedin` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `linkedin_company` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delicious` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `friendfeed` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `digg` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `instagram` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `qqsocial` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qzone` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `whatsapp` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weibo` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `wechat` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `apple` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `blogspot` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `flickr` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `bebo` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `websitename` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `websiteurl` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `rank` tinyint(4) NOT NULL DEFAULT '0',
  `hideEmail` tinyint(1) NOT NULL DEFAULT '1',
  `showOnline` tinyint(1) NOT NULL DEFAULT '1',
  `canSubscribe` tinyint(1) NOT NULL DEFAULT '-1',
  `userListtime` int(11) NOT NULL DEFAULT '-2',
  `thankyou` int(11) DEFAULT '0',
  `ip` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `socialshare` tinyint(1) NOT NULL DEFAULT '1',
  `requirePostApproval` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`userid`),
  KEY `group_id` (`group_id`),
  KEY `posts` (`posts`),
  KEY `uhits` (`uhits`),
  KEY `banned` (`banned`),
  KEY `moderator` (`moderator`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bv2xj_kunena_users`
--

LOCK TABLES `bv2xj_kunena_users` WRITE;
/*!40000 ALTER TABLE `bv2xj_kunena_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `bv2xj_kunena_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bv2xj_user_titles`
--

DROP TABLE IF EXISTS `bv2xj_user_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bv2xj_user_titles` (
  `title` varchar(32) NOT NULL COMMENT 'Or name of flair image file without extension',
  `position` int(11) NOT NULL COMMENT '0 - Flair; 1 - Prefix; 2 - Postfix',
  `display_name` text NOT NULL COMMENT 'Displayed name',
  `unlock_description` text NOT NULL COMMENT 'What you need to do to get the flair (readable)',
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=243 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bv2xj_user_titles`
--

LOCK TABLES `bv2xj_user_titles` WRITE;
/*!40000 ALTER TABLE `bv2xj_user_titles` DISABLE KEYS */;
/*!40000 ALTER TABLE `bv2xj_user_titles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bv2xj_user_titles_earned`
--

DROP TABLE IF EXISTS `bv2xj_user_titles_earned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bv2xj_user_titles_earned` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `titleid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user-title` (`userid`,`titleid`)
) ENGINE=InnoDB AUTO_INCREMENT=3626 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bv2xj_user_titles_earned`
--

LOCK TABLES `bv2xj_user_titles_earned` WRITE;
/*!40000 ALTER TABLE `bv2xj_user_titles_earned` DISABLE KEYS */;
/*!40000 ALTER TABLE `bv2xj_user_titles_earned` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bv2xj_users`
--

DROP TABLE IF EXISTS `bv2xj_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bv2xj_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(400) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `username` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `block` tinyint(4) NOT NULL DEFAULT '0',
  `sendEmail` tinyint(4) DEFAULT '0',
  `registerDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastvisitDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activation` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `params` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastResetTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resetCount` int(11) NOT NULL DEFAULT '0' COMMENT 'Count of password resets since lastResetTime',
  `bluePoster` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Flag for determining Blue Posters on Forums',
  `hasColor` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `colorValue` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000',
  `titleFlair` int(11) NOT NULL DEFAULT '0' COMMENT 'User''s Flair Icon.',
  `titlePrefix` int(11) NOT NULL DEFAULT '0' COMMENT 'User''s selected Title.',
  `titleSuffix` int(11) NOT NULL DEFAULT '0' COMMENT 'User''s selected Suffix.',
  `statusMsg` text COLLATE utf8mb4_unicode_ci COMMENT 'Profile Status Message',
  `profileBanner` int(11) NOT NULL DEFAULT '0' COMMENT 'Profile Banner Image',
  `donations` float NOT NULL DEFAULT '0',
  `credits` int(11) NOT NULL DEFAULT '0',
  `credits_spent` int(11) NOT NULL DEFAULT '0',
  `otpKey` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Two factor authentication encrypted keys',
  `otep` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'One time emergency passwords',
  `requireReset` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Require user to reset password on next login',
  `webchatKey` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_username` (`username`),
  KEY `idx_block` (`block`),
  KEY `email` (`email`),
  KEY `idx_name` (`name`(100))
) ENGINE=InnoDB AUTO_INCREMENT=25544 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bv2xj_users`
--

LOCK TABLES `bv2xj_users` WRITE;
/*!40000 ALTER TABLE `bv2xj_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `bv2xj_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Current Database: `prod_pq`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `prod_pq` /*!40100 DEFAULT CHARACTER SET latin1 COLLATE latin1_general_ci */;

USE `prod_pq`;

--
-- Table structure for table `ex82r_achievement_categories`
--

DROP TABLE IF EXISTS `ex82r_achievement_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_achievement_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text COLLATE latin1_general_ci,
  `bitmap_path` text COLLATE latin1_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_achievement_categories_id_uindex` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_achievement_categories`
--

LOCK TABLES `ex82r_achievement_categories` WRITE;
/*!40000 ALTER TABLE `ex82r_achievement_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_achievement_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_achievement_names`
--

DROP TABLE IF EXISTS `ex82r_achievement_names`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_achievement_names` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT '1',
  `index` int(11) DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `title` text COLLATE latin1_general_ci,
  `description` text COLLATE latin1_general_ci,
  `rating` int(11) DEFAULT '0',
  `reward_flair` int(11) DEFAULT NULL,
  `mask` tinyint(1) DEFAULT '0',
  `manual` tinyint(1) DEFAULT '0' COMMENT 'If the achievement is awarded manually',
  `bitmap_extent` varchar(8) COLLATE latin1_general_ci DEFAULT '113 44',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_achievement_names_id_uindex` (`id`),
  KEY `ex82r_achievement_names_ex82r_achievement_categories_id_fk` (`category_id`),
  CONSTRAINT `ex82r_achievement_names_ex82r_achievement_categories_id_fk` FOREIGN KEY (`category_id`) REFERENCES `ex82r_achievement_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3033 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_achievement_names`
--

LOCK TABLES `ex82r_achievement_names` WRITE;
/*!40000 ALTER TABLE `ex82r_achievement_names` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_achievement_names` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_april20_kings`
--

DROP TABLE IF EXISTS `ex82r_april20_kings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_april20_kings` (
  `user_id` int(11) NOT NULL,
  `total_scores` int(11) DEFAULT NULL,
  `last_update` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `ex82r_april20_kings_user_id_uindex` (`user_id`),
  CONSTRAINT `ex82r_april20_kings_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_april20_kings`
--

LOCK TABLES `ex82r_april20_kings` WRITE;
/*!40000 ALTER TABLE `ex82r_april20_kings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_april20_kings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_gen_mission_stats`
--

DROP TABLE IF EXISTS `ex82r_gen_mission_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_gen_mission_stats` (
  `mission_id` int(11) NOT NULL,
  `count_plays` int(11) DEFAULT '0',
  `count_par` int(11) DEFAULT '0',
  `count_platinum` int(11) DEFAULT '0',
  `count_ultimate` int(11) DEFAULT '0',
  `count_awesome` int(11) DEFAULT '0',
  PRIMARY KEY (`mission_id`),
  UNIQUE KEY `ex82r_gen_mission_stats_mission_id_uindex` (`mission_id`),
  CONSTRAINT `ex82r_gen_mission_stats_ex82r_missions_id_fk` FOREIGN KEY (`mission_id`) REFERENCES `ex82r_missions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_gen_mission_stats`
--

LOCK TABLES `ex82r_gen_mission_stats` WRITE;
/*!40000 ALTER TABLE `ex82r_gen_mission_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_gen_mission_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_gen_world_records`
--

DROP TABLE IF EXISTS `ex82r_gen_world_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_gen_world_records` (
  `score_id` int(11) NOT NULL,
  PRIMARY KEY (`score_id`),
  UNIQUE KEY `ex82r_gen_world_records_score_id_uindex` (`score_id`),
  CONSTRAINT `ex82r_gen_world_records_ex82r_user_scores_id_fk` FOREIGN KEY (`score_id`) REFERENCES `ex82r_user_scores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_gen_world_records`
--

LOCK TABLES `ex82r_gen_world_records` WRITE;
/*!40000 ALTER TABLE `ex82r_gen_world_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_gen_world_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_marble_categories`
--

DROP TABLE IF EXISTS `ex82r_marble_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_marble_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE latin1_general_ci,
  `file_base` text COLLATE latin1_general_ci,
  `sort` int(11) DEFAULT NULL,
  `disabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_marble_categories_id_uindex` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_marble_categories`
--

LOCK TABLES `ex82r_marble_categories` WRITE;
/*!40000 ALTER TABLE `ex82r_marble_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_marble_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_marbles`
--

DROP TABLE IF EXISTS `ex82r_marbles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_marbles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` text COLLATE latin1_general_ci,
  `shape_file` text COLLATE latin1_general_ci,
  `skin` text COLLATE latin1_general_ci,
  `shaderV` text COLLATE latin1_general_ci,
  `shaderF` text COLLATE latin1_general_ci,
  `sort` int(11) DEFAULT NULL,
  `disabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_marbles_id_uindex` (`id`),
  KEY `ex82r_marbles_ex82r_marble_categories_id_fk` (`category_id`),
  CONSTRAINT `ex82r_marbles_ex82r_marble_categories_id_fk` FOREIGN KEY (`category_id`) REFERENCES `ex82r_marble_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=174 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='All possible marbles';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_marbles`
--

LOCK TABLES `ex82r_marbles` WRITE;
/*!40000 ALTER TABLE `ex82r_marbles` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_marbles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_match_scores`
--

DROP TABLE IF EXISTS `ex82r_match_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_match_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `score_id` int(11) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `placement` int(11) DEFAULT '1' COMMENT 'Final place, 1st is 1',
  `time_percent` float DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_mp_game_scores_id_uindex` (`id`),
  KEY `ex82r_mp_game_scores_ex82r_mp_games_id_fk` (`match_id`),
  KEY `ex82r_mp_game_scores_bv2xj_users_id_fk` (`user_id`),
  KEY `ex82r_mp_game_scores_ex82r_mission_scores_id_fk` (`score_id`),
  KEY `ex82r_match_scores_ex82r_match_teams_id_fk` (`team_id`),
  CONSTRAINT `ex82r_match_scores_ex82r_match_teams_id_fk` FOREIGN KEY (`team_id`) REFERENCES `ex82r_match_teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_mp_game_scores_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_mp_game_scores_ex82r_mission_scores_id_fk` FOREIGN KEY (`score_id`) REFERENCES `ex82r_user_scores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_mp_game_scores_ex82r_mp_games_id_fk` FOREIGN KEY (`match_id`) REFERENCES `ex82r_matches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=525759 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_match_scores`
--

LOCK TABLES `ex82r_match_scores` WRITE;
/*!40000 ALTER TABLE `ex82r_match_scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_match_scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_match_teams`
--

DROP TABLE IF EXISTS `ex82r_match_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_match_teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `match_id` int(11) DEFAULT NULL,
  `name` text COLLATE latin1_general_ci,
  `color` int(11) DEFAULT NULL,
  `player_count` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_match_teams_id_uindex` (`id`),
  KEY `ex82r_match_teams_ex82r_matches_id_fk` (`match_id`),
  CONSTRAINT `ex82r_match_teams_ex82r_matches_id_fk` FOREIGN KEY (`match_id`) REFERENCES `ex82r_matches` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5078 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_match_teams`
--

LOCK TABLES `ex82r_match_teams` WRITE;
/*!40000 ALTER TABLE `ex82r_match_teams` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_match_teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_matches`
--

DROP TABLE IF EXISTS `ex82r_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) DEFAULT NULL,
  `player_count` int(11) DEFAULT NULL,
  `team_count` int(11) DEFAULT '0',
  `rating_multiplier` float DEFAULT '1',
  `server_address` text COLLATE latin1_general_ci,
  `server_port` int(11) DEFAULT NULL,
  `dedicated` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_mp_games_id_uindex` (`id`),
  KEY `ex82r_mp_games_ex82r_missions_official_id_fk` (`mission_id`),
  CONSTRAINT `ex82r_mp_games_ex82r_missions_official_id_fk` FOREIGN KEY (`mission_id`) REFERENCES `ex82r_missions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=287072 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_matches`
--

LOCK TABLES `ex82r_matches` WRITE;
/*!40000 ALTER TABLE `ex82r_matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_metrics_graphics_extensions`
--

DROP TABLE IF EXISTS `ex82r_metrics_graphics_extensions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_metrics_graphics_extensions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `graphics_id` int(11) NOT NULL,
  `extension` varchar(64) COLLATE latin1_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_metrics_graphics_extensions_id_uindex` (`id`),
  KEY `ex82r_metrics_graphics_extensions_graphics_id_fk` (`graphics_id`),
  CONSTRAINT `ex82r_metrics_graphics_extensions_graphics_id_fk` FOREIGN KEY (`graphics_id`) REFERENCES `ex82r_metrics_graphics_info` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=445805 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_metrics_graphics_extensions`
--

LOCK TABLES `ex82r_metrics_graphics_extensions` WRITE;
/*!40000 ALTER TABLE `ex82r_metrics_graphics_extensions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_metrics_graphics_extensions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_metrics_graphics_info`
--

DROP TABLE IF EXISTS `ex82r_metrics_graphics_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_metrics_graphics_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `major` tinyint(4) NOT NULL,
  `minor` tinyint(4) NOT NULL,
  `vendor` varchar(32) COLLATE latin1_general_ci NOT NULL,
  `renderer` varchar(256) COLLATE latin1_general_ci DEFAULT NULL,
  `os` varchar(16) COLLATE latin1_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_metrics_graphics_info_id_uindex` (`id`),
  KEY `ex82r_metrics_graphics_info_bv2xj_users_id_fk` (`user_id`),
  CONSTRAINT `ex82r_metrics_graphics_info_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1568 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_metrics_graphics_info`
--

LOCK TABLES `ex82r_metrics_graphics_info` WRITE;
/*!40000 ALTER TABLE `ex82r_metrics_graphics_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_metrics_graphics_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_metrics_screen_resolution`
--

DROP TABLE IF EXISTS `ex82r_metrics_screen_resolution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_metrics_screen_resolution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `width` int(11) NOT NULL DEFAULT '1024',
  `height` int(11) NOT NULL DEFAULT '768',
  `color_depth` int(11) NOT NULL DEFAULT '32',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Resolution per User` (`user_id`,`width`,`height`,`color_depth`),
  CONSTRAINT `ex82r_metrics_screen_resolution_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97618 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_metrics_screen_resolution`
--

LOCK TABLES `ex82r_metrics_screen_resolution` WRITE;
/*!40000 ALTER TABLE `ex82r_metrics_screen_resolution` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_metrics_screen_resolution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_metrics_supported_resolutions`
--

DROP TABLE IF EXISTS `ex82r_metrics_supported_resolutions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_metrics_supported_resolutions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `width` int(11) NOT NULL DEFAULT '1024',
  `height` int(11) NOT NULL DEFAULT '768',
  `color_depth` int(11) NOT NULL DEFAULT '32',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Resolution per User` (`user_id`,`width`,`height`,`color_depth`),
  CONSTRAINT `ex82r_metrics_supported_resolutions_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1798511 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_metrics_supported_resolutions`
--

LOCK TABLES `ex82r_metrics_supported_resolutions` WRITE;
/*!40000 ALTER TABLE `ex82r_metrics_supported_resolutions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_metrics_supported_resolutions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_metrics_window_resolution`
--

DROP TABLE IF EXISTS `ex82r_metrics_window_resolution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_metrics_window_resolution` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `width` int(11) NOT NULL DEFAULT '1024',
  `height` int(11) NOT NULL DEFAULT '768',
  `color_depth` int(11) NOT NULL DEFAULT '32',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Resolution per User` (`user_id`,`width`,`height`,`color_depth`),
  CONSTRAINT `ex82r_metrics_window_resolution_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97816 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_metrics_window_resolution`
--

LOCK TABLES `ex82r_metrics_window_resolution` WRITE;
/*!40000 ALTER TABLE `ex82r_metrics_window_resolution` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_metrics_window_resolution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_mission_change_log`
--

DROP TABLE IF EXISTS `ex82r_mission_change_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_mission_change_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lw3qp_mission_change_log_id_uindex` (`id`),
  KEY `mission_id` (`mission_id`),
  CONSTRAINT `ex82r_mission_change_log_ex82r_missions_official_id_fk` FOREIGN KEY (`mission_id`) REFERENCES `ex82r_missions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=834 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_mission_change_log`
--

LOCK TABLES `ex82r_mission_change_log` WRITE;
/*!40000 ALTER TABLE `ex82r_mission_change_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_mission_change_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_mission_difficulties`
--

DROP TABLE IF EXISTS `ex82r_mission_difficulties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_mission_difficulties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) DEFAULT NULL,
  `name` text COLLATE latin1_general_ci,
  `display` text COLLATE latin1_general_ci,
  `sort_index` int(11) DEFAULT NULL,
  `directory` text COLLATE latin1_general_ci,
  `bitmap_directory` text COLLATE latin1_general_ci,
  `previews_directory` text COLLATE latin1_general_ci,
  `is_local` tinyint(1) DEFAULT '0' COMMENT 'Clients generate from local files, not online',
  `disabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lw3qp_mission_difficulties_id_uindex` (`id`),
  KEY `ex82r_mission_difficulties_ex82r_mission_games_id_fk` (`game_id`),
  CONSTRAINT `ex82r_mission_difficulties_ex82r_mission_games_id_fk` FOREIGN KEY (`game_id`) REFERENCES `ex82r_mission_games` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_mission_difficulties`
--

LOCK TABLES `ex82r_mission_difficulties` WRITE;
/*!40000 ALTER TABLE `ex82r_mission_difficulties` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_mission_difficulties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_mission_games`
--

DROP TABLE IF EXISTS `ex82r_mission_games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_mission_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text COLLATE latin1_general_ci COMMENT 'Internal Name',
  `display` text COLLATE latin1_general_ci COMMENT 'Displayed Name on Level Select',
  `sort_index` int(11) DEFAULT NULL,
  `long_display` text COLLATE latin1_general_ci COMMENT 'Longer Name for Stats',
  `rating_column` text COLLATE latin1_general_ci,
  `game_type` enum('Single Player','Multiplayer') COLLATE latin1_general_ci DEFAULT 'Single Player',
  `has_platinum_times` tinyint(1) DEFAULT '1',
  `has_ultimate_times` tinyint(1) DEFAULT '1',
  `has_awesome_times` tinyint(1) DEFAULT '0',
  `has_easter_eggs` tinyint(1) DEFAULT '1',
  `platinum_time_name` text COLLATE latin1_general_ci,
  `ultimate_time_name` text COLLATE latin1_general_ci,
  `awesome_time_name` text COLLATE latin1_general_ci,
  `easter_egg_name` text COLLATE latin1_general_ci,
  `platinum_time_count` int(11) DEFAULT '0',
  `ultimate_time_count` int(11) DEFAULT '0',
  `awesome_time_count` int(11) DEFAULT '0',
  `egg_count` int(11) DEFAULT '0',
  `force_gamemode` text COLLATE latin1_general_ci,
  `has_blast` tinyint(1) DEFAULT '1',
  `disabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lw3qp_mission_games_id_uindex` (`id`),
  KEY `ex82r_mission_games_game_type_index` (`game_type`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_mission_games`
--

LOCK TABLES `ex82r_mission_games` WRITE;
/*!40000 ALTER TABLE `ex82r_mission_games` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_mission_games` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_mission_rating_info`
--

DROP TABLE IF EXISTS `ex82r_mission_rating_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_mission_rating_info` (
  `mission_id` int(11) NOT NULL,
  `par_time` int(11) DEFAULT '0',
  `platinum_time` int(11) DEFAULT '0',
  `ultimate_time` int(11) DEFAULT '0',
  `awesome_time` int(11) DEFAULT '0',
  `par_score` int(11) DEFAULT '0',
  `platinum_score` int(11) DEFAULT '0',
  `ultimate_score` int(11) DEFAULT '0',
  `awesome_score` int(11) DEFAULT '0',
  `versus_par_score` int(11) DEFAULT '0',
  `versus_platinum_score` int(11) DEFAULT '0',
  `versus_ultimate_score` int(11) DEFAULT '0',
  `versus_awesome_score` int(11) DEFAULT '0',
  `completion_bonus` int(11) DEFAULT '0',
  `set_base_score` int(11) DEFAULT '0',
  `multiplier_set_base` float DEFAULT '0',
  `platinum_bonus` int(11) DEFAULT '0',
  `ultimate_bonus` int(11) DEFAULT '0',
  `awesome_bonus` int(11) DEFAULT '0',
  `standardiser` int(11) DEFAULT '0',
  `time_offset` int(11) DEFAULT '100',
  `difficulty` float DEFAULT '1',
  `platinum_difficulty` float DEFAULT '1',
  `ultimate_difficulty` float DEFAULT '1',
  `awesome_difficulty` float DEFAULT '1',
  `hunt_multiplier` int(11) DEFAULT '0',
  `hunt_divisor` int(11) DEFAULT '0',
  `hunt_completion_bonus` int(11) DEFAULT '1',
  `hunt_par_bonus` int(11) DEFAULT '0',
  `hunt_platinum_bonus` int(11) DEFAULT '0',
  `hunt_ultimate_bonus` int(11) DEFAULT '0',
  `hunt_awesome_bonus` int(11) DEFAULT '0',
  `hunt_max_score` int(11) DEFAULT '0',
  `quota_100_bonus` int(11) DEFAULT '0',
  `gem_count` int(11) DEFAULT '0',
  `gem_count_1` int(11) DEFAULT '0',
  `gem_count_2` int(11) DEFAULT '0',
  `gem_count_5` int(11) DEFAULT '0',
  `gem_count_10` int(11) DEFAULT '0',
  `has_egg` tinyint(1) DEFAULT '0',
  `egg_rating` int(11) DEFAULT '0',
  `disabled` tinyint(1) DEFAULT '0',
  `normally_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE latin1_general_ci,
  PRIMARY KEY (`mission_id`),
  KEY `ex82r_mission_rating_info_mission_id_index` (`mission_id`),
  CONSTRAINT `ex82r_mission_rating_info_ex82r_missions_official_id_fk` FOREIGN KEY (`mission_id`) REFERENCES `ex82r_missions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Has constants for each mission that are used in calculating rating points';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_mission_rating_info`
--

LOCK TABLES `ex82r_mission_rating_info` WRITE;
/*!40000 ALTER TABLE `ex82r_mission_rating_info` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_mission_rating_info` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_missions`
--

DROP TABLE IF EXISTS `ex82r_missions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_missions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `difficulty_id` int(11) NOT NULL,
  `file` text COLLATE latin1_general_ci,
  `basename` text COLLATE latin1_general_ci NOT NULL COMMENT 'Filename base',
  `name` text COLLATE latin1_general_ci NOT NULL COMMENT 'Formatted name',
  `gamemode` text COLLATE latin1_general_ci NOT NULL COMMENT 'Space-separated gamemode list',
  `sort_index` int(11) DEFAULT '1',
  `is_custom` tinyint(1) DEFAULT '0',
  `hash` varchar(64) COLLATE latin1_general_ci DEFAULT NULL COMMENT 'Unique SHA256 hash of mission',
  `modification` text COLLATE latin1_general_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_missions_hash_difficulty_id_uindex` (`hash`,`difficulty_id`),
  KEY `lw3qp_missions_official_lw3qp_mission_difficulties_id_fk` (`difficulty_id`) USING BTREE,
  KEY `ex82r_missions_official_ex82r_mission_games_id_fk` (`game_id`),
  KEY `id_custom_idx` (`is_custom`,`id`) USING BTREE,
  CONSTRAINT `ex82r_missions_official_ex82r_mission_difficulties_id_fk` FOREIGN KEY (`difficulty_id`) REFERENCES `ex82r_mission_difficulties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_missions_official_ex82r_mission_games_id_fk` FOREIGN KEY (`game_id`) REFERENCES `ex82r_mission_games` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7670 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='All missions on the leaderboards, including "custom" missions';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_missions`
--

LOCK TABLES `ex82r_missions` WRITE;
/*!40000 ALTER TABLE `ex82r_missions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_missions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_settings`
--

DROP TABLE IF EXISTS `ex82r_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` text COLLATE latin1_general_ci,
  `value` text COLLATE latin1_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_settings`
--

LOCK TABLES `ex82r_settings` WRITE;
/*!40000 ALTER TABLE `ex82r_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_achievements`
--

DROP TABLE IF EXISTS `ex82r_user_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `achievement_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_user_achievements_id_uindex` (`id`),
  UNIQUE KEY `ex82r_user_achievements_user_id_achievement_id_uindex` (`user_id`,`achievement_id`),
  KEY `ex82r_user_achievements_ex82r_achievement_names_id_fk` (`achievement_id`),
  CONSTRAINT `ex82r_user_achievements_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_user_achievements_ex82r_achievement_names_id_fk` FOREIGN KEY (`achievement_id`) REFERENCES `ex82r_achievement_names` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29327 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_achievements`
--

LOCK TABLES `ex82r_user_achievements` WRITE;
/*!40000 ALTER TABLE `ex82r_user_achievements` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_achievements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_current_marble_selection`
--

DROP TABLE IF EXISTS `ex82r_user_current_marble_selection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_current_marble_selection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `marble_id` int(11) NOT NULL DEFAULT '0' COMMENT 'id in the marble_selections table',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`user_id`),
  KEY `ex82r_user_current_marble_selection_ex82r_marbles_id_fk` (`marble_id`),
  CONSTRAINT `ex82r_user_current_marble_selection_ex82r_marbles_id_fk` FOREIGN KEY (`marble_id`) REFERENCES `ex82r_marbles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_user_marble_selection_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4362366 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_current_marble_selection`
--

LOCK TABLES `ex82r_user_current_marble_selection` WRITE;
/*!40000 ALTER TABLE `ex82r_user_current_marble_selection` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_current_marble_selection` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_eggs`
--

DROP TABLE IF EXISTS `ex82r_user_eggs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_eggs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `mission_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ex82r_mission_eggs_ex82r_missions_official_id_fk` (`mission_id`),
  KEY `ex82r_user_eggs_bv2xj_users_id_fk` (`user_id`),
  KEY `ex82r_user_eggs_mission_id_user_id_time_index` (`mission_id`,`user_id`,`time`),
  CONSTRAINT `ex82r_user_eggs_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_user_eggs_ex82r_missions_id_fk` FOREIGN KEY (`mission_id`) REFERENCES `ex82r_missions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=98858 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_eggs`
--

LOCK TABLES `ex82r_user_eggs` WRITE;
/*!40000 ALTER TABLE `ex82r_user_eggs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_eggs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_event_snowballs`
--

DROP TABLE IF EXISTS `ex82r_user_event_snowballs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_event_snowballs` (
  `score_id` int(11) NOT NULL,
  `snowballs` int(11) DEFAULT '0',
  `hits` int(11) DEFAULT '0',
  PRIMARY KEY (`score_id`),
  CONSTRAINT `ex82r_user_event_snowballs_ex82r_user_scores_id_fk` FOREIGN KEY (`score_id`) REFERENCES `ex82r_user_scores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_event_snowballs`
--

LOCK TABLES `ex82r_user_event_snowballs` WRITE;
/*!40000 ALTER TABLE `ex82r_user_event_snowballs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_event_snowballs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_event_triggers`
--

DROP TABLE IF EXISTS `ex82r_user_event_triggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_event_triggers` (
  `user_id` int(11) NOT NULL,
  `trigger` int(11) NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`trigger`),
  UNIQUE KEY `ex82r_user_event_triggers_user_id_trigger_uindex` (`user_id`,`trigger`),
  CONSTRAINT `ex82r_user_event_triggers_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_event_triggers`
--

LOCK TABLES `ex82r_user_event_triggers` WRITE;
/*!40000 ALTER TABLE `ex82r_user_event_triggers` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_event_triggers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_lap_times`
--

DROP TABLE IF EXISTS `ex82r_user_lap_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_lap_times` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mission_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT '5999999',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ex82r_mission_lap_times_id_uindex` (`id`),
  KEY `ex82r_mission_lap_times_bv2xj_users_id_fk` (`user_id`),
  KEY `ex82r_mission_lap_times_ex82r_missions_official_id_fk` (`mission_id`),
  CONSTRAINT `ex82r_mission_lap_times_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_mission_lap_times_ex82r_missions_official_id_fk` FOREIGN KEY (`mission_id`) REFERENCES `ex82r_missions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18671 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_lap_times`
--

LOCK TABLES `ex82r_user_lap_times` WRITE;
/*!40000 ALTER TABLE `ex82r_user_lap_times` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_lap_times` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_mission_ratings`
--

DROP TABLE IF EXISTS `ex82r_user_mission_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_mission_ratings` (
  `mission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT '0',
  PRIMARY KEY (`user_id`,`mission_id`),
  UNIQUE KEY `ex82r_mission_user_ratings_user_id_uindex` (`user_id`),
  KEY `ex82r_mission_user_ratings_ex82r_missions_id_fk` (`mission_id`),
  CONSTRAINT `ex82r_mission_user_ratings_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_mission_user_ratings_ex82r_missions_id_fk` FOREIGN KEY (`mission_id`) REFERENCES `ex82r_missions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_mission_ratings`
--

LOCK TABLES `ex82r_user_mission_ratings` WRITE;
/*!40000 ALTER TABLE `ex82r_user_mission_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_mission_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_ratings`
--

DROP TABLE IF EXISTS `ex82r_user_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_ratings` (
  `user_id` int(11) NOT NULL,
  `rating_general` int(11) DEFAULT '0',
  `rating_mbg` int(11) DEFAULT '0',
  `rating_mbp` int(11) DEFAULT '0',
  `rating_mbu` int(11) DEFAULT '0',
  `rating_pq` int(11) DEFAULT '0',
  `rating_custom` int(11) DEFAULT '0',
  `rating_egg` int(11) DEFAULT '0',
  `rating_quota_bonus` int(11) DEFAULT '0',
  `rating_achievement` int(11) DEFAULT '0',
  `rating_mp` int(11) DEFAULT '0',
  UNIQUE KEY `ex82r_user_ratings_user_id_uindex` (`user_id`),
  CONSTRAINT `ex82r_user_ratings_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_ratings`
--

LOCK TABLES `ex82r_user_ratings` WRITE;
/*!40000 ALTER TABLE `ex82r_user_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_scores`
--

DROP TABLE IF EXISTS `ex82r_user_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `mission_id` int(11) NOT NULL DEFAULT '0',
  `score` int(11) DEFAULT NULL,
  `score_type` enum('time','score') CHARACTER SET latin1 NOT NULL DEFAULT 'time' COMMENT 'Time for normal, score for hunt',
  `total_bonus` int(11) DEFAULT NULL COMMENT 'Total bonus time',
  `rating` int(11) DEFAULT '0',
  `gem_count` int(11) DEFAULT NULL COMMENT 'Total gems, not points',
  `gems_1_point` int(11) DEFAULT '0' COMMENT 'Red',
  `gems_2_point` int(11) DEFAULT '0' COMMENT 'Yellow',
  `gems_5_point` int(11) DEFAULT '0' COMMENT 'Blue',
  `gems_10_point` int(11) DEFAULT '0' COMMENT 'Platinum',
  `modifiers` int(11) DEFAULT '0' COMMENT 'Bitfield of flags eg no jumping',
  `origin` enum('PhilsEmpire','MarbleBlast.com','MarbleBlastPlatinum','PlatinumQuest','Ratings Viewer','External') COLLATE latin1_general_ci DEFAULT 'PlatinumQuest',
  `extra_modes` text COLLATE latin1_general_ci,
  `sort` int(11) DEFAULT '0' COMMENT 'Sort order, better is smaller',
  `disabled` tinyint(1) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ex82r_mission_scores_sort_index` (`sort`),
  KEY `ex82r_mission_scores_user_id_mission_id_index` (`user_id`,`mission_id`),
  KEY `ex82r_mission_scores_user_id_sort_index` (`user_id`,`sort`),
  KEY `ex82r_user_scores_sort_mission_id_user_id_index` (`sort`,`mission_id`,`user_id`),
  KEY `ex82r_user_scores_id_mission_id_user_id_index` (`id`,`mission_id`,`user_id`),
  KEY `ex82r_mission_scores_mission_id_sort_index` (`mission_id`,`disabled`,`sort`,`user_id`,`id`) USING BTREE,
  CONSTRAINT `ex82r_mission_scores_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ex82r_mission_scores_ex82r_missions_official_id_fk` FOREIGN KEY (`mission_id`) REFERENCES `ex82r_missions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2526063 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_scores`
--

LOCK TABLES `ex82r_user_scores` WRITE;
/*!40000 ALTER TABLE `ex82r_user_scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_user_streaks`
--

DROP TABLE IF EXISTS `ex82r_user_streaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_user_streaks` (
  `user_id` int(11) NOT NULL,
  `mp_games` int(11) DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `ex82r_user_streaks_user_id_uindex` (`user_id`),
  CONSTRAINT `ex82r_user_streaks_bv2xj_users_id_fk` FOREIGN KEY (`user_id`) REFERENCES `prod_joomla`.`bv2xj_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci COMMENT='Win streaks, updated on level finish';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_user_streaks`
--

LOCK TABLES `ex82r_user_streaks` WRITE;
/*!40000 ALTER TABLE `ex82r_user_streaks` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_user_streaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ex82r_versions`
--

DROP TABLE IF EXISTS `ex82r_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ex82r_versions` (
  `version` int(11) DEFAULT NULL,
  `title` varchar(128) COLLATE latin1_general_ci DEFAULT NULL,
  `desc` varchar(21845) COLLATE latin1_general_ci DEFAULT NULL,
  `url` varchar(256) COLLATE latin1_general_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id` int(11) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ex82r_versions`
--

LOCK TABLES `ex82r_versions` WRITE;
/*!40000 ALTER TABLE `ex82r_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ex82r_versions` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-02-01  7:00:37
