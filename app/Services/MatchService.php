<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class MatchService
{
    public function insertMatchData()
    {
        $gameItem = [
            'kpl', 'lol',  'dota2'//, 'csgo'
        ];

        foreach ($gameItem as $val) {
            //$this->insertWanplusSchedule($val);
            if($val=='dota2'){
                $this->pwesports($val);
            }
        }
        return 'finish';
    }


    public function pwesports($game){
        $data1=$this->getGmaeDotaMatch('https://esports.wanmei.com/dpc-match/latest','dpc');//DPC
        $data2=$this->getGmaeDotaMatch('https://esports.wanmei.com/pwl-match/latest','pwl');//PWL
        $missionModel = new MissionModel();
        $cdata=array_merge($data1,$data2);
        if(count($cdata) >0) {
            foreach ($cdata as $val){
                $params1 = [
                    'game' => $game,
                    'mission_type' => 'match',
                    'title' => $val['id'],
                ];

                $val['game']=$game;
                $val['source']='gamedota2';
                $val['type']='match';
                $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                $result = $result ?? 0;
                if ($result == 0) {
                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'match',//赛事
                        "mission_status" => 1,
                        "game" => $game,
                        "source" => 'gamedota2',//
                        'title' => $val['id'],
                        'source_link' => '',
                        "detail" => json_encode($val),
                    ];
                    $insert = (new oMission())->insertMission($data);
                }

            }
        }
        return true;

    }
    //dota2官网赛事
    public function getGmaeDotaMatch($url,$type){
        $data=[];
        $dpcList=curl_get($url);
        if($dpcList['status']=='success'){
            $return=$dpcList['result'] ?? [];
            $current_season=$return['current_season'] ?? 0;//当前赛季
            $selected_phase=$return['selected_phase'] ?? '';//所有阶段
            $data=$return['data'] ?? [];
            if(count($data) > 0){
                foreach ($data as $k=>&$v){
                    $v['type']=$type;
                    $v['season']=$current_season;

                }
            }

        }
        return $data;
    }

}
