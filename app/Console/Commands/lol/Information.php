<?php

namespace App\Console\Commands\lol;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Information extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:lol_information';

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

        $target=23;//23=>'综合',24=>'公告',25=>'赛事',27=>'攻略',28=>'社区'
        //获取分页总数和每页条数
        $init_url='https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page=1&num=16&target='.$target.'&source=web_pc&_='.msectime();
        $data=curl_get($init_url);
        $resultTotal=$data['data']['resultTotal'] ?? '';
        $resultNum=$data['data']['resultNum'] ?? '';
        $lastPage=getLastPage($resultTotal,$resultNum);
        for ($i=0;$i<=$lastPage;$i++){
            $m=$i+1;
            $url='https://apps.game.qq.com/cmc/zmMcnTargetContentList?r0=jsonp&page='.$m.'&num=16&target=24&source=web_pc&_='.msectime();
            $data = [
                "asign_to"=>1,
                "mission_type"=>'information',//符文
                "mission_status"=>1,
                "game"=>'lol',
                "source"=>'lol_qq',//
                "detail"=>json_encode(
                    [
                        "url"=>$url,
                        "game"=>'lol',//英雄联盟
                        "source"=>'lol_qq',//符文
                        "target"=>$target
                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:".$insert;
        }


        //(new oMission())->collect('lol','lol_qq','information');
    }
}
