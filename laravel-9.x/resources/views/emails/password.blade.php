@extends('emails.pbomail')

@section('subject')
    Wijzig je PBO app wachtwoord
@endsection

@section('content')
    Gebruik volgende link om je wachtwoord te resetten.<br/>
    <a href="{{ url('password/reset/'.$token) }}">{{ url('password/reset/'.$token) }}</a>

    Negeer deze email indien u geen aanvraag heeft gedaan om je wachtwoord te resetten.
@endsection



