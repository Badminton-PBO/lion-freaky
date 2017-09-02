* Hoe tijdelijk constraints disablen om gemakkelijker te kunnen omsproen weke data problemen heeft
```
SET GLOBAL FOREIGN_KEY_CHECKS=0; // disable
SET GLOBAL FOREIGN_KEY_CHECKS=1; //enable
```

* Welke spelers staan in meer dan één basisteam opsteld van hetzelfde genre?
```
select * from `lf_player_has_team` where player_playerId in(
SELECT  player_playerId FROM `lf_player_has_team` where player_playerId in (SELECT player_playerId FROM `lf_player_has_team`
group by player_playerId
having count(*)>1)
group by player_playerId, lf_dbload_eventcode(team_teamName)
having count(*)>1
)
order by player_playerId asc
```