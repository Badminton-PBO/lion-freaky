@extends('pboapp')

@section('heads')
    <!-- Knockoutjs -->
    <script type="text/javascript" src="../libs/js/knockout-3.2.0.js"></script>
    <script type="text/javascript" src="../libs/js/moment-with-locales.min.js"></script>

    <style>

        .bar {
            fill: steelblue;
        }

        .bar:hover {
            fill: brown;
        }

    </style>
@endsection

@section('content')
    <div class="container">
        <h2>Ontmoetingen waarbij actie bij PBO staat</h2>
        <span>Ofwel moet PBO data nog invoeren in toernooi.nl, ofwel moet er nog gewacht worden op dagelijke synchronisatie.</span>
        <table class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th>Thuis ploeg</th>
                    <th>Bezoekende ploeg</th>
                    <th>Huidig tijdstip</th>
                    <th>Nieuw overeengekomen tijdstip</th>
                </tr>
            </thead>
            <tbody data-bind="foreach: meetingsWithActionForPBO">
                <tr>
                    <td><span data-bind="text: hTeamName"></span></td>
                    <td><span data-bind="text: oTeamName"></span></td>
                    <td><span data-bind="text: moment(date,'YYYYMMDDHHmm').format('ddd D-M-YYYY HH:mm')"></span></td>
                    <td><span data-bind="text: moment(proposedDate,'YYYYMMDDHHmm').format('ddd D-M-YYYY HH:mm')"></span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="container">
        <h2>Aantal aanvragen die nog actie vereisen van een bepaalde club</h2>
        <table class="table table-bordered table-condensed">
            <thead>
            <tr>
                <th>Club code</th>
                <th>Club name</th>
                <th>Aantal onbeantwoorde aanvragen.</th>
            </tr>
            </thead>
            <tbody data-bind="foreach: meetingWithOpenRequestPerClub">
            <tr>
                <td><span data-bind="text: clubId"></span></td>
                <td><span data-bind="text: clubName"></span></td>
                <td><span data-bind="text: count"></span></td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="container">
        <h2>Ontmoetingen waarvoor een verplaatsingaanvraag nog lopende is</h2>
        <table class="table table-bordered table-condensed">
            <thead>
            <tr>
                <th>Thuis ploeg</th>
                <th>Bezoekende ploeg</th>
                <th>Huidig tijdstip</th>
                <th>Laatste actie genomen op</th>
                <th>Actie bij</th>
            </tr>
            </thead>
            <tbody data-bind="foreach: meetingWithOpenRequest">
            <tr>
                <td><span data-bind="text: hTeamName"></span></td>
                <td><span data-bind="text: oTeamName"></span></td>
                <td><span data-bind="text: moment(date,'YYYYMMDDHHmm').format('ddd D-M-YYYY HH:mm')"></span></td>
                <td><span data-bind="text: moment(max_requested_on,'YYYYMMDDHHmm').format('ddd D-M-YYYY HH:mm')"></span></td>
                <td><span data-bind="text: actionFor"></span></td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="container">
        <h2>Aantal verplaatste ontmoetingen welke volledig zijn verwerkt</h2>
        <table class="table table-bordered table-condensed">
            <thead>
            <tr>
                <th>Maand</th>
                <th>Aantal</th>
            </tr>
            </thead>
            <tbody data-bind="foreach: meetingsMovedPerMonth">
            <tr>
                <td><span data-bind="text: month"></span></td>
                <td><span data-bind="text: month_count"></span></td>
            </tr>
            </tbody>
        </table>
    </div>
@endsection

@section('tailscripts')
    <script type="text/javascript" src="../stats-verplaatsing.js"></script>
@endsection