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

}
