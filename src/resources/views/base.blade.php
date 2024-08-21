<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/assets/icon.png" type="image/icon type">
    <link rel="stylesheet" href="https://unpkg.com/css-skeletons@1.0.7/dist/css-skeletons.min.css" />        
    <title>{{ config('app.name') }}</title>
</head>

<body>
    @if (isset($errors))
        @if (count($errors->all()) > 0)
            <div class="alert alert-danger alert-dismissible fade show" id='messages'>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" id='messages'>
            {!! session('error') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (app('request')->input('_err'))
        <div class="alert alert-danger alert-dismissible fade show" id='messages'>
            {{ base64_decode(app('request')->input('_err')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" id='messages'>
            {!! session('warning') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (app('request')->input('_war'))
        <div class="alert alert-warning alert-dismissible fade show" id='messages'>
            {{ base64_decode(app('request')->input('_war')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" id='messages'>
            {!! session('success') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show" id='messages'>
            {!! session('info') !!}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (app('request')->input('_suc'))
        <div class="alert alert-success alert-dismissible fade show" id='messages'>
            {{ base64_decode(app('request')->input('_suc')) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif



    <main role="main">
        @yield('content')
    </main>
</body>

@stack('scripts')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const mess = document.querySelector(".alert.alert-success.alert-dismissible");
            if (mess) {
                mess.remove();
            }
        }, 3500)
    });
</script>

</html>
