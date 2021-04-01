<?php

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Graph extends Model
{

    public function getOHLCByDateRangeSymbolTimeframe(string $symbol, string $periodIni, string $periodEnd, string $timeframe, int $showed): Collection
    {
        if ($timeframe == 'M0') {
            $res = DB::select(
                "SELECT
            id, 
            a.minute AS minute, 
            UNIX_TIMESTAMP(a.minute) AS time, 
            TRUNCATE(a.open, 2) AS open, 
            TRUNCATE(MAX(a.high), 2) AS high, 
            TRUNCATE(MIN(a.low), 2) AS low, 
            (
                SELECT TRUNCATE(b.close, 2)
                FROM  graphs b
                WHERE b.id = max(a.id)
            ) AS close,
            (
                SELECT b.trades_qty
                FROM  graphs b
                WHERE b.id = max(a.id)
            ) AS trades,
            (
                SELECT b.volume_qty
                FROM  graphs b
                WHERE b.id = max(a.id)
            ) AS volume
            FROM graphs a
            WHERE
                a.symbol='$symbol' AND
                
                a.id >= " . strtotime($periodIni) . " AND
                a.id <= " . strtotime($periodEnd) . " AND

                a.timeframe = '$timeframe' AND

                a.showed = $showed
            GROUP BY a.minute",

            );
        } else {

            $res = DB::table('graphs')
                ->select('id', 'minute', DB::raw('UNIX_TIMESTAMP(minute) AS time'))
                ->addSelect(DB::raw('TRUNCATE(open, 2) AS open'))
                ->addSelect(DB::raw('TRUNCATE(high, 2) AS high'))
                ->addSelect(DB::raw('TRUNCATE(low, 2) AS low'))
                ->addSelect(DB::raw('TRUNCATE(close, 2) AS close'))
                ->addSelect(DB::raw('trades_qty AS trades'))
                ->addSelect(DB::raw('volume_qty AS volume'))
                ->where('symbol', $symbol)
                ->where('id', '>=', strtotime($periodIni))
                ->where('id', '<=', strtotime($periodEnd))
                ->where('timeframe', $timeframe)
                ->where('showed', $showed)
                ->orderBy('minute', 'ASC')
                ->get();
        }

        foreach ($res as $key => $value) {
            $res[$key]->risingFill = '#FFF';
            $res[$key]->fallingFill = '#000';
        }

        return collect($res);
    }


    public function reset(string $symbol, string $timeframe): void
    {
        Graph::where('symbol', $symbol)->where('timeframe', $timeframe)->update(['showed' => 0]);
    }


    public function getNextNotShowed(string $symbol, string $periodIni, string $periodEnd, string $timeframe, int $showed, int $speed)
    {

        if ($timeframe == 'M0') {

            $multiplier = 1;

            if ($speed <= 3) {
                $multiplier = 5;
            } elseif (($speed > 3) && ($speed <= 6)) {
                $multiplier = 10;
            } elseif ($speed > 6) {
                $multiplier = 15;
            }

            $limit = $speed * $multiplier;

            $res = DB::select(
                "SELECT
            id, 
            minute AS minute, 
            UNIX_TIMESTAMP(minute) AS time, 
            TRUNCATE(open, 2) AS open, 
            TRUNCATE(high, 2) AS high, 
            TRUNCATE(low, 2) AS low, 
            TRUNCATE(close, 2) AS close,
            trades_qty AS trades,
            volume_qty AS volume
            FROM graphs
            WHERE
                symbol='$symbol' AND
                
                id >= " . strtotime($periodIni) . " AND
                id <= " . strtotime($periodEnd) . " AND

                timeframe = '$timeframe' AND

                showed = $showed
            ORDER BY id
            LIMIT $limit"
            );
        } else {

            $res = DB::table('graphs')
                ->select('id', 'minute', DB::raw('UNIX_TIMESTAMP(minute) AS time'))
                ->addSelect(DB::raw('TRUNCATE(open, 2) AS open'))
                ->addSelect(DB::raw('TRUNCATE(high, 2) AS high'))
                ->addSelect(DB::raw('TRUNCATE(low, 2) AS low'))
                ->addSelect(DB::raw('TRUNCATE(close, 2) AS close'))
                ->addSelect(DB::raw('trades_qty AS trades'))
                ->addSelect(DB::raw('volume_qty AS volume'))
                ->where('symbol', $symbol)
                ->where('id', '>=', strtotime($periodIni))
                ->where('id', '<=', strtotime($periodEnd))
                ->where('timeframe', $timeframe)
                ->where('showed', $showed)
                ->orderBy('minute', 'ASC')
                ->take($speed)
                ->get();

        }

        foreach ($res as $key => $value) {
            $res[$key]->risingFill = '#FFF';
            $res[$key]->fallingFill = '#000';

            Graph::where('id', $res[$key]->id)->update(['showed' => 1]);
        }

        return collect($res);
    }

}
