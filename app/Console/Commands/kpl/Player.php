<?php

namespace App\Console\Commands\kpl;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Player extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpl:player';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '王者荣耀-队员';

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
        /*$data = [
            "asign_to"=>1,
            "mission_type"=>'player',
            "mission_status"=>1,
            "game"=>'kpl',
            'title'=>'',
            "source"=>'wanplus',
            "detail"=>json_encode(
                [
                    "url"=>'https://www.wanplus.com/kog/player/26376',
                    "game"=>'kpl',//lol
                    "source"=>'wanplus',
                    "name"=>'AT',
                    "position"=>'',
                    "main_img"=>'https://static.wanplus.com/data/kog/player/26376_mid.png',
                ]
            ),
        ];
        $insert = (new oMission())->insertMission($data);
        echo "insert:".$insert;*/
        (new oMission())->collect('kpl','wanplus','player');
    }
}
