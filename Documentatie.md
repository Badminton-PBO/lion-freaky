# Introduction #

Validatie regels gegroepeerd per artikel. Bedoeling van de letterlijke artikelnummers: Kunnen gebruikt worden als documentatie bij een eventuele foutmelding als verklaring voor ploegkapiteins


# Definities #
  * indexSingle = index single bepaald op 1/8, 1/12, 1/3 en 1/5 door PBO,input
  * fixedIndexSingle = indexSingle at 15/5, input PBO

  * indexDouble=index double bepaald op 1/8, 1/12, 1/3 en 1/5 door PBO,input
  * fixedIndexDouble=indexDouble at 15/5, input PBO

  * indexMix=index mix bepaald op 1/8, 1/12, 1/3 en 1/5 door PBO,input
  * fixedIndexMix=indexMix at 15/5, input PBO

  * index(Class A)=20
  * index(Class B1)=10
  * index(Class B2)=6
  * index(Class C1)=4
  * index(Class C2)=2
  * index(Class D)=1

  * indexInsideMixTeam = indexSingle + indexDouble + fixedIndexMix
  * indexInsideManTeam = indexSingle + indexDouble
  * indexInsideWomenTeam = indexSingle + indexDouble

  * fixedIndexInsideMixTeam = fixedIndexSingle + fixedIndexDouble + fixedIndexMix
  * fixedIndexInsideManTeam = fixedIndexSingle + fixedIndexDouble
  * fixedIndexInsideWomenTeam = fixedIndexSingle + fixedIndexDouble

  * baseTeamIndexMix = sum(fixedIndexInsideMixTeam) van 2 basisM spelers en 2 basisV spelers)
  * baseTeamIndexMan = sum(fixedIndexInsideManTeam) van 4 basisM spelers
  * baseTeamIndexWomen = sum(fixedIndexInsideWomenTeam) van 4 basisV spelers

  * effectiveTeamIndexMix = sum(indexInsideMixTeam van 2 effectivespelersV met hoogste indexInsideMixTeam  en 2 effectivespelersM met hoogste fixedindexInsideMixteam)
  * effectiveTeamIndexMan = sum(indexInsideManTeam) van 4 effectiveM met hoogste fixedindexInsideManTeam
  * effectiveTeamIndexWomen = sum(indexInsideWomenTeam) van 4 effectiveV met hoogste fixedindexInsideWomenTeam

```
 Ik twijfel waarom we spelers zouden indelen in basis en effectief. Uiteindelijk zijn het dezelfde personen die een fixed en een huidig klassement hebben. Dus beter gewoon "speler". Het zijn eigenlijk de ploegen die basic en effective zijn. UPDATE: misschien moet een speler veranderen van "speler" naar "effectievespeler" nadat hij in de opstelling gesleept werd, voor de volgorde van opsteling is het toch handig dat we spreken over een effectievespelerM of V GRAAG FEEDBACK
```

# Regels #
## teamindex regel ##
  * baseTeamIndexMix >= effectiveTeamIndexMix
  * baseTeamIndexMan >= effectiveTeamIndexMan
  * baseTeamIndexWomen >= effectiveTeamIndexWomen

## Highest Rank ##
> (je kan hier per Class werken of per Fixedindex)

  * Manteam: Highest fixedindexsingle or fixedindexdouble of basemanteam >= Highest fixedindexsingle or fixedindexdouble of effectivemanteam:OK

  * Manteam: Highest fixedindexsingle or fixedindexdouble of basemanteam < Highest fixedindexsingle or fixedindexdouble of effectivemanteam:NOK
  * Womanteam: Highest fixedindexsingle or fixedindexdouble of baseWomanteam >= Highest fixedindexsingle or fixedindexdouble of effectiveWomanteam:OK

  * Womanteam: Highest fixedindexsingle or fixedindexdouble of baseWomanteam < Highest fixedindexsingle or fixedindexdouble of effectiveWomanteam:NOK
  * Mixteam:
> Highest fixedindexsingle, fixedindexdouble or fixedindexMixteam of MEN of basemixteam >= Highest fixedindexsingle or fixedindexdouble of effectivemixteam AND Highest fixedindexsingle, fixedindexdouble or fixedindexmix of WOMEN of basemixteam >= Highest fixedindexsingle, fixedindexdouble or fixedindexmix of WOMEN of  effectivemixteam:OK

> Highest fixedindexsingle, fixedindexdouble or fixedindexMixteam of MEN of basemixteam >= Highest fixedindexsingle or fixedindexdouble of effectivemixteam AND Highest fixedindexsingle, fixedindexdouble or fixedindexmix of WOMEN of basemixteam < Highest fixedindexsingle, fixedindexdouble or fixedindexmix of WOMEN of  effectivemixteam:NOK

> Highest fixedindexsingle, fixedindexdouble or fixedindexMixteam of MEN of basemixteam < Highest fixedindexsingle or fixedindexdouble of effectivemixteam AND Highest fixedindexsingle, fixedindexdouble or fixedindexmix of WOMEN of basemixteam >= Highest fixedindexsingle, fixedindexdouble or fixedindexmix of WOMEN of  effectivemixteam:NOK

