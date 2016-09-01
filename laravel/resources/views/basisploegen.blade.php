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

        .playerDetail {
            font-size:10px;
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
    </div>
@endsection

@section('printable')

@endsection

@section('tailscripts')
    <script type="text/javascript" src="builderBasisPloegen.js" charset="utf-8"></script>
@endsection