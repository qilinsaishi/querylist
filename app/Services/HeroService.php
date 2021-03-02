<?php

namespace App\Services;

use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\Hero\kplModel;
use App\Models\Hero\lolModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class HeroService
{
    public function insertHeroData()
    {
        $gameItem = [
            'lol', 'kpl', 'dota2'//, 'csgo'
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
                    $this->insertDota2Hero();
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
        $lolHeroModel=new lolModel();
        $cdata=curl_get('https://game.gtimg.cn/images/lol/act/img/js/heroList/hero_list.js');
        $cdata=$cdata['hero'] ?? [];
        if($cdata){
            foreach ($cdata as $val){
                $lolHeroInfo=$lolHeroModel->getHeroInfoById($val['heroId']);
                if(empty($lolHeroInfo)){
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
                }else{
                    echo 'lol英雄数据已采集'. "\n";
                    break;
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
        $kplModel=new kplModel();
        $cdata = $client->curlGet($url);//获取英雄列表
        if(!empty($cdata)){
            foreach ($cdata as $val){

                $url='https://pvp.qq.com/web201605/herodetail/'.$val['ename'].'.shtml';
                $logo='https://game.gtimg.cn/images/yxzj/img201606/heroimg/'.$val['ename'].'/'.$val['ename'].'.jpg';
                $heroInfo=$kplModel->getHeroInfoById($val['ename']);
                if(empty($heroInfo)){
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

                }else{
                    echo 'kpl英雄数据已采集'. "\n";
                    break;
                }

            }
        }
        return true;
    }
    public function insertDota2Hero(){
        $missionModel=new MissionModel();
        $heroDota2=$this->getHeroDota2();
        $item=$heroDota2['item']??[];
        $typeItem=$heroDota2['typeItem']??[];
        foreach ($item as $key=>$val){
            if(empty($heroInfo)){
                $params=[
                    'game'=>'dota2',
                    'mission_type'=>'hero',
                    'source_link'=>$val,
                ];
                $result =$missionModel->getMissionCount($params);//过滤已经采集过的数据
                $result=$result ?? 0;
                if($result <=0) {

                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'hero',//dota2-英雄
                        "mission_status" => 1,
                        "game" => 'dota2',
                        "source" => 'gamedota2',//英雄
                        'source_link'=>$val,
                        "detail" => json_encode(
                            [
                                "url" => $val,
                                "game" => 'dota2',//dota2
                                "source" => 'gamedota2',//dota2官网
                                'hero_type'=>$typeItem[$key] ?? '',

                            ]
                        ),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "insert:" . $insert . ' lenth:' . strlen($data['detail']);
                }else{
                    continue;
                }

            }
        }
        return true;

    }
    public function getHeroDota2(){
        $item=[];
        $typeItem=[];
        //dota2英雄
        $qt=QueryList::get('https://www.dota2.com.cn/heroes/index.htm');
        $item=[];
        $typeItem=[];
        //力量
        $item0=$qt->find(".black_cont .goods_main .hero_list:eq(0) li a")->attrs('href');
        $item3=$qt->find(".black_cont .goods_main .hero_list:eq(3) li a")->attrs('href');
        if(count($item0) >0){
            foreach ($item0 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'str');
            }
        }
        if(count($item3) >0){
            foreach ($item3 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'str');
            }
        }


        //敏捷
        $item1=$qt->find(".black_cont .goods_main .hero_list:eq(1) li a")->attrs('href');
        $item4=$qt->find(".black_cont .goods_main .hero_list:eq(4) li a")->attrs('href');
        if(count($item1) >0){
            foreach ($item1 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'agi');
            }
        }
        if(count($item4) >0){
            foreach ($item4 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'agi');
            }
        }

        //智力
        $item2=$qt->find(".black_cont .goods_main .hero_list:eq(2) li a")->attrs('href');
        $item5=$qt->find(".black_cont .goods_main .hero_list:eq(5) li a")->attrs('href');
        if(count($item2) >0){
            foreach ($item2 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'int');
            }
        }
        if(count($item5) >0){
            foreach ($item5 as $k=>$v){
                array_push($item,$v);
                array_push($typeItem,'int');
            }
        }
        $data=[
            'item'=>$item,
            'typeItem'=>$typeItem,
        ];
        return $data;
    }
}
