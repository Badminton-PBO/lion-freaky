@extends('pboapp')

@section('heads')
    <!-- Knockoutjs -->
    <script type="text/javascript" src="libs/js/knockout-3.2.0.js"></script>
    <script type="text/javascript" src="libs/knockout-sortable-master/build/knockout-sortable.min.js"></script>

    <!-- DateTimePicker stuff -->
    <script type="text/javascript" src="libs/js/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="libs/js/bootstrap-datetimepicker-4.0.0/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet" href="libs/js/bootstrap-datetimepicker-4.0.0/bootstrap-datetimepicker.min.css" />

    <style type="text/css">
        #message, #error, #success {
            font-size: 1em;
            margin-top: 10px;
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

        #success {
            background-color: #5cb85c;
            border-color: #4cae4c;
            color: #fff;
        }
    </style>

@endsection


@section('help')
    <div>
        <a class="btn btn-primary" href="auth/logout" role="button">
            <span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>
        </a>
    </div>
@endsection

@section('content')
    <!-- ko if: !$root.chosenTeam() -->
    <div class="well">
        <h1>Doelstelling</h1>
        Streamline verplaatsings aanvragen PBO competitie.
    </div>
    <!-- /ko -->
    <div class="row hidden-print">
        <div class="col-xs-12">
            <p data-bind="with: chosenClub">
                Kies je team:
                <select data-bind="options: teams.filter(function(team) { return team.type != 'LIGA'}),
                                       optionsText: 'teamName',
                                       value: $parent.chosenTeam,
                                       optionsCaption: 'Selecteer...'"></select>
            </p>
            <div data-bind="if: $root.chosenTeam()">
                <div class="well well-sm" data-bind="style: { height: $root.chosenMeeting() ? '30vh' : '60vh', overflow: 'auto'}">
                    <table class="table table-striped table-condensed">
                        <thead>
                        <tr>
                            <th>Ontmoeting</th>
                            <th>Laatst vastgelegd tijdstip</th>
                            <th>Status</th>
                            <th>Actie bij</th>
                        </tr>
                        </thead>
                        <tbody data-bind="foreach: availableMeetings">
                        <tr>
                            <td><a data-bind="click: $root.chosenMeeting"><span data-bind="text:hTeam"></span> - <span data-bind="text:oTeam"></span></a></td>
                            <td><span data-bind="text:dateLayout"></span> <span data-bind="text:hourLayout"></span></td>
                            <td><span data-bind="text: status"></span></td>
                            <td><span data-bind="text: actionFor"></span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-xs-12" data-bind="with: $root.chosenMeeting()">
            <div>
                <div class="row row-fluid">
                    <div class="col-xs-12">
                        <h2>Verplaatsing aanvraag <span data-bind="text:hTeam"></span> - <span data-bind="text:oTeam"></span></h2>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12" id="error" data-bind="flash: $root.lastError"></div>
                </div>
                <div class="row">
                    <div class="col-xs-12" id="success" data-bind="flash: $root.lastSuccess"></div>
                </div>

                <div class="row-fluid">
                    <table class="table table-condensed" data-bind="visible: proposedChanges().length > 0">
                        <thead>
                        <tr>
                            <th class="col-md-2">Voorstel van</th>
                            <th class="col-md-4">Tijdstip</th>
                            <th class="col-md-2">Beschikbaarheid <span data-bind="text:hTeam"></span></th>
                            <th class="col-md-2">Beschikbaarheid <span data-bind="text:oTeam"></span></th>
                            <th class="col-md-1">Uitgekozen</th>
                            <th class="col-md-1">&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody data-bind="foreach: proposedChanges">
                        <tr>
                            <td>
                                <span data-bind="text:requestedByTeam"></span>
                            </td>
                            <td>
                                <!-- ko ifnot: (requestedByTeam() == $root.chosenTeam().teamName && acceptedState() == '-') -->
                                <span data-bind="text: proposedDateTimeLayout"></span>
                                <!-- /ko -->

                                <!-- ko if: (requestedByTeam() == $root.chosenTeam().teamName && acceptedState() == '-') -->
                                <a class="input-group date">
                                    <input type="text" class="form-control" data-bind="datetimepicker: proposedDateTime" />
                                           <span class="input-group-addon">
                                                <span class="glyphicon glyphicon-calendar"></span>
                                           </span>
                                </a>
                                <!-- /ko -->

                            </td>
                            <td>
                                <!-- ko if: requestedByTeam() == $parent.hTeam -->
                                MOGELIJK
                                <!-- /ko -->
                                <!-- ko ifnot: requestedByTeam() == $parent.hTeam -->
                                <select data-bind="options:$root.proposalAcceptedStates, value: acceptedState, enable: requestedByTeam() != $root.chosenTeam().teamName && !(finallyChosen())"></select>
                                <!-- /ko -->
                            </td>
                            <td>
                                <!-- ko if: requestedByTeam() == $parent.oTeam -->
                                MOGELIJK
                                <!-- /ko -->
                                <!-- ko ifnot: requestedByTeam() == $parent.oTeam -->
                                <select data-bind="options:$root.proposalAcceptedStates, value: acceptedState, enable: requestedByTeam() != $root.chosenTeam().teamName && !(finallyChosen())"></select>
                                <!-- /ko -->

                            </td>
                            <td>
                                <input type="checkbox" data-bind="checked:finallyChosen, enable: isCheckFinalAllowed"/>
                            </td>
                            <td>
                                <!-- ko if: (requestedByTeam() == $root.chosenTeam().teamName && acceptedState() == '-') -->
                                <button class="button" data-bind="click: $parent.removeProposal">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </button>
                                <!-- /ko -->
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <div data-bind="visible: proposedChanges().length == 0">
                        Momenteel zijn er geen verplaatsings aanvragen voor deze ontmoeting.
                    </div>
                </div>
                <div class="row row-fluid">
                    <div class="col-xs-1" style="padding-top:10px; padding-bottom:15px">

                        <div id="add" class='btn btn-default' style='font-size:20px; margin-right:10px' data-bind="visible: isAddProposalAllowed">
                            <a href="#" data-bind="click: $root.addNewProposal">
                                Nieuwe aanvraag toevoegen</span>
                            </a>
                        </div>

                    </div>

                    <div class="col-xs-offset-8 col-xs-3" style="padding-top:10px; padding-bottom:15px">
                        <div id="send" class='btn btn-default' style='font-size:20px;margin-right:10px;'>
                            <a href="#" data-bind="click: $root.send">
                                Bewaar en verstuur <span class="glyphicon glyphicon-send" aria-hidden="true"></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row row-fluid">
                    <div class="well well-sm">
                        <h2>Commentaar</h2>
                        <table class="table table-condensed">
                            <thead>
                            <tr>
                                <th class="col-md-3">Door</th>
                                <th class="col-md-9">Commentaar</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- ko foreach: comments -->
                            <tr>
                                <td>
                                    <span data-bind="text:owner"></span>,<br><span data-bind="text:dateTime"></span>
                                </td>
                                <td>
                                    <span data-bind="text:text"></span>
                                </td>
                            </tr>
                            <!-- /ko -->
                            <tr>
                                <td>
                                    Voeg nieuw commentaar toe:
                                </td>
                                <td>
                                    <textarea data-bind="textInput: $root.newCommentText"></textarea>
                                    <div class='label label-default' style='font-size:30px'>
                                        <a href="#" data-bind="click: $root.addNewComment">
                                            <span class="glyphicon glyphicon-plus"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('printable')
@endsection

@section('tailscripts')
    <script type="text/javascript" src="builderVerplaatsing.js" charset="utf-8"></script>
@endsection