@extends('template')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="{{ url('/auth/login') }}" class="btn btn-lg {{ Request::is('auth/login') ? 'btn-primary' : '' }}" role="button">Login</a>
                    <a href="{{ url('/auth/register') }}" class="btn btn-default btn-lg {{ Request::is('auth/register') ? 'btn-primary' : '' }}" role="button">Register</a>
                </div>
                <div class="panel-body">
                    @include('errors.list')

                    @yield('auth.form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
