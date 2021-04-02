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
use Illuminate\Support\Facades\DB;
use QL\QueryList;

class  PlayerService
{
    public function insertPlayerData($mission_type, $game)
    {
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
            if($game == 'kpl'){
                $url = 'http://www.2cpseo.com/players/kog/p-' . $i;
            }else{
                $url = 'http://www.2cpseo.com/players/'.$game.'/p-' . $i;
            }
            //判断url是否有效
            $headers=get_headers($url,1);
            if(!preg_match('/200/',$headers[0])){
                return  [];
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
                        echo "lol-player-cpseo-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                    } else {
                        echo "lol-Mission-cpseo-exits"."\n";//表示任务表存在记录，跳出继续
                        continue;
                    }
                } else {
                    echo $player_url."\n";
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
        }elseif ($game == 'dota2') {
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
                                            echo $game .$key. $k."-scoregg-player-mission-insert:" . $insert . ' lenth:' . strlen($adata['detail']) . "\n";
                                        }
                                    } else {
                                        echo "exist-playerinfo-scoregg-" . $game . '-' . $v['player_url'] . "\n";//表示playerinfo表记录已存在，跳出继续
                                        continue;
                                    }
                                }else{
                                    echo "player_id:".$v['player_id'];
                                    continue;
                                }

                            }

                        }else{
                            continue;
                        }
                    }
                }else{
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
    public function intergration($id = 0)
    {
        $totalPlayerModel = new TotalPlayerModel();
        $playerMapModel = new PlayerMapModel();
        $playerNameMapModel = new PlayerNameMapModel();
        $playerModel = new PlayerModel();
        $player_intergration = config("app.intergration.player")??[];
        $return = false;
        if($id==0)
        {
            $playerList = $playerModel->getPlayerList(["fields"=>"player_id,player_name,en_name,cn_name,aka,original_source,game","pid"=>0,"page_size"=>3000]);
        }
        else
        {
            $playerInfo = $playerModel->getPlayerById($id);
            $playerList = [$playerInfo];
        }
        foreach($playerList as $playerInfo)
        {
            if(isset($playerInfo['player_id']))
            {
                echo "start to process team:".$playerInfo['player_id']."\n";
                //如果当前来源不相同于默认来源
                if($playerInfo['original_source']==$player_intergration['0']['source'])
                {
                    //尝试获取总表到映射表的对应关系
                    $currentMap = $playerMapModel->getPlayerByPlayerId($playerInfo['player_id']);
                    //如果没取到
                    if(!isset($currentMap['pid']))
                    {
                        DB::beginTransaction();
                        //创建队员
                        $insertPlayer = $totalPlayerModel->insertPlayer(['game'=>$playerInfo['game'],'original_source'=>$playerInfo['original_source']]);
                        //创建成功
                        if($insertPlayer)
                        {
                            //合并入查到的映射里面
                            $mergeToMap = $this->mergeToPlayerMap($playerInfo,$insertPlayer,$playerModel,$playerMapModel,$playerNameMapModel);
                            if(!$mergeToMap)
                            {
                                DB::rollBack();
                            }
                            else
                            {
                                echo "merged ".$playerInfo['player_id']." to created ".$insertPlayer."\n";
                                DB::commit();
                            }
                        }
                        else
                        {
                            //echo "insertPlayerError";
                            DB::rollBack();
                        }
                    }
                    else//找到映射
                    {
                        //合并入查到的映射里面
                        $mergeToMap = $this->mergeToPlayerMap($playerInfo,$currentMap['pid'],$playerModel,$playerMapModel,$playerNameMapModel);
                        if(!$mergeToMap)
                        {
                            DB::rollBack();
                        }
                        else
                        {
                            echo "merged ".$playerInfo['player_id']." to existed ".$currentMap['pid']."\n";
                            DB::commit();
                        }
                    }
                }
                else
                {
                    //根据名称找到映射
                    $name = $this->generateNameHash($playerInfo['player_name']);
                    $currentMap = $playerNameMapModel->getPlayerByNameHash($name,$playerInfo['game']);
                    if(isset($currentMap['pid']))
                    {
                        DB::beginTransaction();
                        //合并入查到的映射里面
                        $mergeToMap = $this->mergeToPlayerMap($playerInfo,$currentMap['pid'],$playerModel,$playerMapModel,$playerNameMapModel);
                        if(!$mergeToMap)
                        {
                            DB::rollBack();
                        }
                        else
                        {
                            echo "merged ".$playerInfo['player_id']." to existed ".$currentMap['pid']."\n";
                            DB::commit();
                        }
                    }
                    else//没有匹配上 创建
                    {
                        DB::beginTransaction();
                        //创建队员
                        $insertPlayer = $totalPlayerModel->insertPlayer(['game'=>$playerInfo['game'],'original_source'=>$playerInfo['original_source']]);
                        //创建成功
                        if($insertPlayer)
                        {
                            //合并入查到的映射里面
                            $mergeToMap = $this->mergeToPlayerMap($playerInfo,$insertPlayer,$playerModel,$playerMapModel,$playerNameMapModel);
                            if(!$mergeToMap)
                            {
                                DB::rollBack();
                            }
                            else
                            {
                                echo "merged ".$playerInfo['player_id']." to created ".$insertPlayer."\n";
                                //sleep(1);
                                DB::commit();
                            }
                        }
                        else
                        {
                            //echo "insertPlayerError";
                            DB::rollBack();
                        }
                    }
                }
            }
        }
    }
    function generateNameHash($name = "")
    {
        $name = strtolower($name);
        $name = trim($name);
        $replaceList = [" ","."];
        foreach($replaceList as $key)
        {
            $name = str_replace($key,"",$name);
        }
        $name = $this->removeEmoji($name);
        //echo "hash:".$name."\n";
        return $name;
    }
    function removeEmoji($text) {
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
    function mergeToPlayerMap($playerInfo = [],$pid,$playerModel,$playerMapModel,$playerNameMapModel)
    {
        $insertMap = $playerMapModel->insertMap(["pid"=>$pid,"player_id"=>$playerInfo['player_id']]);
        if($insertMap)
        {
            $aka = json_decode($playerInfo['aka'], true);
            $nameList = (array_merge([$playerInfo['player_name'],$playerInfo['en_name'],$playerInfo['en_name']], $aka??[]));
            foreach ($nameList as $key => $name)
            {
                if ($name == "")
                {
                    unset($nameList[$key]);
                }
                else
                {
                    $nameList[$key] = $this->generateNameHash($name);
                }
            }
            $nameList = array_unique($nameList);
            foreach ($nameList as $name)
            {
                if($name != "")
                {
                    //保存名称映射
                    $saveMap = $playerNameMapModel->saveMap(["name_hash" => $name, "game" => $playerInfo['game'], "pid" => $pid]);
                    if (!$saveMap) {
                        //echo "insertPlayerMapError";
                        return false;
                        //break;
                    }
                }

            }
            $updateTid = $playerModel->updatePlayer($playerInfo['player_id'],["pid"=>$pid]);
            if(!$updateTid)
            {
                return false;
            }
            return true;
        }
    }
}
