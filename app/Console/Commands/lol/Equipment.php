<?php

namespace App\Console\Commands\lol;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Equipment extends Command
{
    /**
     * The name and signature of the console command.
     *英雄联盟-装备
     * @var string
     */
    protected $signature = 'command:equipment {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description ';

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
            $url = 'https://game.gtimg.cn/images/lol/act/img/js/items/items.js';
            $insert_act = 1;
            $data = [
                "asign_to" => 1,
                "mission_type" => 'equipment',//装备
                "mission_status" => 1,
                "game" => 'lol',
                "source" => 'lol_qq',//装备
                "detail" => json_encode(
                    [
                        "url" => $url,
                        "game" => 'lol',//英雄联盟
                        "source" => 'lol_qq',//装备
                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);

            echo "insert:".$insert.' lenth:'.strlen($data['detail']);
        }else{
            (new oMission())->collect('lol', 'lol_qq', 'equipment');
        }

    }
}
