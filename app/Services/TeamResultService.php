<?php

namespace App\Services;

use App\Models\CollectUrlModel;
use App\Services\MissionService as oMission;

class TeamResultService
{
    public function insertData($mission_type)
    {
        $gameItem = [
            'lol', 'kpl',// 'dota2', 'csgo'
        ];

        foreach ($gameItem as $val) {
            //采集玩家战队列表
            $this->insertWanplusTeam($val,$mission_type);
            switch ($val) {
                case "lol":

                    break;
                case "kpl":
                    $this->insertKplInformation();
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
    //玩加电竞（wanplus）
    public function insertWanplusTeam($game,$mission_type){
        $collectModel=new CollectUrlModel();
        $cdata=$collectModel->getDataFromUrl($game,$mission_type,'wanplus');
        if($cdata){
            foreach ($cdata as $val){
                $data = [
                    "asign_to"=>1,
                    "mission_type"=>$val['mission_type'],
                    "mission_status"=>1,
                    "game"=>$val['game'],
                    "source"=>$val['source'],
                    "detail"=>json_encode(
                        [
                            "url"=>$val['url'],
                            "game"=>$val['game'],//lol
                            "source"=>$val['source'],
                            "title"=>$val['title'],
                        ]
                    ),
                ];
                $insert = (new oMission())->insertMission($data);
               // echo "insert:".$insert.' lenth:'.strlen($data['detail']);
            }
        }
        return true;
    }

}
