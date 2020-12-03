<?php

namespace App\Console\Commands\kpl;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Summoner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpl:summoner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '王者荣耀-召唤师技能';

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
        /*$url = 'https://pvp.qq.com/web201605/js/summoner.json';
        $data = [
            "asign_to" => 1,
            "mission_type" => 'summoner',//召唤师技能
            "mission_status" => 1,
            "game" => 'kpl',
            "source" => 'pvp_qq',//装备
            "detail" => json_encode(
                [
                    "url" => $url,
                    "game" => 'kpl',//王者荣耀
                    "source" => 'pvp_qq',//王者荣耀官网

                ]
            ),
        ];
        $insert = (new oMission())->insertMission($data);
        echo "insert:" . $insert;*/

        (new oMission())->collect('kpl','pvp_qq','summoner');
    }

}
