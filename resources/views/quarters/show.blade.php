@extends('template')

@section('content')

<h1>{{ $quarter->distinction }} Quarter <small>{{ $quarter->location }}</small></h1>
<a href="{{ url('/admin/quarters') }}"><< See All</a><br/><br/>
<a href="{{ url('/admin/quarters/' . $quarter->id . '/edit') }}">Edit</a>

<div class="table-responsive">
    <table class="table table-condensed table-striped">
        <tr>
            <th>City:</th>
            <td>{{ $quarter->location }}</td>
        </tr>
        <tr>
            <th>Distinction:</th>
            <td>{{ $quarter->distinction }}</td>
        </tr>
        <tr>
            <th>Quarter Start:</th>
            <td>{{ $quarter->start_weekend_date->format('M d, Y') }}</td>
        </tr>
        <tr>
            <th>Classroom 1:</th>
            <td>{{ $quarter->classroom1_date->format('M d, Y') }}</td>
        </tr>
        <tr>
            <th>Classroom 2:</th>
            <td>{{ $quarter->classroom2_date->format('M d, Y') }}</td>
        </tr>
        <tr>
            <th>Classroom 3:</th>
            <td>{{ $quarter->classroom3_date->format('M d, Y') }}</td>
        </tr>
        <tr>
            <th>Quarter End:</th>
            <td>{{ $quarter->end_weekend_date->format('M d, Y') }}</td>
        </tr>
    </table>
</div>

@endsection