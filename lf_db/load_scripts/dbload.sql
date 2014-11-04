-- mysql --user=XXXX --password=YYYY DBNAME < dbload.sql
-- When running apparmor for mysql, you need to give mysql permission to read local files  (/etc/apparmor.d/usr.sbin.mysqld)
-- LOAD match data based on CSV Exporteer teamwedstrijden

--
-- Table structure for tmp-tables
--
DROP TABLE IF EXISTS `lf_tmpdbload_15mei`,`lf_tmpdbload_basisopstellingliga`,`lf_tmpdbload_playerscsv`,`lf_tmpdbload_teamscsv`;

CREATE TABLE IF NOT EXISTS `lf_tmpdbload_15mei` (
  `playerId` int(11) NOT NULL,
  `playerLevelSingle` varchar(2) NOT NULL,
  `playerLevelDouble` varchar(2) NOT NULL,
  `playerLevelMixed` varchar(2) NOT NULL
)  ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `lf_tmpdbload_basisopstellingliga` (
  `clubName` varchar(45) NOT NULL,
  `teamName` varchar(45) NOT NULL,
  `playerId` int(11) NOT NULL
)  ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `role` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `lf_tmpdbload_teamscsv` (
  `name` varchar(45) NOT NULL,
  `clubCode` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  `eventName` varchar(80) NOT NULL,
  `drawName` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



DELETE from lf_match;
DELETE from lf_ranking;
DELETE from lf_player_has_team;
DELETE from lf_player;
DELETE from lf_team;
DELETE from lf_club;
DELETE from lf_group;
DELETE from lf_tmpdbload_teamscsv;
DELETE from lf_tmpdbload_playerscsv;
DELETE FROM lf_tmpdbload_15mei;
DELETE FROM lf_tmpdbload_basisopstellingliga;

DROP FUNCTION IF EXISTS lf_dbload_eventcode;
DELIMITER $$
CREATE FUNCTION lf_dbload_eventcode(eventname TEXT)
  RETURNS TEXT
BEGIN
  CASE eventname
	when 'Gemengde Competitie' then return 'MX';
	when 'Heren Competitie' then return 'M';
	when 'Dames Competitie' then return 'L';
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

-- ----------------------------------------
--  LOAD DATA FROM TOERNOOI.NL
-- ----------------------------------------

LOAD DATA INFILE '/home/thomas/projects/lion-freaky/lf_db/data/tmp/clubs.csv'
INTO TABLE lf_club
FIELDS TERMINATED BY ';'
IGNORE 1 LINES
(@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18)
set clubId=@col1,
clubName=@col2;


LOAD DATA INFILE '/home/thomas/projects/lion-freaky/lf_db/data/tmp/teams.csv'
INTO TABLE lf_tmpdbload_teamscsv
FIELDS TERMINATED BY ';'
IGNORE 1 LINES
(@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18,@col19,@col20,@col21,@col22,@col23,@col24,@col25,@col26,@col27)
set name=@col5,
clubCode=@col22,
`year`=2014,
eventName=@col24,
drawName=@col26;

INSERT INTO lf_group (tournament,event,devision,series)
select `year`,lf_dbload_eventcode(eventName),lf_dbload_devision(drawName),lf_dbload_serie(drawName) from lf_tmpdbload_teamscsv
group by `year`,lf_dbload_eventcode(eventName),lf_dbload_devision(drawName),lf_dbload_serie(drawName);

INSERT INTO lf_team (teamName,sequenceNumber,club_clubId, group_groupId)
select name,lf_dbload_teamSequenceNumber(name),clubCode,(select groupId from lf_group lfg where lfg.tournament = t.`year` and lf_dbload_eventcode(t.eventName) = lfg.event and  lf_dbload_devision(t.drawName) = lfg.devision and lf_dbload_serie(t.drawName) = lfg.series)  from lf_tmpdbload_teamscsv t;

LOAD DATA INFILE '/home/thomas/projects/lion-freaky/lf_db/data/tmp/matches.csv'
INTO TABLE lf_match
FIELDS TERMINATED BY ';'
IGNORE 1 LINES
(@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18,@col19,@col20,@col21,@col22,@col23,@col24,@col25,@col26,@col27,@col28,@col29,@col30,@col31,@col32,@col33,@col34,@col35,@col36,@col37,@col38)
set homeTeamName=@col10,
outTeamName=@col12,
locationId=@col16,
locationName=@col17,
matchId=@col2,
`date`= str_to_date(@col14, '%e-%c-%Y %H:%i:%S');

LOAD DATA INFILE '/home/thomas/projects/lion-freaky/lf_db/data/tmp/players.csv'
INTO TABLE lf_tmpdbload_playerscsv
FIELDS TERMINATED BY ';' ENCLOSED BY '"'
IGNORE 1 LINES
(@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18,@col19,@col20,@col21,@col22,@col23,@col24,@col25,@col26,@col27,@col28,@col29,@col30,@col31,@col32)
set memberId=@col4,
firstName=@col8,
lastName=@col5,
gender=@col16,
groupName=@col2,
playerLevelSingle=@col29,
playerLevelDouble=@col30,
playerLevelMixed=@col31,
typeName=@col32,
`role`=@col28;

-- When player is from O-Vl Club and rented to another O-Vl it will appear twice. However, we only wan to keep the record with role='Uitgeleende speler
-- Some tricks needed to avoid mysql limitation: In MySQL, you can't modify the same table which you use in the SELECT part
-- http://stackoverflow.com/questions/45494/mysql-error-1093-cant-specify-target-table-for-update-in-from-clause
delete from lf_tmpdbload_playerscsv
where role = 'Speler'
and memberId in (
select c.memberId from (
select memberId from lf_tmpdbload_playerscsv t
group by memberId
having count(*) >1) as c
);

-- When must import players from type=Recreant too because they can be part of a baseTeam!
INSERT INTO lf_player (playerId,firstName,lastName,gender,club_clubId,type)
select t.memberId,t.firstName,t.lastName, CASE when t.gender='V' then 'F' else t.gender END,c.clubId, case when t.typeName like 'Recreant%' then 'R' when t.typeName like 'Competitie%' then 'C' when t.typeName like 'Jeugd%' then 'J' END from lf_tmpdbload_playerscsv t
join lf_club c on c.clubName=t.groupName;

INSERT INTO lf_ranking (`date`,singles,doubles,mixed,player_playerId)
select SYSDATE(),t.playerLevelSingle,t.playerLevelDouble,t.playerLevelMixed,t.memberId from lf_tmpdbload_playerscsv t
join lf_club c on c.clubName=t.groupName;

	
-- ----------------------------------------
--  LOAD FIXED DATA
-- ----------------------------------------
LOAD DATA INFILE '/home/thomas/projects/lion-freaky/lf_db/data/fixed/basisopstellingen.csv'
INTO TABLE lf_player_has_team
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\r\n'
IGNORE 1 LINES
(@col1,@col2)
set player_playerId=@col1,
team_teamName=@col2;

LOAD DATA INFILE '/home/thomas/projects/lion-freaky/lf_db/data/fixed/indexen_spelers_01052014.csv'
INTO TABLE lf_tmpdbload_15mei
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\r\n'
IGNORE 1 LINES
(@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16,@col17,@col18,@col19)
set playerId=@col3,
playerLevelSingle=@col8,
playerLevelDouble=@col9,
playerLevelMixed=@col10;

insert into lf_ranking(date,singles,doubles,mixed,player_playerId)
select '2014-05-01',t.playerLevelSingle,t.playerLevelDouble,t.playerLevelMixed,t.playerId from lf_tmpdbload_15mei t
join lf_player p on t.playerId = p.playerId;

-- ADDING LIGA TEAMS
LOAD DATA INFILE '/home/thomas/projects/lion-freaky/lf_db/data/fixed/basisopstelling_gemengd_20142015.csv'
INTO TABLE lf_tmpdbload_basisopstellingliga
FIELDS TERMINATED BY ',' ENCLOSED BY '"'
LINES TERMINATED BY '\r\n'
IGNORE 1 LINES
(@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12)
set playerId=@col4,
teamName=@col3,
clubName=@col1;

-- Add fake group for Liga teams
INSERT INTO lf_group (tournament,event) values ('2014','LI');

INSERT INTO lf_team (teamName,sequenceNumber,club_clubId, group_groupId)
select t.teamName,lf_dbload_teamSequenceNumber(t.teamName),c.clubId,(select groupId from lf_group where event='LI') from lf_tmpdbload_basisopstellingliga t
join lf_club c on c.clubName = t.clubName
group by t.teamName,c.clubId;

INSERT INTO lf_player_has_team(player_playerId,team_teamName)
select t.playerId, t.teamName from lf_tmpdbload_basisopstellingliga t
join lf_team lft on lft.teamName = t.teamName;



