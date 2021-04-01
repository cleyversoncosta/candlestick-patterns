<?php

namespace App\Console\Commands;

use App\Repositories\GraphRepository;
use App\UserChart;
use Illuminate\Console\Command;

class UpdateChartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-chart-command';


    private $graphRepository;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GraphRepository $graphRepository)
    {
        parent::__construct();

        $this->graphRepository = $graphRepository;
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        foreach(UserChart::all() as $data){
            $this->graphRepository->broadcastData($data);
        }

        
    }
}
