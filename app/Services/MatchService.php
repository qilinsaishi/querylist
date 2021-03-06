<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
use App\Models\InformationModel;
use App\Models\Match\scoregg\matchListModel;
use App\Models\Match\scoregg\tournamentModel;
use App\Models\MissionModel;
use App\Models\Team\TotalTeamModel;
use App\Models\TeamModel;
use App\Services\Data\RedisService;
use App\Services\MissionService as oMission;
use QL\QueryList;

class MatchService
{
    const MISSION_REPEAT = 500;//调用重复多少条数量就终止

    public function insertMatchData($game, $force = 0, $week = 0)
    {

        if ($game == 'kpl' || $game == 'lol') {
            //$this->saveMissionByScoreggMatchId(12280,$game);//这个是测试单个比赛的方法案例
            $this->scoreggMatch($game, $force);//scoregg 比赛数据
        }
        if ($game == 'dota2') {
            $this->shangniuMatch($game, $week, $force);
            //$this->wcaMatchList($game, $week, $force);
        }
        //https://ray73.com站点比赛
        //$this->rayMatch($force);//暂时关闭
        /* if ($game == 'dota2') {//这个dota2的数据不用改，是拼接起来的专题。暂时不用改
             $this->getDota2International($game);
             $this->getBilibiliDota2($game);
             $this->pwesports($game);
         }*/

        return 'finish';
    }
    //尚牛赛程
    public function rayMatch($force=0){
        $client = new ClientServices();
        $rayMatchModel=new \App\Models\Match\ray\matchListModel();
        $gameList=["DOTA2","英雄联盟","王者荣耀"];
        $matchPageList=[];
        //获取ray游戏接口
        //============================================获取赛前数据分页列表==================================
        $i=0;
        do{
            $url='https://gameinfo.365raylines.com/v2/match?page='.$i.'&match_type=3';
            $result= $client->curlGet($url);
            $resultData=$result['result'] ?? [];
            $i++;
           $uri='https://gameinfo.365raylines.com/v2/match?page='.$i.'&match_type=3';
           array_push($matchPageList,$uri);

        }
        while(count($resultData)>0);
        //============================================获取赛前数据分页列表==================================
        if(count($matchPageList)>0){
            $mission_repeat = 0;
            foreach ($matchPageList as $pageUrl){
                //echo $pageUrl;
                $result= $client->curlGet($pageUrl);
                $matchList=$result['result'] ?? [];
                if(count($matchList)>0){
                    foreach ($matchList as $matchInfo){
                        if($matchInfo['game_name']=="英雄联盟"){
                            $game='lol';
                        }elseif($matchInfo['game_name']=="王者荣耀"){
                            $game='kpl';
                        }elseif($matchInfo['game_name']=="DOTA2"){
                            $game='dota2';
                        }
                        //判断游戏是否在DOTA2,Kpl,lol三个游戏里面
                        if(in_array($matchInfo['game_name'],$gameList)){
                            //　强制爬取
                            if ($force == 1) {
                                $toGet = 1;
                            } elseif ($force == 0) {
                                //获取当前比赛数据
                                $rayMatchInfo = $rayMatchModel->getMatchById($matchInfo['id']);
                                //找到
                                if (isset($rayMatchInfo['match_id'])) {
                                    $toGet = 0;
                                    $mission_repeat++;
                                    echo "exits-ray-match-matchID:" . $matchInfo['id'] . "\n";
                                    if ($mission_repeat >= self::MISSION_REPEAT) {
                                        echo $game . "ray-match-重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                        return;
                                    }
                                } else {
                                    $mission_repeat = 0;
                                    $toGet = 1;
                                }
                            }
                            if($toGet==1){
                                $rt=$this->getOneRayMatchInfo($matchInfo,$game);
                                if($rt>0){
                                    $mission_repeat = 0;
                                    echo $game . "rayMatchInsertToMission success,match_id:".$matchInfo['id']  . "\n";
                                }else{
                                    $mission_repeat++;
                                    if ($mission_repeat >= self::MISSION_REPEAT) {
                                        echo $game . "ray-match-重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                        return;
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
    public function createMatchMission($game,$detail,$source){

        $detail['source']=$source;
        $detail['game']=$game;
        $detail['type']='match';
        $adata = [
            "asign_to" => 1,
            "mission_type" => 'match',
            "mission_status" => 1,
            "game" => $game,
            "source" => $source,
            "title" => $detail['match_name']??'',
            'source_link' => $detail['match_url']??'',
            "detail" => json_encode($detail),
        ];
        $insert = (new oMission())->insertMission($adata);
        return $insert;
    }

    //https://ray73.com站点比赛采集单条数据入库
    public function getOneRayMatchInfo($matchInfo,$game){
        $gameList=["DOTA2","英雄联盟","王者荣耀"];
        $missionModel = new MissionModel();
            $missionId=0;

            $source='ray';
            $matchInfo['match_url']='https://ray73.com/match/'.$matchInfo['id'];
            $params = [
                'game' => $game,
                'mission_type' => 'match',
                'source_link' => $matchInfo['match_url'] ?? '',
            ];
            $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的赛事任务
            if($missionCount==0){
                $missionId=$this->createMatchMission($game,$matchInfo,$source);
            }else{
                echo $game . "rayMatchMissionExits,match_id".$matchInfo['id'] . "\n";
                return $missionId;
            }
            return  $missionId;

    }

    //尚牛赛程
    public function shangniuMatch($game = 'dota2', $week = 0, $force = 0){
        //赛事赛程列表
        $client = new ClientServices();
        $missionModel = new MissionModel();
        $teamModel=new TeamModel();
        $totalTeamModel=new TotalTeamModel();
        $shangniuMatchModel=new \App\Models\Match\shangniu\matchListModel();
        $curtime = date('Y-m-d', strtotime('Monday') - $week * 7 * 86400);
        for($i = 0; $i < 4; $i++){
            $mission_repeat = 0;
            $time = date('Y-m-d', strtotime($curtime) - $i * 7 * 86400);
            echo "date-".$time . "\n";
            $url='https://www.shangniu.cn/api/game/user/index/getWeekMatchList?gameType=dota&date='.$time;
            $referer_url='https://www.shangniu.cn/live/dota';
            $headers = ['referer' => $referer_url];
            $matchBodyList= $client->curlGet($url, [],$headers);
            $matchBodyList=$matchBodyList['body']??[];
            $matchList=[];
            if(count($matchBodyList)>0){
                //一周的比赛数据
                foreach ($matchBodyList as $matchBodyInfo){
                    $matchList=$matchBodyInfo['matchList'] ?? [];
                    if(count($matchList)>0){
                        //比赛列表
                        foreach ($matchList as $matchInfo){
                            //主队战队信息
                            $homeTeamObj=$teamModel->getTeamBySiteId($matchInfo['homeId'],'shangniu',$game);
                            $homeTid=$homeTeamObj['tid']??0;
                            $homeTotalTeamObj=$totalTeamModel->getTeamById($homeTid);
                            //home_display主队显示信息
                            $matchInfo['home_display']=$homeTotalTeamObj['display'] ?? 0;

                            //客队战队信息
                            $awayTeamObj=$teamModel->getTeamBySiteId($matchInfo['awayId'],'shangniu',$game);
                            $awayId=$awayTeamObj['tid'] ?? 0;
                            $awayTotalTeamObj=$totalTeamModel->getTeamById($awayId);
                            //away_display客队显示状态
                            $matchInfo['away_display']=$awayTotalTeamObj['display'] ?? 0;

                            //　强制爬取
                            if ($force == 1) {
                                $toGet = 1;
                            } elseif ($force == 0) {
                                //获取当前比赛数据
                                $shangniuMatchInfo = $shangniuMatchModel->getMatchById($matchInfo['id']);
                                //找到
                                if (isset($shangniuMatchInfo['match_id'])) {
                                    $toGet = 0;
                                    $mission_repeat++;
                                    echo "exits-shangniu-match-matchID:" . $matchInfo['id'] . "\n";
                                    if ($mission_repeat >= self::MISSION_REPEAT) {
                                        echo $game . "shangniu-match-重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                        return;
                                    }
                                } else {
                                    $mission_repeat = 0;
                                    $toGet = 1;
                                }
                            }
                            if($toGet==1){
                                $source_link='https://www.shangniu.cn/esports/dota-live-'.$matchInfo['id'].'.html';
                                $params = [
                                    'game' => $game,
                                    'mission_type' => 'match',
                                    'source_link' => $source_link ?? '',
                                ];
                                $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的赛事任务
                                echo "missionCount:" . $missionCount . "\n";
                                if($missionCount==0){
                                    $matchInfo['game'] = $game;
                                    $matchInfo['source'] = 'shangniu';
                                    $matchInfo['type'] = 'match';
                                    unset($matchInfo['csgoTeamStats']);
                                    unset($matchInfo['lolTeamStatsList']);
                                    unset($matchInfo['kogTeamStatsList']);
                                    $data = [
                                        "asign_to" => 1,
                                        "mission_type" => 'match',//赛事
                                        "mission_status" => 1,
                                        "game" => $game,
                                        "source" => 'shangniu',//
                                        'title' =>date("Y-m-d H:i:s",$matchInfo['matchTime']).'-match_id'.$matchInfo['id'].'-'.$matchInfo['tournamentName'],
                                        'source_link' => $source_link ?? '',
                                        "detail" => json_encode($matchInfo),
                                    ];

                                    $insert = $missionModel->insertMission($data);
                                    if ($insert) {
                                        $mission_repeat = 0;
                                        echo $game . "MatchShangniuInsertMission:" . $insert . "success" . "\n";
                                    } else {
                                        echo $game . "MatchShangniuInsertMission:" . $insert . "fail" . "\n";
                                    }
                                }else{
                                    $mission_repeat++;
                                    echo $game . "shangniuMatchMissionExits" . "\n";
                                    if ($mission_repeat >= self::MISSION_REPEAT) {
                                        echo $game . "shangniu-match-重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                        return;
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

    public function wcaMatchList($game = 'dota2', $week = 0, $force = 0)
    {
        //赛事赛程列表
        $url = 'https://www.wca.com.cn/e/action/score.php';
        $client = new ClientServices();
        $missionModel = new MissionModel();
        $wcaMatchModel = new \App\Models\Match\wca\matchListModel();
        $curtime = date('Y-m-d', strtotime('sunday') - $week * 7 * 86400);
        for ($i = 0; $i < 4; $i++) {
            $mission_repeat = 0;
            $time = date('Y-m-d', strtotime($curtime) - $i * 7 * 86400);
            echo $time . "\n";
            //每次往前推四周
            $param = [
                'time' => $time,
                'id' => '2',
                'page' => '0',
                'action' => 'score'
            ];
            $headers = ['origin' => 'https://www.wca.com.cn'];

            $wcaScoreList = $client->curlPost($url, $param, $headers);
            if (is_array($wcaScoreList) && count($wcaScoreList) > 0) {
                foreach ($wcaScoreList as $wcaScoreInfo) {
                    echo date("Y-m-d H:i:s", $wcaScoreInfo['time']) . "\n";
                    $wcaScoreMatchList = $wcaScoreInfo['data'] ?? [];
                    if (count($wcaScoreMatchList) > 0) {
                        foreach ($wcaScoreMatchList as $wcaScoreMatchInfo) {
                            //　强制爬取
                            if ($force == 1) {
                                $toGet = 1;
                            } elseif ($force == 0) {
                                //获取当前比赛数据
                                $match_id = str_replace(array('https://www.wca.com.cn/score/dota2/', '/'), '', $wcaScoreMatchInfo['url']);
                                $match_id = $match_id ?? 0;
                                $wcaMatchInfo = $wcaMatchModel->getMatchById($match_id);

                                //找到
                                if (isset($wcaMatchInfo['match_id'])) {
                                    $toGet = 0;
                                    $mission_repeat++;
                                    echo "exits-wca-match-matchID:" . $match_id . "\n";
                                    if ($mission_repeat >= self::MISSION_REPEAT) {
                                        echo $game . "wca-match-重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                        return;
                                    }
                                } else {
                                    $mission_repeat = 0;
                                    $toGet = 1;
                                }
                            }
                            if ($toGet == 1) {//wca_match_list数据库不存在
                                $params = [
                                    'game' => $game,
                                    'mission_type' => 'match',
                                    'source_link' => $wcaScoreMatchInfo['url'] ?? '',
                                ];
                                $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的赛事任务
                                echo "missionCount:" . $missionCount . "\n";
                                $result = $result ?? 0;
                                $wcaScoreMatchInfo['game'] = $game;
                                $wcaScoreMatchInfo['source'] = 'wca';
                                if ($result == 0) {//任务表不存在记录则插入数据
                                    $data = [
                                        "asign_to" => 1,
                                        "mission_type" => 'match',//赛事
                                        "mission_status" => 1,
                                        "game" => $game,
                                        "source" => 'wca',//
                                        'title' => '',
                                        'source_link' => $wcaScoreMatchInfo['url'] ?? '',
                                        "detail" => json_encode($wcaScoreMatchInfo),
                                    ];

                                    $insert = $missionModel->insertMission($data);
                                    if ($insert) {
                                        echo $game . "MatchWcaInsertMission:" . $insert . "success" . "\n";

                                    } else {
                                        echo $game . "MatchWcaInsertMission:" . $insert . "fail" . "\n";
                                    }

                                } else {
                                    $mission_repeat++;
                                    echo $game . "MatchWcaMissionExits" . "\n";
                                    if ($mission_repeat >= self::MISSION_REPEAT) {
                                        echo $game . "wca-match-重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                        return;
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

    //https://www.scoregg.com 赛事
    public function scoreggMatch($game, $force = 0)
    {
        $collectResultModel = new CollectResultModel();
        $missionModel = new MissionModel();
        $scoreggMatchModel = new matchListModel();
        //查询赛事列表
        $tournamentModel = new tournamentModel();
        $tournamentParams = [
            'page_size' => 10,
            'game' => $game
        ];
        $tournamentList = $tournamentModel->getTournamentList($tournamentParams);
        $tournamentList = $tournamentList ?? [];//赛事结果
        if (count($tournamentList) > 0) {
            foreach ($tournamentList as $key => $tournament) {
                echo 'tournament_id:'.$tournament['tournament_id']."\n";
                $mission_repeat = 0;
                $ajax_url = 'https://img1.famulei.com/tr/' . $tournament['tournament_id'] . '.json';
                $cdata = curl_get($ajax_url);//获取赛事下面的一级分类
                $cdata = $cdata ?? [];
                if (count($cdata) > 0) {
                    foreach ($cdata as $k => $v) {
                        if (count($v['round_son']) > 0) {//如果存在二级分类，则获取二级分类
                            foreach ($v['round_son'] as $v1) {
                                $url = 'https://img1.famulei.com/tr_round/' . $v1['id'] . '.json';
                                $round_son = curl_get($url);//获取比赛的具体数据
                                $round_son = $round_son ?? [];
                                if (count($round_son) > 0) {
                                    foreach ($round_son as $k2 => $v2) {//获取每一个比赛的具体数据
                                        $cdetail = $this->getMatchDetail($v2['matchID']);
                                        $cdetail['source'] = 'scoregg';
                                        $cdetail['type'] = 'match';
                                        $cdetail['game'] = $game;
                                        $cdetail['r_type'] = $v['r_type'];//赛事下面的一级分类类型
                                        //　强制爬取
                                        if ($force == 1) {
                                            $toGet = 1;
                                        } elseif ($force == 0) {
                                            //获取当前比赛数据
                                            $scoreggMatchInfo = $scoreggMatchModel->getMatchById($v2['matchID']);
                                            //找到
                                            if (isset($scoreggMatchInfo['match_id'])) {
                                                $toGet = 0;
                                                $mission_repeat++;
                                                echo "exits-round_son_scoregg_match-matchID:" . $v2['matchID'] . "\n";
                                                if ($mission_repeat >= self::MISSION_REPEAT) {
                                                    echo $game . "match-scoregg-round_son重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                                    return;
                                                }
                                            } else {
                                                $mission_repeat = 0;
                                                $toGet = 1;
                                            }
                                        }

                                        if ($toGet == 1) {
                                            $params1 = [
                                                'game' => $game,
                                                'mission_type' => 'match',
                                                'source_link' => 'https://www.scoregg.com/match/' . $v2['matchID'],
                                            ];
                                            $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                                            $result = $result ?? 0;
                                            if ($result == 0) {
                                                $insertMissionResult = $this->saveMissionByScoreggMatchId($v2['matchID'], $game);
                                                $mission_repeat = 0;
                                                if ($insertMissionResult > 0) {
                                                    echo "insert:" . $insertMissionResult . ' matchId:' . $v2['matchID'] . '加入任务成功' . "\n";
                                                } else {
                                                    echo "insert:" . $insertMissionResult . ' matchId:' . $v2['matchID'] . '加入任务失败' . "\n";
                                                }
                                            } else {
                                                //表示Mission表记录已存在，跳出继续
                                                $mission_repeat++;//重复记录加一
                                                echo "exist-mission" . '-source_link:' . 'https://www.scoregg.com/match/' . $v2['matchID'] . "\n";
                                                if ($mission_repeat >= self::MISSION_REPEAT) {
                                                    echo $game . "match-scoregg-round_son重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                                    return;
                                                }
                                            }
                                        }

                                    }
                                }

                            }

                        } else {//不存在二级分类则只能生成一级分类链接
                            $url = 'https://img1.famulei.com/tr_round/p_' . $v['roundID'] . '.json';
                            $roundData = curl_get($url);
                            $roundData = $roundData ?? [];
                            if (count($roundData) > 0) {
                                foreach ($roundData as $v2) {//获取比赛的具体数据
                                    //　强制爬取
                                    if ($force == 1) {
                                        $toGet = 1;
                                    } elseif ($force == 0) {
                                        //获取当前比赛数据
                                        $scoreggMatchInfo = $scoreggMatchModel->getMatchById($v2['matchID']);
                                        //找到
                                        if (isset($scoreggMatchInfo['match_id'])) {
                                            $toGet = 0;
                                            $mission_repeat++;
                                            echo "exits-scoregg_match-matchID:" . $v2['matchID'] . "\n";
                                            if ($mission_repeat >= self::MISSION_REPEAT) {
                                                echo $game . "match-scoregg-round重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                                return;
                                            }
                                        } else {
                                            $mission_repeat = 0;
                                            $toGet = 1;
                                        }
                                    }

                                    if ($toGet == 1) {
                                        $params1 = [
                                            'game' => $game,
                                            'mission_type' => 'match',
                                            'source_link' => 'https://www.scoregg.com/match/' . $v2['matchID'],
                                        ];//过滤已经采集过的数据
                                        $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                                        $result = $result ?? 0;
                                        if ($result == 0) {
                                            $insertMissionResult = $this->saveMissionByScoreggMatchId($v2['matchID'], $game);

                                            if ($insertMissionResult > 0) {
                                                $mission_repeat = 0;
                                                echo "insert:" . $insertMissionResult . ' matchId:' . $v2['matchID'] . '加入任务成功' . "\n";
                                            } else {
                                                echo "insert:" . $insertMissionResult . ' matchId:' . $v2['matchID'] . '加入任务失败' . "\n";
                                            }
                                        } else {
                                            //表示Mission表记录已存在，跳出继续
                                            $mission_repeat++;//重复记录加一
                                            echo "exist-mission" . '-source_link:' . 'https://www.scoregg.com/match/' . $v2['matchID'] . "\n";
                                            if ($mission_repeat >= self::MISSION_REPEAT) {
                                                echo $game . "match-scoregg-round重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                                return;
                                            }
                                        }
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

    public function saveMissionByWcaMatchId($matchID, $game)
    {
        $cdetail = $this->getMatchDetail($matchID);
        $cdetail = $cdetail ?? [];
        $insert = 0;
        if (count($cdetail) > 0) {
            $cdetail['source'] = 'scoregg';
            $cdetail['type'] = 'match';
            $cdetail['game'] = $game;
            $cdetail['tournament_name'] = $cdetail['tournament_name'] ?? '';
            $cdata = [
                "asign_to" => 1,
                "mission_type" => 'match',//赛事
                "mission_status" => 1,
                "game" => $game,
                "source" => 'scoregg',//
                'title' => $game . '-' . $cdetail['tournament_name'] . '-' . $cdetail['round_name'],
                'source_link' => 'https://www.scoregg.com/match/' . $matchID,
                "detail" => json_encode($cdetail),
            ];
            $insert = (new oMission())->insertMission($cdata);
        }

        return $insert;

    }

    public function saveMissionByScoreggMatchId($matchID, $game)
    {
        $cdetail = $this->getMatchDetail($matchID);
        $cdetail = $cdetail ?? [];
        $insert = 0;
        if (count($cdetail) > 0) {
            $cdetail['source'] = 'scoregg';
            $cdetail['type'] = 'match';
            $cdetail['game'] = $game;
            $cdetail['tournament_name'] = $cdetail['tournament_name'] ?? '';
            $cdata = [
                "asign_to" => 1,
                "mission_type" => 'match',//赛事
                "mission_status" => 1,
                "game" => $game,
                "source" => 'scoregg',//
                'title' => $game . '-' . $cdetail['tournament_name'] . '-' . $cdetail['round_name'],
                'source_link' => 'https://www.scoregg.com/match/' . $matchID,
                "detail" => json_encode($cdetail),
            ];
            $insert = (new oMission())->insertMission($cdata);
        }

        return $insert;

    }

    //获取比赛详情
    public function getMatchDetail($matchID)
    {
        $url = 'https://www.scoregg.com/services/api_url.php';
        $param = [
            'api_path' => '/services/match/match_info_new.php',
            'platform' => 'web',
            'method' => 'post',
            'language_id' => 1,
            'matchID' => $matchID,
            'api_version' => '9.9.9'
        ];
        $cdata = curl_post($url, $param);
        $cdata = $cdata['data'] ?? [];
        return $cdata;

    }

    //
    public function updateScoreggMatchList($game, $count = 50)
    {
        $scoreggMatchModel = new matchListModel();
        $redisService = new RedisService();
        $params = [
            'page_size' => $count,
            'page' => 1,
            'next_try' => 1,
            'all' => 1,//表示不管home_id和away_id是否有值
            'game' => $game,
            //'round_detailed' => '0',
            'fields' => "match_id,game,next_try,try,start_time",//game,match_status,match_data,match_pre,home_id,away_id,home_score,away_score"
        ];
        $collectClassList = [];
        $matchList = $scoreggMatchModel->getMatchList($params);//获取round_detailed=0的数据

        //$matchList = array_column($matchList, 'match_id');
        $matchList = $matchList ?? [];
        $classList = [];
        if (count($matchList) > 0) {
            foreach ($matchList as &$val) {

                echo 'start_time:' . $val['start_time'] . "-match_id:" . $val['match_id'] . "\n";
                $rt = $this->updateOneScoreggMatchList( $val['match_id'], $game, $val['next_try'],$val['try']);
                if ($rt > 0) {
                    echo "match_id：" . $val['match_id'] . "更新成功" . "\n";
                } else {
                    echo "match_id：" . $val['match_id'] . "更新失败" . "\n";
                }

            }

            $redisService->refreshCache("matchList",['game' =>[$game]]);

        }
        return "第" . $params['page'] . "页游戏" . $params['game'] . "执行完毕";

    }
    //查询查询wcaMatchList里面的数据
    public function updateWcaMatchListStatus($game, $count = 50){
        $wcaMatchModel=new \App\Models\Match\wca\matchListModel();
        $missionModel=new MissionModel();
        $params = [
            'page_size' => $count,
            'page' => 1,
            'game' => $game,
            'start' => 1,//表示启动开始时间条件
            'all' => 1,//表示不管home_id和away_id是否有值
            //'match_status' =>0,//未开赛
            'fields' => "match_id,game,start_time,away_logo,home_logo,game_bo,tournament_id,home_name,away_name",
        ];
        $collectClassList = [];
        $matchCache = [];
        $match_delete = 0;
        $wcaMatchList = $wcaMatchModel->getMatchList($params);//未完成的比赛
        //$wcaMatchList = array_column($wcaMatchList, 'match_id');
        $wcaMatchList = $wcaMatchList ?? [];
        $rt=0;
        if (count($wcaMatchList) > 0) {
            foreach ($wcaMatchList as &$val) {
                echo 'start_time:' . date('Y-m-d H:i:s') . "wca-match_id:" . $val['match_id'] . "\n";
                //================创建任务=======================
                $cdetail['source'] = 'wca';
                $cdetail['start_time'] = strtotime($val['start_time'] );
                $cdetail['home_logo'] = $val['home_logo'];
                $cdetail['away_logo'] = $val['away_logo'];
                $cdetail['home_name'] = $val['home_name'];
                $cdetail['away_name'] = $val['away_name'];
                $cdetail['tournament_id'] = $val['tournament_id'];
                $cdetail['game_bo'] = $val['game_bo'];
                $cdetail['url'] = 'https://www.wca.com.cn/score/dota2/' . $val['match_id']."/";
                $cdetail['game'] = $game;
                $cdetail['title'] = 'wcaMatchId:'.$val['match_id'] ?? '';
                $cdata = [
                    "asign_to" => 1,
                    "mission_type" => 'match',//赛事
                    "mission_status" => 1,
                    "game" => $game,
                    "source" => 'wca',//
                    'title' =>'wcaMatchId:'.$val['match_id'] ?? '',
                    'source_link' => 'https://www.wca.com.cn/score/dota2/' . $val['match_id']."/",
                    "detail" => json_encode($cdetail),
                ];
                $insert_mission=0;
                $insert_mission = $missionModel->insertMission($cdata);
                //============================创建任务=====================================
                if ($insert_mission > 0) {

                    //========================对任务进行进一步处理collect_result================================
                    $mission = $missionModel->getMissionbyId($insert_mission);

                    //判断类库存在
                    if (!isset($collectClassList[$game])) {
                        $className = "App\Collect\match\\" . $game . '\wca';
                        if (class_exists($className)) {
                            $collectClassList[$game] = new $className;
                        }
                    }

                    $collectClass = $collectClassList[$game];
                    $mission['detail'] = json_decode($mission['detail'], true);
                    $collectData = $collectClass->collect($mission);
                    //=========================对任务进行进一步处理collect_result===============================

                    //=========================同步到数据库wca_match_list===============================
                    $collectData['content'] = json_decode($collectData['content'], true);
                    ksort($collectData['content']);

                    $processData = $collectClass->process($collectData);

                    unset($processData['match_list'][0]['tournament_name']);
                    $rt = $wcaMatchModel->saveMatch($processData['match_list'][0]);

                    if ($rt>0) {
                        echo "match_id：" . $val['match_id'] . "wcaMatchList更新成功" . "\n";
                        //任务状态更新为2
                        $missionModel->updateMission($insert_mission, ['mission_status' => 2]);
                    } else {
                        //任务状态更新为3
                        $missionModel->updateMission($insert_mission, ['mission_status' => 3]);
                        $match_delete++;
                        $matchCache[$game] = $match_delete;
                        echo "match_id：" . $val['match_id'] . "更新失败：wca站点的match_id被删除" . "\n";
                    }

                } else {
                    //任务状态更新为3
                    $updateData['match_status'] = 3;
                    $wcaMatchModel->updateMatch($val['match_id'], $updateData);
                    $missionModel->updateMission($insert_mission, ['mission_status' => 3]);
                }


            }
            //=========================同步到数据库wca_match_list===============================

            if (count($matchCache) > 0) {//原站点删除才会刷新缓存
                $redisService = new RedisService();
                $redesReturn = $redisService->refreshCache('matchList', ['game' => array_keys($matchCache)]);
            }

        }
        return "第" . $params['page'] . "页游戏" . $params['game'] . "执行完毕";
    }
    //查询查询shangniuMatchList里面的数据
    public function updateShangniuMatchListStatus($game, $count = 50){
        $shangniuMatchModel=new \App\Models\Match\shangniu\matchListModel();
        $redisService = new RedisService();
        $missionModel=new MissionModel();
        $params = [
            'page_size' => $count,
            'page' => 1,
            'game' => $game,
            'next_try' => 1,
            //'round_detailed' => '0',
            'all' => 1,//表示不管home_id和away_id是否有值
            'fields' => "match_id,game,next_try,try,tournament_id,home_display,away_display",
        ];
        $collectClassList = [];
        $matchCache = [];
        $match_delete = 0;
        $shangniuMatchList = $shangniuMatchModel->getMatchList($params);//未完成的比赛

        $shangniuMatchList = $shangniuMatchList ?? [];
        $rt=0;
        if (count($shangniuMatchList) > 0) {
            foreach ($shangniuMatchList as &$val) {
                echo  "shangniu-match_id:" . $val['match_id'] . "\n";
                $rt=$this->updateOneShangMatchList($val['match_id'], $game,$val['next_try'],$val['try'],$val['tournament_id'],$val['home_display'],$val['away_display']);
                if ($rt > 0) {
                    echo "match_id：" . $val['match_id'] . "更新成功" . "\n";
                } else {
                    echo "match_id：" . $val['match_id'] . "更新失败" . "\n";
                }


            }
            //=========================刷新缓存===============================
            $redisService->refreshCache("matchList",['game' =>[$game]]);


        }
        return "第" . $params['page'] . "页游戏" . $params['game'] . "执行完毕";
    }
    //封装更新一条shangniuMatchList数据
    public function updateOneShangMatchList($match_id, $game,$next_try=0,$try=0,$tournament_id=0,$home_display=0,$away_display=0)
    {
        $shangniuMatchModel=new \App\Models\Match\shangniu\matchListModel();
        $redisService = new RedisService();
        $missionModel=new MissionModel();
        $rt = 0;
        //================创建任务=======================
        $cdetail['next_try'] = $next_try;
        $cdetail['try'] = $try;
        $cdetail['home_display'] = $home_display;
        $cdetail['away_display'] = $away_display;
        $cdetail['source'] = 'shangniu';
        $cdetail['id'] = $match_id;
        $cdetail['url'] = 'https://www.shangniu.cn/esports/dota-live-'.$match_id.'.html';
        $cdetail['game'] = $game;
        $cdetail['act'] = 'update';
        $cdetail['type'] = 'match';
        $cdetail['tournamentId'] = $tournament_id;


        $cdetail['title'] = 'shangniuMatchId:'.$match_id ?? '';
        $cdata = [
            "asign_to" => 1,
            "mission_type" => 'match',//赛事
            "mission_status" => 1,
            "game" => $game,
            "source" => 'shangniu',//
            'title' =>'shangniuMatchId:'.$match_id ?? '',
            'source_link' => 'https://www.shangniu.cn/esports/dota-live-'.$match_id.'.html',
            "detail" => json_encode($cdetail),
        ];
        $insert_mission=0;
        $insert_mission = $missionModel->insertMission($cdata);

        //============================创建任务=====================================
        if ($insert_mission > 0) {

            //========================对任务进行进一步处理collect_result================================
            $mission = $missionModel->getMissionbyId($insert_mission);

            //判断类库存在
            if (!isset($collectClassList[$game])) {
                $className = "App\Collect\match\\" . $game . '\shangniu';
                if (class_exists($className)) {
                    $collectClassList[$game] = new $className;
                }
            }

            $collectClass = $collectClassList[$game];
            $mission['detail'] = json_decode($mission['detail'], true);
            $collectData = $collectClass->collect($mission);
            //=========================对任务进行进一步处理collect_result===============================

            //=========================同步到数据库wca_match_list===============================
            $collectData['content'] = json_decode($collectData['content'], true);
            ksort($collectData['content']);

            $processData = $collectClass->process($collectData);

            unset($processData['match_list'][0]['tournament_name']);
            \DB::connection()->enableQueryLog();
            $rt = $shangniuMatchModel->saveMatch($processData['match_list'][0]);

            if ($rt>0) {
                echo "match_id：" . $match_id . "shangniuMatchList更新成功" . "\n";

                if(isset($rt['site_id']) && isset($rt['source']) && isset($rt['game']))
                {
                    $data = ["api_id"=>2,"data_type"=>"match","site_id"=>$rt['site_id'],"source"=>$rt['source'],"game"=>$rt['game']];
                    //$return = curl_post(config("app.api_url")."/submit",json_encode($data));
                }

                //任务状态更新为2
                $missionModel->updateMission($insert_mission, ['mission_status' =>2]);
                $redisService->refreshCache("matchDetail",['game' =>$game,"match_id" => $match_id]);
                sleep(1);
            } else {
                //任务状态更新为3
                $missionModel->updateMission($insert_mission, ['mission_status' => 3]);

                echo "match_id：" . $match_id . "更新失败：shangniu站点的match_id被删除" . "\n";
            }

        } else {

            $missionModel->updateMission($insert_mission, ['mission_status' => 3]);
        }


        return $rt;

    }




    //封装更新一条ScoreggMatchList数据
    public function updateOneScoreggMatchList($match_id, $game,$next_try=0,$try=0)
    {
        $scoreggMatchModel = new matchListModel();
        $missionModel = new MissionModel();
        $redisService = new RedisService();
        $rt = 0;
        $insert_mission = $this->saveMissionByScoreggMatchId($match_id, $game);
        if ($insert_mission > 0) {
            $mission = $missionModel->getMissionbyId($insert_mission);
            //判断类库存在
            if (!isset($collectClassList[$game])) {
                $className = "App\Collect\match\\" . $game . '\scoregg';
                if (class_exists($className)) {
                    $collectClassList[$game] = new $className;
                }
            }

            $collectClass = $collectClassList[$game];
            $mission['detail'] = json_decode($mission['detail'], true);
            $mission['detail']['type'] = 'match';
            $mission['detail']['next_try'] = $next_try;
            $mission['detail']['try'] = $try;
            $mission['detail']['act'] = 'update';
            $collectData = $collectClass->collect($mission);

            $collectData['content'] = json_decode($collectData['content'], true);

            ksort($collectData['content']);

            $processData = $collectClass->process($collectData);
            if(isset($processData['match_list'][0]['round']) && count($processData['match_list'][0]['round'])>0 ){
                unset($processData['match_list'][0]['round']);
            }

            $rt = $scoreggMatchModel->saveMatch($processData['match_list'][0]);
            if ($rt) {
                if(isset($rt['site_id']) && isset($rt['source']) && isset($rt['game']))
                {
                    $data = ["api_id"=>2,"data_type"=>"match","site_id"=>$rt['site_id'],"source"=>$rt['source'],"game"=>$rt['game']];
                    //$return = curl_post(config("app.api_url")."/submit",json_encode($data));
                }
                //任务状态更新为2
                $missionModel->updateMission($insert_mission, ['mission_status' => 2]);
                $redisService->refreshCache("matchDetail",['game' =>$game,"match_id" =>$match_id]);
                sleep(1);
            } else {
                //任务状态更新为3
                $missionModel->updateMission($insert_mission, ['mission_status' => 3]);
            }

        } else {
            //任务状态更新为3
            $updateData['round_detailed'] = 1;//原站点数据删除，把round_detailed转成1；
            $updateData['match_status'] = 3;
            $scoreggMatchModel->updateMatch($match_id, $updateData);
            $missionModel->updateMission($insert_mission, ['mission_status' => 3]);
        }

        return $rt;

    }
    public function setDota2TournamentDisplay()
    {
        $tournamentModel = new \App\Models\Match\shangniu\tournamentModel();
        $matchListModel = new \App\Models\Match\shangniu\matchListModel();
        $tournamentList = $tournamentModel->getTournamentList(['page_size'=>2000,"fields"=>"tournament_id","all"=>1]);
        foreach ($tournamentList as $tournament)
        {
            $matchList = $matchListModel->getMatchList(["tournament_id"=>$tournament['tournament_id'],"page_size"=>1,"all"=>0,"fields"=>'match_id']);
            if(count($matchList)>0)
            {
                $tournamentModel->updateTournament($tournament['tournament_id'],['display'=>1]);
            }
            else
            {
            //    $tournamentModel->updateTournament($tournament['tournament_id'],['display'=>0]);
            }
        }
    }


}
