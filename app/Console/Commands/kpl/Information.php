<?php

namespace App\Console\Commands\kpl;

use App\Libs\ClientServices;
use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Information extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kpl_information  {operation}';

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
        $operation = ($this->argument("operation")??"insert");
        $type=1765;//1761=>新闻,1762=>公告,1763=>活动,1764=>赛事,1765=>攻略
        //获取分页总数和每页条数
        if($operation=='insert'){
            $lastPage=49;
            for ($i=0;$i<=$lastPage;$i++){
                $m=$i+1;
              /*  $url='https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&order=sIdxTime&r0=cors&type=iTarget&source=app_news_search&pagesize=12&page='.$m.'&id='.$type;
                $pageData = curl_get($url);资讯*/
                $client=new ClientServices();
                $url='https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page='.$m.'&pagesize=15&order=sIdxTime&_='.msectime();
                $refeerer = 'https://pvp.qq.com/web201605/searchResult.shtml';

                $headers = [
                    'Referer'  => $refeerer,
                    'Accept' => 'application/json',
                ];
                $pageData=$client->curlGet($url,'',$headers);//攻略
                $cdata=$pageData['msg']['result'] ?? [];
                if($cdata){
                    foreach ($cdata as $key=>$val){
                        //$detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?p0=18&source=web_pc&id='.$val['iNewsId'];//资讯
                        $detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?source=pvpweb_detail&p0=18&id='.$val['iNewsId'].'&&_='.msectime();//攻略
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
                        echo "insert:".$insert.' lenth:'.strlen($data['detail']);
                    }
                }
            }
        }else{
            (new oMission())->collect('kpl','pvp_qq','information');
        }
    }
}