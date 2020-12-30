<?php

namespace App\Console\Commands\lol;

use App\Services\MissionService as oMission;
use Illuminate\Console\Command;
use QL\QueryList;

class Strategy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lol:strategy  {operation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'lol攻略';

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
            for($i=0;$i<=32;$i++){
                $m=$i+1;
                $url='http://lol.kuai8.com/gonglue/index_'.$m.'.html';
                $ql = QueryList::get($url);
                $data=$ql->rules([
                    'title' => ['.con .tit', 'text'],
                    'desc' => ['.con  .txt', 'text'],
                    'link' => ['.img  a', 'href'],
                    'img_url' => ['.img img', 'src'],
                    'dtime' => ['.con  .time', 'text']
                ])->range('.Cont .news-list li')->queryData();
                foreach ($data as $val){
                    $data = [
                        "asign_to"=>1,
                        "mission_type"=>'strategy',//攻略
                        "mission_status"=>1,
                        "game"=>'lol',
                        "source"=>'kuai8',//
                        'title'=>'',
                        "detail"=>json_encode(
                            [
                                "url"=>$val['link'] ?? '',
                                "game"=>'lol',//英雄联盟
                                "source"=>'kuai8',//资讯
                                "title"=>$val['title'] ?? '',
                                "desc"=>$val['desc'] ?? '',
                                "img_url"=>$val['img_url'] ?? '',
                                "dtime"=>$val['dtime'] ?? '',

                            ]
                        ),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "insert:".$insert.' lenth:'.strlen($data['detail']);
                }
            }
        }else{
            (new oMission())->collect('lol','kuai8','strategy');
        }
    }
}
