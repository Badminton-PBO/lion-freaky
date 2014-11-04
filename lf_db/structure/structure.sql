-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 18, 2014 at 11:40 PM
-- Server version: 5.5.38-0ubuntu0.14.04.1-log
-- PHP Version: 5.5.9-1ubuntu4.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `lionfreaky`
--

-- --------------------------------------------------------

--
-- Table structure for table `lf_club`
--

CREATE TABLE IF NOT EXISTS `lf_club` (
  `clubId` int(11) NOT NULL,
  `clubName` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`clubId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lf_group`
--

CREATE TABLE IF NOT EXISTS `lf_group` (
  `groupId` int(11) NOT NULL AUTO_INCREMENT,
  `tournament` year(4) DEFAULT NULL,
  `event` varchar(2) DEFAULT NULL,
  `devision` int(11) DEFAULT NULL,
  `series` varchar(1) DEFAULT NULL,
  PRIMARY KEY (`groupId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=27 ;

-- --------------------------------------------------------

--
-- Table structure for table `lf_match`
--

CREATE TABLE IF NOT EXISTS `lf_match` (
  `homeTeamName` varchar(45) NOT NULL,
  `outTeamName` varchar(45) NOT NULL,
  `date` datetime NOT NULL,
  `locationName` varchar(100) NOT NULL,
  `locationId` int(11) NOT NULL,
  `matchId` int(11) NOT NULL COMMENT 'toernooinl matchid',
  UNIQUE KEY `matchId` (`matchId`),
  KEY `homeTeamName` (`homeTeamName`),
  KEY `outTeamName` (`outTeamName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lf_player`
--

CREATE TABLE IF NOT EXISTS `lf_player` (
  `playerId` int(11) NOT NULL,
  `firstName` varchar(45) DEFAULT NULL,
  `lastName` varchar(45) DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `club_clubId` int(11) NOT NULL,
  `type` varchar(1) NOT NULL,
  PRIMARY KEY (`playerId`),
  KEY `fk_player_club1_idx` (`club_clubId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lf_player_has_team`
--

CREATE TABLE IF NOT EXISTS `lf_player_has_team` (
  `player_playerId` int(11) NOT NULL,
  `team_teamName` varchar(45) NOT NULL,
  PRIMARY KEY (`player_playerId`,`team_teamName`),
  KEY `fk_player_has_team_team1_idx` (`team_teamName`),
  KEY `fk_player_has_team_player1_idx` (`player_playerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lf_ranking`
--

CREATE TABLE IF NOT EXISTS `lf_ranking` (
  `date` date NOT NULL,
  `singles` varchar(2) DEFAULT NULL,
  `doubles` varchar(2) DEFAULT NULL,
  `mixed` varchar(2) DEFAULT NULL,
  `player_playerId` int(11) NOT NULL,
  PRIMARY KEY (`date`,`player_playerId`),
  KEY `fk_ranking_player1_idx` (`player_playerId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lf_team`
--

CREATE TABLE IF NOT EXISTS `lf_team` (
  `teamName` varchar(45) NOT NULL,
  `sequenceNumber` int(11) DEFAULT NULL,
  `club_clubId` int(11) NOT NULL,
  `group_groupId` int(11) NOT NULL,
  PRIMARY KEY (`teamName`,`group_groupId`),
  KEY `fk_team_club1_idx` (`club_clubId`),
  KEY `fk_team_group1_idx` (`group_groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lf_match`
--
ALTER TABLE `lf_match`
  ADD CONSTRAINT `fk_match_teamname1` FOREIGN KEY (`homeTeamName`) REFERENCES `lf_team` (`teamName`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_match_teamname2` FOREIGN KEY (`outTeamName`) REFERENCES `lf_team` (`teamName`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `lf_player`
--
ALTER TABLE `lf_player`
  ADD CONSTRAINT `fk_player_club1` FOREIGN KEY (`club_clubId`) REFERENCES `lf_club` (`clubId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `lf_player_has_team`
--
ALTER TABLE `lf_player_has_team`
  ADD CONSTRAINT `fk_player_has_team_player1` FOREIGN KEY (`player_playerId`) REFERENCES `lf_player` (`playerId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_player_has_team_team1` FOREIGN KEY (`team_teamName`) REFERENCES `lf_team` (`teamName`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `lf_ranking`
--
ALTER TABLE `lf_ranking`
  ADD CONSTRAINT `fk_ranking_player1` FOREIGN KEY (`player_playerId`) REFERENCES `lf_player` (`playerId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `lf_team`
--
ALTER TABLE `lf_team`
  ADD CONSTRAINT `fk_team_club1` FOREIGN KEY (`club_clubId`) REFERENCES `lf_club` (`clubId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_team_group1` FOREIGN KEY (`group_groupId`) REFERENCES `lf_group` (`groupId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
