<?php

namespace App\Console\Commands\lol;

use App\Models\CollectResultModel;
use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Information extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:lol_information  {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '英雄联盟-资讯';

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

        $target=28;//23=>'综合',24=>'公告',25=>'赛事',27=>'攻略',28=>'社区'
        $operation = ($this->argument("operation")??"insert");
        //获取分页总数和每页条数
        if($operation=='insert'){
            $collectResultModel=new CollectResultModel();
            $init_url='https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page=1&num=10&target='.$target.'&source=web_pc&_='.msectime();
            $data=curl_get($init_url);
            $resultTotal=$data['data']['resultTotal'] ?? '';
            $resultNum=$data['data']['resultNum'] ?? '';
            $lastPage=getLastPage($resultTotal,$resultNum);
            $lastPage=1;
            for ($i=0;$i<=$lastPage;$i++){
                $m=$i+1;
                $url='https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page='.$m.'&num=16&target='.$target.'&source=web_pc';
                $params=[
                    'game'=>'lol',
                    'mission_type'=>'information',
                    'source_link'=>$url,
                ];
                $result=$collectResultModel->getCollectResultCount($params);
                $result=$result ?? 0;
                if($result <=0){
                    $data = [
                        "asign_to"=>1,
                        "mission_type"=>'information',//资讯
                        "mission_status"=>1,
                        "game"=>'lol',
                        "source"=>'lol_qq',//
                        'title'=>'',
                        "detail"=>json_encode(
                            [
                                "url"=>$url,
                                "game"=>'lol',//英雄联盟
                                "source"=>'lol_qq',//资讯
                                "target"=>$target
                            ]
                        ),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "insert:".$insert.' lenth:'.strlen($data['detail']);
                }

            }
        }else{
            (new oMission())->collect('lol','lol_qq','information');
        }

    }
}
