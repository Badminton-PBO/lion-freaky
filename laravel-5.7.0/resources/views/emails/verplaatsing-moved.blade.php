@extends('emails.pbomail')

@section('subject')
    Verplaatsings aanvraag {{$hTeam}} - {{$oTeam}} verwerkt
@endsection

@section('content')
    Beste,
    <br/><br/>
    De wedstrijd "{{$hTeam}} - {{$oTeam}}" is verplaatst op toernooi.nl naar "{{$dateTimeLayout}}" <br/>
@endsection
