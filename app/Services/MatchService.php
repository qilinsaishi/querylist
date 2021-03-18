<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
use App\Models\InformationModel;
use App\Models\Match\scoregg\matchListModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class MatchService
{
    public function insertMatchData()
    {
        $gameItem = [
            'lol', 'kpl', 'dota2'//, 'csgo'
        ];

        foreach ($gameItem as $val) {
            if ($val == 'kpl' || $val == 'lol') {
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
                    echo "insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                } else {
                    echo "exits" . "\n";
                    continue;
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
                    continue;
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
                    continue;
                }

            }
        }
        return true;

    }

    //https://www.scoregg.com 赛事
    public function scoreggMatch($game)
    {
        $collectResultModel = new CollectResultModel();
        $missionModel = new MissionModel();
        $scoreggMatchModel = new matchListModel();
        $collectResult = $collectResultModel->getCollectResult($game, 'match', 'scoregg');
        $collectResult = $collectResult ?? [];//赛事结果
        if (count($collectResult) > 0) {
            foreach ($collectResult as $key => $val) {
                $cdata = curl_get($val['source_link']);//获取赛事下面的一级分类
                $cdata = $cdata ?? [];
                if (count($cdata) > 0) {
                    foreach ($cdata as $k => $v) {
                        if (count($v['round_son']) > 0) {//如果存在二级分类，则获取二级分类
                            foreach ($v['round_son'] as $v1) {
                                $url = 'https://img1.famulei.com/tr_round/' . $v1['id'] . '.json';
                                $arr = curl_get($url);//获取比赛的具体数据
                                $arr = $arr ?? [];
                                if (count($arr) > 0) {
                                    foreach ($arr as $k2 => $v2) {//获取每一个比赛的具体数据
                                        $cdetail = $this->getMatchDetail($v2['matchID']);
                                        $cdetail['source'] = 'scoregg';
                                        $cdetail['type'] = 'match';
                                        $cdetail['game'] = $game;
                                        $cdetail['r_type'] = $v['r_type'];//赛事下面的一级分类类型
                                        $scoreggMatchInfo = $scoreggMatchModel->getMatchById($v2['matchID']);
                                        if (count($scoreggMatchInfo) == 0) {
                                            $params1 = [
                                                'game' => $game,
                                                'mission_type' => 'match',
                                                'source_link' => 'https://www.scoregg.com/match/' . $v2['matchID'],
                                            ];
                                            $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                                            $result = $result ?? 0;
                                            if ($result == 0) {
                                                $cdata = [
                                                    "asign_to" => 1,
                                                    "mission_type" => 'match',//赛事
                                                    "mission_status" => 1,
                                                    "game" => $game,
                                                    "source" => 'scoregg',//
                                                    'title' => $v['name'] . '-' . $v1['name'] . $v2['matchID'],
                                                    'source_link' => 'https://www.scoregg.com/match/' . $v2['matchID'],
                                                    "detail" => json_encode($cdetail),
                                                ];
                                                $insert = (new oMission())->insertMission($cdata);
                                                echo "insert:" . $insert . ' lenth:' . strlen($cdata['detail']) . "\n";
                                            } else {
                                                //表示Mission表记录已存在，跳出继续
                                                echo "exist-mission" . '-source_link:' . 'https://www.scoregg.com/match/' . $v2['matchID'] . "\n";
                                                continue;
                                            }
                                        } else {
                                            //表示scoregg_match_info表记录已存在，跳出继续
                                            echo "exits-scoregg_match-matchID:" . $v2['matchID'] . "\n";
                                            break;
                                        }
                                        ///////////////
                                    }
                                }

                            }

                        } else {//不存在二级分类则只能生成一级分类链接
                            $url = 'https://img1.famulei.com/tr_round/p_' . $v['roundID'] . '.json';
                            $arr = curl_get($url);
                            $arr = $arr ?? [];
                            if (count($arr) > 0) {
                                foreach ($arr as $v2) {//获取比赛的具体数据
                                    $cdetail = $this->getMatchDetail($v2['matchID']);
                                    $cdetail['source'] = 'scoregg';
                                    $cdetail['type'] = 'match';
                                    $cdetail['game'] = $game;
                                    $cdetail['r_type'] = $v['r_type'];//赛事下面的一级分类类型
                                    $scoreggMatchInfo = $scoreggMatchModel->getMatchById($v2['matchID']);
                                    $scoreggMatchInfo = $scoreggMatchInfo ?? [];
                                    if (count($scoreggMatchInfo) == 0) {
                                        $params1 = [
                                            'game' => $game,
                                            'mission_type' => 'match',
                                            'source_link' => 'https://www.scoregg.com/match/' . $v2['matchID'],
                                        ];//过滤已经采集过的数据
                                        $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                                        $result = $result ?? 0;
                                        if ($result == 0) {
                                            $cdata = [
                                                "asign_to" => 1,
                                                "mission_type" => 'match',//赛事
                                                "mission_status" => 1,
                                                "game" => $game,
                                                "source" => 'scoregg',//
                                                'title' => $game . '-' . $v['name'] . '-' . $v2['matchID'],
                                                'source_link' => 'https://www.scoregg.com/match/' . $v2['matchID'],
                                                "detail" => json_encode($cdetail),
                                            ];
                                            $insert = (new oMission())->insertMission($cdata);
                                            echo "insert:" . $insert . ' lenth:' . strlen($cdata['detail']) . "\n";
                                        } else {
                                            //表示Mission表记录已存在，跳出继续
                                            echo "exist-mission" . '-source_link:' . 'https://www.scoregg.com/match/' . $v2['matchID'] . "\n";
                                            continue;
                                        }
                                    } else {
                                        //表示scoregg_match_info表记录已存在，跳出继续
                                        echo "exits-scoreggMatch-key:" . $key . '-matchID:' . $v2['matchID'] . "\n";
                                        break;
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

    //https://www.scoregg.com/services/api_url.php
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


}
