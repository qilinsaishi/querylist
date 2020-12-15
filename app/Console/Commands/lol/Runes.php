<?php

namespace App\Console\Commands\lol;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Runes extends Command
{
    /**
     * The name and signature of the console command.
     *符文
     * @var string
     */
    protected $signature = 'command:runes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '符文';

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
        $url='https://lol.qq.com/act/a20170926preseason/data/cn/runes.json';
        $data = [
            "asign_to"=>1,
            "mission_type"=>'runes',//符文
            "mission_status"=>1,
            "game"=>'lol',
            "source"=>'lol_qq',//
            "detail"=>json_encode(
                [
                    "url"=>$url,
                    "game"=>'lol',//英雄联盟
                    "source"=>'lol_qq',//符文
                ]
            ),
        ];
        $insert = (new oMission())->insertMission($data);
        echo "insert:".$insert;

        //(new oMission())->collect('lol','lol_qq','runes');
    }
}
