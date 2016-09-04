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
            <div id="playerListId" class="well well-sm" style="">
                <table class="table table-bordered table-condensed">
                    <thead>
                    <tr>
                        <th>Speler</th>
                        <th class="playerDetail">vast index E,D,G</th>
                    </tr>
                    </thead>
                    <tbody data-bind="foreach: availablePlayers">
                    <tr>
                        <td data-bind="draggable: $data"><span class="glyphicon glyphicon-user"></span> <span data-bind="text: fullName" style="color:#428bca"></span></td>
                        <td data-bind="text: fixedRankingLayout('G')" class="playerDetail"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-7">
            <div id="ploegopstelling">
                <div class="row row-fluid">
                    <div class="col-xs-12 col-sm-12">
                        <h2>Basisploegen</h2>
                        <ul class="nav nav-tabs nav-justified">
                            <li class="active"><a  href="#H" data-bind="click: function(data, event) { showTeams('H', data, event) }">Heren (<span data-bind="text: $root.numberOfTeamsOfTeamType('H')"></span>)</a></li>
                            <li><a href="#D" data-bind="click: function(data, event) { showTeams('D', data, event) }">Dames (<span data-bind="text: $root.numberOfTeamsOfTeamType('D')"></span>)</a></li>
                            <li><a href="#G" data-bind="click: function(data, event) { showTeams('G', data, event) }">Gemengd (<span data-bind="text: $root.numberOfTeamsOfTeamType('G')"></span>)</a></li>
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Acties<span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li>Ploeg toevoegen</li>
                                    <li><a href="#" data-bind="click: function(data, event) { addTeam('H', data, event) }">Herenploeg</a></li>
                                    <li><a href="#" data-bind="click: function(data, event) { addTeam('D', data, event) }">Damesploeg</a></li>
                                    <li><a href="#" data-bind="click: function(data, event) { addTeam('G', data, event) }">Gemengde ploeg</a></li>
                                    <li>Bewaar</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12" id="error" data-bind="flash: lastError"></div>
            </div>
            <div class="row hidden-xs hidden-sm">
                <!-- ko if: $root.filteredTeams().length != 0 -->
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th class="col-md-2">Team</th>
                        <th class="col-md-7">Spelers</th>
                        <th class="col-md-2">Team index</th>
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
                                    <span data-bind="text: vblId"></span>,
                                    <span data-bind="text: fixedIndexInsideTeam($parent.teamType)"></span>
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
                                <span data-bind="text: totalFixedIndexInsideTeamLayout"></span>
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