@extends('template')

@section('content')
    <h1>{{ $title }}</h1>

    <video src="https://tmlpstats.crast.us/videos/{{ $file }}" style="width: 100%" controls>
    </video>

    <a href="{{ action('HelpController@index') }}">Back to video listing</a>

@endsection
