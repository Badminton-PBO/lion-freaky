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
                    <div class="col-xs-12 col-sm-6">
                        <h2>Basisploegen</h2>
                    </div>
                    <div class="col-xs-12 col-sm-6" style="padding-top:10px; padding-bottom:15px">
                        <div id="addH" class='label label-default' style='font-size:30px;margin-right:10px;'>
                            <a href="#" data-bind="click: function(data, event) { addTeam('H', data, event) }">
                                    <span class="glyphicon glyphicon-plus"></span> H
                            </a>
                        </div>
                        <div id="addD" class='label label-default' style='font-size:30px;margin-right:10px;'>
                            <a href="#" data-bind="click: function(data, event) { addTeam('D', data, event) }">
                                <span class="glyphicon glyphicon-plus"></span> D
                            </a>
                        </div>
                        <div id="addG" class='label label-default' style='font-size:30px;margin-right:10px;'>
                            <a href="#" data-bind="click: function(data, event) { addTeam('G', data, event) }">
                                <span class="glyphicon glyphicon-plus"></span> G
                            </a>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12" id="error" data-bind="flash: lastError"></div>
            </div>
            <div class="row hidden-xs hidden-sm">
                <table class="table table-condensed">
                    <thead>
                    <tr>
                        <th class="col-md-2">Team</th>
                        <th class="col-md-7">Spelers</th>
                        <th class="col-md-2">Totale index</th>
                        <th class="col-md-1"></th>
                    </tr>
                    </thead>
                    <tbody data-bind="foreach: teams">
                    <tr>
                        <td style="padding:0px">
                            <div>
                                <span class="label label-default" data-bind="text: teamName"></span>
                            </div>
                        </td>
                        <td style="padding:0px">
                            <div data-bind="sortable: {data : playersInTeam, allowDrop: allowMorePlayers,beforeMove: $root.verifyAssignments}" class="baseTeam">
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
            </div>
        </div>
    </div>
@endsection

@section('printable')

@endsection

@section('tailscripts')
    <script type="text/javascript" src="builderBasisPloegen.js" charset="utf-8"></script>
@endsection