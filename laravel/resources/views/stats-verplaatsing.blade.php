@extends('pboapp')

@section('heads')
    <!-- Knockoutjs -->
    <script type="text/javascript" src="../libs/js/knockout-3.2.0.js"></script>

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
                    <td><span data-bind="text: oTeamName"></span></td>
                    <td><span data-bind="text: hTeamName"></span></td>
                    <td><span data-bind="text: date"></span></td>
                    <td><span data-bind="text: proposedDate"></span></td>
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
                <td><span data-bind="text: oTeamName"></span></td>
                <td><span data-bind="text: hTeamName"></span></td>
                <td><span data-bind="text: date"></span></td>
                <td><span data-bind="text: max_requested_on"></span></td>
                <td><span data-bind="text: actionFor"></span></td>
            </tr>
            </tbody>
        </table>
    </div>


@endsection

@section('tailscripts')
    <script type="text/javascript" src="../stats-verplaatsing.js"></script>
@endsection