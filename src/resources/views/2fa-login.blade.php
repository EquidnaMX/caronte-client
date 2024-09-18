@extends('caronte::base')

@section('content')
    <div class="mt-5 col-6 mx-auto">
        <form method="POST">
            @csrf
            <div class="form-group mt-5">
                <h4>Correo electrónico registrado</h4>
                <div class="d-flex flex-row">
                    <input type="email" name="email" class="form-control" placeholder="Correo electrónico" value="{{ session('email') }}">
                    <input type="submit" value="Entrar" class="btn btn-success ml-2">
                </div>
                <input type="hidden" name="callback_url">
            </div>
        </form>
    </div>
@endsection
