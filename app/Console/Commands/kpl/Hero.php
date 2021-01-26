<?php

namespace App\Console\Commands\kpl;

use App\Libs\ClientServices;
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
    protected $signature = 'kpl:hero {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '王者荣耀-英雄';

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
        $game='kpl';
        $source='pvp_qq';
        $mission_type='hero';
        $operation = ($this->argument("operation") ?? "insert");
        if($operation=='insert'){
            $url = 'https://pvp.qq.com/web201605/js/herolist.json';
            $collectResultModel=new CollectResultModel();
            $client = new ClientServices();
            $cdata = $client->curlGet($url);//获取英雄列表
            if(!empty($cdata)){
                foreach ($cdata as $val){
                    $url='https://pvp.qq.com/web201605/herodetail/'.$val['ename'].'.shtml';
                    $logo='https://game.gtimg.cn/images/yxzj/img201606/heroimg/'.$val['ename'].'/'.$val['ename'].'.jpg';
                    $params=[
                        'game'=>'kpl',
                        'mission_type'=>'hero',
                        'source_link'=>$url,
                    ];
                    $result=$collectResultModel->getCollectResultCount($params);
                    $result=$result ?? 0;
                    if($result <=0) {

                        $data = [
                            "asign_to" => 1,
                            "mission_type" => $mission_type,//王者荣耀-英雄
                            "mission_status" => 1,
                            "game" => $game,
                            "source" => $source,//装备
                            "detail" => json_encode(
                                [
                                    "url" => $url,
                                    "game" => $game,//王者荣耀
                                    "source" => $source,//王者荣耀官网
                                    'cname'=>$val['cname'] ?? '',
                                    'title'=>$val['title'] ?? '',
                                    'hero_type'=>$val['hero_type'] ?? '',
                                    'hero_type2'=>$val['hero_type2'] ?? '',
                                    'logo'=>$logo,
                                    'ename'=>$val['ename'] ?? '',
                                    //'skin_name'=>$val['skin_name']
                                ]
                            ),
                        ];
                        $insert = (new oMission())->insertMission($data);
                        echo "insert:" . $insert . ' lenth:' . strlen($data['detail']);
                    }


                }
            }
        }else{
            (new oMission())->collect($game, $source, $mission_type);
        }

    }
}
