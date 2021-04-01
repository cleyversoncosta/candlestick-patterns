<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Drop Semijóias') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <script src="https://cdn.anychart.com/releases/v8/js/anychart-base.min.js"></script>
    <script src="https://cdn.anychart.com/releases/v8/js/anychart-ui.min.js"></script>
    <script src="https://cdn.anychart.com/releases/v8/js/anychart-exports.min.js"></script>
    <script src="https://cdn.anychart.com/releases/v8/js/anychart-stock.min.js"></script>
    <script src="https://cdn.anychart.com/releases/v8/js/anychart-data-adapter.min.js"></script>
    <script src="https://cdn.anychart.com/releases/v8/themes/dark_blue.min.js"></script>

    <link href="https://cdn.anychart.com/releases/v8/css/anychart-ui.min.css" type="text/css" rel="stylesheet">
    <link href="https://cdn.anychart.com/releases/v8/fonts/css/anychart-font.min.css" type="text/css" rel="stylesheet">


    <style type="text/css">
        html,
        body,
        #container {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .anychart-tooltip {
            width: 300px;
            background: white;
            color: black;
            box-shadow: 5px 5px 18px #333;
        }

        .anychart-tooltip h3 {
            text-align: center;
        }

        .anychart-tooltip img {
            max-height: 50px;
            display: block;
            margin: 0 auto 10px;
        }

        .anychart-tooltip table {
            width: 100%;
        }

        .anychart-tooltip table tr td:last-child {
            text-align: right;
        }

        .anychart-tooltip .k-square {
            color: #67b6f4;
        }

        .anychart-tooltip .d-square {
            color: #428cd6;
        }

        .anychart-tooltip .j-square {
            color: #ed6c20;
        }

    </style>
    <!-- Styles -->
    <link href="{{ mix('assets/css/app.css') }}" rel="stylesheet">

    @yield('head')

    <script type="text/javascript">
        var base_url = "{{ URL::to('/') }}";

    </script>


    @livewireStyles
</head>

<body>

    @if (Auth::user()->deleted)
        <div class="container mt-5">
            <div class="alert alert-warning" role="alert">
                Sua conta foi programada para exclusão. Todos os seus dados serão removidos em até 2 semanas.
            </div>
        </div>
    @else
        <div class="container-fluid">
            <div class="row">

                @if (Session::get('alert_message'))
                    <div class="col-12">
                        <div class="alert alert-{{ Session::get('alert_message_type') }} mt-3" role="alert">
                            {{ Session::get('alert_message') }}
                        </div>
                    </div>
                @endif


                @yield('body')

            </div>
        </div>
    @endif

    <!-- Scripts -->
    <script src="{{ mix('assets/js/vendor.js') }}"></script>
    <script src="{{ mix('assets/js/app.js') }}"></script>

    <script>
        Echo.channel('update_chart_event_{{ $userChart->id }}')
            .listen('UpdateChartEvent', (e) => {
                dataTable.addData([e.candle]);
            });
    </script>

    @livewireScripts

    @yield('footer')
</body>

</html>
