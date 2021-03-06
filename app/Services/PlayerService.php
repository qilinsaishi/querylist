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
use App\Models\Team\TotalTeamModel as TotalTeamModel;
use App\Models\Player\TotalPlayerModel as TotalPlayerModel;
use App\Models\Player\PlayerNameMapModel as PlayerNameMapModel;
use App\Services\Data\IntergrationService;
use Illuminate\Support\Facades\DB;
use QL\QueryList;

class  PlayerService
{
    const MISSION_REPEAT = 40;//调用重复多少条数量就终止
    public function insertPlayerData($game,$force = 0)
    {
        //$this->getPlayerListByCollectResult($game, $mission_type);
        if($game !='dota2'){
            $this->getScoreggPlayerDetail($game, $force);
        }

        //$this->insertCpseoPlayer($game, $mission_type);
        //$this->repairPlayerData();//修复队员scoregg 下面team_id错误数据
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

                    }
                } else {
                    echo $player_url . "\n";

                }

            }
        }
        return true;
    }

    public function getScoreggPlayerDetail($game,$force = 0)
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
                $mission_repeat= 0;
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
                                    //　强制爬取
                                    if ($force == 1) {
                                        $toGet = 1;
                                    } elseif ($force == 0) {
                                        //获取当前比赛数据
                                        $playerInfo = $playerModel->getPlayerBySiteId($v['player_id'], $game, 'scoregg');
                                        //找到
                                        if (isset($playerInfo['site_id'])) {
                                            $toGet = 0;
                                            $mission_repeat++;
                                            echo "exits-player-site_id:" . $v['player_id'] . "\n";
                                            if ($mission_repeat >= self::MISSION_REPEAT) {
                                                echo "重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                                return;
                                            }
                                        } else {
                                            $mission_repeat = 0;
                                            $toGet = 1;
                                        }
                                    }

                                    //$playerInfo = $playerInfo ?? [];
                                    if ($toGet == 1) {
                                        $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的文章
                                        $missionCount = $missionCount ?? 0;
                                        if ($missionCount !== 0) {
                                            $mission_repeat++;//重复记录加一
                                            echo "exist-mission-scoregg-" . $game . '-' . $v['player_url'] . "\n";//表示Mission表记录已存在，跳出继续
                                            if ($mission_repeat >= self::MISSION_REPEAT) {
                                                echo "重复任务超过" . self::MISSION_REPEAT . "次，任务终止\n";
                                                return;
                                            }
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
                                            $mission_repeat = 0;
                                            echo $game . $key . $k . "-scoregg-player-mission-insert:" . $insert . ' lenth:' . strlen($adata['detail']) . "\n";
                                        }
                                    }
                                } else {
                                    echo "player_id:" . $v['player_id'];

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
    public function createPlayerMission($game,$player_id,$source){
       // echo "game:".$game."-id:".$player_id."-：".$source."\n";
        if($source == "scoregg")
        {
            $player_url = 'https://www.scoregg.com/big-data/player/' . $player_id . '?tournamentID=&type=baike';
            $params = [
                'player_url' => $player_url,
                'game' => $game,
                'player_id'=>$player_id,
                'source' => $source];
        }
        elseif($source = "shangniu")
        {
            $player_url='https://www.shangniu.cn/esports/dota-player-'.$player_id.'.html';
            $params = [
                'player_url' => $player_url,
                'game' => $game,
                'player_id'=>$player_id,
                'source' => $source];

        }
        $params['player_id']=$player_id;
        $adata = [
            "asign_to" => 1,
            "mission_type" => 'player',
            "mission_status" => 1,
            "game" => $game,
            "source" => $source,
            "title" => "",
            'source_link' => $player_url,
            "detail" => json_encode($params),
        ];
        $insert = (new oMission())->insertMission($adata);
        return $insert;
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
        $playerNameMapModel = new PlayerNameMapModel();
        $playerModel = new PlayerModel();
        $teamModel = new TeamModel();
        $player_intergration = config("app.intergration.player.".$game) ?? [];
        $team_intergration = config("app.intergration.team".$game) ?? [];
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
                                $mergeToMap = $this->mergeToPlayerMap($playerInfo, $pid, $playerModel,  $playerNameMapModel);
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
                            $mergeToMap = $this->mergeToPlayerMap($playerInfo, $insertPlayer, $playerModel,  $playerNameMapModel);
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
    function mergeToPlayerMap($playerInfo = [], $pid, $playerModel, $playerNameMapModel)
    {
            $aka = json_decode($playerInfo['aka'], true);
            $nameList = (array_merge([$playerInfo['player_name'], $playerInfo['en_name'], $playerInfo['cn_name']], is_array($aka)?$aka : []));
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
            $updatePid = $playerModel->updatePlayer($playerInfo['player_id'], ["pid" => $pid]);
            if (!$updatePid) {
                return false;
            }
            return true;
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
    //修复数据
    public function repairPlayerData(){
        $playerModel=new PlayerModel();
        $params=[
            'source'=>'scoregg','page_size'=>1000,'page'=>3,'fields'=>"team_id,player_id,site_id,game"];
        $playerInfo=$playerModel->getPlayerList($params);
        $playerInfo=$playerInfo ?? [];
        if(count($playerInfo)>0) {
            foreach($playerInfo as $val){
                $score_url='https://www.scoregg.com/big-data/player/'.$val['site_id'].'?type=baike';
                $qt = QueryList::get($score_url);
                $team_url=$qt->find('.page-big-data-player-baike .left-content .game-history .hero-info .info-item a')->attr('href');
                if($team_url){

                    $site_id=intval(str_replace('/big-data/team/','',$team_url));
                    $teamInfo = (new TeamModel())->getTeamBySiteId($site_id,"scoregg",$val['game']);

                    if(isset($teamInfo['team_id'])){
                        if($val['team_id']!=$teamInfo['team_id']){
                            $data['team_id']=$teamInfo['team_id'];
                            (new PlayerModel())->updatePlayer($val['player_id'],$data);
                            continue;
                        }

                    }else{

                        if($site_id >0){
                            $team_name=$qt->find('.page-big-data-player-baike .left-content .game-history .hero-info .info-item a ')->text();
                            $team_image=$qt->find('.page-big-data-player-baike .left-content .game-history .hero-info .info-item a img')->attr('src');
                            $detail=[
                                'team_id'=>$site_id,
                                'team_url'=>'https://www.scoregg.com'.$team_url.'?tournamentID=&type=baike',
                                'team_name'=>$team_name,
                                'team_image'=>$team_image,
                                'source'=>'scoregg',
                                'game'=>$val['game'],
                                'cn_name'=>'',
                                'en_name'=>$team_name
                            ];
                            $params = [
                                'game' =>$val['game'],
                                'mission_type' => 'team',
                                'source_link' => $detail['team_url'],
                            ];
                            $missionModel = new MissionModel();
                            $missionCount = $missionModel->getMissionCount($params);
                            $missionCount = $missionCount ?? 0;
                            if ($missionCount <= 0) {
                                $adata = [
                                    "asign_to" => 1,
                                    "mission_type" => 'team',
                                    "mission_status" => 1,
                                    "game" =>$val['game'],
                                    "source" => 'scoregg',
                                    "title" => $arr['content']['team_name'] ?? '',
                                    'source_link' => $detail['team_url'],
                                    "detail" => json_encode($detail),
                                ];
                                $insert = (new oMission())->insertMission($adata);
                                echo $val['game']."player-scoregg-insert-team:" . $insert . ' lenth:' . strlen($adata['detail']) . "\n";
                                continue;
                            }
                        }


                    }

                }

            }
        }
        return true;
    }
    //合并1个未整合过的队员
    public function merge1unmergedPlayer($playerid=0)
    {
        $return = ["result"=>false,"log"=>[]];
        $teamModel = new TeamModel();
        $playerModel = new PlayerModel();
        $totalPlayerModel = new TotalPlayerModel();
        $playerNameMapModel = new PlayerNameMapModel();
        if($playerid<=0)
        {
            $return["result"] = false;
            $return["log"][] = "ID有误";
            return $return;
        }
        else
        {
            $playerInfo = $playerModel->getPlayerById($playerid);
            if(!$playerInfo['player_id'])
            {
                $return["result"] = false;
                $return["log"][] = "队员不存在";
                return $return;
            }
            else
            {
                if($playerInfo['pid']>0)
                {
                    $return["result"] = true;
                    $return["log"][] = "转入队员是一个已经整合了的队员";
                    return $return;
                }
            }
        }
        //开启事务
        DB::beginTransaction();
        $insertPlayer = $totalPlayerModel->insertPlayer(['game'=>$playerInfo['game'],'original_source'=>$playerInfo['original_source']]);
        //创建成功
        if($insertPlayer)
        {
            $return["log"][] = "创建整合队员成功";
            //合并入查到的映射里面
            $mergeToMap = $this->mergeToPlayerMap($playerInfo, $insertPlayer, $playerModel, $playerNameMapModel);
            if (!$mergeToMap)
            {
                DB::rollBack();
                $return["result"] = false;
                $return["log"][] = "整合失败";
                return $return;
            }
            else
            {
                DB::commit();
                $return["result"] = true;
                $return["log"][] = "整合成功";
                return $return;
            }
        }
        else
        {
            DB::rollBack();
            $return["result"] = false;
            $return["log"][] = "创建整合队员失败";
            return $return;
        }
    }
    //合并2个未整合过的队员
    public function merge2unmergedPlayer($playerid=0,$playerId2Merge=0)
    {
        $return = ["result"=>false,"log"=>[]];
        $teamModel = new TeamModel();
        $playerModel = new PlayerModel();
        $totalPlayerModel = new TotalPlayerModel();
        $playerNameMapModel = new PlayerNameMapModel();
        if($playerid<=0 || $playerId2Merge<=0)
        {
            $return["result"] = false;
            $return["log"][] = "ID有误";
            return $return;
        }
        elseif($playerid == $playerId2Merge)
        {
            $return["result"] = false;
            $return["log"][] = "同一个队员不需要合并";
            return $return;
        }
        else
        {
            $playerInfo = $playerModel->getPlayerById($playerid);
            $player2MergeInfo = $playerModel->getPlayerById($playerId2Merge);
            if(!$playerInfo['player_id'])
            {
                $return["result"] = false;
                $return["log"][] = "转入队员不存在";
                return $return;
            }
            elseif($playerInfo['pid']>0)
            {
                $return["result"] = false;
                $return["log"][] = "转入队员是一个已经整合了的队员";
                return $return;
            }
            if(!$player2MergeInfo['player_id'])
            {
                $return["result"] = false;
                $return["log"][] = "被转入队员不存在";
                return $return;
            }
            elseif($player2MergeInfo['pid']>0)
            {
                if($player2MergeInfo['pid']==$playerInfo['pid'])
                {
                    $return["result"] = true;
                    $return["log"][] = "属于同一个整合队员";
                    return $return;
                }
                else
                {
                    $return["result"] = false;
                    $return["log"][] = "被转入队员是一个已经整合了的队伍";
                    return $return;
                }
            }
            //如果不是同一个队伍
            if($playerInfo['team_id'] != $player2MergeInfo['team_id'])
            {
                $teamInfo_1 = $teamModel->getTeamById($playerInfo['team_id'],"team_id,tid");
                $teamInfo_2 = $teamModel->getTeamById($player2MergeInfo['team_id'],"team_id,tid");
                if(!isset($teamInfo_1['tid']) || !isset($teamInfo_2['tid']))
                {
                    $return["result"] = false;
                    $return["log"][] = "队伍数据有误";
                    return $return;
                }
                elseif($teamInfo_1['tid'] ==0 || $teamInfo_2['tid'] ==0)
                {
                    $return["result"] = false;
                    $return["log"][] = "未整合的队伍中的队员不做整合操作";
                    return $return;
                }
                if($teamInfo_1['tid'] != $teamInfo_2['tid'])
                {
                    $return["result"] = false;
                    $return["log"][] = "不属于同一整合队伍中的队员不做整合操作";
                    return $return;
                }
            }
        }
        //开启事务
        DB::beginTransaction();
        $insertPlayer = $totalPlayerModel->insertPlayer(['game'=>$playerInfo['game'],'original_source'=>$playerInfo['original_source']]);
        //创建成功
        if($insertPlayer)
        {
            //合并入新增的映射里面
            $mergeToMap1 = $this->mergeToPlayerMap($playerInfo, $insertPlayer, $playerModel, $playerNameMapModel);
            if (!$mergeToMap1)
            {
                DB::rollBack();
                $return["result"] = false;
                $return["log"][] = "整合队员1失败";
                return $return;
            }
            //合并入新增的映射里面
            $mergeToMap2 = $this->mergeToPlayerMap($player2MergeInfo, $insertPlayer, $playerModel, $playerNameMapModel);
            if (!$mergeToMap2)
            {
                DB::rollBack();
                $return["result"] = false;
                $return["log"][] = "整合队员2失败";
                return $return;
            }
            else
            {
                DB::commit();
                $return["result"] = true;
                $return["log"][] = "整合成功";
                return $return;
            }
        }
        else
        {
            DB::rollBack();
            $return["result"] = false;
            $return["log"][] = "创建整合队员失败";
            return $return;
        }
    }
    public function mergePlayer2mergedPlayer($pid,$playerId2Merge=0)
    {
        $return = ["result"=>false,"log"=>[]];
        $teamModel = new TeamModel();
        $playerModel = new PlayerModel();
        $totalPlayerModel = new TotalPlayerModel();
        $playerNameMapModel = new PlayerNameMapModel();
        if($pid<=0 || $playerId2Merge<=0)
        {
            $return["result"] = false;
            $return["log"][] = "ID有误";
            return $return;
        }
        else
        {
            $player2MergeInfo = $playerModel->getPlayerById($playerId2Merge);
            if(!$player2MergeInfo['player_id'])
            {
                $return["result"] = false;
                $return["log"][] = "被转入队员不存在";
                return $return;
            }
            elseif($player2MergeInfo['pid']>0)
            {
                if($player2MergeInfo['pid']==$pid)
                {
                    $return["result"] = true;
                    $return["log"][] = "属于同一个整合队员";
                    return $return;
                }
                else
                {
                    $return["result"] = false;
                    $return["log"][] = "被转入队员是一个已经整合了的队伍";
                    return $return;
                }
            }
            //获取整合前的用户列表
            $playerList = $playerModel->getPlayerList(["pid"=>$pid,"fields"=>"team_id,pid"]);
            $teamList = $teamModel->getTeamList(["team_ids"=>array_column($playerList,'team_id'),"fields"=>"tid,team_id"]);
            $teamList = $teamModel->getTeamList(['tid'=>array_unique(array_column($teamList,"tid"))['0'],"fields"=>"team_id"]);
            if(!in_array($player2MergeInfo['team_id'],array_column($teamList,'team_id')))
            {
                $return["result"] = false;
                $return["log"][] = "不属于同一整合队伍中的队员不做整合操作";
                return $return;
            }
            else
            {
                $teamInfo_2 = $teamModel->getTeamById($player2MergeInfo['team_id'],"team_id,tid");
                if($teamInfo_2['tid'] ==0)
                {
                    $return["result"] = false;
                    $return["log"][] = "未整合的队伍中的队员不做整合操作";
                    return $return;
                }
            }
        }
        //开启事务
        DB::beginTransaction();
        //合并入新增的映射里面
        $mergeToMap = $this->mergeToPlayerMap($player2MergeInfo, $pid, $playerModel, $playerNameMapModel);
        if (!$mergeToMap)
        {
            DB::rollBack();
            $return["result"] = false;
            $return["log"][] = "整合队员失败";
            return $return;
        }
        else
        {
            DB::commit();
            $return["result"] = true;
            $return["log"][] = "整合成功";
            return $return;
        }
    }
    //合并2个整合过的队员
    public function merge2mergedPlayer($pid=0,$pid2Merge=0)
    {
        $return = ["result"=>false,"log"=>[]];
        //如果双方ID有问题
        if($pid<=0 || $pid2Merge<=0)
        {
            $return["result"] = false;
            $return["log"][] = "ID有误";
            return $return;
        }
        //如果是同一个
        elseif($pid == $pid2Merge)
        {
            $return["result"] = true;
            $return["log"][] = "同一个队员，无需再次合并";
            return $return;
        }
        else
        {
            $totalTeamModel = new TotalTeamModel();
            $totalPlayerModel = new TotalPlayerModel();
            $teamModel = new TeamModel();
            $playerModel = new PlayerModel();
            $playerNameMapModel = new PlayerNameMapModel();
            $totalPlayer1 = $totalPlayerModel->getPlayerById($pid,"pid,redirect");
            if(isset($totalPlayer1['pid']))
            {
                $totalPlayer1['redirect'] = json_decode($totalPlayer1['redirect'],true);
                if(isset($totalPlayer1['redirect']['player_id']) || isset($totalPlayer1['redirect']['pid']) )
                {
                    //主队员已经重定向
                    $return["result"] = false;
                    $return["log"][] = "主队员已经重定向了";
                    return $return;
                }

                //队员2的详情列表
                $playerList2Merge = $playerModel->getPlayerList(["pid"=>$pid2Merge,"fields"=>"player_id,pid,team_id,game,player_name,en_name,cn_name,aka"]);
                //没有队员
                if(count($playerList2Merge)==0)
                {
                    //主队伍已经重定向
                    $return["result"] = false;
                    $return["log"][] = "转入队伍找不到了";
                    return $return;
                }
                else
                {
                    //队员1的详情列表
                    $playerList1Merge = $playerModel->getPlayerList(["pid"=>$pid,"fields"=>"player_id,pid,team_id,game,player_name,en_name,cn_name,aka"]);
                    $teamList = $teamModel->getTeamList(["fields"=>"team_id,tid","team_ids"=>array_merge(array_unique(array_column($playerList1Merge,'team_id')),array_unique(array_column($playerList2Merge,'team_id')))]);
                    if(count(array_unique(array_column($teamList,"tid")))>1)
                    {
                        $return["result"] = false;
                        $return["log"][] = "不属于同一整合队伍中的队员不做整合操作";
                        return $return;
                    }
                    //开启事务
                    DB::beginTransaction();
                    foreach($playerList2Merge as $player2Merge)
                    {
                        //解绑队伍
                        $disintergration = $this->disintegration($player2Merge['player_id'],$playerModel,$totalPlayerModel,$playerNameMapModel,0);
                        $return['log'] = array_merge($return['log'],$disintergration['log']);
                        if($disintergration)
                        {
                            $return["log"][] = "队员:".$player2Merge['player_id']."解绑成功";
                            //合并
                            $merge = $this->mergeToPlayerMap($player2Merge,$pid,$playerModel,$playerNameMapModel);
                            if($merge)
                            {
                                $return["log"][] = "队员:".$player2Merge['player_id']."并入成功";

                            }
                            else
                            {
                                DB::rollBack();
                                $return["result"] = false;
                                $return["log"][] = "队员:".$player2Merge['player_id']."并入失败";
                                return $return;
                            }
                        }
                        else
                        {
                            DB::rollBack();
                            $return["result"] = false;
                            $return["log"][] = "队员:".$player2Merge['player_id']."解绑失败";
                            return $return;
                        }
                    }
                    //更新映射
                    $addRedirect = $this->addRedirect($totalPlayerModel,$pid2Merge,0,$pid);
                    if(!$addRedirect)
                    {
                        DB::rollBack();
                        $return["result"] = false;
                        $return["log"][] = "映射更新失败";
                        return $return;
                    }
                    else
                    {
                        $return["log"][] = "映射更新成功";
                        DB::commit();
                        $return["result"] = true;
                        $return["log"][] = "合并成功";
                        return $return;
                    }
                }
            }
            else
            {
                //需要合并的主队伍不存在
                $return["result"] = false;
                $return["log"][] = "主队员不存在";
                return $return;
            }
        }
    }
    //在总表中更新到新数据的映射
    public function addRedirect($totalPlayerModel,$pid,$new_player_id=0,$new_pid=0)
    {
        $totalPlayer = $totalPlayerModel->getPlayerById($pid,"pid,redirect");
        if(isset($totalPlayer['pid']))
        {
            $totalPlayer['redirect'] = json_decode($totalPlayer['redirect'],true)??[];
            if($new_player_id>0)
            {
                $totalPlayer['redirect']['player_id'] = $new_player_id;
            }
            else
            {
                unset($totalPlayer['redirect']['player_id']);
            }
            if($new_pid>0)
            {
                $totalPlayer['redirect']['pid'] = $new_pid;
            }
            else
            {
                unset($totalPlayer['redirect']['pid']);
            }
            return $totalPlayerModel->updatePlayer($pid,$totalPlayer);
        }
        else
        {
            return false;
        }
    }
    //解绑某个队伍
    function disintegration($player_id,$playerModel,$totalPlayerModel,$playerNameMapModel,$transaction = 1)
    {
        $return = ["result"=>false,"log"=>[]];
        //获取队员信息
        $playerInfo = $playerModel->getPlayerById($player_id,"player_id,tid,player_name,cn_name,en_name,aka");
        //找到队伍
        if(isset($playerInfo['player_id']))
        {
            if($playerInfo['pid']>0)
            {
                if($transaction)
                {
                    //自动打开事务
                    $return["log"][] = "事务开启";
                    DB::beginTransaction();
                }
                //-----------------------------------删除名称映射
                //获取当前占用的名称列表
                $currentHashList = $playerNameMapModel->getHashByPid($playerInfo['pid']);
                //获取团队中其他队伍需要占用的名称列表
                $toKeepHashList = [];
                $otherPlayerList = $playerModel->getPlayerList(['pid'=>$playerInfo['pid'],"except_player"=>$player_id,"fields"=>"player_id,game,pid,player_name,cn_name,en_name,aka"]);
                foreach($otherPlayerList as $otherPlayer)
                {
                    $aka = json_decode($otherPlayer['aka'], true);
                    //合并 去重
                    $toKeepHashList = array_unique(array_merge($toKeepHashList, getNames($otherPlayer,["player_name","en_name","cn_name"],["aka"])));
                }
                foreach($currentHashList as $hash)
                {
                    //不在其他队伍需要占用的列表中
                    if(!in_array($hash['name_hash'],$toKeepHashList))
                    {
                        //删除
                        $deleteHash = $playerNameMapModel->deleteMap($hash['id']);
                        if(!$deleteHash)
                        {
                            $return["log"][] = "映射:".$hash['id']."删除失败";
                            if($transaction)
                            {
                                DB::rollBack();
                            }
                            $return['result'] = false;
                            return $return;
                        }
                        else
                        {
                            $return["log"][] = "映射:".$hash['id']."删除成功";
                        }
                    }
                }
                //-----------------------------------删除名称映射
                //改写队伍记录
                $updatePlayer = $playerModel->updatePlayer($playerInfo['player_id'],["pid"=>0]);
                if(!$updatePlayer)
                {
                    $return["log"][] = "队员表：".$playerInfo['player_id']."改写失败";
                    if($transaction)
                    {
                        DB::rollBack();
                    }
                    $return['result'] = false;
                    return $return;
                }
                else
                {
                    $return["log"][] = "队员表：".$playerInfo['player_id']."改写成功";
                }
                //如果还有其他队伍
                if(count($otherPlayerList))
                {
                    //总表记录不做修改
                    $return["log"][] = "无需修改总表";
                    if($transaction)
                    {
                        DB::commit();
                    }
                    $return['result'] = true;
                    return $return;
                }
                else
                {
                    //总表记录需要写入新的映射
                    $updateTotalPlayer = $this->addRedirect($totalPlayerModel,$playerInfo['pid'],$playerInfo['player_id'],0);
                    if($updateTotalPlayer)
                    {
                        $return["log"][] = "改写总表映射成功";
                        if($transaction)
                        {
                            DB::commit();
                        }
                        $return['result'] = true;
                        return $return;
                    }
                    else
                    {
                        $return["log"][] = "改写总表映射失败";
                        if($transaction)
                        {
                            DB::rollBack();
                        }
                        $return['result'] = false;
                        return $return;
                    }
                }
            }
            else//未整合 返回成功
            {
                $return["log"][] = "队员未进行整合，跳过";
                $return['result'] = true;
                return $return;
            }
        }
        else
        {
            $return["log"][] = "队员不存在";
            $return['result'] = false;
            return $return;
        }
    }

    //通过scoregg站点的team_id获取战队的基础数据
    public function getScoreggPlayerInfo($player_id=0){
        $scoregg_url='https://www.scoregg.com/big-data/player/'.$player_id;
        $qt=QueryList::get($scoregg_url);

        //联赛胜率相关统计
        $victory_rate=$qt->find('.left-content .basic-data .data-progress .win-rate .progress-top-text .title')->text();//胜率
        $victory_rate=trim($victory_rate,'%');
        $victory_detail=$qt->find('.left-content .basic-data .data-progress .win-rate .progress-top-text .r-text')->text();
        $victory_detail=explode(" ",$victory_detail);
        $total_count=isset($victory_detail[0]) ? trim($victory_detail[0],'场'):0;//总场数
        $win_count=isset($victory_detail[1]) ? trim($victory_detail[1],'胜'):0;//胜场数
        $lose_count=isset($victory_detail[2]) ? trim($victory_detail[2],'负'):0;//负场数

        //参团率相关
        $join_rate=$qt->find('.left-content .basic-data .data-progress .join-rate .progress-top-text .title')->text();
        $join_rate=trim($join_rate,'%');
        //参团排名
        $join_rank=$qt->find('.left-content .basic-data .data-progress .join-rate .progress-top-text .light-text')->text();

        //kda相关
        $kda=$qt->find('.left-content .basic-data .data-progress .kda .kda-progress-text .kda-num')->text();
        $kda=trim($kda,'KDA');
        //kda排名
        $kda_rank=$qt->find('.left-content .basic-data .data-progress .kda .kda-progress-text .kda-des span')->text();
        $kda_detail=$qt->find('.left-content .basic-data .data-progress .kda .kda-progress-text .r-text span')->text();
        $kda_detail=explode(" / ",$kda_detail);
        //总击杀
        $total_kills=isset($kda_detail[0]) ? trim($kda_detail[0]):0;//总击杀
        $total_deaths=isset($kda_detail[1]) ? trim($kda_detail[1]):0;//总死亡
        $total_assists=isset($kda_detail[2]) ? trim($kda_detail[2]):0;//总助攻
        $base_ability_detail=[];
        $base_ability_detail['kda']=[
            'score-num'=>$kda,
            'score-des'=>"KDA",
            'score-rank'=>$kda_rank
        ];


        //获取数据明细
        $data_list_item=$qt->rules(array(
            'score-num' => array('.score-num','text'),
            'score-des' => array('.score-des','text'),
            'score-rank' => array('.score-rank','text'),
        ))->range('.left-content .basic-data  .data-list-item .item')->queryData();
        $data_list_item=$data_list_item ?? [];
        if(count($data_list_item) >0) {
            foreach ($data_list_item as $key=>$val){
                //击杀
                if(strpos($val['score-des'],'击杀')!==false ){
                    $base_ability_detail['kills']['score-num']=$val['score-num'];
                    $base_ability_detail['kills']['score-des']=$val['score-des'];
                    $base_ability_detail['kills']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //死亡
                if(strpos($val['score-des'],'死亡')!==false ){
                    $base_ability_detail['deaths']['score-num']=$val['score-num'];
                    $base_ability_detail['deaths']['score-des']=$val['score-des'];
                    $base_ability_detail['deaths']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //助攻
                if(strpos($val['score-des'],'助攻')!==false ){
                    $base_ability_detail['assists']['score-num']=$val['score-num'];
                    $base_ability_detail['assists']['score-des']=$val['score-des'];
                    $base_ability_detail['assists']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //分均伤害injury
                if(strpos($val['score-des'],'分均伤害')!==false ){
                    $base_ability_detail['minute_injury']['score-num']=$val['score-num'];
                    $base_ability_detail['minute_injury']['score-des']=$val['score-des'];
                    $base_ability_detail['minute_injury']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //伤害占比
                if(strpos($val['score-des'],'伤害占比')!==false ){
                    $base_ability_detail['injury_rate']['score-num']=trim($val['score-num'],"%");
                    $base_ability_detail['injury_rate']['score-des']=$val['score-des'];
                    $base_ability_detail['injury_rate']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //伤害转化率
                if(strpos($val['score-des'],'伤害转化率')!==false ){
                    $base_ability_detail['injury_inversion_rate']['score-num']=trim($val['score-num'],"%");
                    $base_ability_detail['injury_inversion_rate']['score-des']=$val['score-des'];
                    $base_ability_detail['injury_inversion_rate']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //分均承伤
                if(strpos($val['score-des'],'分均承伤')!==false ){
                    $base_ability_detail['minute_damagetaken']['score-num']=trim($val['score-num'],"%");
                    $base_ability_detail['minute_damagetaken']['score-des']=$val['score-des'];
                    $base_ability_detail['minute_damagetaken']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //承伤占比
                if(strpos($val['score-des'],'承伤占比')!==false ){
                    $base_ability_detail['damagetaken_rate']['score-num']=trim($val['score-num'],"%");
                    $base_ability_detail['damagetaken_rate']['score-des']=$val['score-des'];
                    $base_ability_detail['damagetaken_rate']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //分均补刀
                if(strpos($val['score-des'],'分均补刀')!==false ){
                    $base_ability_detail['minute_hits']['score-num']=trim($val['score-num']);
                    $base_ability_detail['minute_hits']['score-des']=$val['score-des'];
                    $base_ability_detail['minute_hits']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //分均插眼
                if(strpos($val['score-des'],'分均插眼')!==false ){
                    $base_ability_detail['minute_wardsplaced']['score-num']=trim($val['score-num']);
                    $base_ability_detail['minute_wardsplaced']['score-des']=$val['score-des'];
                    $base_ability_detail['minute_wardsplaced']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //分均排眼
                if(strpos($val['score-des'],'分均排眼')!==false ){
                    $base_ability_detail['minute_wardkilled']['score-num']=trim($val['score-num']);
                    $base_ability_detail['minute_wardkilled']['score-des']=$val['score-des'];
                    $base_ability_detail['minute_wardkilled']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }
                //英雄池
                if(strpos($val['score-des'],'英雄池')!==false ){
                    $base_ability_detail['hero_pool']['score-num']=trim($val['score-num'],"%");
                    $base_ability_detail['hero_pool']['score-des']=$val['score-des'];
                    $base_ability_detail['hero_pool']['score-rank']=trim($val['score-rank'],' 联赛第 ');
                }

            }

        }

        $player_ability_and_base=[
            'victory_rate'=>$victory_rate,//胜率
            'total_count'=>$total_count,//比赛场数
            'win'=>$win_count,//胜利场数
            'lose'=>$lose_count,//失败场数
            'total_kills'=>$total_kills,//总击杀数
            'total_deaths'=>$total_deaths,//总死亡数
            'total_assists'=>$total_assists,//总助攻数
            'base_ability_detail'=>$base_ability_detail,//基础数据明细
            'join_rate'=>$join_rate,//参团率
            'join_rank'=>$join_rank,//参团排名
            'kda'=>$kda,//kda
            'kda_rank'=>$kda_rank,//kda排名

        ];
        return $player_ability_and_base;
    }
    //设置关联队员的各项显示状态
    //-1表示沿用当前状态 0表示设定隐藏 1表示设定显示
    public function processPlayerDisplay($team_id = 0,$player_id = 0,$status = -1)
    {
        $playerModel = new PlayerModel();
        $totalPlayerModel = new TotalPlayerModel();
        if($team_id>0)
        {
            $playerList = $playerModel->getPlayerList(['team_id'=>$team_id,'page_size'=>1000,"fields"=>"player_id,pid,status,original_source"]);
            foreach($playerList as $playerInfo)
            {
                //不沿用当前状态 且目标状态与现有状态不一致
                if($status !=-1 && $playerInfo['status']!=$status)
                {
                    $this->processPlayerDisplay(0,$playerInfo['player_id'],$status);
                }
                else
                {
                    echo "sameStatus\n";
                    echo "pass\n";
                }
            }
        }
        elseif($player_id>0)
        {
            $playerInfo = $playerModel->getPlayerById($player_id,"player_id,pid,status,original_source");
            //不沿用当前状态 且目标状态与现有状态不一致
            if($status != -1 && $status != $playerInfo['status'])
            {
                //更新原有记录
                $playerModel->updatePlayer($player_id,['status'=>$status]);
            }
            $connectPlayerList = $playerModel->getPlayerList(['pid'=>$playerInfo['pid'],"fields"=>"player_id,pid,status,original_source"]);
            $displayStatus = array_sum(array_column($connectPlayerList,'status'));
            $totalPlayerModel->updatePlayer($playerInfo['pid'],['display'=>$displayStatus>0?1:0]);
           /* if($displayStatus == 0)
            {
                $totalPlayerModel->updatePlayer($playerInfo['pid'],['display'=>$displayStatus]);
            }*/
            (new IntergrationService())->getPlayerInfo(0,$playerInfo['pid'],1,1);
        }
    }
}
