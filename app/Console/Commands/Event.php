<?php

namespace App\Console\Commands;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Event extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '赛程管理';

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
        /*for ($i=0;$i<=10;$i++){
            $m=$i+1;
            $url='https://www.chaofan.com/event/lol?status=0&page='.$m;
            $data = [
                "asign_to"=>1,
                "mission_type"=>'event',//赛事
                "mission_status"=>1,
                "game"=>'lol',
                "source"=>'chaofan',//
                "detail"=>json_encode(
                    [
                        "url"=>$url,
                        "game"=>'lol',
                        "source"=>'chaofan',
                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:".$insert;
        }*/
        (new oMission())->collect('lol','chaofan','event');
    }
}
