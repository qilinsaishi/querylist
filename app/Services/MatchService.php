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
use App\Services\MissionService as oMission;
use QL\QueryList;

class MatchService
{
    public function insertMatchData($game,$force)
    {

        if ($game == 'kpl' || $game == 'lol') {
            //$this->saveMissionByScoreggMatchId(12280,$game);//这个是测试单个比赛的方法案例
            $this->scoreggMatch($game,$force);//scoregg 比赛数据
        }
        /* if ($game == 'dota2') {//这个dota2的数据不用改，是拼接起来的专题。暂时不用改
             $this->getDota2International($game);
             $this->getBilibiliDota2($game);
             $this->pwesports($game);
         }*/

        return 'finish';
    }


    public function pwesports($game)
    {
        $data1 = $this->getGmaeDotaMatch('https://esports.wanmei.com/dpc-match/latest', 'dpc');//DPC
        $data2 = $this->getGmaeDotaMatch('https://esports.wanmei.com/pwl-match/latest', 'pwl');//PWL
        $missionModel = new MissionModel();
        $cdata = array_merge($data1, $data2);
        if (count($cdata) > 0) {
            foreach ($cdata as $val) {
                $params1 = [
                    'game' => $game,
                    'mission_type' => 'match',
                    'title' => $val['id'],
                ];

                $val['game'] = $game;
                $val['source'] = 'gamedota2';//来源dota2.com.cn
                $val['type'] = 'match';
                $val['subtype'] = 'gamedota2';//官网
                $result = $missionModel->getMissionCount($params1);//过滤已经采集过的赛事任务
                $result = $result ?? 0;
                if ($result == 0) {//任务表不存在记录则插入数据
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
                    echo $game . "match-gamedota2-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                } else {
                    echo "exits-match-gamedota2-" . $val['id'] . "\n";
                }

            }
        }
        return true;

    }

    //dota2官网赛事
    public function getGmaeDotaMatch($url, $type)
    {
        $data = [];
        $dpcList = curl_get($url);
        if ($dpcList['status'] == 'success') {
            $return = $dpcList['result'] ?? [];
            $current_season = $return['current_season'] ?? 0;//当前赛季
            $selected_phase = $return['selected_phase'] ?? '';//所有阶段
            $data = $return['data'] ?? [];
            if (count($data) > 0) {
                foreach ($data as $k => &$v) {
                    $v['link'] = 'https://esports.wanmei.com/' . $type . '-match/latest';
                    $v['season'] = $current_season;
                }
            }

        }
        return $data;
    }

    //沙星杯
    public function getBilibiliDota2($game)
    {
        $data = [];
        $bilibiList = curl_get('https://api.bilibili.com/x/esports/matchs/top?aid=51&pn=1&ps=44&sort=1&etime=2021-03-08&tp=0');
        $cdata = $bilibiList['data']['list'] ?? [];//来源bilibil列表
        $missionModel = new MissionModel();
        if (count($cdata) > 0) {
            foreach ($cdata as $val) {
                $params1 = [
                    'game' => $game,
                    'mission_type' => 'match',
                    'title' => 'bilibili' . $val['id'],
                ];
                $val['season']['logo'] = 'https://i0.hdslb.com/' . $val['season']['logo'];//赛事logo
                $val['home_team']['logo'] = 'https://i0.hdslb.com/' . $val['home_team']['logo'];//主队logo
                $val['away_team']['logo'] = 'https://i0.hdslb.com/' . $val['away_team']['logo'];//客队logo
                $val['stime'] = date("Y-m-d H:i:s", $val['stime']);//开始时间
                $val['etime'] = date("Y-m-d H:i:s", $val['etime']);//结束时间
                $val['game'] = $game;
                $val['source'] = 'gamedota2';//官网
                $val['type'] = 'match';
                $val['link'] = 'https://www.bilibili.com/blackboard/activity-KQm-HYV7F.html?aid=51';
                $val['subtype'] = 'bilibili';
                $detail = [];
                $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                $result = $result ?? 0;
                if ($result == 0) {//表示任务表不存在记录，则插入数据
                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'match',//赛事
                        "mission_status" => 1,
                        "game" => $game,
                        "source" => 'gamedota2',//
                        'title' => 'bilibili' . $val['id'],
                        'source_link' => '',
                        "detail" => json_encode($val),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                } else {
                    echo "exits" . "\n";//Mission 表存在记录跳过
                }

            }
        }
        return true;

    }

