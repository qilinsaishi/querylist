<?php

namespace App\Services;

use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;

class EquipmentService
{
    public function insertEquipmentData()
    {
        $gameItem = [
            'lol', 'kpl', 'dota2'//, 'csgo'
        ];

        foreach ($gameItem as $val) {
            switch ($val) {
                case "lol":

                    $this->insertLolEquipment();
                    break;
                case "kpl":
                    $this->insertKplEquipment();
                    break;
                case "dota2":
                    $this->insertDota2Equipment();
                    break;
                case "csgo":

                    break;
                default:

                    break;
            }
        }
        return 'finish';
    }

    //英雄联盟资讯采集
    public function insertLolEquipment()
    {
        $missionModel=new MissionModel();
        $url = 'https://game.gtimg.cn/images/lol/act/img/js/items/items.js';
        $params=[
            'game'=>'lol',
            'mission_type'=>'equipment',
            'source_link'=>$url,
        ];
        $result =$missionModel->getMissionCount($params);//过滤已经采集过的数据
        $result=$result ?? 0;
        if($result <=0) {
            $data = [
                "asign_to" => 1,
                "mission_type" => 'equipment',//装备
                "mission_status" => 1,
                "game" => 'lol',
                "source" => 'lol_qq',//装备
                'source_link'=>$url,
                "detail" => json_encode(
                    [
                        "url" => $url,
                        "game" => 'lol',//英雄联盟
                        "source" => 'lol_qq',//装备
                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);

            echo "lol-equipment-insert:".$insert.' lenth:'.strlen($data['detail']). "\n";
        }

        return true;
    }

    public function insertKplEquipment()
    {
        $missionModel=new MissionModel();
        $cList=array(
            array('url'=>'https://pvp.qq.com/web201605/js/item.json','type'=>1),
            array('url'=>'https://pvp.qq.com/zlkdatasys/data_zlk_bjtwitem.json','type'=>2)
        );
        foreach ($cList as $val){
            $params=[
                'game'=>'kpl',
                'mission_type'=>'equipment',
                'source_link'=>$val['url'],
            ];
            $result =$missionModel->getMissionCount($params);//过滤已经采集过的数据
            $result=$result ?? 0;
            if($result <=0) {
                $data = [
                    "asign_to"=>1,
                    "mission_type"=>'equipment',//装备
                    "mission_status"=>1,
                    "game"=>'kpl',
                    "source"=>'pvp_qq',//装备
                    'source_link'=>$val['url'],
                    "detail"=>json_encode(
                        [
                            "url"=>$val['url'],
                            "game"=>'kpl',//王者荣耀
                            "source"=>'pvp_qq',//王者荣耀官网
                            'type'=>$val['type']
                        ]
                    ),
                ];
                $insert = (new oMission())->insertMission($data);
                echo "insert:" . $insert . ' lenth:' . strlen($data['detail']);
            }

        }
        return true;
    }

    //dota2装备采集
    public function insertDota2Equipment()
    {
        $missionModel=new MissionModel();
        //物品
        $item_url='https://www.dota2.com.cn/items/json';
        $itemData=curl_get($item_url);
        if(isset($itemData['itemdata'])){
            foreach ( $itemData['itemdata'] as $key=>$val) {
                $val['en_name']=$key;
                $val['game']='dota2';
                $val['source']='gamedota2';
                $val['img']='https://www.dota2.com.cn/items/images/'.$val['img'];
                $params=[
                    'game'=>'dota2',
                    'mission_type'=>'equipment',
                    'title'=>$key,
                ];
                $result =$missionModel->getMissionCount($params);//过滤已经采集过的数据
                $result=$result ?? 0;
                if($result ==0) {
                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'equipment',//装备
                        "mission_status" => 1,
                        "game" => 'dota2',
                        "source" => 'gamedota2',//装备
                        'source_link'=>'',
                        'title'=>$key,
                        "detail" => json_encode($val),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "lol-equipment-insert:".$insert.' lenth:'.strlen($data['detail']). "\n";
                }

            }
        }

        return true;
    }
}
