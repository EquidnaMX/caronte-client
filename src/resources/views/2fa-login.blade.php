@extends('layouts.new_base')

@section('content')
    <div class="mt-5 col-6 mx-auto">
        {!! Form::open(['url' => URL::full(), 'method' => 'POST']) !!}
        <div class="form-group mt-5">
            <h4>Correo electrónico registrado</h4>
            <div class="d-flex flex-row">
                {{ Form::email('email', session('email'), ['class' => 'form-control', 'placeholder' => 'Correo electrónico']) }}
                {{ Form::submit('Entrar', ['class' => 'btn btn-success ml-2']) }}
            </div>
            {{ Form::hidden('callback_url') }}
        </div>
        {!! Form::close() !!}
    </div>
@endsection
