@extends('emails.pbomail')

@section('subject')
    Verplaatsings aanvraag {{$hTeam}} - {{$oTeam}}
@endsection

@section('content')
    Beste,
    <br/><br/>

    {{$requester}} diende een verzoek in om de wedstrijd "{{$hTeam}} - {{$oTeam}}" van "{{$dateTimeLayout}}" te verplaatsen.<br/>
    U kan de data bekijken en bevestigen of een nieuw tegenvoorstel indienen via <a href="{!! $link !!}">{!! $link !!}</a>
@endsection
