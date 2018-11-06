@extends('emails.pbomail')

@section('subject')
    Verplaatsings aanvraag {{$hTeam}} - {{$oTeam}}
@endsection

@section('content')
    Beste,
    <br/><br/>

    U hebt zonet wijzigingen aangebracht aan de verplaatsingsaanvraag voor de ontmoeting "{{$hTeam}} - {{$oTeam}}" van "{{$dateTimeLayout}}"<br/>
    U kan de data nog altijd corrigeren via <a href="{!! $link !!}">{!! $link !!}</a>
@endsection
