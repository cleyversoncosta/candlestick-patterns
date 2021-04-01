@extends('user.base')


@section('footer')

    <style type="text/css">
        #chart {
            width: 100%;
            height: 650px;
            margin: 0;
            padding: 0;
        }

    </style>

    <script>
        const symbol = "{{ Request::get('symbol') }}";

        const timeframe = "{{ strtolower(Request::get('timeframe')) }}";
        let timeframe_qty = 120;
        let timeframe_period = 'hour';

        switch (timeframe) {
            case 'm15':
                timeframe_qty = 300;
                break;
            case 'm30':
                timeframe_qty = 600;
                break;
            case 'h1':
                timeframe_qty = 1200;
                break;
        }

        var dataTable;

        anychart.onDocumentReady(function() {
            // set chart theme
            //anychart.theme('darkBlue');

            anychart.data.loadJsonFile(
                "{{ action('API\APIV1Controller@ohlc') }}?{!! Arr::query(Request::all()) !!}",
                function(data) {

                    // create data table on loaded data
                    dataTable = anychart.data.table();
                    dataTable.addData(data);

                    // map loaded data
                    var mapping = dataTable.mapAs({
                        open: 1,
                        high: 2,
                        low: 3,
                        close: 4,
                    });
                    mapping.addField('risingFill', 7);
                    mapping.addField('risingStroke', 8);
                    mapping.addField('fallingFill', 8);
                    mapping.addField('fallingStroke', 8);

                    var volumeMapping = dataTable.mapAs({
                        value: 6
                    });

                    // create stock chart
                    var chart = anychart.stock(true);
                    chart.padding().left(70);

                    // create value plot on the chart
                    var plot = chart.plot(0);
                    plot.yGrid(true).yMinorGrid(false);

                    // create candlestick series
                    var series = plot.candlestick(mapping);
                    series.name(symbol);

                    // set max labels settings
                    series
                        .maxLabels()
                        .enabled(false)
                        .position('high')
                        .fontWeight('bold')
                        .fontColor('#6da632');

                    // set min labels settings
                    series
                        .minLabels()
                        .enabled(false)
                        .position('low')
                        .fontWeight('bold')
                        .fontColor('#d81e05');

                    // create volume plot on the chart
                    var volumePlot = chart.plot(1);
                    volumePlot.height('20%');
                    volumePlot.column(volumeMapping).name('Volume');
                    // set yAxis labels format
                    volumePlot
                        .yAxis()
                        .labels()
                        .format('{%Value}{scale: (1000000)|(mln)}');
                    // set crosshair yLabel format
                    volumePlot
                        .crosshair()
                        .yLabel()
                        .format('{%Value}{scale: (1000000)|(mln), decimalsCount: 0}');

                    anychart.data.loadJsonFile(
                        "{{ action('API\APIV1Controller@events') }}?{!! Arr::query(Request::all()) !!}",
                        function(eventMarkersCandlePattern) {

                            // add event markers
                            plot.eventMarkers({
                                "groups": eventMarkersCandlePattern
                            });

                            plot.eventMarkers().tooltip().titleFormat(function() {
                                return this.getData("short_desc");
                            });

                            plot.eventMarkers().tooltip().format(function() {
                                return this.getData("description");
                            });


                            eventMarkersCandlePattern.forEach((group) => {
                                group.data.forEach((item) => {
                                    let event_markers_candle_pattern = document.getElementById("event_markers_candle_pattern");
                                    event_markers_candle_pattern.innerHTML += item.date + ' - ' + item.short_desc + ' - <a target=\'blank\' href=' + item.description + '>LINK</a><br>';
                                })
                            })

                            // create scroller series with mapped data
                            chart.scroller().candlestick(mapping);
                            // set container id for the chart
                            chart.container('chart');
                            // initiate chart drawing
                            chart.draw();

                            // set values for selected range
                            chart.selectRange(timeframe_period, timeframe_qty, 'last-date');

                            // create range picker
                            //var rangePicker = anychart.ui.rangePicker();
                            // init range picker
                            //rangePicker.render(chart);

                            // create range selector
                            var rangeSelector = anychart.ui.rangeSelector();
                            // init range selector
                            rangeSelector.render(chart);
                        });
                }
            );
        });

    </script>
@endsection


@section('body')

    <div class="col-12">


        <div class="col-12 p-2 mb-2 bg-dark">

            <form method="GET" action="{{ route('dashboard') }}">
                <div class="row">

                    <div class="col-12  col-md-2">
                        @livewire('graph-speed-control')
                    </div>

                    <div class="col-12  col-md-2">
                        {{ Form::date('period_ini', is_null(Request::input('period_ini')) ? env('GRAPH_DEFAULT_PERIOD_INI') : Request::input('period_ini'), ['id' => 'period_ini', 'class' => 'form-control', 'placeholder' => 'Data ínicio']) }}
                    </div>
                    <div class="col-12  col-md-2">
                        {{ Form::date('period_end', is_null(Request::input('period_end')) ? env('GRAPH_DEFAULT_PERIOD_END') : Request::input('period_end'), ['id' => 'period_end', 'class' => 'form-control', 'placeholder' => 'Data término']) }}
                    </div>

                    <div class="col-12  col-md-2">
                        {!! Form::select('symbol', $symbols, is_null(Request::input('symbol')) ? env('GRAPH_DEFAULT_SYMBOL') : Request::input('symbol'), ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-12  col-md-2">
                        {!! Form::select('timeframe', $timeframes, is_null(Request::input('timeframe')) ? env('GRAPH_DEFAULT_TIMEFRAME') : Request::input('timeframe'), ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-12  col-md-1">
                        <button class="btn btn-primary btn-block" type="submit">Selecionar</button>
                    </div>
                    <div class="col-12  col-md-1">
                        <a class="btn btn-light btn-block" href="{{ route('logout') }}">Sair</a>
                    </div>

                </div>
            </form>
        </div>

        <div id="chart"></div>

        <div>
            <div class="h3">Padrões encontrados</div>
            <div id="event_markers_candle_pattern"></div>
        </div>

    </div>

    <br><br><br>

@endsection
