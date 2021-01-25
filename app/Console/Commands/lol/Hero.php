<?php

namespace App\Console\Commands\lol;

use App\Models\CollectResultModel;
use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Hero extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lol:hero {operation}';

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
    {   //英雄联盟官网-英雄数据
        $operation = ($this->argument("operation")??"insert");
        if($operation=='insert'){
            $collectResultModel=new CollectResultModel();
            $cdata=curl_get('https://game.gtimg.cn/images/lol/act/img/js/heroList/hero_list.js');
            $cdata=$cdata['hero'] ?? [];
            if($cdata){
                foreach ($cdata as $val){
                    $url='https://game.gtimg.cn/images/lol/act/img/js/hero/'.$val['heroId'].'.js';
                    $params=[
                        'game'=>'lol',
                        'mission_type'=>'hero',
                        'source_link'=>$url,
                    ];
                    $result=$collectResultModel->getCollectResultCount($params);
                    $result=$result ?? 0;
                    if($result <=0){
                        $data = [
                            "asign_to"=>1,
                            "mission_type"=>'hero',
                            "mission_status"=>1,
                            "game"=>'lol',
                            "source"=>'lol_qq',
                            "detail"=>json_encode(
                                [
                                    "url"=>$url,
                                    "game"=>'lol',//英雄联盟
                                    "source"=>'lol_qq',
                                ]
                            ),
                        ];
                        $insert = (new oMission())->insertMission($data);
                        echo "insert:".$insert.' lenth:'.strlen($data['detail']);
                    }

                }
            }
        }else{
            (new oMission())->collect('lol','lol_qq','hero');
        }

    }
}
