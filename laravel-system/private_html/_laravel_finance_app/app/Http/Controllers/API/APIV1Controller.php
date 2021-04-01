<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Arr;
use LupeCode\phpTraderNative\Trader;
use App\Repositories\GraphRepository;
use App\UserChart;
use Illuminate\Support\Facades\Auth;

class APIV1Controller extends Controller
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
    public function ohlc(Request $request)
    {

        $params = $this->graphRepository->prepareParams($request);

        return $this->graphRepository->getOHLCByDateRangeSymbolTimeframe($params['symbol'], $params['periodIni'], $params['periodEnd'], $params['timeframe'], 1);

    }

    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function events(Request $request)
    {

        $params = $this->graphRepository->prepareParams($request);

        return $this->graphRepository->getEvents($params['symbol'], $params['periodIni'], $params['periodEnd'], $params['timeframe'], 1);

    }


}
