<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'title') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ mix('assets/css/app.css') }}" rel="stylesheet">

    @yield('head')

    <script type="text/javascript">
        var base_url = "{{ URL::to('/') }}";
    </script>
</head>

<body>

    <main class="my-5">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <!-- Scripts -->
    <script src="{{ mix('assets/js/vendor.js') }}" ></script>
    <script src="{{ mix('assets/js/app.js') }}" ></script>

    @yield('js')

</body>

</html>
