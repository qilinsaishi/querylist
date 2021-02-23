<?php

namespace App\Services;

use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;

class SummonerService
{
    public function insertSummonerData()
    {
        $gameItem = [
            'lol', 'kpl', 'dota2', 'csgo'
        ];

        foreach ($gameItem as $val) {
            switch ($val) {
                case "lol":
                    $this->insertLolSummoner();
                    break;
                case "kpl":
                    $this->insertKplSummoner();
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

    //英雄联盟召唤师技能采集
    public function insertLolSummoner()
    {
        $missionModel=new MissionModel();
        $url='http://lol.qq.com/biz/hero/summoner.js';
        $params = [
            'game' => 'lol',
            'mission_type' => 'summoner',
            'source_link' => $url,
        ];
        $result = $missionModel->getMissionCount($params);
        //过滤已经采集过的文章
        $result = $result ?? 0;
        if($result <=0) {
            $data = [
                "asign_to"=>1,
                "mission_type"=>'summoner',//召唤师
                "mission_status"=>1,
                "game"=>'lol',
                "source"=>'lol_qq',//召唤师
                'source_link' => $url,
                "detail"=>json_encode(
                    [
                        "url"=>$url,
                        "game"=>'lol',//英雄联盟
                        "source"=>'lol_qq',//召唤师
                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:".$insert.' lenth:'.strlen($data['detail']);
        }

        return true;
    }

    public function insertKplSummoner()
    {
        $missionModel=new MissionModel();
        $url = 'https://pvp.qq.com/web201605/js/summoner.json';
        $params = [
            'game' => 'kpl',
            'mission_type' => 'summoner',
            'source_link' => $url,
        ];
        $result = $missionModel->getMissionCount($params);
        //过滤已经采集过的文章
        $result = $result ?? 0;
        if($result <=0) {
            $data = [
                "asign_to" => 1,
                "mission_type" => 'summoner',//召唤师技能
                "mission_status" => 1,
                "game" => 'kpl',
                "source" => 'pvp_qq',//装备
                'source_link' => $url,
                "detail" => json_encode(
                    [
                        "url" => $url,
                        "game" => 'kpl',//王者荣耀
                        "source" => 'pvp_qq',//王者荣耀官网

                    ]
                ),
            ];
            $insert = (new oMission())->insertMission($data);
            echo "insert:" . $insert;
        }

        return true;
    }
}
