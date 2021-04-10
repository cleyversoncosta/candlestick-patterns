<?php

namespace App\Repositories;

use App\Graph;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LupeCode\phpTraderNative\Trader;
use Illuminate\Database\Eloquent\Model;

class GraphRepository
{

    private $model;

    private $graphOnly = ['minute', 'open', 'high', 'low', 'close', 'trades', 'volume', 'risingFill', 'fallingFill'];

    public function __construct(Graph $model)
    {
        $this->model = $model;
    }

    /**
     * find
     *
     * @param  mixed $id
     * @return Model
     */
    public function find($id): Model
    {
        return $this->model->find($id);
    }


    /**
     * getOHLCByDateRangeSymbolTimeframe
     *
     * @param  mixed $symbol
     * @param  mixed $periodIni
     * @param  mixed $periodEnd
     * @param  mixed $timeframe
     * @param  mixed $showed
     * @return Collection
     */
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


    /**
     * getEvents
     *
     * @param  mixed $symbol
     * @param  mixed $periodIni
     * @param  mixed $periodEnd
     * @param  mixed $timeframe
     * @param  mixed $showed
     * @return void
     */
    public function getEvents(string $symbol, string $periodIni, string $periodEnd, string $timeframe, int $showed)
    {

        $candles = $this->model->getOHLCByDateRangeSymbolTimeframe($symbol, $periodIni, $periodEnd, $timeframe, $showed);

        return $this->createEvents($candles);
    }



    /**
     * getNextNotShowed
     *
     * @param  mixed $symbol
     * @param  mixed $periodIni
     * @param  mixed $periodEnd
     * @param  mixed $timeframe
     * @param  mixed $showed
     * @param  mixed $speed
     * @return void
     */
    public function getNextNotShowed(string $symbol, string $periodIni, string $periodEnd, string $timeframe, int $showed, int $speed)
    {
        $candles = $this->model->getNextNotShowed($symbol, $periodIni, $periodEnd, $timeframe, $showed, $speed);

        $candles = $candles->map(function ($item, $key) {
            return collect($item)->only($this->graphOnly)->toArray();
        });

        return $candles;
    }

    /**
     * reset
     *
     * @param  mixed $symbol
     * @param  mixed $timeframe
     * @return void
     */
    public function reset(string $symbol, string $timeframe): void
    {
        $this->model->reset($symbol, $timeframe);
    }

    /**
     * prepareParams
     *
     * @param  mixed $request
     * @return void
     */
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

    /**
     * broadcastData
     *
     * @param  mixed $data
     * @return void
     */
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


    /**
     * colorCandlePattern
     *
     * @param  mixed $candles
     * @return void
     */
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


    /**
     * createEvents
     *
     * @param  mixed $candles
     * @return void
     */
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

    
    /**
     * existFormat
     *
     * @param  mixed $eventsLetter
     * @param  mixed $letter
     * @return void
     */
    function existFormat($eventsLetter, $letter)
    {

        foreach ($eventsLetter as $key => $value) {
            if ($value['format'] == $letter) {
                return true;
            }
        }

        return false;
    }


    /**
     * setCandlePatternColor
     *
     * @param  mixed $signal
     * @param  mixed $pattern
     * @param  mixed $reliability
     * @param  mixed $is_up_down
     * @return void
     */
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


    /**
     * setCandlePatternLetter
     *
     * @param  mixed $signal
     * @param  mixed $reliability
     * @return void
     */
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


    /**
     * identifyCandlePatternMarkers
     *
     * @param  mixed $data
     * @return void
     */
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

        
    /**
     * createSMA_EMA
     *
     * @param  mixed $type
     * @param  mixed $data
     * @param  mixed $periods
     * @return void
     */
    public function createSMA_EMA($type, $data, $periods)
    {

        switch ($type) {
            case 'sma':
                $ma = Trader::sma(Arr::pluck($data, 'close'), $periods);
                break;
            case 'ema':
                $ma = Trader::ema(Arr::pluck($data, 'close'), $periods);
                break;
        }

        $pos = 0;
        $nData = null;

        for ($x = --$periods; $x < count($data); $x++) {
            if ($x > $periods) {

                $nData[] = [
                    'time' => $data[$x]['time'],
                    'value' => $ma[$pos + $periods]
                ];
                $pos++;
            }
        }

        return collect($nData);
    }
}
