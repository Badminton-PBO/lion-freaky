@extends('emails.pbomail')

@section('subject')
    Verplaatsings aanvraag {{$hTeam}} - {{$oTeam}}
@endsection

@section('content')
    Beste,
    <br/><br/>

    {{$requester}} heeft zonet wijzigingen aangebracht aan de verplaatsingsaanvraag voor de ontmoeting "{{$hTeam}} - {{$oTeam}}" van "{{$dateTimeLayout}}" te verplaatsen.<br/>
    Gelieve te bevestigen en/of een tegenvoorstel in te dienen via <a href="{!! $link !!}">{!! $link !!}</a>
@endsection
