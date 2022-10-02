@extends('pboapp')

@section('heads')
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
@endsection


@section('help')
    <div class="col-xs-2">
        <div class="pull-right">
            <button id="nonplayersbutton" type="button" class="btn btn-primary" data-toggle="modal" data-target="#myHelpModal" data-theVideo="https://www.youtube.com/embed/rfn2SO5XN6Q">
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
                            <p>In onderstaande video wordt de login procedure uitgelegd en de manier om paswoord te resetten.</p>
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
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Login</div>
                    <div class="panel-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <strong>Hmmm!</strong> Login problemen.<br><br>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="form-group">
                                <label class="col-md-4 control-label">E-Mail adres</label>
                                <div class="col-md-6">
                                    <input type="email" class="form-control" name="email" value="{{ old('email') }}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-md-4 control-label">Wachtwoord</label>
                                <div class="col-md-6">
                                    <input type="password" class="form-control" name="password">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Onthouden
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-4">
                                    <button type="submit" class="btn btn-primary">Login</button>

                                    <a class="btn btn-link" href="{{ route('password.request') }}">Wachtwoord vergeten?</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
