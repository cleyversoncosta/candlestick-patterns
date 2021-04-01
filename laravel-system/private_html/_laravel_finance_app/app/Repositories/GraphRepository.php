<?php

namespace App\Repositories;

use App\Graph;
use Illuminate\Support\Arr;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Illuminate\Support\Collection;
use LupeCode\phpTraderNative\Trader;
use Illuminate\Database\Eloquent\Model;

class GraphRepository
{

    private $model;

    private $speed = 0;

    private $graphOnly = ['minute', 'open', 'high', 'low', 'close', 'trades', 'volume', 'risingFill', 'fallingFill'];

    public function __construct(Graph $model)
    {
        $this->model = $model;
    }

    public function find($id): Model
    {
        return $this->model->find($id);
    }


    public function getOHLCByDateRangeSymbolTimeframe(string $symbol, string $periodIni, string $periodEnd, string $timeframe, int $showed): Collection
    {

        $candles = $this->model->getOHLCByDateRangeSymbolTimeframe($symbol, $periodIni, $periodEnd, $timeframe, $showed);

        $candles = $candles->map(function ($item, $key) {
            return collect($item)->only($this->graphOnly)->toArray();
        });

        $candles = $this->colorCandlePattern($candles);

        foreach ($candles as $key => $value) {
            $candles[$key] = Arr::flatten($value);
        }

        return $candles;
    }


    public function getEvents(string $symbol, string $periodIni, string $periodEnd, string $timeframe, int $showed)
    {

        $candles = $this->model->getOHLCByDateRangeSymbolTimeframe($symbol, $periodIni, $periodEnd, $timeframe, $showed);

        return $this->createEvents($candles);
    }



    public function getNextNotShowed(string $symbol, string $periodIni, string $periodEnd, string $timeframe, int $showed, int $speed)
    {
        $candles = $this->model->getNextNotShowed($symbol, $periodIni, $periodEnd, $timeframe, $showed, $speed);

        $candles = $candles->map(function ($item, $key) {
            return collect($item)->only($this->graphOnly)->toArray();
        });

        return $candles;
    }

    public function reset(string $symbol, string $timeframe): void
    {
        $this->model->reset($symbol, $timeframe);
    }

    public function prepareParams($request)
    {
        $periodIni = strtoupper($request->period_ini);

        if (strlen($periodIni) == 0) {
            $periodIni = env('GRAPH_DEFAULT_PERIOD_INI');
        }

        $periodIni .= ' 00:00:00';

        $periodEnd = strtoupper($request->period_end);

        if (strlen($periodEnd) == 0) {
            $periodEnd = env('GRAPH_DEFAULT_PERIOD_END');
        }
        $periodEnd .= ' 23:59:59';

        $symbol = strtoupper($request->symbol);

        if (strlen($symbol) == 0) {
            $symbol = env('GRAPH_DEFAULT_SYMBOL');
        }

        $timeframe = strtoupper($request->timeframe);

        if (strlen($timeframe) == 0) {
            $timeframe = env('GRAPH_DEFAULT_TIMEFRAME');
        }

        return [
            'periodIni' => $periodIni,
            'periodEnd' => $periodEnd,
            'symbol' => $symbol,
            'timeframe' => $timeframe,
        ];
    }

    public function broadcastData($data)
    {

        $candles = $this->getNextNotShowed($data->symbol, $data->period_ini, $data->period_end, $data->timeframe, 0, $data->speed);

        $sleep = null;

        if ($candles->count() > 1) {
            // 1000000 = 1second
            $sleep = 1000000 / $candles->count();
        }

        foreach ($candles as $candle) {

            $candle = Arr::flatten(Arr::except($candle, ['id']));

            event(new \App\Events\UpdateChartEvent($data->id, $candle));

            if (!is_null($sleep)) {
                usleep($sleep);
            }
        }
    }

    public function updateSpeed($speed)
    {
        $this->speed = $speed;
    }


    public function getSpeed()
    {
        return $this->speed;
    }

