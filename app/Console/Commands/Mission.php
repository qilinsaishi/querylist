<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\CollectService as Collect;
use App\Services\MissionService as oMission;
class Mission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mission:info';

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
        /*
        $currentTime = date("Y-m-d H:i:s");
        $data = [
            "asign_to"=>1,
            "mission_type"=>"page",
            "mission_status"=>1,
            "detail"=>json_encode(
                [
                    "url"=>"www.xxx.com?page=1",
                    "source"=>"site1"
                ]
            ),
        ];
        $insert = (new oMission())->insertMission($data);
        echo "insert:".$insert;
        */
        (new oMission())->processMission();
    }
}
