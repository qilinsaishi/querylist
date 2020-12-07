<?php

namespace App\Console\Commands\kpl;

use App\Libs\ClientServices;
use App\Services\MissionService as oMission;
use Illuminate\Console\Command;

class Hero extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kpl:hero';

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
        /*$url = 'https://pvp.qq.com/web201605/js/herolist.json';
           $client = new ClientServices();
           $cdata = $client->curlGet($url);//获取英雄列表
           if(!empty($cdata)){
                  foreach ($cdata as $val){
                      $url='https://pvp.qq.com/web201605/herodetail/'.$val['ename'].'.shtml';
                      $logo='https://game.gtimg.cn/images/yxzj/img201606/heroimg/'.$val['ename'].'/'.$val['ename'].'.jpg';
                      $data = [
                          "asign_to" => 1,
                          "mission_type" => 'hero',//王者荣耀-英雄
                          "mission_status" => 1,
                          "game" => 'kpl',
                          "source" => 'pvp_qq',//装备
                          "detail" => json_encode(
                              [
                                  "url" => $url,
                                  "game" => 'kpl',//王者荣耀
                                  "source" => 'pvp_qq',//王者荣耀官网
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
                      echo "insert:" . $insert;
                  }
              }*/


        (new oMission())->collect('kpl','pvp_qq','hero');
    }
}
