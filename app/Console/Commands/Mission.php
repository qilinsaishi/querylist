<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\MissionService as oMission;
class Mission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mission:collect {operation}';

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
