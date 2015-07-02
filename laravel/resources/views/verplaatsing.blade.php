@extends('pboapp')

@section('heads')
    <!-- Knockoutjs -->
    <script type="text/javascript" src="libs/js/knockout-3.2.0.js"></script>


    <!-- DateTimePicker stuff -->
    <script type="text/javascript" src="libs/js/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="libs/js/bootstrap-datetimepicker-4.0.0/bootstrap-datetimepicker.min.js"></script>
    <link rel="stylesheet" href="libs/js/bootstrap-datetimepicker-4.0.0/bootstrap-datetimepicker.min.css" />


    <script>
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
        .selectedMeeting {
            font-weight: bold;
        }
    </style>

@endsection


@section('help')
<div class="col-xs-2">
    <div class="pull-right">
        <button id="nonplayersbutton" type="button" class="btn btn-primary" data-toggle="modal" data-target="#myHelpModal" data-theVideo="https://www.youtube.com/embed/GCQUNj13kPo">
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
                        <p>Gelieve problemen met deze tool te melden via <a href="mailto:competitie@badminton-pbo.be?SUBJECT=Online%20Verplaatsing">competitie[at]badminton-pbo.be</a></p>
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
    <!-- ko if: !$root.chosenTeam() -->
    <div class="well">
        <h1>Doelstelling</h1>
        Begin augustus worden jaarlijks de nieuwe speeldata vrijgegeven door PBO. Tussen dit moment en voor aanvang van de eerste speeldag van de competitie (circa 1 sept.) krijgen de clubs/ploegen de kans om speelmomenten die minder gepast zijn te wisselen in samenspraak met de tegenstander van de desbetreffende wedstrijd.
        Tot voor lancering van deze applicatie kon iedereen dit via zijn eigen methode, wat zijn voor- en nadelen had.
        Omdat het voor PBO en sommigen soms moeilijk was om het overzicht te bewaren in sommige ellenlange emailconversaties werd deze applicatie geschreven. Deze applicatie zou vrij eenvoudig te gebruiken moeten zijn maar als je er toch nog vragen hebt of ergens vastzit, aarzel dan niet om contact op te nemen via email op  <a href="mailto:competitie@badminton-pbo.be?SUBJECT=Online%20Verplaatsing">competitie[at]badminton-pbo.be</a>
    </div>
    <!-- /ko -->
    <div class="row hidden-print">
        <div class="col-xs-12">
            <p data-bind="with: chosenClub">
                Kies een team om andere ontmoetingen te wijzigen
                <select data-bind="options: teams.filter(function(team) { return team.type != 'LIGA'}),
                                       optionsText: 'teamName',
                                       value: $parent.chosenTeam,
                                       optionsCaption: $root.selectTeamCaption()"></select>
            </p>
            <div data-bind="if:($root.chosenClub() && !($root.chosenTeam()) && $root.chosenClub().openRequests.length > 0)">
                <h2>Ontmoeting die actie vereisen van <span data-bind="text: $root.chosenClub().clubName"></span></h2>
                <div class="panel panel-default">
                    <table class="table table-striped table-condensed" style="table-layout:fixed">
                        <thead>
                            <tr>
                                <th>Ontmoeting</th>
                                <th>Huidig tijdstip</th>
                                <th>Actie bij</th>
                            </tr>
                        </thead>
                        <tbody data-bind="foreach: $root.chosenClub().openRequests">
                            <tr>
                                <td><a data-bind="click: $root.goToOpenRequest"><span data-bind="text:hTeamName"/></span>-<span data-bind="text:oTeamName"></span></a></td>
                                <td><span data-bind="text: date"></span> </td>
                                <td><span data-bind="text: actionFor"></span> </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div data-bind="if: $root.chosenTeam()">
                <div class="panel panel-default">
                    <table class="table table-striped table-condensed" style="table-layout:fixed">
                        <thead>
                        <tr>
                            <th>Ontmoeting</th>
                            <th>Huidig tijdstip</th>
                            <th>Status</th>
                            <th>Actie bij</th>
                        </tr>
                        </thead>
                    </table>
                    <div data-bind="style: { height: $root.chosenMeeting() ? '30vh' : '60vh', overflow: 'auto'}">
                        <table class="table table-striped table-condensed" style="table-layout:fixed">
                            <tbody data-bind="foreach: availableMeetings">
                            <tr data-bind="css: { selectedMeeting: $root.chosenMeeting() && ($root.chosenMeeting().matchIdExtra == matchIdExtra) }">
                                <td><a data-bind="click: $root.chosenMeeting"><span data-bind="text:hTeam"></span> - <span data-bind="text:oTeam"></span></a></td>
                                <td><span data-bind="text:dateLayout"></span> <span data-bind="text:hourLayout"></span></td>
                                <td><span data-bind="text: dbStatus"></span>
                                    <!-- ko if: (dbStatus() == 'OVEREENKOMST') -->
                                    voor <span data-bind="text: finalDateTime"></span>
                                    <!-- /ko -->
                                </td>
                                <td><span data-bind="text: dbActionFor"></span></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12" data-bind="if: $root.chosenTeam()">
            <div data-bind="with: $root.chosenMeeting()">
                <div class="row row-fluid">
                    <div class="col-xs-12">
                        <h2>Verplaatsingsaanvraag <span data-bind="text:hTeam"></span> - <span data-bind="text:oTeam"></span></h2>
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
                                <span data-bind="visible: requestedByTeam() == $parent.hTeam">MOGELIJK</span>

                                <div data-bind="visible: requestedByTeam() != $parent.hTeam && requestedByTeam() != $root.chosenTeam().teamName && !(finallyChosen())">
                                    <select data-bind="options: proposalAcceptedStates, value: acceptedState"></select>
                                </div>

                                <span data-bind="text: acceptedState, visible: requestedByTeam() != $parent.hTeam && !(requestedByTeam() != $root.chosenTeam().teamName && !(finallyChosen()))"></span>

                            </td>
                            <td>
                                <span data-bind="visible: requestedByTeam() == $parent.oTeam">MOGELIJK</span>

                                <div data-bind="visible: requestedByTeam() != $parent.oTeam  && requestedByTeam() != $root.chosenTeam().teamName && !(finallyChosen())">
                                    <select data-bind="options: proposalAcceptedStates, value: acceptedState"></select>
                                </div>

                                <span data-bind="text: acceptedState, visible: requestedByTeam() != $parent.oTeam  && !(requestedByTeam() != $root.chosenTeam().teamName && !(finallyChosen()))"></span>

                            </td>
                            <td>
                                <input type="checkbox" data-bind="checked:finallyChosen, enable: isCheckFinalAllowed"/>
                            </td>
                            <td>
                                <button class="button" data-bind="click: $parent.removeProposal, visible: requestedByTeam() == $root.chosenTeam().teamName && acceptedState() == '-'">
                                    <span class="glyphicon glyphicon-trash"></span>
                                </button>
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


                        <div data-bind="visible: isAddProposalAllowed">
                            <button type="button" class="btn btn-primary start" id="add" data-bind="click: $root.addNewProposal"><span data-bind="text: $root.giveAddNewProposalButtonText"></span></button>
                        </div>

                    </div>

                    <div class="col-xs-offset-8 col-xs-3" style="padding-top:10px; padding-bottom:15px">
                        <div data-bind="visible : isSaveAndSendAllowed">
                            <button type="button" class="btn btn-primary start" id="saveAndSend" data-loading-text="Verwerking..." data-bind="click:$root.send"><span data-bind="text: $root.giveSaveAndSendButtonText"/></span> <span class="glyphicon glyphicon-send" aria-hidden="true"></span></button>
                        </div>
                    </div>
                </div>
                <div class="row row-fluid">
                    <div class="col-xs-12">
                        <div class="well col-xs-5">
                            <h4>Commentaar <span data-bind="text: hTeam"></span> (optioneel)</h4>
                            <textarea rows="10" style="width: 100%" data-bind="value: hTeamComment, enable:  $root.chosenTeam().teamName == hTeam" maxlength="2500"></textarea>
                        </div>
                        <div class="well col-xs-5 col-xs-offset-2">
                            <h4>Commentaar <span data-bind="text: oTeam"></span> (optioneel)</h4>
                            <textarea rows="10" style="width: 100%" data-bind="value: oTeamComment, enable:  $root.chosenTeam().teamName == oTeam" maxlength="2500"></textarea>
                        </div>
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