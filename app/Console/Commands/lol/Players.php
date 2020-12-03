<?php

namespace App\Console\Commands\lol;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Players extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lol:player';

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
       /* $data = [
            "asign_to"=>1,
            "mission_type"=>'player',
            "mission_status"=>1,
            "game"=>'lol',
            "source"=>'wanplus',
            "detail"=>json_encode(
                [
                    "url"=>'https://www.wanplus.com//lol/player/1246',
                    "game"=>'lol',//lol
                    "source"=>'wanplus',
                    "name"=>'Nuclear',
                    "position"=>'ADC',
                    "main_img"=>'https://static.wanplus.com/data/lol/player/1246.png',
                ]
            ),
        ];
        $insert = (new oMission())->insertMission($data);
        echo "insert:".$insert;*/
        (new oMission())->collect('lol','wanplus','player');
    }
}
