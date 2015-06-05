@extends('emails.pbomail')

@section('subject')
    Verplaatsings aanvraag {{$hTeam}} - {{$oTeam}}
@endsection

@section('content')
    Beste,
    <br/><br/>

    U diende net een verzoek in om de wedstrijd "{{$hTeam}} - {{$oTeam}}" van "{{$dateTimeLayout}}" te verplaatsen.<br/>
    U kan de data nog altijd corrigeren via <a href="{!! $link !!}">{!! $link !!}</a>
@endsection
