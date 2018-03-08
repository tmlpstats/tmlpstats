@extends('template')

@section('content')
    <h2>Welcome {{ $invite->first_name }}</h2>

    <p>We aren't able to find your invitation. If you have already registered, please <a href="{{ url('/auth/login') }}" class="btn btn-success">Login</a></p>
@endsection




