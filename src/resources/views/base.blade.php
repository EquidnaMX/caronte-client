<!doctype html>
<html lang="{{ app()->getLocale() }}">
    
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/assets/icon.png" type="image/icon type">    
    <title>{{ config('app.name') }}</title>
</head>

<body>
  @include('caronte::messages')
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
