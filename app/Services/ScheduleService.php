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
    public function insertScheduleData($game)
    {

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
    public function tournamentList($game)
    {
        if ($game == 'lol') {
            $gameId = 1;
        } elseif ($game == 'kpl') {
            $gameId = 2;
        }
        $list = $this->getEventList($gameId);
        $missionModel = new MissionModel();
        $tournamentModel = new tournamentModel();
        if (count($list) > 0) {
            foreach ($list as $v) {
                if (count($v) > 0) {
                    foreach ($v as $key => $val) {
                        $params1 = [
                            'game' => $game,
                            'mission_type' => 'match',
                            'source_link' => $val['ajax_url'],
                        ];
                        $tournamentInfo = $tournamentModel->getTournamentById($val['tournamentID']);
                        $tournamentInfo = $tournamentInfo ?? [];
                        $val['game'] = $game;
                        $val['source'] = 'scoregg';
                        $val['type'] = 'tournament';
                        if (count($tournamentInfo) == 0) {
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
                                echo $game . $key . "-scoregg-match-tournament-mission-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                            } else {
                                echo "exits--scoregg-match-tournament-mission" . $key . '-' . $val['ajax_url'] . "\n";//表示Mission表记录已存在，跳出继续
                            }
                        } else {
                            //表示scoregg_tournament_info表记录已存在，跳出继续
                            echo "exits-scoregg_tournament_info" . $key . '-' . $val['ajax_url'] . "\n";
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
