<?php


namespace App\Http\Controllers\User;

use App\Graph;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use LupeCode\phpTraderNative\Trader;
use App\Repositories\GraphRepository;
use App\UserChart;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    private $graphRepository;

    public function __construct(GraphRepository $graphRepository)
    {
        $this->graphRepository = $graphRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $params = $this->graphRepository->prepareParams($request);

        UserChart::where('uid', Auth::user()->uid)->delete();

        $userChart = UserChart::create([
            'uid' => Auth::user()->uid,
            'symbol' => $params['symbol'],
            'timeframe' => $params['timeframe'],
            'period_ini' => $params['periodIni'],
            'period_end' => $params['periodEnd']
        ]);

        $symbols = Graph::groupBy('symbol')->pluck('symbol', 'symbol');

        $timeframes = Graph::groupBy('timeframe')->orderBy('timeframe')->pluck('timeframe', 'timeframe');

        return view('user.dashboard', compact('userChart', 'params', 'symbols', 'timeframes'));
    }

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
