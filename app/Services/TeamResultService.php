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
            'lol', 'kpl', 'dota2'//,  'csgo'
        ];

        foreach ($gameItem as $val) {
            //采集玩加（www.wanplus.com）战队信息
            $this->insertWanplus($val, $mission_type);
            //采集cpseo（2cpseo.com）战队信息
            $this->insertCpseoTeam($val, $mission_type);

        }
        return 'finish';
    }

    //玩加电竞（wanplus）
    public function insertWanplus($game, $mission_type)
    {

        $AjaxModel = new AjaxRequest();
        $missionModel = new MissionModel();
        $teamModel = new TeamModel();
        if ($game == 'dota2') {
            $totalPage = 24;
            $gametype = 1;
        } elseif ($game == 'lol') {
            $totalPage = 17;
            $gametype = 2;
        } elseif ($game == 'kpl') {
            $totalPage = 4;
            $gametype = 6;
        } elseif ($game == 'csgo') {
            $totalPage = 10;
            $gametype = 4;
        }
        for ($i = 1; $i <= $totalPage; $i++) {
            //玩家团队ajax链接
            $url = 'https://www.wanplus.com/ajax/detailranking?country=0&type=1&teamPage=' . $i . '&game=' . $gametype;
            $list = $AjaxModel->ajaxGetData($url);
            if (!empty($list) && count($list) > 0) {//每页战队数据组
                foreach ($list as $val) {
                    //战队url
                    $team_url = 'https://www.wanplus.com/' . $game . '/' . $mission_type . '/' . $val['teamid'];
                    $params = [
                        'game' => $game,
                        'mission_type' => $mission_type,
                        'source_link' => $team_url,
                    ];

                    $site_id = $val['teamid'] ?? 0;//原平台战队id
                    if ($site_id > 0) {
                        $teamInfo = $teamModel->getTeamBySiteId($site_id, 'wanplus', $game);
                        if (empty($teamInfo)) {//teaminfo表记录不存在
                            $result = $missionModel->getMissionCount($params);//过滤已经采集过的文章
                            $result = $result ?? 0;
                            $title = $val['teamname'] ?? '';
                            if ($result <= 0 && $title != '') {
                                $data = [
                                    "asign_to" => 1,
                                    "mission_type" => $mission_type,
                                    "mission_status" => 1,
                                    "game" => $game,
                                    "source" => 'wanplus',
                                    "title" => $title,
                                    'source_link' => $team_url,
                                    "detail" => json_encode(
                                        [
                                            "url" => $team_url,
                                            "game" => $game,//lol
                                            "source" => 'wanplus',
                                            "title" => $title,
                                            "country" => $val['country'] ?? '',
                                            "teamalias" => $val['teamalias'] ?? '',
                                            'site_id' => $val['teamid']
                                        ]
                                    ),
                                ];
                                $insert = (new oMission())->insertMission($data);
                                echo "lol-information-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                            }else{
                                echo "exits"."\n";//表示任务表存在记录，跳出继续
                                continue;
                            }
                        }
                    } else {
                        echo "exits"."\n";//表示teaminfo表记录，跳出继续
                        continue;
                    }

                }
            }

        }
        return true;
    }

    //玩加电竞（wanplus）
    public function insertCpseoTeam($game, $mission_type)
    {
        $AjaxModel = new AjaxRequest();
        $missionModel = new MissionModel();
        $teamModel = new TeamModel();
        $count = 0;
        if ($game == 'lol') {
            $count = 3;
        } elseif ($game == 'kpl') {
            $count = 1;
        } elseif ($game == 'dota2') {
            $count = 10;
        } elseif ($game == 'csgo') {
            $count = 12;
        }
        for ($i = 1; $i <= $count; $i++) {
            $m = $i + 1;
            if ($game == 'lol') {
                $url = 'http://www.2cpseo.com/teams/lol/p-' . $m;
            } elseif ($game == 'kpl') {
                $url = 'http://www.2cpseo.com/teams/kog/p-' . $m;
            } elseif ($game == 'dota2') {
                $url = 'http://www.2cpseo.com/teams/dota2/p-' . $m;
            }/*elseif($game=='csgo'){
                $count=12;
            }*/
            $ql = QueryList::get($url);
            $list = $ql->find('.team-list a')->attrs('href')->all();
            foreach ($list as $val) {
                $team_url = $val;
                $params = [
                    'game' => $game,
                    'mission_type' => $mission_type,
                    'source_link' => $team_url,
                ];

                $site_id = str_replace('http://www.2cpseo.com/team/', '', $val) ?? 0;
                $teamInfo = $teamModel->getTeamBySiteId($site_id, 'cpseo', $game);
                $result = $missionModel->getMissionCount($params);//过滤已经采集过的文章
                if (count($teamInfo) == 0) {
                    $result = $result ?? 0;
                    if ($result == 0) {
                        $data = [
                            "asign_to" => 1,
                            "mission_type" => $mission_type,
                            "mission_status" => 1,
                            "game" => $game,
                            "source" => 'cpseo',
                            'source_link' => $team_url,
                            "detail" => json_encode(
                                [
                                    "url" => $team_url,
                                    "game" => $game,
                                    "source" => 'cpseo',
                                ]
                            ),
                        ];
                        $insert = (new oMission())->insertMission($data);
                        echo "lol-information-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                    } else {
                        echo "lol-information exits"."\n";//表示任务表存在记录，跳出继续
                        continue;
                    }
                } else {
                    echo "team_info exits"."\n";//表示teaminfo表记录，跳出继续
                    continue;
                }

            }
        }
        return true;
    }


}
