@extends('template')

@section('content')
<h2>{{ $statsReport->reportingDate->format('F j, Y') }}</h2>

@include('errors.list')

{!! Form::model($statsReport, ['url' => 'admin/statsreports/' . $statsReport->reportingDate, 'method' => 'PUT', 'class' => 'form-horizontal']) !!}

    @include('statsreport.form', ['submitButtonText' => 'Update'])

{!! Form::close() !!}

@stop
