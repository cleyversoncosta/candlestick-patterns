<div class="bg-dark">
    <div class="container">
        <div class="row">
            <div class="col-3 py-3">
                <a href="{{ action('HomeController@index') }}">
                    {!! Html::image('assets/images/logo-small.png', config('app.name'), ['width' => '193', 'height' => '40', 'class' => 'd-none d-md-block']) !!}

                    {!! Html::image('assets/images/logo-small.png', config('app.name'), ['width' => '150', 'height' => '31', 'class' => 'd-block d-md-none d-lg-none']) !!}
                </a>
            </div>
            <div class="col-9 text-right">
                <a class="btn btn-dark mt-3" href="{{ route('register') }}">Cadastre-se</a>
                <a class="btn btn-primary mt-3" href="{{ route('login') }}">Entrar</a>
            </div>
        </div>
    </div>
</div>
