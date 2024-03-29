@extends('pboapp')

@section('heads')
    <!-- Knockoutjs -->
    <script type="text/javascript" src="libs/js/knockout-3.2.0.js"></script>
    <script type="text/javascript" src="libs/knockout-sortable-master/build/knockout-sortable.min.js"></script>

    <!-- drag/drop support in touch devices -->
    <script type="text/javascript" src="libs/js/jquery.ui.touch-punch.min.js"></script>


    <script>
        function togglePlayers(x) {
            $("#nonplayerstable").toggle('fold');
            if ($("#nonplayerstable").is(':visible')) {
                $("html, body").animate({scrollTop: $("#nonplayerstable").offset().top});
            }
        }

        //FUNCTION TO GET AND AUTO PLAY YOUTUBE VIDEO FROM DATATAG
        function autoPlayYouTubeModal(){
            var trigger = $("body").find('[data-toggle="modal"]');
            trigger.click(function() {
                var theModal = $(this).data( "target" );
                videoSRC = $(this).attr( "data-theVideo" );
                videoSRCauto = videoSRC+"?autoplay=1" ;
                $(theModal+' iframe').attr('src', videoSRCauto);
                $(theModal+' button.close').click(function () {
                    $(theModal+' iframe').attr('src', videoSRC);
                });
            });
        }

        $(document).ready(function(){
            autoPlayYouTubeModal();
        });

    </script>

    <style type="text/css">
        .doubleGame {
            border: solid 3px #666;
            min-height: 60px;
            background-color: #666;
            margin: 5px;
            padding: 2px;
            border-radius: 5px;
            box-shadow: 2px 2px 2px #999;
            color: #fff;
            min-width:150px;
        }

        .singleGame {
            border: solid 3px #666;
            min-height: 30px;
            background-color: #666;
            margin: 5px;
            padding: 2px;
            border-radius: 5px;
            box-shadow: 2px 2px 2px #999;
            color: #fff;
            min-width:150px;
        }

        .doubleTitleGame {
            padding: 20px 0px 0px 10px;
        }

        .secondplayer {
            padding-top: 10px;
        }

        .singleTitleGame {
            padding: 12px;
        }

        /* unvisited link */
        #print a:link, #print a:visited, #print a:hover, #print a:active, #print2pdf a:link, #print2pdf a:visited, #print2pdf a:hover, #print2pdf a:active {
            color: #005500;
        }

        .playerDetail {
            font-size:10px;
        }

        #message, #error {
            font-size: 1em;
            margin-top: 10px;
            background-color: orange;
            color: #444;
            padding: 2px;
            text-align: center;
            border-radius: 5px;
            box-shadow: 2px 2px 2px #999;
        }

        #error {
            background-color: #ff3333;
            color: #ddd;
        }

        .trash {
            background-color: #000;
            width: 300px;
            height: 50px;
            background-color: #AAA;
        }

        @media(min-width:0px) {
            #playerListId {
                height:20vh;
                overflow:auto;
            }
        }

        @media(min-width:992px) {
            #playerListId {
                height:55vh;
                overflow:auto;
            }
        }


    </style>
@endsection


