<?php

namespace App\Console\Commands\lol;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class item extends Command
{
    /**
     * The name and signature of the console command.
     *装备
     * @var string
     */
    protected $signature = 'command:item';

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
        $cdata=curl_get('https://game.gtimg.cn/images/lol/act/img/js/heroList/hero_list.js');
        $cdata=$cdata['hero'] ?? [];
        if($cdata){
            foreach ($cdata as $val){
                $url='https://game.gtimg.cn/images/lol/act/img/js/hero/'.$val['heroId'].'.js';
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
                            "source"=>'hero',
                        ]
                    ),
                ];
                $insert = (new oMission())->insertMission($data);
                echo "insert:".$insert;
            }
        }*/

        (new oMission())->collect('lol','hero');
    }
}
