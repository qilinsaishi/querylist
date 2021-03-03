<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class ScheduleService
{
    public function insertScheduleData()
    {
        $gameItem = [
            'kpl', 'lol',  'dota2'//, 'csgo'
        ];

        foreach ($gameItem as $val) {
            $this->insertWanplusSchedule($val);
        }
        return 'finish';
    }

    //英雄联盟资讯采集
    public function insertWanplusSchedule($game)
    {
        if($game=='dota2'){
            $gametype=1;
        }elseif($game=='lol'){
            $gametype=2;
        }elseif($game=='kpl'){
            $gametype=6;
        }elseif($game=='csgo'){
            $gametype=4;
        }
        $AjaxModel = new AjaxRequest();
        $missionModel = new MissionModel();
        //比赛列表
        //获取每周的周一时间;
        $weekday=date("w");
        $weekday=($weekday + 6) % 7;
        $date=strtotime(date('Y-m-d',strtotime("-{$weekday} day")));
        $url='http://www.wanplus.com/ajax/schedule/list';
        $param=[
            'game'=>$gametype,
            'time'=>$date,
            'eids'=>''
        ];
        $list=$AjaxModel->getMatchList($url, $param );
        if(isset($list['scheduleList'])){
            foreach($list['scheduleList'] as $val) {
                //https://www.wanplus.com/schedule/68605.html

                if($val['list']){
                    foreach ($val['list'] as $v){
                        $url='https://www.wanplus.com/schedule/'.$v['scheduleid'].'.html';
                        $params1 = [
                            'game' => $game,
                            'mission_type' => 'schedule',
                            'source_link' => $url,
                        ];
                        $v['url']=$url;
                        $v['game']=$game;
                        $v['source']='wanplus';
                        $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                        $result = $result ?? 0;
                        if ($result <= 0) {
                            $data = [
                                "asign_to" => 1,
                                "mission_type" => 'schedule',//赛事
                                "mission_status" => 1,
                                "game" => $game,
                                "source" => 'wanplus',//
                                'title' => '',
                                'source_link' => $url,
                                "detail" => json_encode($v),
                            ];
                            $insert = (new oMission())->insertMission($data);
                        }

                    }
                }else{
                    continue;
                }
            }
        }
        return true;
    }

}
