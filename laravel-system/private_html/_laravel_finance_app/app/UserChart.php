<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserChart extends Model
{
   protected $table = 'user_charts';

   protected $fillable = ['uid', 'symbol', 'timeframe', 'period_ini', 'period_end'];

}
