@extends('template')

@section('content')
<h2>{{ $quarter->start_weekend_date->format('M Y') }} - {{ $quarter->location }} Quarter</h2>


@include('errors.list')

{!! Form::model($quarter, ['url' => '/admin/quarters/' . $quarter->id, 'method' => 'PUT', 'class' => 'form-horizontal']) !!}

    @include('quarters.form', ['submitButtonText' => 'Update'])

{!! Form::close() !!}

@stop