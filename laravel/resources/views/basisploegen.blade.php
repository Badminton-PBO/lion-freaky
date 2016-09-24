@extends('pboapp')

@section('heads')
    <!-- Knockoutjs -->
    <script type="text/javascript" src="libs/js/knockout-3.2.0.js"></script>
    <script type="text/javascript" src="libs/knockout-sortable-master/build/knockout-sortable.min.js"></script>

    <!-- drag/drop support in touch devices -->
    <script type="text/javascript" src="libs/js/jquery.ui.touch-punch.min.js"></script>


    <script>

    </script>

    <style type="text/css">
        .baseTeam {
            border: solid 3px #666;
            min-height: 100px;
            background-color: #666;
            margin: 5px;
            padding: 2px;
            border-radius: 5px;
            box-shadow: 2px 2px 2px #999;
            color: #fff;
            min-width:150px;
        }

        .playerDetail {
            font-size:10px;
        }

        #message, #error, #warning {
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
            background-color: #d9534f;
            border-color: #d43f3a;
            color: #fff;
        }

        #warning {
            background-color: #ff8c00;
            border-color: #d43f3a;
            color: #fff;
        }

        #success {
            background-color: #5cb85c;
            border-color: #4cae4c;
            color: #fff;
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
@endsection

@section('content')
    <div class="row hidden-print">
        <div class="col-md-5">
            <div class="well well-sm">
                <div>Filter beschikbare spelers:  <span data-bind="text: $root.filteredAndSortedPlayers().length"/></span>/<span data-bind="text: $root.availablePlayers().length"/></div>
                <div class="row">
                    <div class="col-xs-5">
                        <!-- ko if: $root.selectedTeamTypeIsMultiSex() -->
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
            <div>
                <ul class="nav nav-tabs nav-justified">
                    <li  class="active"><a  href="#playerListId" data-toggle="tab"><span data-bind="text: $root.clubName"/></span> spelers</a></li>
                    <li><a href="#transfers" data-toggle="tab">Uit andere clubs</a></li>
                    <li><a href="#notFound" data-toggle="tab">Niet gevonden</a></li>
                </ul>
            </div>
            <div class="tab-content">
                <div id="playerListId" class="tab-pane active well well-sm" style="">
                    <table class="table table-bordered table-condensed">
                        <thead>
                        <tr>
                            <th>
                                <a href="#" data-bind="click: function(data, event) { $root.toggleSortPlayers('NAME') }">
                                    Speler
                                    <!-- ko if: $root.selectedPlayerSortType() =='NAME' -->
                                    <div class="pull-right">
                                        <span class="glyphicon" data-bind="css: sortingDirectionGlyphicon"></span>
                                    </div>
                                    <!-- /ko -->
                                </a>
                            </th>
                            <th>
                                <a href="#" data-bind="click: function(data, event) { $root.toggleSortPlayers('FIXED-INDEX') }">
                                    <span data-bind="text: $root.fixedRankingHeaderLayout($root.selectedTeamType())"></span>
                                    <!-- ko if: $root.selectedPlayerSortType() =='FIXED-INDEX' -->
                                    <div class="pull-right">
                                        <span class="glyphicon" data-bind="css: sortingDirectionGlyphicon"></span>
                                    </div>
                                    <!-- /ko -->
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody data-bind="foreach: filteredAndSortedPlayers">
                        <tr>
                            <td data-bind="draggable: $data"><span class="glyphicon glyphicon-user"></span> <span data-bind="text: fullName" style="color:#428bca"></span></td>
                            <td data-bind="text: fixedRankingLayout($root.selectedTeamType())" class="playerDetail"></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div id="transfers" class="tab-pane">
                    Zoek een VBL speler obv. VBL nummer
                    <div class="input-group">
                        <span class="input-group-addon" id="basic-addon1">VBL nummer</span>
                        <input type="text" class="form-control" placeholder="500123456" aria-describedby="basic-addon1" data-bind="textInput: transferSearchVblId">
                    </div>
                    <input class="btn btn-default pull-right" type="submit" value="Zoek" data-bind="click: searchPlayersUsingVblId">
                    <div id="transferPlayerListId" class="tab-pane well well-sm" style="">
                        <table class="table table-bordered table-condensed">
                            <thead>
                            <tr>
                                <th>Speler</th>
                                <th><span data-bind="text: $root.fixedRankingHeaderLayout($root.selectedTeamType())"></span></th>
                            </tr>
                            </thead>
                            <!-- ko if: $root.foundTransferPlayer().length > 0 -->
                            <tbody data-bind="foreach: foundTransferPlayer">
                            <tr>
                                <td data-bind="draggable: $data"><span class="glyphicon glyphicon-user"></span> <span data-bind="text: fullName" style="color:#428bca"></span> (<span data-bind="text: gender" style="color:#428bca"></span>)</td>
                                <td data-bind="text: fixedRankingLayout($root.selectedTeamType())" class="playerDetail"></td>
                            </tr>
                            </tbody>
                            <!-- /ko -->
                            <!-- ko if: $root.foundTransferPlayer().length == 0 -->
                            <tbody>
                                <tr>
                                    <td colspan="2">Geen speler gevonden.</td>
                                </tr>
                            </tbody>
                            <!-- /ko -->
                        </table>
                    </div>


                </div>
                <div id="notFound" class="tab-pane">
                    Not found
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div id="ploegopstelling">
                <div class="row row-fluid">
                    <div class="col-xs-12 col-sm-12">
                        <h2>Basisploegen <span data-bind="text: $root.clubName"/></span> seizoen <span data-bind="text: $root.season"></span></h2>
                        <ul class="nav nav-tabs nav-justified">
                            <li class="active"><a  href="#H" data-bind="click: function(data, event) { showTeams('M', data, event) }">Heren (<span data-bind="text: $root.numberOfTeamsOfTeamType('M')"></span>)</a></li>
                            <li><a href="#D" data-bind="click: function(data, event) { showTeams('L', data, event) }">Dames (<span data-bind="text: $root.numberOfTeamsOfTeamType('L')"></span>)</a></li>
                            <li><a href="#G" data-bind="click: function(data, event) { showTeams('MX', data, event) }">Gemengd (<span data-bind="text: $root.numberOfTeamsOfTeamType('MX')"></span>)</a></li>
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Acties<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li>Ploeg toevoegen</li>
                                    <li><a href="#" data-bind="click: function(data, event) { addTeam('M', data, event) }">Herenploeg</a></li>
                                    <li><a href="#" data-bind="click: function(data, event) { addTeam('L', data, event) }">Damesploeg</a></li>
                                    <li><a href="#" data-bind="click: function(data, event) { addTeam('MX', data, event) }">Gemengde ploeg</a></li>
                                    <li><a href="#" data-bind="click: $root.save">Bewaar</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12" id="error" data-bind="flash: $root.lastError"></div>
            </div>
            <div class="row">
                <div class="col-xs-12" id="warning" data-bind="flash: $root.lastWarning"></div>
            </div>
            <div class="row">
                <div class="col-xs-12" id="success" data-bind="flash: $root.lastSuccess"></div>
            </div>
            <div id="teamListId" class="row hidden-xs hidden-sm">
                <!-- ko if: $root.filteredTeams().length != 0 -->
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th class="col-md-1">Team</th>
                        <th class="col-md-4">Papieren ploeg</th>
                        <th class="col-md-4">Effectieve ploeg (optioneel)</th>
                        <th class="col-md-1">Teamindex papieren ploeg</th>
                        <th class="col-md-1">Teamindex effectieve ploeg</th>
                        <th class="col-md-1"></th>
                    </tr>
                    </thead>
                    <tbody data-bind="foreach: filteredTeams">
                    <tr>
                        <td style="padding:0px">
                            <div>
                                <span class="label label-default" data-bind="text: teamName"></span>
                            </div>
                        </td>
                        <td style="padding:0px">
                            <div data-bind="sortable: {data : playersInTeam, allowDrop: allowMorePlayers,beforeMove: $root.verifyAssignments,afterMove: $root.verifyAssignmentsAfterMove}" class="baseTeam">
                                <div>
                                    <span data-bind="text: fullName"></span>,
                                    <span data-bind="text: fixedIndexInsideTeam($parent.teamType)"></span>
                                    <div class="pull-right">
                                        <a href="#" data-bind="click: $parent.removePlayer">
                                            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:0px">
                            <div data-bind="sortable: {data : realPlayersInTeam, allowDrop: true,beforeMove: $root.verifyAssignmentsRealPlayer, afterMove: $root.verifyAssignmentsAfterMove}" class="baseTeam">
                                <div>
                                    <span data-bind="text: fullName"></span>,
                                    <span data-bind="text: fixedIndexInsideTeam($parent.teamType)"></span>
                                    <div class="pull-right">
                                        <a href="#" data-bind="click: $parent.removeRealPlayer">
                                            <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <span data-bind="text: totalFixedIndexInsideTeamLayout"></span>
                            </div>
                        </td>
                        <td>
                            <div>
                                <span data-bind="text: totalFixedIndexInsideTeamForRealPlayersLayout"></span>
                            </div>
                        </td>
                        <td>
                            <div class="pull-right">
                                <a href="#" data-bind="click: $root.removeTeam">
                                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <!-- /ko -->
                <!-- ko if: $root.filteredTeams().length == 0 -->
                <br>
                <div class="well">
                    <span data-bind="text: $root.noTeamsLayout"></span>
                </div>
                <!-- /ko -->
            </div>
        </div>
    </div>
@endsection

@section('printable')

@endsection

@section('tailscripts')
    <script type="text/javascript" src="builderBasisPloegen.js" charset="utf-8"></script>
@endsection