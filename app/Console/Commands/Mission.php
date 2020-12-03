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
    protected $signature = 'mission:collect {operation} {game} {mission_type}';

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
        $game = ($this->argument("game")??"");
        $mission_type = ($this->argument("mission_type")??"");
        switch ($operation) {
            case "collect":
                (new oMission())->collect();
                break;
            case "process":
                (new oMission())->process($game,"",$mission_type);
                break;
            default:
                (new oMission())->collect();
                break;
        }



    }
}
