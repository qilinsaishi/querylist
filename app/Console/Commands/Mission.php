<?php

namespace App\Console\Commands;


use App\Services\TeamCollectService;
use Illuminate\Console\Command;
use App\Services\CollectService as Collect;
use App\Services\MissionService as oMission;
class Mission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mission:collect {opetation}';

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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $operation = ($this->argument("operation")??"collect");
        switch ($operation) {
            case "collect":
                (new oMission())->collect();
                break;
            case "process":
                (new oMission())->process();
                break;
            default:
                (new oMission())->collect();
                break;
        }



    }
}
