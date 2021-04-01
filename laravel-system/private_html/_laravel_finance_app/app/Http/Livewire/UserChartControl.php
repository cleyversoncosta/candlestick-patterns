<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserChartRepository;

class UserChartControl extends Component
{

    public $speed = 1;
    public $speedMin = 0;
    public $speedMax = 15;

    public function __construct()
    {

    }

    public function increment(Request $request) {
        if ($this->speed < $this->speedMax) {
            $this->speed++;
        }
    }


    public function decrement(Request $request) {
        if ($this->speed > $this->speedMin) {
            $this->speed--;
        }
    }

    public function render()
    {
        resolve(UserChartRepository::class)->update(Auth::user()->uid, $this->speed);

        return view('livewire.user-chart-control');
    }
}
