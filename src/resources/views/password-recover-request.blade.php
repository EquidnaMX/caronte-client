@extends('layouts.login')

@section('title-form', 'Recupera tu contraseña')

@section('content')
    <form method="POST">
        @csrf
        <div class="form-group mt-3 text-center">
            <label for="email">Correo electrónico:</label>
            <input class="form-control mt-2" type="email" name="email" required autofocus>
            <input type="hidden" name="callback_url" value="{{ Request::get('callback_url') }}">
        </div>
        <div class="form-group mt-4 d-flex justify-content-center">
            <button type="submit" class="btn btn-primary">Enviar</button>
        </div>
    </form>
@endsection
