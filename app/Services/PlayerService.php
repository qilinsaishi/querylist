<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
use App\Models\Match\scoregg\tournamentModel;
use App\Models\MissionModel;
use App\Models\PlayerModel;
use App\Models\TeamModel;
use App\Services\MissionService as oMission;
use QL\QueryList;

class  PlayerService
{
    public function insertPlayerData($mission_type, $game)
    {
        $gameItem = [
            'lol', 'kpl', 'dota2'//,  'csgo'
        ];
        $this->getScoreggPlayerDetail($game);

        return 'finish';
    }

    public function getScoreggPlayerDetail($game)
    {
        $playerModel = new PlayerModel();
        $missionModel = new MissionModel();
        if ($game == 'kpl') {
            $gameID = 2;
        } elseif ($game == 'lol') {
            $gameID = 1;
        }
        $tournament_url = 'https://www.scoregg.com/services/api_url.php';
        $tournament_param = [
            'api_path' => '/services/match/tournament_list.php',
            'method' => 'post',
            'platform' => 'web',
            'api_version' => '9.9.9',
            'language_id' => 1,
            'gameID' => $gameID ?? 0,

        ];
        $tournament_data = curl_post($tournament_url, $tournament_param);
        $tournamentList = $tournament_data['data']['list'] ?? [];
        if (count($tournamentList) > 0) {
            foreach ($tournamentList as $val) {//获取赛事列表
                $cdata = $this->getScoreggData($gameID, $val['tournamentID']);//获取队员明细列表
                $cdata = $cdata ?? [];
                if (count($cdata) > 0) {
                    foreach ($cdata as $key=>$vo) {//获取明细
                        $vo = $vo ?? [];
                        if (count($vo) > 0) {
                            foreach ($vo as $k=>$v) {
                                $v['game'] = $game;
                                $v['source'] = 'scoregg';
                                $params = [
                                    'game' => $game,
                                    'mission_type' => 'player',
                                    'source_link' => $v['player_url'] ?? '',
                                ];
                                $v['player_id']=$v['player_id'] ?? 0;
                                if($v['player_id'] >0){
                                    $playerInfo = $playerModel->getPlayerBySiteId($v['player_id'], $game, 'scoregg');
                                    $playerInfo = $playerInfo ?? [];
                                    if (count($playerInfo) == 0) {
                                        $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的文章
                                        $missionCount = $missionCount ?? 0;
                                        if ($missionCount !== 0) {
                                            echo "exits-mission" . $key . '-' . $v['player_url'] . "\n";//表示Mission表记录已存在，跳出继续
                                            continue; //表示Mission表记录已存在，跳出继续
                                        } else {
                                            $adata = [
                                                "asign_to" => 1,
                                                "mission_type" => 'player',
                                                "mission_status" => 1,
                                                "game" => $game,
                                                "source" => 'scoregg',
                                                "title" => $v['player_chinese_name'] ?? '',
                                                'source_link' => $v['player_url'],
                                                "detail" => json_encode($v),
                                            ];
                                            $insert = (new oMission())->insertMission($adata);
                                            echo $game .$key. $k."-scoregg-mission-insert:" . $insert . ' lenth:' . strlen($adata['detail']) . "\n";
                                        }
                                    } else {
                                        echo "exits-playerinfo" . $key . '-' . $v['player_url'] . "\n";//表示playerinfo表记录已存在，跳出继续
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

    //获取scoreegg队员数据
    public function getScoreggData($gameId, $tournament_id)
    {
        $url = 'https://www.scoregg.com/services/api_url.php';
        //$gameId=1;
        //$tournament_id=191;
        $list = [];
        $param = [
            'api_path' => '/services/gamingDatabase/match_data_ssdb_list.php',
            'method' => 'post',
            'platform' => 'web',
            'api_version' => '9.9.9',
            'language_id' => 1,
            'tournament_id' => $tournament_id,
            'type' => 'player',
            'order_type' => 'KDA',
            'order_value' => 'DESC',
            'team_name' => '',
            'player_name' => '',
            'positionID' => '',
            'page' => 1,

        ];
        $data = curl_post($url, $param);
        $totalCount = $data['data']['data']['count'] ?? 0;
        $pageCount = ceil($totalCount / 12);
        if ($totalCount != 0) {
            $totalPage = ceil($totalCount / 12);
            for ($i = 1; $i <= $totalPage; $i++) {
                $param['page'] = $i;
                $cdata = curl_post($url, $param);
                $list[$i] = $cdata['data']['data']['list'] ?? 0;
                if (count($list[$i]) > 0) {
                    foreach ($list[$i] as $k => &$val) {
                        $ajax_url = 'https://www.scoregg.com/big-data/player/' . $val['player_id'] . '?tournamentID=&type=baike';
                        $val['player_url'] = $ajax_url;
                    }
                }

            }
        }
        return $list;
    }


}
