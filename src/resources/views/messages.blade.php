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


@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" id='messages'>
        {!! session('warning') !!}
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

@if (session('message'))
    <div class="alert alert-info alert-dismissible fade show" id='messages'>
        {!! session('message') !!}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (app('request')->input('_err'))
    <div class="alert alert-danger alert-dismissible fade show" id='messages'>
        {{ base64_decode(app('request')->input('_err')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (app('request')->input('_war'))
    <div class="alert alert-warning alert-dismissible fade show" id='messages'>
        {{ base64_decode(app('request')->input('_war')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (app('request')->input('_suc'))
    <div class="alert alert-success alert-dismissible fade show" id='messages'>
        {{ base64_decode(app('request')->input('_suc')) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif


