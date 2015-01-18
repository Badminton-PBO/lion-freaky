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
  `clubCode` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`clubId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `lf_group`
--

CREATE TABLE IF NOT EXISTS `lf_group` (
  `groupId` int(11) NOT NULL AUTO_INCREMENT,
  `tournament` year(4) DEFAULT NULL,
  `type` varchar(4) DEFAULT NULL,  
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
  `captainName` VARCHAR( 90 ) NOT NULL,
  PRIMARY KEY (`teamName`,`group_groupId`),
  KEY `fk_team_club1_idx` (`club_clubId`),
  KEY `fk_team_group1_idx` (`group_groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table structure for table `lf_event`
--

CREATE TABLE IF NOT EXISTS `lf_event` (
  `eventId` int(11) NOT NULL AUTO_INCREMENT,
  `eventType` varchar(10) DEFAULT NULL,
  `when` datetime DEFAULT NULL,
  `who` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`eventId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

-- TMP Table to faciliate load from CSV into the DB
CREATE TABLE IF NOT EXISTS `lf_tmpdbload_15mei` (
  `playerId` int(11) NOT NULL,
  `playerLevelSingle` varchar(2) NOT NULL,
  `playerLevelDouble` varchar(2) NOT NULL,
  `playerLevelMixed` varchar(2) NOT NULL
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lf_tmpdbload_basisopstellingliga` (
  `clubName` varchar(45) NOT NULL,
  `teamName` varchar(45) NOT NULL,
  `discipline` varchar(45) NOT NULL,
  `playerId` int(11) NOT NULL
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `lf_tmpdbload_playerscsv` (
  `memberId` int(11) NOT NULL,
  `firstName` varchar(45) NOT NULL,
  `lastName` varchar(45) NOT NULL,
  `gender` varchar(1) NOT NULL,
  `groupName` varchar(45) NOT NULL,
  `playerLevelSingle` varchar(2) NOT NULL,
  `playerLevelDouble` varchar(2) NOT NULL,
  `playerLevelMixed` varchar(2) NOT NULL,
  `typeName` varchar(45) NOT NULL,
  `role` varchar(45) NOT NULL,
  `groupCode` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `lf_tmpdbload_teamscsv` (
  `name` varchar(45) NOT NULL,
  `clubCode` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  `eventName` varchar(80) NOT NULL,
  `drawName` varchar(80) NOT NULL,
  `captainName` VARCHAR( 90 ) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `lf_tmpdbload_playersremoved` (
  `playerId` int(11) NOT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `club_clubId` int(11) NOT NULL,
  KEY `fk_player_club1_idx` (`club_clubId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- Functions to faciliate load from CSV into the DB

DROP FUNCTION IF EXISTS lf_dbload_eventcode;
DELIMITER $$
CREATE FUNCTION lf_dbload_eventcode(eventname TEXT)
  RETURNS TEXT
BEGIN
  CASE eventname
	when 'Gemengde Competitie' then return 'MX';
	when 'Heren Competitie' then return 'M';
	when 'Dames Competitie' then return 'L';
	when 'Gemengd' then return 'MX';
	when 'Heren' then return 'M';
	when 'Dames' then return 'L';
	else
		BEGIN
			return '??';
		END;
   END CASE;
END;
$$
DELIMITER ;


DROP FUNCTION IF EXISTS lf_dbload_devision;
DELIMITER $$
CREATE FUNCTION lf_dbload_devision(drawname TEXT)
  RETURNS TEXT
BEGIN
	return substring(drawname,1,1);
END;
$$
DELIMITER ;


DROP FUNCTION IF EXISTS lf_dbload_serie;
DELIMITER $$
CREATE FUNCTION lf_dbload_serie(drawname TEXT)
  RETURNS TEXT
BEGIN
	CASE
	when instr(drawname,'provinciale ') > 0 then return substring(drawname, instr(drawname,'provinciale ')+length('provinciale '));
	else 
		begin
			return '';
		end;
	END CASE;
END;
$$
DELIMITER ;

DROP FUNCTION IF EXISTS lf_dbload_teamSequenceNumber;
DELIMITER $$
CREATE FUNCTION lf_dbload_teamSequenceNumber(teamName TEXT)
  RETURNS TEXT
BEGIN
	return reverse(trim(substring(REVERSE(teamName),2,2)));
END;
$$
DELIMITER ;

DROP FUNCTION IF EXISTS lf_dbload_teamType;
DELIMITER $$
CREATE FUNCTION lf_dbload_teamType(teamName TEXT)
  RETURNS TEXT
BEGIN
	return reverse(trim(substring(REVERSE(teamName),1,1)));
END;
$$
DELIMITER ;


DROP FUNCTION IF EXISTS lf_dbload_genderCount;
DELIMITER $$
CREATE FUNCTION lf_dbload_genderCount(teamName TEXT,gender TEXT)
  RETURNS int(2)
  NOT DETERMINISTIC
BEGIN
	DECLARE select_var int(2);
	SET select_var = (
select  count(*) from lf_team t
join lf_player_has_team pht on pht.team_teamName = t.teamName
join lf_player p on p.playerId = pht.player_playerId
where t.teamName=teamName and p.gender=gender);
	return select_var;
END;
$$
DELIMITER ;


-- Add index for lf_event
ALTER TABLE `lf_event` ADD INDEX ( `eventType` ) ;

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
