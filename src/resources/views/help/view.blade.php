@extends('template')

@section('content')
    <h1>{{ $title }}</h1>
    <a href="{{ action('HelpController@index') }}"><< Back to video listing</a>
    <br/><br/>
    <video src="{{ $url }}" style="width: 100%" controls></video>
@endsection
