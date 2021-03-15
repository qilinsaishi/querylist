<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
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
            if($val=='kpl' || $val=='lol'){
                $this->scoreggMatch($val);//scoregg 比赛数据
            }
            if($val=='dota2'){
                $this->getDota2International($val);
                $this->getBilibiliDota2($val);
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
                $val['subtype']='gamedota2';
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
                    echo "insert:".$insert.' lenth:'.strlen($data['detail'])."\n";
                }else{
                    echo "exits"."\n";
                    continue;
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
                    $v['link']='https://esports.wanmei.com/'.$type.'-match/latest';
                    $v['season']=$current_season;
                }
            }

        }
        return $data;
    }
    //沙星杯
    public function getBilibiliDota2($game){
        $data=[];
        $bilibiList=curl_get('https://api.bilibili.com/x/esports/matchs/top?aid=51&pn=1&ps=44&sort=1&etime=2021-03-08&tp=0');
        $cdata=$bilibiList['data']['list'] ?? [];
        $missionModel = new MissionModel();
        if(count($cdata) >0) {
            foreach ($cdata as $val){
                $params1 = [
                    'game' => $game,
                    'mission_type' => 'match',
                    'title' => 'bilibili'.$val['id'],
                ];
                $val['season']['logo']='https://i0.hdslb.com/'.$val['season']['logo'];
                $val['home_team']['logo']='https://i0.hdslb.com/'.$val['home_team']['logo'];
                $val['away_team']['logo']='https://i0.hdslb.com/'.$val['away_team']['logo'];
                $val['stime']=date("Y-m-d H:i:s",$val['stime']);
                $val['etime']=date("Y-m-d H:i:s",$val['etime']);
                $val['game']=$game;
                $val['source']='gamedota2';
                $val['type']='match';
                $val['link']='https://www.bilibili.com/blackboard/activity-KQm-HYV7F.html?aid=51';
                $val['subtype']='bilibili';
                $detail=[];
                $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                $result = $result ?? 0;
                if ($result == 0) {
                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'match',//赛事
                        "mission_status" => 1,
                        "game" => $game,
                        "source" => 'gamedota2',//
                        'title' => 'bilibili'.$val['id'],
                        'source_link' => '',
                        "detail" => json_encode($val),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "insert:".$insert.' lenth:'.strlen($data['detail'])."\n";
                }else{
                    echo "exits"."\n";
                    continue;
                }

            }
        }
        return true;

    }
    //2019刀塔国际邀请赛
    public function getDota2International($game){
        $data=[];
        $bilibiList=curl_get('https://www.dota2.com.cn/international/2019/rank?task=main_map');
        $cdata=$bilibiList['result'] ?? [];
        $missionModel = new MissionModel();
        if(count($cdata) >0) {
            foreach ($cdata as $val){
                $params1 = [
                    'game' => $game,
                    'mission_type' => 'match',
                    'title' => 'international'.$val['win_team_id'],
                ];

                $val['game']=$game;
                $val['source']='gamedota2';
                $val['type']='match';
                $val['link']='https://www.dota2.com.cn/international/2019/overview';
                $val['subtype']='international';
                $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                $result = $result ?? 0;
                if ($result == 0) {
                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'match',//赛事
                        "mission_status" => 1,
                        "game" => $game,
                        "source" => 'gamedota2',//
                        'title' => 'international'.$val['win_team_id'],
                        'source_link' => '',
                        "detail" => json_encode($val),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "insert:".$insert.' lenth:'.strlen($data['detail'])."\n";
                }else{
                    echo "exits"."\n";
                    continue;
                }

            }
        }
        return true;

    }
    public function scoreggMatch($game){
        $collectResultModel=new CollectResultModel();
        $missionModel = new MissionModel();
        $collectResult=$collectResultModel->getCollectResult($game,'match','scoregg');
        $collectResult=$collectResult ?? [];
        if(count($collectResult) > 0) {
            foreach ($collectResult  as $val){
                $cdata=curl_get($val['source_link']);//获取赛事下面的一级分类
                $cdata=$cdata ?? [];
                if(count($cdata)>0){
                    foreach ($cdata as $v){
                        if(count($v['round_son']) >0) {//如果存在二级分类，则获取二级分类
                            foreach ($v['round_son'] as $v1){
                                $url='https://img1.famulei.com/tr_round/'.$v1['id'].'.json';
                                $arr=curl_get($url);//获取比赛的具体数据
                                $arr=$arr ?? [];
                                if(count($arr) > 0){
                                    foreach ($arr as $v2){//获取每一个比赛的具体数据
                                        $v2['r_type']=$v['r_type'];
                                        $v2['roundID']=$v['roundID'];
                                        $v2['round_name']=$v['name'];
                                        $v2['tournamentID']=$v['tournamentID'];
                                        $v2['source']='scoregg';
                                        $v2['type']='match';
                                        $v2['game']=$game;
                                        $v2['round_son_id']=$v1['id'];
                                        $v2['round_son_pid']=$v['roundID'];
                                        $v2['round_son_name']=$v1['name'];
                                        ///////////////
                                        $params1 = [
                                            'game' => $game,
                                            'mission_type' => 'match',
                                            'source_link' =>'https://www.scoregg.com/match/'.$v2['matchID'],
                                        ];
                                        $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                                        $result = $result ?? 0;
                                        if($result==0){
                                            $cdata = [
                                                "asign_to" => 1,
                                                "mission_type" => 'match',//赛事
                                                "mission_status" => 1,
                                                "game" => $game,
                                                "source" => 'scoregg',//
                                                'title' =>$v['name'].'-'.$v1['name'].$v2['matchID'],
                                                'source_link' => 'https://www.scoregg.com/match/'.$v2['matchID'],
                                                "detail" => json_encode($v2),
                                            ];
                                            $insert = (new oMission())->insertMission($cdata);
                                            echo "insert:".$insert.' lenth:'.strlen($cdata['detail'])."\n";
                                        }else{
                                            echo "exits"."\n";
                                            continue;
                                        }

                                    }
                                }

                            }

                        }else{//不存在二级分类则只能生成一级分类链接
                            $url='https://img1.famulei.com/tr_round/p_'.$v['roundID'].'.json';
                            $arr=curl_get($url);
                            $arr=$arr ?? [];
                            if(count($arr) > 0){
                                foreach ($arr as $v2){//获取比赛的具体数据
                                    $v2['r_type']=$v['r_type'];
                                    $v2['roundID']=$v['roundID'];
                                    $v2['round_name']=$v['name'];
                                    $v2['tournamentID']=$v['tournamentID'];
                                    $v2['source']='scoregg';
                                    $v2['type']='match';
                                    $v2['game']=$game;
                                    $v2['round_son_id']='';
                                    $v2['round_son_pid']='';
                                    $v2['round_son_name']='';
                                    ///////////////
                                    $params1 = [
                                        'game' => $game,
                                        'mission_type' => 'match',
                                        'source_link' => 'https://www.scoregg.com/match/'.$v2['matchID'],
                                    ];//过滤已经采集过的数据
                                    $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                                    $result = $result ?? 0;
                                    if($result==0){
                                        $cdata = [
                                            "asign_to" => 1,
                                            "mission_type" => 'match',//赛事
                                            "mission_status" => 1,
                                            "game" => $game,
                                            "source" => 'scoregg',//
                                            'title' =>$game.'-'.$v['name'].'-'.$v2['matchID'],
                                            'source_link' =>'https://www.scoregg.com/match/'.$v2['matchID'],
                                            "detail" => json_encode($v2),
                                        ];
                                        $insert = (new oMission())->insertMission($cdata);
                                        echo "insert:".$insert.' lenth:'.strlen($cdata['detail'])."\n";
                                    }else{
                                        echo "exits"."\n";
                                        continue;
                                    }

                                }
                            }

                        }
                    }
                }
            }
        }
        return true;

    }


}