    public function colorCandlePattern($candles)
    {

        $patterns = \App\GraphPattern::where('active', 1)->orderBy('reliability')->get();

        $trader = new Trader;

        foreach ($patterns as $keyA => $pattern) {

            $func = $pattern->name;

            $result = $trader->$func(Arr::pluck($candles, 'open'), Arr::pluck($candles, 'high'), Arr::pluck($candles, 'low'), Arr::pluck($candles, 'close'));

            for ($keyB = 0; $keyB < count($candles); $keyB++) {
                if ((isset($result[$keyB])) && ($result[$keyB] > 0)) {

                    $color['risingFill'] = $color['fallingFill'] = $this->setCandlePatternColor($pattern->signal, $pattern->pattern, $pattern->reliability, $pattern->is_up_down);

                    $candles[$keyB] = array_merge($candles[$keyB], $color);

                    /*
                    if ($pattern->amount > 1) {
                        for ($i=1; $i < $pattern->amount; $i++) { 
                            $candles[$keyB-$i] = array_merge($candles[$keyB-$i], $color);
                        }
                    }
                    */
                }
            }
        }

        return $candles;
    }

    public function createEvents($candles)
    {

        $patterns = \App\GraphPattern::where('active', 1)->orderBy('reliability')->get();

        $trader = new Trader;

        $eventsData = [];


        foreach ($patterns as $keyA => $pattern) {

            $func = $pattern->name;

            $result = $trader->$func(Arr::pluck($candles, 'open'), Arr::pluck($candles, 'high'), Arr::pluck($candles, 'low'), Arr::pluck($candles, 'close'));

            $letter = $this->setCandlePatternLetter($pattern->signal, $pattern->reliability);

            if ((empty($eventsData)) || (!$this->existFormat($eventsData, $letter))) {
                $eventsData[] = [
                    'format' => $this->setCandlePatternLetter($pattern->signal, $pattern->reliability),
                    'data' => []
                ];
            }

            for ($keyB = 0; $keyB < count($candles); $keyB++) {
                if ((isset($result[$keyB])) && ($result[$keyB] > 0)) {

                    foreach ($eventsData as $keyC => $value) {
                        if ($value['format'] == $letter) {
                            $eventsData[$keyC]['data'][] = [
                                "date" => $candles[$keyB]->minute,
                                "description" => $pattern->link,
                                "short_desc" => strtoupper("$pattern->text - $pattern->signal - $pattern->pattern - $pattern->reliability"),
                                "fill" => $this->setCandlePatternColor($pattern->signal, $pattern->pattern, $pattern->reliability, $pattern->is_up_down)
                            ];
                        }
                    }
                }
            }
        }

        return $eventsData;
    }

    function existFormat($eventsLetter, $letter)
    {

        foreach ($eventsLetter as $key => $value) {
            if ($value['format'] == $letter) {
                return true;
            }
        }

        return false;
    }

    function in_array_recursive($needle, $haystack)
    {
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($haystack));
        foreach ($it as $element) {
            if ($element == $needle) {
                return true;
            }
        }
        return false;
    }



    public function setCandlePatternColor($signal, $pattern, $reliability, $is_up_down)
    {

        if ($is_up_down) return 'blue';

        switch (strtolower($signal)) {
            case 'bullish': {
                    switch (strtolower($pattern)) {
                        case 'reversal':
                            return 'green';
                        case 'continuation':
                            return 'green';
                    }
                }
            case 'bearish': {
                    switch (strtolower($pattern)) {
                        case 'reversal':
                            return 'red';
                        case 'continuation':
                            return 'red';
                    }
                }
            case 'indecision': {
                    return 'yellow';
                }
        }
    }


    public function setCandlePatternLetter($signal, $reliability)
    {
        switch (strtolower($signal)) {
            case 'bullish': {
                    return 'A';
                }
            case 'bearish': {
                    return 'B';
                }
            case 'indecision': {
                    return 'I';
                }
        }
    }


    public function identifyCandlePatternMarkers($data)
    {

        $patterns = \App\GraphPattern::where('active', 1)->orderBy('reliability')->get()->toArray();

        $trader = new Trader;

        $markers = null;

        foreach ($patterns as $keyA => $p) {

            $func = $p['name'];
            $pattern = $trader->$func(Arr::pluck($data, 'open'), Arr::pluck($data, 'high'), Arr::pluck($data, 'low'), Arr::pluck($data, 'close'));

            foreach ($data as $keyB => $item) {
                $marker = [
                    'id' => $data[$keyB]['id']
                ];

                if ((isset($pattern[$keyB])) && ($pattern[$keyB] > 0)) {

                    $marker = array_merge($marker, [
                        'value' => $pattern[$keyB],
                        'position' => $p['position'],
                        'color' => $p['color'],
                        'shape' => $p['shape'],
                        'text' => $p['text'],
                        'size' => 1
                    ]);
                }

                if (($keyA == 0) || ((isset($pattern[$keyB])) && ($pattern[$keyB] > 0))) {
                    $markers[$keyB] = $marker;
                }
            }
        }



        return collect($markers);
    }
}
