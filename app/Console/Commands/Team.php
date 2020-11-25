<?php

namespace App\Console\Commands;

use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
use App\Models\TeamCollectModel;
use App\Services\MissionService as oMission;
use App\Services\TeamCollectService;
use Illuminate\Console\Command;

class Team extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:team';

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
        $collectModel=new CollectUrlModel();
        /*$cdata=$collectModel->getDataFromUrl('kpl',20);
       if($cdata){
            foreach ($cdata as $val){
                $data = [
                    "asign_to"=>1,
                    "mission_type"=>$val['mission_type'],
                    "mission_status"=>1,
                    "game"=>$val['game'],
                    "source"=>$val['source'],
                    "detail"=>json_encode(
                        [
                            "url"=>$val['url'],
                            "game"=>$val['game'],//王者荣耀
                            "source"=>$val['source'],
                            "title"=>$val['title'],
                        ]
                    ),
                ];
                $insert = (new oMission())->insertMission($data);
                echo "insert:".$insert;
            }
        }*/

        (new oMission())->processMission('kpl','baidu_baike');
    }
}
