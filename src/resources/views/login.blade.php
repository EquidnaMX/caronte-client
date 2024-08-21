@extends('caronte::base')

@section('content')
    <div class="main-content">
        <form action="{{ URL::full() }}" method="POST">
            @csrf
            <div class="Wrapper__Login d-flex justify-content-center">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-3 col-md-2"></div>
                        <div class="col-lg-6 col-md-8 login-box">
                            <div class="col-lg-12 login-title py-5">
                                INICIAR SESIÓN
                            </div>
                            <div class="col-lg-12 login-form">
                                <div class="form-group">
                                    <label for="email" class="form-control-label">Correo electrónico</label>
                                    <input type="email" name="email" value="{{ session('email') }}" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="password" class="form-control-label">Contraseña</label>
                                    <input type="password" name="password" class="form-control">
                                    <a href="{{ config('caronte.URL') . 'password/recover?callback_url=' . base64_encode(config('app.url') . config('caronte.LOGIN_URL')) . '&application=' . base64_encode(config('app.name')) }}">¿Olvidaste tu contraseña?</a>
                                </div>
                                <div class="col-lg-12 loginbttm">
                                    <div class="col-lg-12 login-btm login-button">
                                        <input type="submit" value="Entrar" class="btn btn-outline-primary">
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection