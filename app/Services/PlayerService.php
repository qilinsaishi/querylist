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
use App\Models\Player\TotalPlayerModel as TotalPlayerModel;
use App\Models\Player\PlayerMapModel as PlayerMapModel;
use App\Models\Player\PlayerNameMapModel as PlayerNameMapModel;
use App\Services\Data\IntergrationService;
use Illuminate\Support\Facades\DB;
use QL\QueryList;

class  PlayerService
{
    public function insertPlayerData($mission_type, $game)
    {
        $this->getPlayerListByCollectResult($game, $mission_type);
        $this->getScoreggPlayerDetail($game, $mission_type);
        $this->insertCpseoPlayer($game, $mission_type);
        return 'finish';
    }

    public function insertCpseoPlayer($game, $mission_type)
    {
        $AjaxModel = new AjaxRequest();
        $missionModel = new MissionModel();

        $page_count = 0;
        if ($game == 'lol') {
            $page_count = 74;
        } elseif ($game == 'kpl') {
            $page_count = 11;
        } elseif ($game == 'dota2') {
            $page_count = 31;
        } elseif ($game == 'csgo') {
            $page_count = 63;
        }
        for ($i = 1; $i <= $page_count; $i++) {
            if ($game == 'kpl') {
                $url = 'http://www.2cpseo.com/players/kog/p-' . $i;
            } else {
                $url = 'http://www.2cpseo.com/players/' . $game . '/p-' . $i;
            }
            //判断url是否有效
            $headers = get_headers($url, 1);
            if (!preg_match('/200/', $headers[0])) {
                return [];
            }

            $ql = QueryList::get($url);
            $list = $ql->find('.player-list a')->attrs('href')->all();

            foreach ($list as $val) {
                $player_url = $val;
                $params = [
                    'game' => $game,
                    'mission_type' => $mission_type,
                    'source_link' => $player_url,
                ];

                $site_id = str_replace('http://www.2cpseo.com/player/', '', $val) ?? 0;
                //$teamInfo = $teamModel->getTeamBySiteId($site_id, 'cpseo', $game);
                $result = $missionModel->getMissionCount($params);//过滤已经采集过的文章

                if (is_numeric($site_id)) {
                    $result = $result ?? 0;
                    if ($result == 0) {
                        $data = [
                            "asign_to" => 1,
                            "mission_type" => $mission_type,
                            "mission_status" => 1,
                            "game" => $game,
                            "source" => 'cpseo',
                            'source_link' => $player_url,
                            "detail" => json_encode(
                                [
                                    "url" => $player_url,
                                    "game" => $game,
                                    "source" => 'cpseo',
                                ]
                            ),
                        ];
                        $insert = (new oMission())->insertMission($data);
                        echo $game . "-player-cpseo-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                    } else {
                        echo $game . "-Mission-cpseo-exits" . "\n";//表示任务表存在记录，跳出继续
                        continue;
                    }
                } else {
                    echo $player_url . "\n";
                    continue;

                }

            }
        }
        return true;
    }

