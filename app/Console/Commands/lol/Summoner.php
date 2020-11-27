<?php

namespace App\Console\Commands\lol;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Summoner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:summoner';

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
        /*$url='http://lol.qq.com/biz/hero/summoner.js';
        $data = [
            "asign_to"=>1,
            "mission_type"=>'summoner',//召唤师
            "mission_status"=>1,
            "game"=>'lol',
            "source"=>'lol_qq',//装备
            "detail"=>json_encode(
                [
                    "url"=>$url,
                    "game"=>'lol',//英雄联盟
                    "source"=>'lol_qq',//装备
                ]
            ),
        ];
        $insert = (new oMission())->insertMission($data);
        echo "insert:".$insert;*/

        (new oMission())->collect('lol','lol_qq','summoner');
    }
}
