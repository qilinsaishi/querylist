<?php

namespace App\Console\Commands\lol;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Hero extends Command
{
    /**
     * The name and signature of the console command.
     *英雄
     * @var string
     */
    protected $signature = 'lol:hero';

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
    {   //英雄联盟官网-英雄数据

        $url='https://game.gtimg.cn/images/lol/act/img/js/items/items.js';
        $data = [
            "asign_to"=>1,
            "mission_type"=>'lol_qq',
            "mission_status"=>1,
            "game"=>'lol',
            "source"=>'hero',
            "detail"=>json_encode(
                [
                    "url"=>$url,
                    "game"=>'lol',//英雄联盟
                    "source"=>'item',
                ]
            ),
        ];
        $insert = (new oMission())->insertMission($data);
        echo "insert:".$insert;

        //(new oMission())->collect('lol','item');
    }
}
