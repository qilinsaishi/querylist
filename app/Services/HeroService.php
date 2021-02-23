<?php

namespace App\Services;

use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;

class HeroService
{
    public function insertHeroData()
    {
        $gameItem = [
            'lol', 'kpl', 'dota2', 'csgo'
        ];

        foreach ($gameItem as $val) {
            switch ($val) {
                case "lol":
                    $this->insertLolHero();
                    break;
                case "kpl":
                    $this->insertKplHero();
                    break;
                case "dota2":

                    break;
                case "csgo":

                    break;
                default:

                    break;
            }
        }
        return 'finish';
    }

    //英雄联盟英雄采集
    public function insertLolHero()
    {
        $missionModel=new MissionModel();
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
                $result =$missionModel->getMissionCount($params);//过滤已经采集过的数据
                $result=$result ?? 0;
                if($result <=0){
                    $data = [
                        "asign_to"=>1,
                        "mission_type"=>'hero',
                        "mission_status"=>1,
                        "game"=>'lol',
                        "source"=>'lol_qq',
                        'source_link'=>$url,
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
        return true;
    }
    //王者荣耀英雄入库
    public function insertKplHero()
    {
        $url = 'https://pvp.qq.com/web201605/js/herolist.json';
        $missionModel=new MissionModel();
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
                $result =$missionModel->getMissionCount($params);//过滤已经采集过的数据
                $result=$result ?? 0;
                if($result <=0) {

                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'hero',//王者荣耀-英雄
                        "mission_status" => 1,
                        "game" => 'kpl',
                        "source" => 'pvp_qq',//英雄
                        'source_link'=>$url,
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
                            ]
                        ),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "insert:" . $insert . ' lenth:' . strlen($data['detail']);
                }


            }
        }
        return true;
    }
}
