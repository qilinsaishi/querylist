<?php

namespace App\Services;

use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class TeamResultService
{
    public function insertTeamData($mission_type)
    {
        $gameItem = [
            'lol', 'kpl',// 'dota2', 'csgo'
        ];

        foreach ($gameItem as $val) {
            //采集玩加（www.wanplus.com）战队信息
            $this->insertWanplusTeam($val,$mission_type);
            //采集cpseo（2cpseo.com）战队信息
            $this->insertCpseoTeam($val,$mission_type);

        }
        return 'finish';
    }
    //玩加电竞（wanplus）
    public function insertWanplusTeam($game,$mission_type){
        $collectModel=new CollectUrlModel();
        $collectResultModel = new CollectResultModel();
        $cdata=$collectModel->getDataFromUrl($game,$mission_type,'wanplus');
        if($cdata){
            foreach ($cdata as $val){
                $params = [
                    'game' => $game,
                    'mission_type' => $mission_type,
                    'source_link' => $val['url'],
                ];
                $result = $collectResultModel->getCollectResultCount($params);//过滤已经采集过的文章

                $result = $result ?? 0;
                if ($result <= 0) {
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
                }

            }
        }
        return true;
    }
    //2ceseo 战队列表
    public function insertCpseoTeam($game,$mission_type){
        if($game=='lol'){
            $count=3;
        }elseif($game=='kpl'){
            $count=1;
        }elseif($game=='dota2'){
            $count=10;
        }elseif($game=='csgo'){
            $count=12;
        }


        for ($i = 0; $i <= $count; $i++) {
            $m = $i + 1;
            if($game=='lol'){
                $url = 'http://www.2cpseo.com/teams/lol/p-' . $m;
            }elseif($game=='kpl'){
                $url = 'http://www.2cpseo.com/teams/kog/p-' . $m;
            }/*elseif($game=='dota2'){
                $count=10;
            }elseif($game=='csgo'){
                $count=12;
            }*/

            $ql = QueryList::get($url);
            $links = $ql->find('.team-list a')->attrs('href')->all();
            if (isset($links) && $links) {
                $collectResultModel=new CollectResultModel();
                foreach ($links as $v) {
                    $params=[
                        'game'=>$game,
                        'mission_type'=>$mission_type,
                        'source_link'=>$v,
                    ];
                    $result=$collectResultModel->getCollectResultCount($params);
                    $result=$result ?? 0;
                    if($result <=0){
                        $data = [
                            "asign_to" => 1,
                            "mission_type" => $mission_type,//赛事
                            "mission_status" => 1,
                            "game" => $game,
                            "source" => 'cpseo',//
                            "detail" => json_encode(
                                [
                                    "url" => $v,
                                    "game" => $game,
                                    "source" => 'cpseo',
                                ]
                            ),
                        ];
                        if($data){
                            $insert = (new oMission())->insertMission($data);
                            echo "insert:" . $insert . ' lenth:' . strlen($data['detail']);
                        }
                    }

                }
            }

        }



        return true;
    }


}
