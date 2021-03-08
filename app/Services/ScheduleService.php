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
            if($val=='dota2'){
                $this->tournament($val);
            }
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

    public function tournament($game){
        $data=[];
        $missionModel = new MissionModel();
        $count=5;
        for($i=1;$i<=$count;$i++){
            $url='https://www.dota2.com.cn/Activity/gamematch/index'.$i.'.htm';
            $item=QueryList::get($url)->rules(array(
                'title' => array('.brief h3','text'),//标题
                'desc' => array('.brief p','text'),//描述
                'logo' => array('img','src'),//图片
                'link' => array('a ','href')//链接
            ))->range('.content .activities  .activity')->queryData();
            if(count($item) >0){
                foreach ($item as $key=>$val){
                    if(strpos($val['link'],'pwesports.cn')!==false) {
                        $type=str_replace(array('https://','.pwesports.cn/'),'',$val['link']);
                        $detail_url='https://esports.wanmei.com/'.$type.'-match/latest';
                        $val['link']=$detail_url;
                        $params1 = [
                            'game' => $game,
                            'mission_type' => 'match',
                            'title' => $val['title'],
                        ];

                        $val['game']=$game;
                        $val['source']='gamedota2';
                        $val['type']='tournament';
                        $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                        $result = $result ?? 0;
                        if ($result == 0) {
                            $data = [
                                "asign_to" => 1,
                                "mission_type" => 'match',//赛事
                                "mission_status" => 1,
                                "game" => $game,
                                "source" => 'gamedota2',//
                                'title' => $val['title'],
                                'source_link' => $val['link'],
                                "detail" => json_encode($val),
                            ];
                            $insert = (new oMission())->insertMission($data);
                        }

                    }
                }
            }

        }

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
                $val['type']='tournament';
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
