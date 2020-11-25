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
    protected $signature = 'mission:info';

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
        $model=new TeamCollectService();


        $game_list = ['lol','dota2','kpl',];
        $source_list = ['site1','site2','site3'];
        $currentTime = date("Y-m-d H:i:s");
        for($i=1;$i<=10;$i++)
        {
            $game = $game_list[array_rand($game_list)];
            $source = $source_list[array_rand($source_list)];
            $data = [
                "asign_to"=>1,
                "mission_type"=>"page",
                "mission_status"=>1,
                "game"=>$game,
                "source"=>$source,
                "detail"=>json_encode(
                    [
                        "url"=>"www.xxx.com?page=1",
                        "game"=>$game,
                        "source"=>$source,
                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:".$insert;
        }



        (new oMission())->processMission();
    }
}
