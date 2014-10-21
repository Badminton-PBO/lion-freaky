-- mysql --user=XXXX --password=YYYY DBNAME < dbload.sql
-- LOAD match data based on CSV Exporteer teamwedstrijden
LOAD DATA INFILE '/home/thomas/proef/pbo/exportteammatches113198.csv'
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
