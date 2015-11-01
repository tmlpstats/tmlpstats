@extends('template')

@section('content')
<h2>Add a Center</h2>

@include('errors.list')

{!! Form::open(['url' => 'admin/centers', 'class' => 'form-horizontal']) !!}

    @include('admin.centers.form', ['submitButtonText' => 'Create'])

{!! Form::close() !!}

@endsection
