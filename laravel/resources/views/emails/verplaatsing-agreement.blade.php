@extends('emails.pbomail')

@section('subject')
    Verplaatsings aanvraag {{$hTeam}} - {{$oTeam}} bevestigd
@endsection

@section('content')
    Beste,
    <br/><br/>
    Beide ploegen hebben een overeenkomst bereikt om de wedstrijd "{{$hTeam}} - {{$oTeam}}" van "{{$dateTimeLayout}}" te verplaatsen naar<br/>
    "{{$proposedDateTimeLayout}}"
    <br>
    Bijgevolg zal deze wedstrijd binnenkort aangepast worden op toernooi.nl
@endsection
