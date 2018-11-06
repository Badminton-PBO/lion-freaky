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


@section('content')
		<div class="container">
			<div class="content">
                <div class="jumbotron">
                    <div class="span6 offset3">
                    <h1 style="text-align: center">PBO apps</h1>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                        <p><a class="btn btn-primary btn-lg" href="opstelling" role="button">Competitie opstellings formulier</a></p>
                        <p>Stel een geldige ploegopstelling samen</p>
                        </div>
                    </div>
                    <!--
                    <div class="row">
                        <div class="col-md-8">
                        <br><br>
                        <p><a class="btn btn-primary btn-lg" href="verplaatsing" role="button">Verplaatsing competitie match</a></p>
                        <p>Dien een aanvraag in tot verplaatsing van een competitie match</p>
                        </div>
                        <div class="col-md-4">
                            <div id="nonplayersbutton" class="btn" data-toggle="modal" data-target="#myHelpModal" data-theVideo="https://www.youtube.com/embed/koEOIdsnRSc" style="position: relative; left: 0; top: 0;">
                                <img src="images/VerplaatsingYoutube.png" width="300" style="position: relative; top: 0; left: 0;">
                                <div class="btn btn-primary" style="position: absolute; top: 50px; left: 140px;">
                                    <span class="glyphicon glyphicon-question-sign"  aria-hidden="true"></span>
                                </div>
                            </div>
                            <!- Help Modal --
                            <div class="modal fade" id="myHelpModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog" style="width: 830px">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Sluiten</span></button>
                                            <h4 class="modal-title" id="myModalLabel">Introductie</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>In onderstaande video wordt het gebruik van de verplaatsings module getoond.</p>
                                            <!-- iframe-scr will be set upon modal load to avoid unnecessary loadings when help button is not used--
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
                    -->

                    <div class="row">
                        <div class="col-md-8">
                            <p><a class="btn btn-primary btn-lg" href="agenda" role="button">Competitie agenda</a></p>
                            <p>Synchroniseer een competitie agenda per ploeg naar je smartphone.</p>
                        </div>
                    </div>
                </div>
			</div>
		</div>
@endsection