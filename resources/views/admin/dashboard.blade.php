@extends('template')

@section('content')
<h1>Dashboard</h1>
<ul>
    <li><a href="{{ url('/admin/centers') }}">View Centers</a></li>
    <li><a href="{{ url('/admin/quarters') }}">View Quarters</a></li>
    <li><a href="{{ url('/admin/users') }}">View Users</a></li>
    <li><a href="{{ url('/admin/import') }}">Import Previous Quarter Stats Sheets</a></li>
</ul>
@endsection