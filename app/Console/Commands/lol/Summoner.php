<?php

namespace App\Console\Commands\lol;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Summoner extends Command
{
    /**
     * The name and signature of the console command.
     *召唤师技能
     * @var string
     */
    protected $signature = 'command:summoner {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '召唤师技能';

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
        $operation = ($this->argument("operation")??"insert");
        if($operation=='insert'){
            $url='http://lol.qq.com/biz/hero/summoner.js';
            $data = [
                "asign_to"=>1,
                "mission_type"=>'summoner',//召唤师
                "mission_status"=>1,
                "game"=>'lol',
                "source"=>'lol_qq',//召唤师
                "detail"=>json_encode(
                    [
                        "url"=>$url,
                        "game"=>'lol',//英雄联盟
                        "source"=>'lol_qq',//召唤师
                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:".$insert.' lenth:'.strlen($data['detail']);
        }else{
            (new oMission())->collect('lol','lol_qq','summoner');
        }
    }
}
