<?php


namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\GraphRepository;
use Illuminate\Database\Eloquent\Collection;

class APIV1Controller extends Controller
{

    private $graphRepository;

    /**
     * __construct
     *
     * @param  mixed $graphRepository
     * @return void
     */
    public function __construct(GraphRepository $graphRepository)
    {
        $this->graphRepository = $graphRepository;
    }

    /**
     * ohlc
     *
     * @param  mixed $request
     * @return Collection
     */
    public function ohlc(Request $request): Collection
    {

        $params = $this->graphRepository->prepareParams($request);

        return $this->graphRepository->getOHLCByDateRangeSymbolTimeframe($params['symbol'], $params['periodIni'], $params['periodEnd'], $params['timeframe'], 1);
    }

    /**
     * events
     *
     * @param  mixed $request
     * @return void
     */
    public function events(Request $request)
    {

        $params = $this->graphRepository->prepareParams($request);

        return $this->graphRepository->getEvents($params['symbol'], $params['periodIni'], $params['periodEnd'], $params['timeframe'], 1);
    }
}