> Highest fixedindexsingle, fixedindexdouble or fixedindexMixteam of MEN of basemixteam < Highest fixedindexsingle or fixedindexdouble of effectivemixteam AND Highest fixedindexsingle, fixedindexdouble or fixedindexmix of WOMEN of basemixteam < Highest fixedindexsingle, fixedindexdouble or fixedindexmix of WOMEN of  effectivemixteam:NOK

## Opstellingsvolgorderegels ##
### Algemeen ###
  * elke gameType (HE, DE, HD, DD, GD) moet in dalende index volgorde worden opgesteld
  * effectieve speler kan max 2 dubbels, 1 enkel en 1 mix spelen

### Uitgewerkt ###
  * Womenteam:
    * sum(indexDouble DD1 players) >= sum(indexDouble DD2 players) >= sum(indexDouble DD3 players) => sum(indexDouble DD4 players)
    * indexSingle(DE1) >= indexSingle(DE2) >= indexSingle(DE3) >= indexSingle(DE4)
    * effectivespelerV MAX 2 Doubles en 1 Single


  * Menteam:
    * sum(indexDouble HD1 players) >= sum(indexDouble HD2 players) >= sum(indexDouble HD3 players) => sum(indexDouble HD4 players)
    * indexSingle(HE1) >= indexSingle(HE2) >= indexSingle(HE3) >= indexSingle(HE4)
    * effectivespelerV MAX 2 Doubles en 1 Single

  * Mixteam:
    * indexSingle(HE1) >= indexSingle(HE2)
    * indexSingle(DE1) >= indexSingle(DE2)
    * sum(indexMix GD1 players) >= sum(indexMix GD2 players)
    * effectivespeler MAX 1 Doubles, 1 Single en 1 Mix

**Spelers:
Artikel 40.
Iedere speler in het bezit van een geldig lidnummer mag worden opgesteld in een competitieploeg van de club waarbij hij/zij aangesloten is of waaraan hij/zij uitgeleend is.**

- Speler lidnummer (8 cijfers)

- Speler heeft klassement per discipline

- Categorie="competitiespeler"

- "m" voor heren- of mixcompetitie, "v" voor vrouwen- of mixcompetitie

- speler "vastepersindex", bepaald op 15/5. vastepersindex=som van index enkel+dubbel(voor heren/of damesploeg)+mix (voor mixploegen)

- speler "variabelepersindex", gewijzigd op 1/8, 1/12, 1/3 en 1/5 (idem als vorige regel)

- Speler is lid van "papierenploeg"

- Ploegkapitein=speler + emailadres


**Club:**

-Lidnummer VBL (c+55cijfers)

-Club bestaat uit >of=1 team.

**Team:
Papierenploeg:
artikel 50.2. Een ploeg bestaat minimaal uit 4 basisspelers:

> voor de gemengde competitie worden 2 dames en 2 heren opgegeven

> voor de herencompetitie worden 4 heren opgegeven**

> voor de damescompetitie worden 4 dames opgegeven

artikel 51.2. Aan elk klassement wordt een waarde toegekend: A = 20 B1 = 10 B2 = 6 C1 = 4 C2 = 2 D = 1 3. Door de optelling van de somwaardes van de basisspelers, wordt de ploegindex verkregen.
artikel 51.4. Indien een club slechts één ploeg in competitie heeft per soort competitie, mogen alle spelers van de club, die voldoen aan de voorwaarden van artikels 40 t.e.m. 43, aantreden in die ploeg, ongeacht hun klassementen en index, waarbij de nationale, Vlaamse en provinciale competitie één geheel vormen.

-Elke papierenploeg heeft een teamindex (som van "vastepersindex" van 4 "m" (herencompetitie), 4 "v" (vrouwencompetitie) of 2 "m" + 2 "v" (mixcompetitie)
-Team speelt op vast plaats en uur

Effectieve ploeg:
artikel 53.2. Een titularis mag onder geen enkel beding:

- een hoger klassement hebben dan dat van de opgegeven basisspeler met het hoogste klassement van hetzelfde geslacht van de betrokken ploeg, waarbij de index, die vóór aanvang van de ontmoeting verkregen wordt door het optellen van de klassementen van de 4 titularissen met het hoogste klassement (2 heren en 2 dames in de gemengde competitie, 4 heren in de herencompetitie, 4 dames in de damescompetitie), nooit de ploegindex van de basisspelers mag overschrijden (uitgezonderd artikel 51 lid 4 en 5)

-opgegeven basisspeler zijn van een ploeg uit een hogere afdeling van dezelfde soort competitie;

-opgegeven basisspeler zijn van een ploeg uit dezelfde afdeling van dezelfde soort competitie.

-"Effectieve speler" is niet gelijk aan papieren speler uit hoger team, of team op zelfde provinciale niveau.

-"effectieve speler" met hoogste klassement is gelijk aan of lager dan de "papieren speler" met het hoogste klassement (gelijk welk onderdeel)

-de "effectieveteamindex" (zelfde berekeningswijze als hierboven, hievoor worden de 4 hoogste "vastepersindexen gebruikt bij heren/damescompetitie en 2(heren + 2(dames)hoogste vastepersindexen)< of= "papierenteamindex"

**Regels voor wedstrijd:**

-elke wedstrijd heeft tijdstip + locatie

-uitploeg en thuisploeg

**Regels voor opstelling**


to be continued