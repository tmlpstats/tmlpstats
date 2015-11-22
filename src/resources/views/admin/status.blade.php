@extends('template')

@section('content')
<h1>Status</h1>

<h2>Active Users</h2>
<h4>As of {{ \Carbon\Carbon::now(Auth::user()->center->timezone)->format('M g, Y \a\t g:i a') }}</h4>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
        <tr>
            <th>Name</th>
            <th>Request</th>
            <th>Request Start</th>
            <th>Duration</th>
            <th>Time Since</th>
        </tr>
        </thead>
        @foreach ($sessions as $session)
            <tr>
                <td><a href="{{ url("admin/users/{$session['user']->id}") }}">{{ $session['user']->firstName }} {{ $session['user']->lastName }}</a></td>
                <td>{{ $session['route'] }}</td>
                <td>{{ $session['start']->format('g:i:s a') }}</td>
                <td>{{ $session['end'] ? $session['start']->diffInSeconds($session['end']) . ' seconds' : '' }}</td>
                <td>{{ ($session['end'] ? \Carbon\Carbon::now()->diffInMinutes($session['end']) : \Carbon\Carbon::now()->diffInMinutes($session['start'])) . ' minutes' }}</td>
            </tr>
            @if(isset($session['previousRequests']))
            @foreach ($session['previousRequests'] as $previousSession)
                <tr>
                    <td><a href="{{ url("admin/users/{$session['user']->id}") }}">{{ $session['user']->firstName }} {{ $session['user']->lastName }}</a></td>
                    <td>{{ $previousSession['route'] }}</td>
                    <td>{{ $previousSession['start']->format('g:i:s a') }}</td>
                    <td>{{ $previousSession['end'] ? $previousSession['start']->diffInSeconds($previousSession['end']) . ' seconds' : '' }}</td>
                    <td>{{ ($session['end'] ? \Carbon\Carbon::now()->diffInMinutes($session['end']) : \Carbon\Carbon::now()->diffInMinutes($session['start'])) . ' minutes' }}</td>
                </tr>
            @endforeach
            @endif
        @endforeach
    </table>
</div>

@endsection
