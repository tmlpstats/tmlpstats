@extends('template')

@section('content')
<h2>{{ $center->name }} Center</h2>

@include('errors.list')

{!! Form::model($center, ['url' => 'admin/centers/' . $center->abbreviation, 'method' => 'PUT', 'class' => 'form-horizontal']) !!}

    @include('admin.centers.form', ['submitButtonText' => 'Update'])

{!! Form::close() !!}

@stop
