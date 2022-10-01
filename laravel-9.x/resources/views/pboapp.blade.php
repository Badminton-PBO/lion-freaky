<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
	<title>PBO apps</title>

    <link rel="shortcut icon" href="{{ asset('/images/favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('/images/favicon.ico') }}" type="image/x-icon">

    <!-- JQuery -->
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>

    <!-- Bootstrap -->
    <script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

    @yield('heads')

</head>
<body>

    <div class="container-fluid">
        <div class="row hidden-print">
            <div class="col-xs-10 hidden-xs">
                <a href="http://www.badminton-pbo.be/" target="_new"><img src="{{ asset('/images/logo.png') }}"></a>
            </div>
            <div class="col-xs-10 visible-xs">
                <a href="http://www.badminton-pbo.be/" target="_new"><img src="{{ asset('/images/logo_noText.png') }}"></a>
            </div>


            <div class="col-xs-2" style="padding-top: 10px">
                @if (!(Auth::guest()))
                        <a class="btn btn-primary" href="{{ route('logout') }}web" role="button" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                            <span class="glyphicon glyphicon-log-out" aria-hidden="true"></span>
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                @else
                        <a class="btn btn-primary" href="{{route('home')}}" role="button" >
                            <span class="glyphicon glyphicon-home" aria-hidden="true"></span>
                        </a>
                @endif
                @yield('help')
            </div>


        </div>

	    @yield('content')

    </div>

    @yield('printable')

    @yield('tailscripts')

</body>
</html>
