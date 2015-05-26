<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>PBO apps</title>

	<link href="{{ asset('/css/app.css') }}" rel="stylesheet">

    @yield('heads')

    <!-- Bootstrap -->
    <script type="text/javascript" src="libs/bootstrap-3.3.1-dist/js/bootstrap.min.js"></script>
    <link href="libs/bootstrap-3.3.1-dist/css/bootstrap.min.css" rel="stylesheet">

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

            @yield('help')
        </div>

	    @yield('content')

    </div>
    @yield('printable')
    @yield('tailscripts')
</body>
</html>
