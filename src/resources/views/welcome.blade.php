@extends('template')

@section('headers')
<style rel="stylesheet">
    p {
        font-size: medium;
    }
</style>
@endsection

@section('content')
<h1>Welcome Statisticians</h1>
<div class="content">
    <p>You found the home of the Team, Managment, and Leadership Programs Statistics! Here you will find tools for checking your work,
        submitting, and viewing your team's statistics.</p>
    <p>If you already have an account, go ahead and <a href="{{ url('/auth/login') }}" class="btn btn-success">Login</a></p>
</div>
@endsection
