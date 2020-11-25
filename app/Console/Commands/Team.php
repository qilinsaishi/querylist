<?php

namespace App\Console\Commands;

use App\Models\CollectResultModel;
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
        $collectModel=new CollectResultModel();
        $cdata=$collectModel->getDataFromUrl('kpl',10);
        /*if($cdata){
            foreach ($cdata as $val){
                $data = [
                    "asign_to"=>1,
                    "mission_type"=>"team",
                    "mission_status"=>1,
                    "game"=>'kpl',
                    "source"=>'baidu_baike',
                    "detail"=>json_encode(
                        [
                            "url"=>$val['source_link'],
                            "game"=>'kpl',//王者荣耀
                            "source"=>'baidu_baike',
                            "id"=>$val['id']
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
