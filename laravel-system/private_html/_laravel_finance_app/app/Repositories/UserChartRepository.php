<?php

namespace App\Repositories;

use App\UserChart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserChartRepository
{

    private $userChartModel;

    public function __construct(UserChart $userChartModel)
    {
        $this->userChartModel = $userChartModel;
    }

    /**
     * update
     *
     * @param  mixed $uid
     * @param  mixed $speed
     * @return void
     */
    public function update($uid, $speed)
    {
        UserChart::where('uid', $uid)->update(['speed' => $speed]);
    }

    /**
     * getByUID
     *
     * @param  mixed $uid
     * @return void
     */
    public function getByUID($uid)
    {
        return UserChart::where('uid', $uid)->get()->first();
    }
}
