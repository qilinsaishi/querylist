<?php

namespace App\Console\Commands;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class CpseoPlayer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:seo_player   {operation} {game} {mission_type} {source}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AZ电竞队员';

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
        // 英雄联盟队员信息
       /* $mission_type = 'player';
        $game = 'lol';
        $source = 'cpseo';*/
        $game = ($this->argument("game")??"");
        $mission_type = ($this->argument("mission_type")??"");
        $source = ($this->argument("source")??"");
        $operation = ($this->argument("operation")??"insert");
        if ($operation == 'insert') {
            $data = [
                "asign_to"=>1,
                "mission_type"=>$mission_type,
                "mission_status"=>1,
                "game"=>$game,
                "source"=>$source,
                "detail"=>json_encode(
                    [
                        "url"=>'http://www.2cpseo.com/player/616',
                        "game"=>$game,//lol
                        "source"=>$source,

                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:".$insert;
        }else{
            (new oMission())->collect($game, $source, $mission_type);
        }
        //王者荣耀信息

        /* */
        (new oMission())->collect($game, $source, $mission_type);
    }
}
