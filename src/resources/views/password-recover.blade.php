@extends('layouts.login')

@section('title-form', 'Hola ' . $user->name)

@section('content')
    <form method="POST">
        @csrf
        <div class="form-group mt-3 text-center">
            <label for="new_password">Nueva contraseña:</label>
            <input class="form-control mt-2" type="password" name="password" required autofocus>
        </div>
        <div class="form-group mt-3 text-center">
            <label for="new_password">Confirma tu contraseña:</label>
            <input class="form-control mt-2" type="password" name="password_confirmation" required>
            <input type="hidden" name="callback_url" value="{{ Request::get('callback_url') }}">
        </div>
        <div class="form-group mt-4 d-flex justify-content-center">
            <button type="submit" class="btn btn-primary">Enviar</button>
        </div>
    </form>
@endsection
