<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\InformationModel;
use App\Models\Match\scoregg\tournamentModel;
use App\Models\MissionModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class ScheduleService
{
    const MISSION_REPEAT=10;//调用重复多少条数量就终止
    public function insertScheduleData($game, $force=0)
    {
        if ($game == 'dota2') {//dota 赛事是不用改，这个没有新的数据
            $this->shangniuTournament($game,$force);
        }

        if ($game == 'kpl' || $game == 'lol') {
            $this->tournamentList($game);
        }
        //$this->insertWanplusSchedule($game);暂时不需要采集玩家赛事方法
        /*if ($game == 'dota2') {//dota 赛事是不用改，这个没有新的数据
            $this->tournament($game);
        }*/

        return 'finish';
    }

    //英雄联盟资讯采集
    public function insertWanplusSchedule($game)
    {
        if ($game == 'dota2') {
            $gametype = 1;
        } elseif ($game == 'lol') {
            $gametype = 2;
        } elseif ($game == 'kpl') {
            $gametype = 6;
        } elseif ($game == 'csgo') {
            $gametype = 4;
        }
        $AjaxModel = new AjaxRequest();
        $missionModel = new MissionModel();
        //比赛列表
        //获取每周的周一时间;
        $weekday = date("w");
        $weekday = ($weekday + 6) % 7;
        $date = strtotime(date('Y-m-d', strtotime("-{$weekday} day")));
        $url = 'http://www.wanplus.com/ajax/schedule/list';
        $param = [
            'game' => $gametype,
            'time' => $date,
            'eids' => ''
        ];
        $list = $AjaxModel->getMatchList($url, $param);
        if (isset($list['scheduleList'])) {
            foreach ($list['scheduleList'] as $val) {
                //https://www.wanplus.com/schedule/68605.html
                $val['list'] = $val['list'] ?? [];
                if (is_array($val['list']) && count($val['list']) > 0) {
                    foreach ($val['list'] as $v) {
                        $url = 'https://www.wanplus.com/schedule/' . $v['scheduleid'] . '.html';
                        $params1 = [
                            'game' => $game,
                            'mission_type' => 'schedule',
                            'source_link' => $url,
                        ];
                        $v['url'] = $url;
                        $v['game'] = $game;
                        $v['source'] = 'wanplus';
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
                            echo "insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                        }

                    }
                }
            }
        }
        return true;
    }
    //尚牛赛事数据
    public function shangniuTournament($game,$force=0){
        $client = new ClientServices();
        $tournamentModel=new \App\Models\Match\shangniu\tournamentModel();
        $missionModel=new MissionModel();
        //===========================获取总页数==============================
        $shangniu_url='https://www.shangniu.cn/api/game/user/tournament/getTournamentVoPage?pageIndex=1&pageSize=20&gameType=dota';
        $shangniu_headers = ['referer' => 'https://www.shangniu.cn/match/dota'];
        $shangniuTournament= $client->curlGet($shangniu_url, [],$shangniu_headers);
        $pageTotal=$shangniuTournament['body']['pageTotal'] ?? 1;

        //===========================获取总页数==============================
        for($pageIndex=1;$pageIndex<=$pageTotal;$pageIndex++){
            $mission_repeat = 0;
            $url='https://www.shangniu.cn/api/game/user/tournament/getTournamentVoPage?pageIndex='.$pageIndex.'&pageSize=20&gameType=dota';
            if($pageIndex==1){
                $referer_url='https://www.shangniu.cn/match/dota';
            }else{
                $referer_url='https://www.shangniu.cn/match/dota/'.($pageIndex-1);
            }
            $headers = ['referer' => $referer_url];
            $tournamentList= $client->curlGet($url, [],$headers);
            $tournamentList=$tournamentList['body']['rows'] ?? [];//获取每一页的赛事数据
            if(count($tournamentList) >0){
                foreach ($tournamentList  as $tournamentInfo){
                    echo 'currentPage:'.$pageIndex.'tournamentId'.$tournamentInfo['tournamentId']."\n";
                //　强制爬取
                    if ($force == 1) {
                        $toGet = 1;
                    } elseif ($force == 0) {
                        $tournament=$tournamentModel->getTournamentById($tournamentInfo['tournamentId']);

                        //找到
                        if (isset($tournament['tournament_id'])) {
                            $toGet = 0;
                            $mission_repeat++;
                            echo "exits-shangniu-tournament-tournamentId:" . $tournament['tournament_id'] . "\n";
                            if ($mission_repeat >= self::MISSION_REPEAT) {
                                echo $game . "shangniu-tournament-重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                return;
                            }
                        } else {
                            $mission_repeat = 0;
                            $toGet = 1;
                        }
                    }
                    if($toGet==1){
                        $source_link='https://www.shangniu.cn/gd/dota?pid=1&tid='.$tournamentInfo['tournamentId'];
                        $params = [
                            'game' => $game,
                            'mission_type' => 'match',
                            'source_link' => $source_link?? '',
                        ];
                        $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的赛事任务
                        $tournamentInfo['game'] = $game;
                        $tournamentInfo['source'] = 'shangniu';
                        $tournamentInfo['type'] = 'tournament';
                        $tournamentInfo['url'] = $source_link;
                        if($missionCount==0){
                            $data = [
                                "asign_to" => 1,
                                "mission_type" => 'match',//赛事
                                "mission_status" => 1,
                                "game" => $game,
                                "source" => 'shangniu',//
                                'title' => 'shangniu-tournament'.$tournamentInfo['tournamentName'],
                                'source_link' => $source_link,
                                "detail" => json_encode($tournamentInfo),
                            ];
                            $insert = $missionModel->insertMission($data);
                            if($insert){
                                $mission_repeat = 0;
                                echo "insert:" . $insert . ' tournament:' . $tournamentInfo['tournamentId'] . '加入任务成功' . "\n";
                            } else {
                                echo "insert:" . $insert . ' tournament:' . $tournamentInfo['tournamentId'] . '加入任务失败' . "\n";
                            }
                            $mission_repeat = 0;
                        }else{
                            $mission_repeat++;//重复记录加一
                            echo "exist-mission" . '-source_link:' . $source_link. "\n";
                            if ($mission_repeat >= self::MISSION_REPEAT) {
                                echo $game . "tournament-shangniu重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                return;
                            }
                        }

                    }
                }
            }
        }
        return true;

    }

    public function tournament($game)
    {
        $data = [];
        $missionModel = new MissionModel();
        $count = 5;
        for ($i = 1; $i <= $count; $i++) {
            $url = 'https://www.dota2.com.cn/Activity/gamematch/index' . $i . '.htm';
            $item = QueryList::get($url)->rules(array(
                'title' => array('.brief h3', 'text'),//标题
                'desc' => array('.brief p', 'text'),//描述
                'logo' => array('img', 'src'),//图片
                'link' => array('a ', 'href')//链接
            ))->range('.content .activities  .activity')->queryData();
            if (count($item) > 0) {
                foreach ($item as $key => $val) {
                    if (strpos($val['link'], 'pwesports.cn') !== false) {
                        $type = str_replace(array('https://', '.pwesports.cn/'), '', $val['link']);
                        $val['link'] = 'https://esports.wanmei.com/' . $type . '-match/latest';
                    }
                    $val['link'] = $detail_url ?? $val['link'];
                    $params1 = [
                        'game' => $game,
                        'mission_type' => 'match',
                        'title' => $val['title'],
                    ];

                    $val['game'] = $game;
                    $val['source'] = 'gamedota2';
                    $val['type'] = 'tournament';
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
                        echo "insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                    }

                }
            }

        }
        return true;

    }


    //scoregg
    public function tournamentList($game, $force = 0)
    {
        if ($game == 'lol') {
            $gameId = 1;
        } elseif ($game == 'kpl') {
            $gameId = 2;
        }
        $mission_repeat=0;
        $list = $this->getEventList($gameId);
        $missionModel = new MissionModel();
        $tournamentModel = new tournamentModel();
        if (count($list) > 0) {
            foreach ($list as $v) {
                if (count($v) > 0) {
                    foreach ($v as $key => $val) {
                        //　强制爬取
                        if ($force == 1) {
                            $toGet = 1;
                        } elseif ($force == 0) {
                            //获取当前比赛数据
                            $tournamentInfo = $tournamentModel->getTournamentById($val['tournamentID']);
                            //找到
                            if (isset($tournamentInfo['tournament_id'])) {
                                $toGet = 0;
                                $mission_repeat++;
                                echo "exits-tournament-tournament_id:" . $val['tournamentID'] . "\n";
                                if ($mission_repeat >= self::MISSION_REPEAT) {
                                    echo "重复任务超过".self::MISSION_REPEAT. "次，任务终止\n";
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
                                'source_link' => $val['ajax_url'],
                            ];
                            $val['game'] = $game;
                            $val['source'] = 'scoregg';
                            $val['type'] = 'tournament';
                            $result = $missionModel->getMissionCount($params1);//过滤已经采集过的文章
                            $result = $result ?? 0;
                            if ($result == 0) {
                                $data = [
                                    "asign_to" => 1,
                                    "mission_type" => 'match',//赛事
                                    "mission_status" => 1,
                                    "game" => $game,
                                    "source" => 'scoregg',//
                                    'title' => $val['name'],
                                    'source_link' => $val['ajax_url'],
                                    "detail" => json_encode($val),
                                ];
                                $insert = (new oMission())->insertMission($data);
                                $mission_repeat = 0;
                                echo $game . $key . "-scoregg-match-tournament-mission-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                            } else {
                                $mission_repeat ++ ;//重复记录加一
                                echo "exits--scoregg-match-tournament-mission" . $key . '-' . $val['ajax_url'] . "\n";//表示Mission表记录已存在，跳出继续
                                if($mission_repeat>=self::MISSION_REPEAT)
                                {
                                    echo "重复任务超过".self::MISSION_REPEAT. "次，任务终止\n";
                                    return;
                                }
                            }
                        }

                    }
                }

            }
        }

        return true;

    }

    //scoregg

    public function getEventList($gameId)
    {
        $url = 'https://www.scoregg.com/services/api_url.php';
        $limit = 18;
        $list = [];
        $param = [
            'api_path' => '/services/match/web_tournament_group_list.php',
            'platform' => 'web',
            'method' => 'GET',
            'language_id' => 1,
            'gameID' => $gameId,//2王者荣耀
            'type' => 'all',
            'limit' => $limit,
            'year' => '',
            'api_version' => '9.9.9'
        ];
        $data = curl_post($url, $param);
        $totalCount = $data['data']['count'] ?? 0;
        if ($totalCount != 0) {
            $totalPage = ceil($totalCount / $limit);
            for ($i = 1; $i <= $totalPage; $i++) {
                $param['page'] = $i;
                $cdata = curl_post($url, $param);
                $list[$i] = $cdata['data']['list'] ?? [];
                if (count($list[$i]) > 0) {
                    foreach ($list[$i] as $k => &$val) {
                        $ajax_url = 'https://img1.famulei.com/tr/' . $val['tournamentID'] . '.json';
                        $val['ajax_url'] = $ajax_url;

                    }
                }

            }
        }
        return $list;
    }


}
