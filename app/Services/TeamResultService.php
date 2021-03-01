<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
use App\Models\MissionModel;
use App\Models\TeamModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class TeamResultService
{
    public function insertTeamData($mission_type)
    {
        $gameItem = [
             'lol',  'kpl','dota2'//,  'csgo'
        ];

        foreach ($gameItem as $val) {
            //采集玩加（www.wanplus.com）战队信息
            $this->insertWanplus($val,$mission_type);
            //采集cpseo（2cpseo.com）战队信息
           // $this->insertCpseoTeam($val,$mission_type);

        }
        return 'finish';
    }
    //玩加电竞（wanplus）
    public function insertWanplus($game,$mission_type){

        $AjaxModel = new AjaxRequest();
        $missionModel=new MissionModel();
        $teamModel=new TeamModel();
        if($game=='dota2'){
            $totalPage=24;
            $gametype=1;
        }elseif($game=='lol'){
            $totalPage=17;
            $gametype=2;
        }elseif($game=='kpl'){
            $totalPage=4;
            $gametype=6;
        }elseif($game=='csgo'){
            $totalPage=10;
            $gametype=4;
        }
        for($i=1;$i<=$totalPage;$i++) {
            $url='https://www.wanplus.com/ajax/detailranking?country=0&type=1&teamPage='.$i.'&game='.$gametype;
            $list=$AjaxModel->ajaxGetData($url );
            if(!empty($list) && count($list)>0){
                foreach ($list as $val){
                    $team_url='https://www.wanplus.com/'.$game.'/'.$mission_type.'/'.$val['teamid'];
                    $params = [
                        'game' => $game,
                        'mission_type' => $mission_type,
                        'source_link' => $team_url,
                    ];

                    $site_id=$val['teamid'] ?? 0;
                    if($site_id > 0){
                        $teamInfo=$teamModel->getTeamBySiteId($site_id);
                        if(empty($teamInfo)){
                            $result =$missionModel->getMissionCount($params);//过滤已经采集过的文章
                            $result = $result ?? 0;
                            $title=$val['teamname'] ?? '';
                            if ($result <= 0 && $title!='') {
                                $data = [
                                    "asign_to"=>1,
                                    "mission_type"=>$mission_type,
                                    "mission_status"=>1,
                                    "game"=>$game,
                                    "source"=>'wanplus',
                                    "title"=>$title,
                                    'source_link'=>$team_url,
                                    "detail"=>json_encode(
                                        [
                                            "url"=>$team_url,
                                            "game"=>$game,//lol
                                            "source"=>'wanplus',
                                            "title"=>$title,
                                            "country"=>$val['country'] ?? '',
                                            "teamalias"=>$val['teamalias'] ?? '',
                                            'site_id'=>$val['teamid']
                                        ]
                                    ),
                                ];
                                $insert = (new oMission())->insertMission($data);
                            }
                        }
                    }else{
                        continue;
                    }

                }
            }

        }
        return true;
    }
    //玩加电竞（wanplus）
    public function insertWanplusTeam($game,$mission_type){
        $collectModel=new CollectUrlModel();
        $missionModel=new MissionModel();
        $cdata=$collectModel->getDataFromUrl($game,$mission_type,'wanplus');

        if($cdata){
            foreach ($cdata as $val){
                $t1 = microtime(true);
                $params = [
                    'game' => $game,
                    'mission_type' => $mission_type,
                    'source_link' => $val['url'],
                ];
                $urlArr=explode('team/',$val['url']);
                $site_id=$urlArr[1] ?? 0;
                $teamModel=new TeamModel();
                if($site_id>0){
                    $teamInfo=$teamModel->getTeamBySiteId($site_id);
                    if(empty($teamInfo)){
                        $result =$missionModel->getMissionCount($params);//过滤已经采集过的文章
                        $result = $result ?? 0;
                        if ($result <= 0) {
                            $data = [
                                "asign_to"=>1,
                                "mission_type"=>$val['mission_type'],
                                "mission_status"=>1,
                                "game"=>$val['game'],
                                "title"=>$val['title'],
                                "source"=>$val['source'],
                                'source_link'=>$val['url'],
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

                $t2 = microtime(true);
               // echo '耗时'.round($t2-$t1,3).'秒';

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
            }elseif($game=='dota2'){
                $url = 'http://www.2cpseo.com/teams/dota2/p-' . $m;
                $count=10;
            }/*elseif($game=='csgo'){
                $count=12;
            }*/


            if(isset($url)){
                $ql = QueryList::get($url);
                $links = $ql->find('.team-list a')->attrs('href')->all();
                if (isset($links) && $links) {
                    $missionModel=new MissionModel();
                    $teamModel=new TeamModel();
                    foreach ($links as $v) {
                        $params=[
                            'game'=>$game,
                            'mission_type'=>$mission_type,
                            'source_link'=>$v,
                        ];
                        $urlArr=explode('team/',$v);
                        $site_id=$urlArr[1] ?? 0;
                        if($site_id>0) {
                            $teamInfo = $teamModel->getTeamBySiteId($site_id);//过滤已经采集过的数据
                            if (empty($teamInfo)) {
                                $result=$missionModel->getMissionCount($params);//过滤已经加入过的任务
                                $result=$result ?? 0;
                                if($result <=0){
                                    $data = [
                                        "asign_to" => 1,
                                        "mission_type" => $mission_type,//赛事
                                        "mission_status" => 1,
                                        "game" => $game,
                                        "source" => 'cpseo',//
                                        'source_link'=>$v,
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
                                        //echo "insert:" . $insert . ' lenth:' . strlen($data['detail']);
                                    }
                                }
                            }
                        }


                    }
                }
            }else{
                echo $url;exit;
            }

        }



        return true;
    }


}
