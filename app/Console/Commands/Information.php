<?php

namespace App\Console\Commands;

use App\Libs\ClientServices;
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
    protected $signature = 'command:information {operation}  {mission_type}';

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
        $operation = ($this->argument("operation")??"collect");
        //$game = ($this->argument("game")??"");
        $mission_type = ($this->argument("mission_type")??"");
        $gameItem=[
            'lol','kpl','dota2','csgo'
        ];

        if($operation=='insert'){
            foreach ($gameItem as $val){
                switch ($val) {
                    case "lol":
                        $total=$this->insertLolInformation();
                        //return $total;
                        break;
                    case "kpl":
                        $total=$this->insertKplInformation();
                        //return $total;
                        break;
                    case "dota2":

                        break;
                    case "csgo":

                        break;
                    default:

                        break;
                }
            }
            return  'finish';

        }else{
            (new oMission())->collect('','',$mission_type);
        }


    }
    //英雄联盟资讯采集
    public function insertLolInformation(){
        //$target=28;//23=>'综合',24=>'公告',25=>'赛事',27=>'攻略',28=>'社区'
        $targetItem=[
            23,24,25,27,28
        ];
        $total=0;
        foreach ($targetItem as $val){
            $target=$val;
            $collectResultModel=new CollectResultModel();
            $lastPage=49;
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
                    $total+=$i;
                    //echo "insert:".$insert.' lenth:'.strlen($data['detail']);
                }

            }

        }
        return true;
    }
    public function insertKplInformation(){
        //1761=>新闻,1762=>公告,1763=>活动,1764=>赛事,1765=>攻略
        $targetItem=[
            1761,1762,1763,1764,1765
        ];
        $total=0;
        foreach ($targetItem as $val){
            $type=$val;
            $collectResultModel=new CollectResultModel();
            $lastPage=50;
            for ($i=0;$i<=$lastPage;$i++){
                $m=$i+1;
                if($val!=1765){
                    $url='https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&order=sIdxTime&r0=cors&type=iTarget&source=app_news_search&pagesize=12&page='.$m.'&id='.$type;
                    $pageData = curl_get($url);//资讯
                }else{
                    $client=new ClientServices();
                    $url='https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page='.$m.'&pagesize=15&order=sIdxTime';
                    $refeerer = 'https://pvp.qq.com/web201605/searchResult.shtml';

                    $headers = [
                        'Referer'  => $refeerer,
                        'Accept' => 'application/json',
                    ];
                    $pageData=$client->curlGet($url,'',$headers);//攻略
                }

                $cdata=$pageData['msg']['result'] ?? [];
                if($cdata){
                    foreach ($cdata as $key=>$val){
                        //$detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?p0=18&source=web_pc&id='.$val['iNewsId'];//资讯
                        $detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?source=pvpweb_detail&p0=18&id='.$val['iNewsId'];//攻略
                        $params=[
                            'game'=>'kpl',
                            'mission_type'=>'information',
                            'source_link'=>$detail_url,
                        ];
                        $result=$collectResultModel->getCollectResultCount($params);
                        $result=$result ?? 0;
                        if($result <=0){
                            $data = [
                                "asign_to"=>1,
                                "mission_type"=>'information',//资讯
                                "mission_status"=>1,
                                "game"=>'kpl',
                                "source"=>'pvp_qq',//
                                'title'=>$val['sTitle'] ?? '',
                                "detail"=>json_encode(
                                    [
                                        "url"=>$detail_url,
                                        "game"=>'kpl',//王者荣耀
                                        "source"=>'pvp_qq',//资讯
                                        'type'=>$type,//1761=>新闻,1762=>公告,1763=>活动,1764=>赛事,1765=>攻略
                                    ]
                                ),
                            ];
                            $insert = (new oMission())->insertMission($data);
                            $total+=1;
                        }

                    }
                }
            }
        }
        return true;
    }
}