@section('help')
    <div class="col-xs-2">
        <div class="pull-right">
            <button id="nonplayersbutton" type="button" class="btn btn-primary" data-toggle="modal" data-target="#myHelpModal" data-theVideo="https://www.youtube.com/embed/Ka_RfATnW0E">
                <span class="glyphicon glyphicon-question-sign"  aria-hidden="true"></span>
            </button>
            <!-- Help Modal -->
            <div class="modal fade" id="myHelpModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog" style="width: 830px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Sluiten</span></button>
                            <h4 class="modal-title" id="myModalLabel">Help</h4>
                        </div>
                        <div class="modal-body">
                            <p>In onderstaande video wordt het gebruik van deze tool getoond.</p>
                            <!-- iframe-scr will be set upon modal load to avoid unnecessary loadings when help button is not used-->
                            <iframe width="800" height="600" src="" frameborder="0"></iframe>

                            <p>Gelieve problemen met deze tool te melden via <a href="mailto:competitie@badminton-pbo.be?SUBJECT=Online%20Ploegopstellingsformulier">competitie[at]badminton-pbo.be</a></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Sluiten</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <!-- ko if: !$root.chosenClub() -->
    <div class="well">
        <h1>Doelstelling</h1>
        <a href="http://www.badmintonvlaanderen.be" target="_new">Badminton Vlaanderen</a> veranderde voor het seizoen 2014-2015 een pak artikelnummers van de C320. De <a href="http://www.badmintonvlaanderen.be/page/27552/Reglementen#C%20320" target="_new">C320</a> is het competitiereglement dat gebruikt wordt om oa. de PBO-competitie in goede banen te leiden. Deze verandering werd vooral gedaan omdat elke competitiespeler sinds het seizoen 2013-2014 drie klassementen heeft: enkel, dubbel en gemengd). De reglementen zijn dus bepaald door Badminton Vlaanderen. Omdat PBO echter veel vragen krijgt omtrent deze materie is er besloten om een tool te ontwikkelen om te controleren of een opstelling kan/mag gebruikt worden. Zowel de spelers die je kan selecteren als de opstellingsvolgorde worden gescreend in deze tool. Dmv. deze tool kan je dus rustig puzzelen aan een opstelling en deze ook afprinten of doormailen in PDF.
        <br><br>
        <span class="glyphicon glyphicon-info-sign"></span> Deze tool werd aangepast om conform te zijn met de C320 voor seizoen 2021-2022.
    </div>
    <!-- /ko -->
    <div class="row hidden-print">
        <div class="col-md-5">
            <!-- ko if: !$root.chosenClub() -->
            <p data-bind="with: $root.dbload">Gebaseerd op gegevens van <span data-bind="text: dateLayout"> </span>, <span data-bind="text: hourLayout">  </span></p>
            <!-- /ko -->
            <p>
                Kies je badmintonclub:
                <select data-bind="options: sampleClubs,
                                       optionsText:'clubName',
                                       value: chosenClub,
                                       optionsCaption: 'Selecteer...'"></select>
            </p>
            <p data-bind="with: chosenClub">
                Kies je team:
                <select data-bind="options: teams.filter(function(team) { return team.type != 'LIGA'}),
                                       optionsText: 'teamName',
                                       value: $parent.chosenTeamName,
                                       optionsCaption: 'Selecteer...'"></select>
            </p>
            <p data-bind="with: chosenTeam">
                Kies een ontmoeting:
                <select data-bind="options: meetings,
                                       optionsText: 'fullMeetingLayout',
                                       value: $root.chosenMeeting,
                                       optionsCaption: 'Selecteer...'"></select>
            </p>
            <div data-bind="if: $root.chosenMeeting()" class="hidden-xs hidden-sm">

                <div class="well well-sm">
                    <div>Filter opstelbare spelers:  <span data-bind="text: $root.filteredAvailablePlayers().length"/></span>/<span data-bind="text: $root.availablePlayers().length"/></div>
                    <div class="row">
                        <div class="col-xs-5">
                            <!-- ko if: $root.chosenTeam().isMultiSexTeam() -->
                            <div class="btn-group" aria-label="b1" data-toggle="buttons" data-bind="foreach:$root.genderButtons">
                                <label class="btn btn-primary" data-bind="css: {active: selected},click: $root.selectGenderButton">
                                    <input type="radio" name="playerGender"><span data-bind="text: name"></span></input>
                                </label>
                            </div>
                            <!-- /ko -->
                        </div>
                        <div class="col-xs-7">
                            <div class="btn-group" aria-label="b2" data-toggle="buttons" data-bind="foreach:$root.playerTypeButtons">
                                <label class="btn btn-primary" data-bind="css: {active: selected}, click: $root.selectPlayerTypeButton">
                                    <input type="radio" name="playerType" ><span data-bind="text: name"></span></input>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="playerListId" class="well well-sm" style="">
                    <table class="table table-bordered table-condensed">
                        <thead>
                        <tr>
                            <th>Speler</th>
                            <th class="playerDetail">Discipline-index <span data-bind="text: $root.chosenTeam().rankingLayout()"></span></th>
                            <th class="playerDetail">Opstellingsindex <span data-bind="text: $root.chosenTeam().rankingLayout()"></span></th>
                        </tr>
                        </thead>
                        <tbody data-bind="foreach: filteredAvailablePlayers">
                        <tr>
                            <td data-bind="draggable: $data"><span class="glyphicon glyphicon-user"></span> <span data-bind="text: fullName" style="color:#428bca"></span></td>
                            <td data-bind="text: fixedRankingLayout($root.chosenTeam().teamType)" class="playerDetail"></td>
                            <td data-bind="text: rankingLayout($root.chosenTeam().teamType)" class="playerDetail"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div data-bind="if: ($root.chosenMeeting()  && ($root.notAllowedPlayersOtherBaseTeam().length > 0 || $root.notAllowedPlayersStrongestPlayerRanking().length > 0))" class="hidden-xs hidden-sm">
                    <button id="nonplayersbutton" type="button" class="btn btn-primary" onclick="togglePlayers();">Niet opstelbare spelers &raquo;</button>
                    <div id="nonplayerstable" class="well well-sm" style="display:none">
                        <table class="table table-bordered table-condensed">
                            <tbody>
                            <!-- ko foreach: notAllowedPlayersOtherBaseTeam -->
                            <tr data-toggle="tooltip" data-placement="right" title="De titularis mag niet in de basisopstelling van een ploeg uit dezelfde of hogere afdeling staan ">
                                <td data-bind="text: fullName"></td>
                            </tr>
                            <!-- /ko -->
                            <!-- ko foreach: notAllowedPlayersStrongestPlayerRanking -->
                            <tr data-toggle="tooltip" data-placement="right" title="Het sterkste individueel klassement in relevant disciplines mag niet sterker zijn dan toegelaten in deze afdeling.">
                                <td><span data-bind="text: fullName"></span> (sterkste individueel klassement=<span data-bind="text: strongestFixedIndexInsideTeam($root.chosenTeam().teamType)"></span>)</td>
                            </tr>
                            <!-- /ko -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7" data-bind="if: $root.chosenMeeting()">
            <div id="ploegopstelling">
                <div class="row row-fluid">
                    <div class="col-xs-12 col-sm-6">
                        <h2>Ploegopstelling</h2>
                    </div>
                    <div class="col-xs-12 col-sm-6" style="padding-top:10px; padding-bottom:15px">
                        <!-- ko if: $root.chosenMeeting() -->
                        <!-- ko if: $root.chosenTeam().isFull() -->
                        <div id="print2pdf" class='label label-default' style='font-size:30px;margin-right:10px;'>
                            <a href="#" data-bind="click: print2pdf">
                                        <span class="glyphicon glyphicon-floppy-save"></span>
                            </a>
                        </div>
                        <!-- /ko -->
                        <!-- ko ifnot: $root.chosenTeam().isFull() -->
                        <div id="print2pdf" class='label label-default' style='font-size:30px;margin-right:10px;'>
                            <span class="glyphicon glyphicon-floppy-save"></span>
                        </div>
                        <!-- /ko -->
                        <div id="reset" class='label label-default' style='font-size:30px'>
                            <a href="#" data-bind="click: resetForm">
                                <span class="glyphicon glyphicon-trash"></span>
                            </a>
                        </div>
                        <!-- /ko -->
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-4" data-bind="with:$root.chosenTeam"><span data-bind="text: teamTypeLayout()"></span>, Provenciaal</div>
                    <div class="col-xs-4" data-bind="with:$root.chosenTeam">Afdeling: <span data-bind="text: devision"></span> / Reeks: <span data-bind="text: series"></span></div>
                    <div class="col-xs-4" data-bind="with:$root.chosenMeeting">Locatie: <input data-bind="textInput: locationName" /></div>
                </div>
                <div class="row">
                    <div class="col-xs-4" data-bind="with:$root.chosenMeeting">Thuisploeg: <span data-bind="text: hTeam"></span></div>
                    <div class="col-xs-4" data-bind="with:$root.chosenMeeting">Bezoekers: <span data-bind="text: oTeam"></span></div>
                    <div class="col-xs-4" data-bind="with:$root.chosenMeeting">Datum/Uur: <span data-bind="text: dateLayout"></span>  <span data-bind="text: hourLayout"></span></div>
                </div>
                <div class="row">
                    <div class="col-xs-8" data-bind="with:$root.chosenTeam">Ploegkapitein <span data-bind="text: teamName"></span>: <input data-bind="textInput: captainName" /></div>
                </div>
                <div class="row">
                    <div class="col-xs-12" id="error" data-bind="flash: lastError"></div>
                </div>
                <div class="row hidden-xs hidden-sm">
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th class="col-md-2">Discipline</th>
                            <th class="col-md-8">Spelers</th>
                            <th class="col-md-2">Opstellingsindex</th>
                        </tr>
                        </thead>
                        <tbody data-bind="foreach: games">
                            <tr>
                                <td style="padding:0px">
                                    <div data-bind="css: gameTitleCss()">
                                                    <span class="label label-default" data-bind="text: id"></span>
                                    </div>
                                </td>
                                <td style="padding:0px">
                                    <div data-bind="sortable: {data : playersInGame, allowDrop: allowMorePlayers, beforeMove: $root.verifyAssignments, afterMove: $root.verifyAssignmentsAfterMove}, css: gameCss()">
                                        <div>
                                            <span data-bind="text: fullName"></span>,
                                            <span data-bind="text: vblId"></span>,
                                            index=<span data-bind="text: index($parent.gameType)"></span>
                                            <div class="pull-right">
                                                <a href="#" data-bind="click: $parent.removePlayer">
                                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span data-bind="text: totalIndexWithLayout"></span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="row visible-xs visible-sm">
                    <!--div class="row"-->
                    <table class="table table-condensed">
                        <thead>
                        <tr>
                            <th class="col-md-2">Discipline</th>
                            <th class="col-md-6">Spelers</th>
                            <th class="col-md-2">&nbsp;</th>
                            <th class="col-md-2">Opstellingsindex</th>
                        </tr>
                        </thead>
                        <tbody data-bind="foreach: games">
                        <tr>
                            <td style="padding:0px">
                                <div data-bind="css: gameTitleCss()">
                                                <span class="label label-default" data-bind="text: id">
                                </div>
                            </td>
                            <td style="padding:0px">
                                <div data-bind="sortable: {data : playersInGame, allowDrop: allowMorePlayers, beforeMove: $root.verifyAssignments, afterMove: $root.verifyAssignmentsAfterMove}, css: gameCss()">
                                    <div data-bind="css: {secondplayer: $index() > 0}">
                                        <span data-bind="text: fullName"></span>,
                                        <span data-bind="text: vblId"></span>,
                                        <span data-bind="text: ranking($parent.gameType)"></span>=<span data-bind="text: index($parent.gameType)"></span>
                                        <div class="pull-right" style="font-size:20px;">
                                            <a href="#" data-bind="click: $parent.removePlayer">
                                                <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <!-- ko if: allowMorePlayers() -->
                                <button id="selectplayersbutton" type="button" class="btn btn-primary" data-toggle="modal" data-target="#selectPlayersModal" data-bind="attr: { 'data-game-id': id }">
                                    <span class="glyphicon glyphicon-plus"  aria-hidden="true"></span>
                                </button>
                                <!-- /ko -->
                            </td>
                            <td>
                                <span data-bind="text: totalIndexWithLayout"></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <!-- Select Player Modal -->
                    <div class="modal selectPlayersModal" id="selectPlayersModal" tabindex="-1" role="dialog" aria-labelledby="selectPlayersModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="btn btn-primary pull-right" data-dismiss="modal"><span aria-hidden="true" style='font-size:15px'>&times;</span><span class="sr-only">Sluiten</span></button>
                                    <h4 class="modal-title" id="myModalLabel">Selecteer speler</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="well well-sm">
                                        <div>Filter opstelbare spelers:  <span data-bind="text: $root.filteredAvailablePlayers().length"/></span>/<span data-bind="text: $root.availablePlayers().length"/></div>
                                        <div class="row">
                                            <div class="col-xs-5">
                                                <!-- ko if: $root.chosenTeam().isMultiSexTeam() -->
                                                <div class="btn-group" aria-label="b1" data-toggle="buttons" data-bind="foreach:$root.filteredGenderButtons">
                                                    <label class="btn btn-primary" data-bind="css: {active: selected},click: $root.selectGenderButton">
                                                        <input type="radio" name="playerGender"><span data-bind="text: name"></span></input>
                                                    </label>
                                                </div>
                                                <!-- /ko -->
                                            </div>
                                            <div class="col-xs-7">
                                                <div class="btn-group" aria-label="b2" data-toggle="buttons" data-bind="foreach:$root.playerTypeButtons">
                                                    <label class="btn btn-primary" data-bind="css: {active: selected}, click: $root.selectPlayerTypeButton">
                                                        <input type="radio" name="playerType" ><span data-bind="text: name"></span></input>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="well well-sm" style="">
                                        <table class="table table-bordered table-condensed">
                                            <thead>
                                            <tr>
                                                <th>Speler</th>
                                                <th class="playerDetail">discipline index <span data-bind="text: $root.chosenTeam().rankingLayout()"></span></th>
                                                <th class="playerDetail">opstellings index <span data-bind="text: $root.chosenTeam().rankingLayout()"></span></th>
                                                <td>&nbsp;</td>
                                            </tr>
                                            </thead>
                                            <tbody data-bind="foreach: $root.filteredAvailablePlayers">
                                            <tr>
                                                <td>
                                                    <button type="button" class="btn btn-primary" data-bind="click: $root.addPlayer"><span data-bind="text: fullName"></span></button>
                                                </td>
                                                <td data-bind="text: fixedRankingLayout($root.chosenTeam().teamType)" class="playerDetail"></td>
                                                <td data-bind="text: rankingLayout($root.chosenTeam().teamType)" class="playerDetail"></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" data-bind="with: chosenTeam">
                    <div class="col-xs-12 col-sm-6">
                        <div class="well well-sm">
                            Basisspelers: ploegindex=<span data-bind="text: baseTeamIndex"></span>
                            <ul data-bind="foreach: playersInBaseTeam">
                                <li>
                                    <span data-bind="text: fullName"></span>,
                                    <span data-bind="text: fixedIndexInsideTeam($root.chosenTeam().teamType)"></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-6">
                        <div class="well well-sm">
                            Titularissen: ploegindex=<span data-bind="text: effectiveTeamIndex"></span>
                            <ul data-bind="foreach: effectivePlayersInTeam">
                                <li>
                                    <span data-bind="text: fullName"></span>,
                                    <span data-bind="text: fixedIndexInsideTeam($root.chosenTeam().teamType)"></span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('printable')
    <div class="container">
        <div id="printframe" style="display:none">
            <iframe src="" id="iPrint" style="display:none" frameborder="0">
            </iframe>
        </div>
    </div>
@endsection

@section('tailscripts')
    <script type="text/javascript" src="builder.js" charset="utf-8"></script>
@endsection
