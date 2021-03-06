<?php

namespace App\Console\Commands;


use App\Services\AliyunSercies;
use App\Services\AliyunService;
use App\Services\EquipmentService;
use Illuminate\Console\Command;
use App\Services\TeamService as TeamService;
use App\Services\PlayerService as PlayerService;

class Intergration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intergration {mission_type} {--game=}';

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
        $game = $this->option("game")??"lol";
        $mission_type = ($this->argument("mission_type")??"");
        switch ($mission_type) {
            case "team":
                (new TeamService())->intergration($game);
                break;
            case "player":
                (new PlayerService())->intergration($game);
                break;
            default:
                break;
        }
    }
}