    public function getScoreggPlayerDetail($game)
    {
        $playerModel = new PlayerModel();
        $missionModel = new MissionModel();
        if ($game == 'kpl') {
            $gameID = 2;
        } elseif ($game == 'lol') {
            $gameID = 1;
        } elseif ($game == 'dota2') {
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
                    foreach ($cdata as $key => $vo) {//获取明细
                        $vo = $vo ?? [];
                        if (count($vo) > 0) {
                            foreach ($vo as $k => $v) {
                                $v['game'] = $game;
                                $v['source'] = 'scoregg';
                                $params = [
                                    'game' => $game,
                                    'mission_type' => 'player',
                                    'source_link' => $v['player_url'] ?? '',
                                ];
                                $v['player_id'] = $v['player_id'] ?? 0;
                                if ($v['player_id'] > 0) {
                                    $playerInfo = $playerModel->getPlayerBySiteId($v['player_id'], $game, 'scoregg');
                                    $playerInfo = $playerInfo ?? [];
                                    if (count($playerInfo) == 0) {
                                        $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的文章
                                        $missionCount = $missionCount ?? 0;
                                        if ($missionCount !== 0) {
                                            echo "exist-mission-scoregg-" . $game . '-' . $v['player_url'] . "\n";//表示Mission表记录已存在，跳出继续
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
                                            echo $game . $key . $k . "-scoregg-player-mission-insert:" . $insert . ' lenth:' . strlen($adata['detail']) . "\n";
                                        }
                                    } else {
                                        echo "exist-playerinfo-scoregg-" . $game . '-' . $v['player_url'] . "\n";//表示playerinfo表记录已存在，跳出继续
                                        continue;
                                    }
                                } else {
                                    echo "player_id:" . $v['player_id'];
                                    continue;
                                }

                            }

                        } else {
                            continue;
                        }
                    }
                } else {
                    continue;
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
                $list[$i] = $cdata['data']['data']['list'] ?? [];
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

    public function intergration($game = "lol")
    {
        $intergrationService = new IntergrationService();
        $totalPlayerModel = new TotalPlayerModel();
        $playerMapModel = new PlayerMapModel();
        $playerNameMapModel = new PlayerNameMapModel();
        $playerModel = new PlayerModel();
        $teamModel = new TeamModel();
        $player_intergration = config("app.intergration.player") ?? [];
        $team_intergration = config("app.intergration.team") ?? [];
        $teamList = [];
        $return = false;
        $playerList = $playerModel->getPlayerList(["fields" => "player_id,player_name,en_name,cn_name,aka,original_source,game,team_id", "pid" => 0, "game" => $game, "sources" => array_column($player_intergration, "source"), "page_size" => 3000]);
        $teamIdList = array_unique(array_column($playerList, "team_id"));
        $teamList = $teamModel->getTeamList(["fields" => "team_id,tid", "game" => $game, "ids" => array_values($teamIdList), "sources" => array_column($team_intergration, "source"), "page_size" => 99999999]);
        $teamList = array_combine(array_column($teamList, "team_id"), array_values($teamList));
        foreach ($teamList as $team_id => $team) {
            $intergrated_team_info = getFieldsFromArray($intergrationService->getTeamInfo(0, $team['tid'], 1, 1)['data'], "tid,intergrated_id_list");
            $teamList[$team_id]['intergrated_id_list'] = $intergrated_team_info['intergrated_id_list'];
        }
        foreach ($playerList as $playerInfo) {
            if (isset($playerInfo['player_id'])) {
                echo "start to process player:" . $playerInfo['player_id'] . "\n";
                if (isset($teamList[$playerInfo['team_id']])) {
                    $merged = 0;
                    //获取当前队伍的队员总表
                    $playerList_toProcess = $playerModel->getPlayerList(["team_id" => $teamList[$playerInfo['team_id']]['intergrated_id_list'], "fields" => "player_id,pid", "page_size" => 1000]);
                    //获取整合后的队员id列表
                    $pidList = array_unique(array_column($playerList_toProcess, "pid"));
                    foreach ($pidList as $pid) {
                        if ($pid > 0) {
                            //获取整合后数据
                            $playerInfoToMerge = getFieldsFromArray($intergrationService->getPlayerInfo(0, $pid, 1, 1)['data'], "player_name,cn_name,en_name,aka");
                            //拆解名字
                            $playerNameList = getNames($playerInfoToMerge, ["player_name", "en_name", "cn_name"], ["aka"]);
                            //处理当前的名字
                            $playerName = generateNameHash($playerInfo['player_name']);
                            //如果匹配上之前曾用过的任意名字
                            if (in_array($playerName, $playerNameList)) {
                                echo "to Merge\n";
                                DB::beginTransaction();
                                //合并入创建的映射里面
                                $mergeToMap = $this->mergeToPlayerMap($playerInfo, $pid, $playerModel, $playerMapModel, $playerNameMapModel);
                                if (!$mergeToMap) {
                                    echo "merge to existed intergrated player Error\n";
                                    DB::rollBack();
                                } else {
                                    echo "merged player " . $playerInfo['player_id'] . " to existed " . $pid . "\n";
                                    DB::commit();
                                    $merged = 1;
                                    break;
                                }
                            }
                        }
                    }
                    //如果没有合并
                    if ($merged == 0) {
                        echo "to Create\n";
                        DB::beginTransaction();
                        //创建队员
                        $insertPlayer = $totalPlayerModel->insertPlayer(['game' => $playerInfo['game'], 'original_source' => $playerInfo['original_source']]);
                        //创建成功
                        if ($insertPlayer) {
                            //合并入创建的映射里面
                            $mergeToMap = $this->mergeToPlayerMap($playerInfo, $insertPlayer, $playerModel, $playerMapModel, $playerNameMapModel);
                            if (!$mergeToMap) {
                                echo "create intergrated player Error\n";
                                DB::rollBack();
                            } else {
                                echo "merged player " . $playerInfo['player_id'] . " to created " . $insertPlayer . "\n";
                                DB::commit();
                            }
                        }
                    }
                } else {
                    echo "team not intergrated pass\n";
                }
            }
        }
    }

    function generateNameHash($name = "")
    {
        $name = strtolower($name);
        $name = trim($name);
        $replaceList = [" ", "."];
        foreach ($replaceList as $key) {
            $name = str_replace($key, "", $name);
        }
        $name = $this->removeEmoji($name);
        //echo "hash:".$name."\n";
        return $name;
    }

    function removeEmoji($text)
    {
        $clean_text = "";
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
        return $clean_text;
    }

    //把对于合并到已经查到的队员映射
    function mergeToPlayerMap($playerInfo = [], $pid, $playerModel, $playerMapModel, $playerNameMapModel)
    {
        $insertMap = $playerMapModel->insertMap(["pid" => $pid, "player_id" => $playerInfo['player_id']]);
        if ($insertMap) {
            $aka = json_decode($playerInfo['aka'], true);
            $nameList = (array_merge([$playerInfo['player_name'], $playerInfo['en_name'], $playerInfo['cn_name']], $aka ?? []));
            foreach ($nameList as $key => $name) {
                if ($name == "") {
                    unset($nameList[$key]);
                } else {
                    $nameList[$key] = $this->generateNameHash($name);
                }
            }
            $nameList = array_unique($nameList);
            foreach ($nameList as $name) {
                if ($name != "") {
                    //保存名称映射
                    $saveMap = $playerNameMapModel->saveMap(["name_hash" => $name, "game" => $playerInfo['game'], "pid" => $pid]);
                    if (!$saveMap) {
                        //echo "insertPlayerMapError";
                        return false;
                        //break;
                    }
                }
            }
            $updateTid = $playerModel->updatePlayer($playerInfo['player_id'], ["pid" => $pid]);
            if (!$updateTid) {
                return false;
            }
            return true;
        }
    }

    public function getPlayerListByCollectResult($game)
    {
        $collectResult = new CollectResultModel();
        $teamInfoModel = new TeamModel();
        $playerModel = new PlayerModel();
        $missionModel = new MissionModel();
        $teamParams = [
            "fields" => 'tid,team_name,site_id,original_source,team_id',
            "game" => $game,
            'page_size' => 5000,
            "source" => 'scoregg'
        ];
        $teamList = $teamInfoModel->getTeamList($teamParams);//查出所有scoregg的数据
        $clist = array_column($teamList, 'site_id');
        if (count($teamList) > 0) {
            foreach ($teamList as $val) {
                //通过scoregg站点的teamid获取他下面的所有队员
                $playerList = $this->getTeamPlayerList($val['site_id'], 'playerID');
                //通过scoregg站点的teamid获取他下面的所有图片
                $playerImages = $this->getTeamPlayerList($val['site_id'], 'player_image');
                if (isset($playerList) && count($playerList) > 0) {
                    $detail = [];
                    foreach ($playerList as $k => $v) {
                        try{
                            $detail['player_url'] ='https://www.scoregg.com/big-data/player/' . $v . '?tournamentID=&type=baike';
                            $detail['player_image'] = $playerImages[$k] ?? '' ;
                            $detail['source'] = 'scoregg';
                            $detail['game'] = $game;
                            $detail['team_id'] = $val['site_id'];
                            $detail['player_id'] = $v;
                            $params = [
                                'game' => $game,
                                'mission_type' => 'player',
                                'source_link' => $detail['player_url'] ?? '',
                            ];
                            $playerInfo = $playerModel->getPlayerBySiteId($v, $game, 'scoregg');
                            $playerInfo = $playerInfo ?? [];
                            if (count($playerInfo) == 0) {
                                $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的文章
                                $missionCount = $missionCount ?? 0;
                                if ($missionCount !== 0) {
                                    echo "exist-mission-scoregg-" . $game . '-' . $detail['player_url'] . "\n";//表示Mission表记录已存在，跳出继续
                                    continue; //表示Mission表记录已存在，跳出继续
                                } else {
                                    $adata = [
                                        "asign_to" => 1,
                                        "mission_type" => 'player',
                                        "mission_status" => 1,
                                        "game" => $game,
                                        "source" => 'scoregg',
                                        "title" => $detail['player_chinese_name'] ?? '',
                                        'source_link' => $detail['player_url'],
                                        "detail" => json_encode($detail),
                                    ];
                                    $insert = (new oMission())->insertMission($adata);
                                    echo $game . "-scoregg-team_player-mission-insert:" . $insert . ' lenth:' . strlen($adata['detail']) . "\n";
                                }

                            } else {
                                echo "exist-playerinfo-scoregg-" . $game . '-' . $detail['player_url'] . "\n";//表示playerinfo表记录已存在，跳出继续
                                continue;
                            }
                        }catch (\Exception $e){
                            echo $detail['player_url'];
                            print_r($e->getMessage());
                        }

                    }

                } else {
                    continue;
                }
            }
        }
    }

    /**
     * @param $site_id //socregg原站点id
     * @param string $preg_header 正则以什么开头
     * @return array
     */

    public function getTeamPlayerList($site_id, $preg_header = '')
    {
        $playerData = [];
        $url = 'https://www.scoregg.com/big-data/team/' . $site_id;
        $qt = QueryList::get($url);
        $playerListHtml = $qt->find('body')->html();
        //---------------------------拆解最初级目录    a,b,c,d,.......
        $start = strpos($playerListHtml, '{return');
        $end = strpos($playerListHtml, '</script><script ');
        $start1 = strpos($playerListHtml, 'function');
        $end1 = strpos($playerListHtml, '{return');
        $html1 = substr($playerListHtml, $start1, $end1 - $start1);
        $html1 = str_replace(array('function(', ')'), '', $html1);
        $html1 = explode(',', $html1);
        //映射反转
        $html1 = array_flip($html1);
        //---------------------------拆解最初级目录    a,b,c,d,.......


        //---------------------------拆解第二级目录    变量名-》最初级目录映射
        $start3 = strripos($playerListHtml, '}}}');
        $end3 = strpos($playerListHtml, '</script><script ');
        $html3 = substr($playerListHtml, $start3, $end3 - $start3);
        $html3 = str_replace('}}}(', '[', $html3);
        $html3 = str_replace('));', ']', $html3);
        $html3 = json_decode($html3, true);
        //---------------------------拆解第二级目录    变量名-》最初级目录映射

        //---------------------------拆解第三级目录    变量名-》数值
        $start4 = strpos($playerListHtml, '{layout:"LayoutBigData",');
        $end4 = ($start3 + 3);
        $html4 = substr($playerListHtml, $start4, $end4 - $start4);
        $html4 = explode('teamPercent', $html4);
        //匹配固定开头的数据
        $end_str = ",";
        $pattern = '/(' . $preg_header . ').*?(' . $end_str . ')/is';
        preg_match_all($pattern, $html4[0], $playerInfoIds);
        foreach ($playerInfoIds['0'] as $value) {
            $t = explode(":", $value);
            if (isset($t['1'])) {
                $t['1'] = str_replace(",", "", $t[1]);
                //查找在第三级目录中的位置
                if (isset($html1[$t['1']])) {
                    //查找具体数据
                    if (isset($html3[$html1[$t['1']]])) {
                        $playerID = $html3[$html1[$t['1']]];
                        array_push($playerData, $playerID);
                        //echo "player_id:key:".$html1[$t['1']].",ID:".$html3[$html1[$t['1']]]."\n";
                    }
                }
            }
        }
        return array_unique($playerData);
    }
}
