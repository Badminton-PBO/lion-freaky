## Daily load ##
Some data can be considered fixed for every competition season, other is rather volatile:
Ex: fixed players ranking: raking from 15mei, used to form base teams
Ex: variable player ranking that is updated 4 times a year
Ex: player role {competitie, recreant, jeugd} can change every day if a club decides to active a player in the competition
Ex: liga base team data is currently only available as of 1 dec
So there is definitely a requirement to automate this process, example once a day.

## Implementation restrictions ##
  * our hosting, one.com, doesn't support cron alike scheduling -> external scheduler needed
  * our hosting, one.com, doesn't support the LOAD DATA INFILE '/path/to/my/text.csv' INTO TABLE in the mysql-cmdline
  * our hosting, one.com, has php runtime restrictions of 128MB
  * some (luckily non volatile) data cannot be fetched from toernooi.nl using PBO credentials: prov bease teams, liga base teams, rankings on 15mei


## Implementation ##
  * php script to downloads necessary CSV from toernooi.nl using php cURL client http://php.net/manual/en/book.curl.php
  * php script is triggered externally using a free webcron https://mywebcron-com
  * php script reloads the complete DB, so clean + full reload
  * some tmp-tables are used because the CSV's don't always match with the defined DB model
  * cURL commands are doing a form alike logon to toernooi.nl, not using some API calls. So if toernooi.nl is change there site, the script functionality can be broken.

source:  lf\_front/api/index.php

## Usage ##
  * http://competitie.badminton-pbo.be/api/dbload : normal full load, no testclub
  * http://competitie.badminton-pbo.be/api/dbload/true/true : full load with testclub