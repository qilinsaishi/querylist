<?php

namespace App\Console\Commands\kpl;

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
        //获取分页总数和每页条数
        if($operation=='insert'){


            $lastPage=99;
            for ($i=0;$i<=$lastPage;$i++){
                $m=$i+1;
                $url = 'https://apps.game.qq.com/wmp/v3.1/?p0=18&p1=searchNewsKeywordsList&page='.$m.'&pagesize=15';
                $refeerer = 'Referer: https://pvp.qq.com/web201605/searchResult.shtml';
                $pageData = curl_get($url, $refeerer);
                $cdata=$pageData['msg']['result'] ?? [];
                if($cdata){
                    foreach ($cdata as $key=>$val){
                        $refeerer_detail ='Referer: https://pvp.qq.com/web201605/newsDetail.shtml?G_Biz='.$val['iBiz'].'&tid='.$val['iNewsId'];
                        $detail_url='https://apps.game.qq.com/wmp/v3.1/public/searchNews.php?source=pvpweb_detail&p0=18&id='.$val['iNewsId'];
                        $data = [
                            "asign_to"=>1,
                            "mission_type"=>'information',//资讯
                            "mission_status"=>1,
                            "game"=>'kpl',
                            "source"=>'pvp_qq',//
                            'title'=>'',
                            "detail"=>json_encode(
                                [
                                    "url"=>$detail_url,
                                    "refeerer_detail"=>$refeerer_detail,
                                    "game"=>'kpl',//王者荣耀
                                    "source"=>'pvp_qq',//资讯

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
