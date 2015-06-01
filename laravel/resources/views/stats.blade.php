@extends('pboapp')

@section('heads')
    <!-- d3 -->
    <script type="text/javascript" src="libs/js/d3.v3.min.js"></script>

    <!-- Knockoutjs -->
    <script type="text/javascript" src="libs/js/knockout-3.2.0.js"></script>

    <style>

        .bar {
            fill: steelblue;
        }

        .bar:hover {
            fill: brown;
        }

        .axis {
            font: 10px sans-serif;
        }

        .axis path,
        .axis line {
            fill: none;
            stroke: #000;
            shape-rendering: crispEdges;
        }

        .x.axis path {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container" id="graphX">
        <h2>Number of teamselects & print commands per week</h2>
    </div>
    <div class="container">
        <h2>Total number of teamselects & print commands per team</h2>
        <table class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th>Club</th>
                    <th>Team</th>
                    <th>#select</th>
                    <th>#print</th>
                </tr>
            </thead>
            <tbody data-bind="foreach: totalSelectAndPrintCmdPerTeam">
                <tr>
                    <td><span data-bind="text: clubName"></span></td>
                    <td><span data-bind="text: teamName"></span></td>
                    <td><span data-bind="text: select"></span></td>
                    <td><span data-bind="text: print"></span></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection

@section('tailscripts')
    <script type="text/javascript" src="stats.js"></script>
@endsection