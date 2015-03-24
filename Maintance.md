## Hoe aanloggen op de productie omgeving? ##
Momenteel (2015/01/31), wordt applicatie gehost bij one.com waar reeds andere spullen van PBO staan.
  * SSH : "badminton-pbo.be@ssh.badminton-pbo.be" 22
  * Remote folder : badminton-pbo.be/httpd.www/competitie

## Hoe deployen? ##
Alles vanuit Source>lf\_front simpelweg overkopiëren. Momenteel is er geen build-phase op basis van de sourcecode.
Enkel de api/config.php moet aangepast worden aan de omgeving (bv MySQL) & actuele toernooi.nl parameters

## Wat moet er bij elke competitieseizoen aangepast/gecontroleerd worden? ##
### api/config.php ###
  * Er moet een toernooi.nl-user zijn met zowel toegang op "Beheer: Oost-Vlaanderen" als relevante "PBO Competitie 20xx".  Dit omdat sommige data moet komen van "Oost-vlaanderen"  (bv spelers-data) en sommige data van "PBO Competitie" (bv. ontmoetingen)
```
/** User credentials of toernooi.nl user with enough credentials to download CSV from PBO competition */
define('PROV_USERNAME','pbo');
define('PROV_PWD','YYY');
```
  * Correcte verwijzingen naar PB id (=PBO Competitie 2015) en PROV id (= O.Vlaanderen) op toernooi.nl
Deze id's zijn eenvoudig te achteren via de URL's bij gebruik van toernooi.nl
Ex: https://www.toernooi.nl/organization/group.aspx?id=638D0B55-C39B-43AB-8A9D-D50D62017FBE&gid=3825E3C5-1371-4FF6-94AF-C4A3B152802A#
Ex https://www.toernooi.nl/sport/tournament.aspx?id=EF6D253B-4410-4B4F-883D-48A61DDA350D
```
/** toernooi.nl related data */
define('PB_COMPETITIE_ID','EF6D253B-4410-4B4F-883D-48A61DDA350D');
define('PB_COMPETITIE_START_DAY','20140801');
define('PB_COMPETITIE_END_DAY','20150731');
define('PROV_ID','638D0B55-C39B-43AB-8A9D-D50D62017FBE');
define('PROV_GID','3825E3C5-1371-4FF6-94AF-C4A3B152802A');		
```
### update fixed data ###
Om de validatieregels van C320 te kunnen uitvoeren hebben we niet voldoende aan de data zoals beschikbaar op toernooil.nl. Deze ontbrekende data moet dus manueel onderhouden worden. Gelukkig wijzigd deze data niet frequent.
De zogenaamde fixed-data staat onder folder "data/fixed" en is gelukkig niet privacy gevoelig waardoor ze ook opgenomen is in de Source code.

Omdat deze data vanuit verschillende bronnen komt zijn headers & field seperators verschillend. Ze zijn echter cruciaal voor correct werking van de DB-load dus opletten geblazen.

  * data/fixed/basisopstellingen.csv
Basisopstelling van provenciale ploegen, dus NIET de liga ploegen
Let op komma field-seperato!
```
player_playerId,team_teamName
50000420,"Landegem 2D"
...
```

  * data/fixed/indexen\_spelers\_01052014.csv
Zogenaamde vaste indexen van spelers aan begin van competitie-seizoen
```
Lidnummer;Klassement enkel;Klassement dubbel;Klassement gemengd
50995006;D;D;D
...
```
  * data/fixed/liga\_basisopstelling\_gemengd\_20142015.csv
Basisopstelling van gemengde LIGA ploegen
```
Club,Discipline,Teamnaam,Lidnummer,Voornaam,Achternaam,Geboortedatum,geslacht,Player level single,Player level double,Player level mixed,Positie
BC DE DIJLEVALLEI VZW,Gemengd,Dijlevallei 2G,50044584,Geert,Provoost,4/17/1980,M,B1,A,B1,40
...
```

## Hoe kan ik DB load manueel triggeren om zo eventuele fouten tijdens load te achterhalen? ##
Via standaard browser volgende URL aanroepen: http://competitie.badminton-pbo.be/api/dbload/true/true