# Stappenplan bij seizoens switch
bv. van 2018-2019 naar 2019-2020

Omdat de basisopstellingen in juli/augustus nog niet beschikbaar zijn moet er in fases gewerkt worden.

## 1. Verplaatsingsapp activeren, opstellingsapp de-activeren
Wanneer? : begin juli

Benodigheden:
* nieuwe competitie id op toernooi.nl
* initiele ontmoetings data moeten beschikbaar zijn

Competitie id terug vinden op toernooi.nl? staat in URL

* bv. https://www.toernooi.nl/sport/tournament.aspx?id=CDACB157-6AA4-4B4C-B4F8-A207D9D43190
* dan competitie id = CDACB157-6AA4-4B4C-B4F8-A207D9D43190

Werkwijze
* link naar opstellings app in commentaar plaatsen
```
laravel-5.7.0/resources/views/welcome.blade.php
```
* In `laravel-5.7.0/app/Http/Controllers/DBLoadController.php`, volgende zaken wijzigen
Loading van enkele controllers disablen
```
    DBLoadController::loadCSV($clubCSV,'clubs');
    DBLoadController::loadCSV($teamsCSV,'teams');
    DBLoadController::loadCSV($matchesCSV,'matches');
    DBLoadController::loadCSV($playersCSV,'players');
    DB::statement("set names utf8");//set to windows encoding
    //TDE 2018/06/21 temporaly disable opstelling-app because no data yet
    //DBLoadController::loadCSV($baseTeamCSV,'baseTeam');
    //DBLoadController::loadCSV($fixedRankingCSV,'fixedRanking');
    //DBLoadController::loadCSV($ligaBaseTeamCSV,'ligaBaseTeam');
    DBLoadController::loadCSV($locationsCSV,'locations');
```

* In `lf_db/load_scripts/download.sh`, volgende zaken aanpassen
FYI: dit script wordt enkel gebruikt voor lokale ontwikkeling, niet op productie. Maar toch beter in sync houden. 
```
PBO_COMPETITIE_ID='CDACB157-6AA4-4B4C-B4F8-A207D9D43190'
PBO_COMPETITIE_START_DAY='20190801'
PBO_COMPETITIE_END_DAY='20200731'
``` 

* In `laravel-5.7.0/.env`, volgende zaken aanpassen
``` 
PB_COMPETITIE_ID=CDACB157-6AA4-4B4C-B4F8-A207D9D43190
PB_COMPETITIE_START_DAY=20190801
PB_COMPETITIE_END_DAY=20200731
``` 
Opgelet: deze configuratie file zit bewust NIET in git omwille van pwd (en public git repo)

* Committen, pushen.
* SSH naar PBO machine bij one.com
    *  xxx.sh script uitvoeren
    * "laravel-5.7.0/.env" via vim aanpassen
    
## 2. Google competitie agenda
Wanneer? : begin juli

Benodigheden:
* nieuwe competitie id op toernooi.nl
* initiele ontmoetings data moeten beschikbaar zijn

Werkwijze
* Java sync programma op Rasberry PI configureren
* enkele dagen laten lopen om volledig sync te hebben (omwille van google API limits op aantal items/tijdseenheid )
* log-output bevat alle calendars ids (lijken op email addressen) die je dan moeten plaatsen in
```
lf_front/data/fixed/2019-2020/googleCalendarData.txt 
```
* Doordat er ploegen bijkomen/verdwijnen en calender ids cross-seizoen moeten werken bevat deze output mogelijks calendars ids voor ploegen die dit seizoen niet meer bestaan
Maw. sorteren & manueel overlopen en ploeglijnen verwijderen die niet nodig zijn.

* In laravel-5.7.0/resources/views/agenda.blade.php, volgende zaken aanpassen
``` 
    $(document).ready(function(){
        $.ajax({
            type: "GET",
            url: "data/fixed/2019-2020/googleCalendarData.txt",
            dataType: "text",
            success: function(data) {processData(data);}
        });
    });
``` 

* Committen, pushen
* SSH naar PBO machine bij one.com
    *  xxx.sh script uitvoeren
    
    
## 3. Opstellings app
Wanneer? eind augustus

Benodigdheden
* vaste indexen (= index van spelers eind mei vastgelegd die gebruikt worden voor papieren ploegen)
* basis opstelling van alle ploegen in PBO competitie
* basis opstelling van alle ploegen in liga waarvoor ander team van zelfde club aan PBO deelneemt   
    
Werkwijze
* vaste indexen : `lf_front/data/fixed/2018-2019/indexen_spelers.csv`
Bron: https://www.badmintonvlaanderen.be > Competities > Documenten >  Indexen spelers (seizoen 2019-2020)

Via spreadsheet programma, exact zelfde formaat met header krijgen als vorige seizoenen 
* basisopstelling: `lf_front/data/fixed/2018-2019/basisopstellingen.csv`    
* basisopstelling liga: `lf_front/data/fixed/2018-2019/liga_nationale_basisopstelling.csv`
* In `laravel-5.7.0/app/Http/Controllers/DBLoadController.php`, volgende zaken wijzigen
Loading van enkele controllers enablen
```
    DBLoadController::loadCSV($clubCSV,'clubs');
    DBLoadController::loadCSV($teamsCSV,'teams');
    DBLoadController::loadCSV($matchesCSV,'matches');
    DBLoadController::loadCSV($playersCSV,'players');
    DB::statement("set names utf8");//set to windows encoding
    DBLoadController::loadCSV($baseTeamCSV,'baseTeam'); // <--
    DBLoadController::loadCSV($fixedRankingCSV,'fixedRanking'); // <--
    DBLoadController::loadCSV($ligaBaseTeamCSV,'ligaBaseTeam'); // <--
    DBLoadController::loadCSV($locationsCSV,'locations');
```
* Lokaal testen want meestal zitten er enkele kleine foutjes (in vooral basisopstelling) waardoor dbload niet lukt (constraint violations enzo)
```
http://localhost:8080/dbload/true/true
```
* Committen, pushen
* SSH naar PBO machine bij one.com
    *  xxx.sh script uitvoeren    