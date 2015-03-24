## Verwachte calls naar/van backend (2014/10/09) ##
  * http-get method die alle clubs & ploegen geeft, geen input args
URL
```
 /api/clubsAndTeams
```
Result
```
[
    {
        "clubName": "4GHENT BC",
        "teams": [
            {
                "teamName": "4Ghent 1G",
                "type": "PROV",
                "event": "MX",
                "devision": "2",
                "series": "B",
                "baseTeam": [
                    "50060756",
                    "50085592",
                    "50102716",
                    "50107650"
                ]
            },
...
            {
                "teamName": "4Ghent 3G",
                "type": "PROV",
                "event": "MX",
                "devision": "3",
                "series": "C",
                "baseTeam": [
                    "50066901",
                    "50074216",
                    "50032467",
                    "50064767"
                ]
            }
        ]
    },
...
   {
        "clubName": "WIT-WIT BC",
        "teams": [
            {
                "teamName": "Wit-Wit 1G",
                "type": "LIGA",
                "event": "MX",
                "devision": "0",
                "series": null,
                "baseTeam": [
                    "50058095",
                    "50083838",
                    "50055223",
                    "50055606"
                ]
            },
            ...
        ]
    }
]
```

Query
```
select c.clubName,t.teamName,g.event,g.`type`,g.devision,g.series,p.playerId from lf_club c 
join lf_team t on c.clubId = t.club_clubId 
join lf_group g on g.groupId = t.group_groupId 
join lf_player_has_team pt on pt.team_teamName = t.teamName 
join lf_player p on p.playerId = pt.player_playerId 
order by c.clubName,t.teamName
```

  * http-get method die gegeven een teamName alle spelers geeft van deze club + alle nog af te werken ontmoetingen
URL
```
/api/teamAndClubPlayers/{teamName}
Example /api/teamAndClubPlayers/Gentse%203G
```
Result
```
{
    "clubName": "GENTSE BC",
    "teamName": "Gentse 3G",
    "meetings": [
        {
            "hTeam": "Gentse 3G",
            "oTeam": "Denderleeuw 2G",
            "dateTime": "20141214190000",
            "locationName": "Sporthal Bourgoyen"
        },
        {
            "hTeam": "Gentse 3G",
            "oTeam": "Danlie 1G",
            "dateTime": "20150111190000",
            "locationName": "Sporthal Bourgoyen"
        },
...    ],
    "players": [
        {
            "firstName": "Jerome",
            "lastName": "Degryse",
            "vblId": "30034039",
            "gender": "M",
            "type": "R",
            "fixedRanking": [
                "D",
                "D",
                "D"
            ],
            "ranking": [
                "D",
                "D",
                "D"
            ]
        },
        {
            "firstName": "Eileen",
            "lastName": "Demaret",
            "vblId": "50000277",
            "gender": "F",
            "type": "C",
            "fixedRanking": [
                "D",
                "D",
                "D"
            ],
            "ranking": [
                "D",
                "D",
                "D"
            ]
        },
...
   ]
}
```

Query
```
select c.clubName,p.playerId,p.firstName,p.lastName,p.gender,p.type, rF.singles fSingles,rF.doubles fDoubles,rF.mixed fMixed, rV.Singles vSingles,rV.doubles vDoubles,rV.mixed vMixed from lf_club c
join lf_player p on p.club_clubId = c.clubId
join lf_ranking rF on rF.player_playerId = p.playerId
join lf_ranking rV on rV.player_playerId = p.playerId
where c.clubName = (
    select c.clubName from lf_club c
    join lf_team t on c.clubId = t.club_clubId
    where t.teamName=:team
)
and rV.date = (
select max(rr.date) from lf_player pp
join lf_ranking rr on rr.player_playerId = pp.playerId
where pp.playerId = p.playerId
group by p.playerId
)
and rF.date = (
select min(rr.date) from lf_player pp
join lf_ranking rr on rr.player_playerId = pp.playerId
where pp.playerId = p.playerId
group by p.playerId)
```

## Doelstellingen ##
Badmintonvlaanderen veranderde voor het seizoen 2014-2015 een pak artikelnrs. van de C320. De c320 is het competitiereglement dat gebruikt wordt om de PBO-competitie in goede banen te leiden. Deze verandering werd vooral gedaan omdat elke competitiespeler sinds het seizoen 2013-2014 drie klassementen heeft (enkel, dubbel en gemengd). De reglementen zijn dus bepaald door Badmintonvlaanderen, maar omdat PBO veel vragen krijgt omtrent deze materie besloten we om een tool te ontwikkelen om te controleren of een opstelling kan/mag gebruikt worden volgens de c320. Zowel de spelers die je kan selecteren als de opstellingsvolgorde worden gescreend in deze module. Dmv deze tool kan je dus rustig puzzelen aan een opstelling en deze ook afprinten of doormailen.

## Validatie meldingen ##
  * HE/HD should only contain man
  * DE/DD should only contain women
  * GD should only contain one man and one woman
  * Can not add the same player twice to the same game
  * The same player can only play two double games
  * The same player can only play one HE/DE/GD
  * Players in game HE/HD/DE/DD/GD must be ordered from highest to lowest index
  * Teamindex van de effectieve ploeg mag de teamindex van papieren ploeg niet overschrijden
  * Op papier in team van gelijke of hogere divisie
  * Vaste index hoger dan dat van basisspeler