    //2019刀塔国际邀请赛
    public function getDota2International($game)
    {
        $data = [];
        $bilibiList = curl_get('https://www.dota2.com.cn/international/2019/rank?task=main_map');//接口链接
        $cdata = $bilibiList['result'] ?? [];
        $missionModel = new MissionModel();
        if (count($cdata) > 0) {//接口返回数组
            foreach ($cdata as $val) {
                $params1 = [
                    'game' => $game,
                    'mission_type' => 'match',
                    'title' => 'international' . $val['win_team_id'],
                ];

                $val['game'] = $game;
                $val['source'] = 'gamedota2';//官网
                $val['type'] = 'match';//赛事
                $val['link'] = 'https://www.dota2.com.cn/international/2019/overview';//来源链接
                $val['subtype'] = 'international';
                $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                $result = $result ?? 0;
                if ($result == 0) {//表示Mission 不存在则插入数据
                    $data = [
                        "asign_to" => 1,
                        "mission_type" => 'match',//赛事
                        "mission_status" => 1,
                        "game" => $game,
                        "source" => 'gamedota2',//
                        'title' => 'international' . $val['win_team_id'],
                        'source_link' => $val['link'],
                        "detail" => json_encode($val),
                    ];
                    $insert = (new oMission())->insertMission($data);
                    echo "insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                } else {
                    echo "exits" . "\n";//表示Mission 任务表不存记录
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
            'page_size' => 150,
            'game' => $game
        ];
        $tournamentList = $tournamentModel->getTournamentList($tournamentParams);
        $mission_repeat = 0;
        $tournamentList = $tournamentList ?? [];//赛事结果
        if (count($tournamentList) > 0) {

            foreach ($tournamentList as $key => $tournament) {
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
                                                $mission_repeat ++;
                                                echo "exits-round_son_scoregg_match-matchID:" . $v2['matchID'] . "\n";
                                                if($mission_repeat>=100)
                                                {
                                                    echo "重复任务过多，任务终止\n";
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
                                                if ($insertMissionResult) {
                                                    echo "insert:" . $insertMissionResult . ' matchId:' . $v2['matchID'] . '加入任务成功' . "\n";
                                                } else {
                                                    echo "insert:" . $insertMissionResult . ' matchId:' . $v2['matchID'] . '加入任务失败' . "\n";
                                                }
                                            } else {
                                                //表示Mission表记录已存在，跳出继续
                                                $mission_repeat ++ ;//重复记录加一
                                                echo "exist-mission" . '-source_link:' . 'https://www.scoregg.com/match/' . $v2['matchID'] . "\n";
                                                if($mission_repeat>=100)
                                                {
                                                    echo "重复任务过多，任务终止\n";
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
                                            $mission_repeat ++;
                                            echo "exits-scoregg_match-matchID:" . $v2['matchID'] . "\n";
                                            if($mission_repeat>=100)
                                            {
                                                echo "重复任务过多，任务终止\n";
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
                                            $mission_repeat = 0;
                                            if ($insertMissionResult) {
                                                echo "insert:" . $insertMissionResult . ' matchId:' . $v2['matchID'] . '加入任务成功' . "\n";
                                            } else {
                                                echo "insert:" . $insertMissionResult . ' matchId:' . $v2['matchID'] . '加入任务失败' . "\n";
                                            }
                                        } else {
                                            //表示Mission表记录已存在，跳出继续
                                            $mission_repeat ++ ;//重复记录加一
                                            echo "exist-mission" . '-source_link:' . 'https://www.scoregg.com/match/' . $v2['matchID'] . "\n";
                                            if($mission_repeat>=100)
                                            {
                                                echo "重复任务过多，任务终止\n";
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

    public function saveMissionByScoreggMatchId($matchID, $game)
    {
        $cdetail = $this->getMatchDetail($matchID);
        $cdetail['source'] = 'scoregg';
        $cdetail['type'] = 'match';
        $cdetail['game'] = $game;
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
    public function updateScoreggMatchList($game)
    {
        $scoreggMatchModel = new matchListModel();
        $params = [
            'page_size' => 100,
            'page' => 1,
            'game' => $game,
            'round_detailed' => '0',
            'fields' => "match_id,game",//game,match_status,match_data,match_pre,home_id,away_id,home_score,away_score"
        ];
        $collectClassList = [];
        $matchList = $scoreggMatchModel->getMatchList($params);//获取round_detailed=0的数据
        $matchList = $matchList ?? [];
        $classList = [];
        if (count($matchList) > 0) {
            foreach ($matchList as &$val) {
                $rt=$this->getOneScoreggMatchList($val);
                if ($rt) {
                    echo "match_id：" . $val['match_id'] . "更新成功" . "\n";
                } else {
                    echo "match_id：" . $val['match_id'] . "更新失败" . "\n";
                }
                sleep(1);
            }

        }
        return "第" . $params['page'] . "页游戏" . $params['game'] . "执行完毕";

    }
    //查询scoreggMatchList里面所有不等于2的数据重新采集更新
    public function updateScoreggMatchListStatus($game){
        $scoreggMatchModel = new matchListModel();
        $params = [
            'page_size' => 1,
            'page' => 1,
            'game' => $game,
            'start_time'=>1,//表示启动开始时间条件
            'match_status' => ["0","1"],
            'fields' => "match_id,game",//game,match_status,match_data,match_pre,home_id,away_id,home_score,away_score"
        ];
        $collectClassList = [];
        $matchList = $scoreggMatchModel->getMatchList($params);//获取round_detailed=0的数据
        $matchList = $matchList ?? [];
        $classList = [];
        if (count($matchList) > 0) {
            foreach ($matchList as &$val) {
                $rt=$this->getOneScoreggMatchList($val);
                if ($rt) {
                    echo "match_id：" . $val['match_id'] . "更新成功" . "\n";
                } else {
                    echo "match_id：" . $val['match_id'] . "更新失败" . "\n";
                }

            }
        }
        return "第" . $params['page'] . "页游戏" . $params['game'] . "执行完毕";
    }

    //封装更新一条ScoreggMatchList数据
    public function getOneScoreggMatchList($val){
        $scoreggMatchModel = new matchListModel();
        $insert_mission = $this->saveMissionByScoreggMatchId($val['match_id'],$val['game']);

        $mission = (new MissionModel())->getMissionbyId($insert_mission);
        //判断类库存在
        if(!isset($collectClassList[$val['game']]))
        {
            $className = "App\Collect\match\\".$val['game'].'\scoregg';
            if(class_exists($className))
            {
                $collectClassList[$val['game']] = new $className;
            }
        }

        $collectClass = $collectClassList[$val['game']];
        $mission['detail'] = json_decode($mission['detail'], true);
        $mission['detail']['type']='match';

        $collectData = $collectClass->collect($mission);

        $collectData['content']=json_decode($collectData['content'], true);
        ksort($collectData['content']);

        $processData = $collectClass->process($collectData);
        $processData['match_list'][0]['round_detailed']=1;
        $rt=$scoreggMatchModel->saveMatch($processData['match_list'][0]);
        return $rt;

    }



}
