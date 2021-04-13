<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
use App\Models\Match\scoregg\tournamentModel;
use App\Models\MissionModel;
use App\Models\TeamModel;
use App\Models\PlayerModel;

use App\Models\Player\TotalPlayerModel as TotalPlayerModel;
use App\Models\Player\PlayerMapModel as PlayerMapModel;
use App\Models\Player\PlayerNameMapModel as PlayerNameMapModel;

use App\Models\Team\TotalTeamModel as TotalTeamModel;
use App\Models\Team\TeamMapModel as TeamMapModel;
use App\Models\Team\TeamNameMapModel as TeamNameMapModel;

use App\Services\PlayerService;
use Illuminate\Support\Facades\DB;
use QL\QueryList;

class TeamResultService
{
    public function insertTeamData($mission_type,$game)
    {
        $this->insertCpseoTeam($game, $mission_type);
        $this->getScoreggTeamDetail($game);
        //采集玩加（www.wanplus.com）战队信息
         //$this->insertWanplus($game, $mission_type);
        //采集cpseo（2cpseo.com）战队信息



        return 'finish';
    }

    public function getScoreggTeamDetail($game){
        $teamModel = new TeamModel();
        $missionModel = new MissionModel();
        if($game=='kpl'){
            $gameID=2;
        }elseif($game=='lol'){
            $gameID=1;
        }
        $tournament_url='https://www.scoregg.com/services/api_url.php';
        $tournament_param = [
            'api_path' => '/services/match/tournament_list.php',
            'method' => 'post',
            'platform' => 'web',
            'api_version' => '9.9.9',
            'language_id' => 1,
            'gameID' => $gameID ?? 0,

        ];
        $tournament_data = curl_post($tournament_url, $tournament_param);
        $tournamentList=$tournament_data['data']['list'] ?? [];
        $pageTotal=2;
        $missionCount=0;
        if(count($tournamentList) >0 ) {
            foreach ($tournamentList as $val) {
                for ($i = 1; $i <= $pageTotal; $i++) {
                    $url = 'https://www.scoregg.com/services/api_url.php';
                    $param = [
                        'api_path' => '/services/gamingDatabase/match_data_ssdb_list.php',
                        'method' => 'post',
                        'platform' => 'web',
                        'api_version' => '9.9.9',
                        'language_id' => 1,
                        'tournament_id' => $val['tournamentID'] ?? 0,
                        'type' => 'team',
                        'order_type' => 'KDA',
                        'order_value' => 'DESC',
                        'team_name' => '',
                        'player_name' => '',
                        'positionID' => '',
                        'page' => $i,

                    ];
                    $cdata = curl_post($url, $param);
                    $cdata = $cdata['data']['data']['list'] ?? [];
                    if (count($cdata) > 0) {
                        //
                        foreach ($cdata as $k=>$v) {
                            $team_url = 'https://www.scoregg.com/big-data/team/' . $v['team_id'] . '?tournamentID=&type=baike';
                            if (strpos($v['team_image'], '_100X100') !== false) {
                                $v['team_image'] = str_replace('_100X100', '', $v['team_image']);
                            }
                            $v['team_url'] = $team_url;
                            $v['game'] = $game;
                            $v['source'] = 'scoregg';
                            $params = [
                                'game' => $game,
                                'mission_type' => 'team',
                                'source_link' => $team_url,
                            ];
                            $teamInfo = $teamModel->getTeamBySiteId($v['team_id'], 'scoregg', $game);

                            $teamInfo = $teamInfo ?? [];
                            if (count($teamInfo) == 0) {
                                $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的文章
                                $missionCount = $missionCount ?? 0;
                                if($missionCount!==0){
                                    echo "exits-mission-".$game.'-'.$team_url. "\n";//表示Mission表记录已存在，跳出继续
                                    continue; //表示Mission表记录已存在，跳出继续
                                }else{
                                    $adata = [
                                        "asign_to" => 1,
                                        "mission_type" => 'team',
                                        "mission_status" => 1,
                                        "game" => $game,
                                        "source" => 'scoregg',
                                        "title" => $v['team_name'] ?? '',
                                        'source_link' => $team_url,
                                        "detail" => json_encode($v),
                                    ];
                                    $insert = (new oMission())->insertMission($adata);
                                    echo $game."-scoregg-mission-insert:" . $insert . ' lenth:' . strlen($adata['detail']) . "\n";
                                }
                            }else{
                                echo "exits-teaminfo-scoregg-".$game.'-'.$team_url. "\n";//表示teaminfo表记录已存在，跳出继续
                                continue;
                            }
                        }
                    }

                }

            }

        }
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
            $list=$list ?? [];
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
                                echo $game."-information-wanplus-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                            }else{
                                echo $game."exits-Mission-wanplus".$site_id."\n";//表示任务表存在记录，跳出继续
                                continue;
                            }
                        }
                    } else {
                        echo $game."exits-information-wanplus-".$site_id."\n";//表示teaminfo表记录，跳出继续
                        continue;
                    }

                }
            }else{
                continue;
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
        $page_count = 0;
        if ($game == 'lol') {
            $page_count = 4;
        } elseif ($game == 'kpl') {
            $page_count = 2;
        } elseif ($game == 'dota2') {
            $page_count = 11;
        } elseif ($game == 'csgo') {
            $page_count = 13;
        }
        for ($i = 1; $i <= $page_count; $i++) {
            if ($game == 'kpl') {
                $url = 'http://www.2cpseo.com/teams/kog/p-'.$i;
            } else{
                $url = 'http://www.2cpseo.com/teams/'.$game.'/p-'.$i;
            }
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
                            'source_link' => $team_url,
                            "detail" => json_encode(
                                [
                                    "url" => $team_url,
                                    "game" => $game,
                                    "source" => 'cpseo',
                                    "site_id" => $site_id,
                                ]
                            ),
                        ];
                        $insert = (new oMission())->insertMission($data);
                        echo "lol-team-cpseo-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
                    } else {
                        echo "lol-Mission-cpseo exits"."\n";//表示任务表存在记录，跳出继续
                        continue;
                    }
                } else {
                    echo "team_info-cpseo-exits".$site_id."\n";//表示teaminfo表记录，跳出继续
                    continue;
                }

            }
        }
        return true;
    }
    public function intergration($game = "lol")
    {
        $totalPlayerModel = new TotalPlayerModel();
        $playerMapModel = new PlayerMapModel();
        $playerNameMapModel = new PlayerNameMapModel();

        $totalTeamModel = new TotalTeamModel();
        $teamMapModel = new TeamMapModel();
        $teamNameMapModel = new TeamNameMapModel();

        $teamModel = new TeamModel();
        $playerModel = new PlayerModel();

        $playerServices = new PlayerService();
        $team_intergration = config("app.intergration.team")??[];
        $return = [];
        $teamList = $teamModel->getTeamList(["fields"=>"team_id,team_name,en_name,cn_name,aka,original_source,game","tid"=>0,"game"=>$game,"sources"=>array_column($team_intergration,"source"),"page_size"=>99999999]);
        echo count($teamList);
        //die();//生成所有队伍的可用名称列表
        foreach($teamList as $key => $teamInfo)
        {
            $nameList[$teamInfo['team_id']] = [];
            $aka = json_decode($teamInfo['aka'], true);
            $detailNameList = getNames($teamInfo,["team_name","en_name","cn_name"],["aka"]);
            $nameList[$teamInfo['team_id']] = $detailNameList;
            $teamList[$key]['nameList'] = $detailNameList;
        }
        //循环队伍
        foreach($teamList as $teamInfo)
        {
            echo "start to process team:".$teamInfo['team_id']."\n";
            $currentExistedPlayer = [];
            //尝试获取总表到映射表的对应关系
            $currentMap = $teamMapModel->getTeamByTeamId($teamInfo['team_id']);
            //如果没取到
            if(!isset($currentMap['tid']))
            {
                $playerList_toProcess = $playerModel->getPlayerList(["team_id"=>$teamInfo['team_id'],"fields"=>"player_id,pid,player_name,cn_name,en_name,aka,game,original_source","page_size"=>1000]);
                $ourPlayer = [];
                foreach($playerList_toProcess as $player_toProcess)
                {
                    $name = generateNameHash($player_toProcess['player_name']);
                    if($name!="")
                    {
                        $ourPlayer[$player_toProcess['player_id']][] = $name;
                    }
                }
                //根据名称找到映射
                $name = generateNameHash($teamInfo['team_name']);
                $currentMapList = $teamNameMapModel->getTeamByNameHash($name,$teamInfo['game']);
                //循环现有映射 进行比对
                //$tidList = array_column($currentMapList,"tid");
                $toCreate = 1;
                if(count($currentMapList)>0)
                {
                    foreach($currentMapList as $currentMap)
                    {
                        $matchedPlayer = 0;
                        $currentMapTeamList = $teamMapModel->getTeamByTid(["tid"=>$currentMap['tid']]);
                        $teamIdList = array_column($currentMapTeamList,"team_id");
                        $playerList_toMerge = $playerModel->getPlayerList(["team_ids"=>$teamIdList,"fields"=>"player_id,pid,player_name,cn_name,en_name,aka,game,original_source","page_size"=>1000]);
                        //双方队员数量都大于2
                        if(count($playerList_toMerge)>=3 && count($playerList_toProcess)>=3)
                        {
                            $toMerge = [];
                            foreach($playerList_toMerge as $k => $playerInfo)
                            {
                                $aka = json_decode($playerInfo['aka'], true);
                                $playerNameList = getNames($playerInfo,["player_name","en_name","cn_name"],["aka"]);
                                foreach ($playerNameList as $key => $nameToCheck)
                                {

                                    foreach($ourPlayer as $player_id => $playerName)
                                    {
                                        if(in_array($nameToCheck,$playerName))
                                        {
                                            $matchedPlayer++;
                                            echo "playerInfo:".$playerInfo['player_id']."\n";
                                            $toMerge[$player_id][] = $playerInfo['pid'];
                                            $toMerge[$player_id] = array_unique($toMerge[$player_id]);
                                            //$toMerge[$player_id][] = ["pid"=>$playerInfo['pid'],"name"=>$nameToCheck];
                                        }
                                    }
                                }
                            }
                        }
                        if($matchedPlayer>=3)
                        {
                            //print_R($playerNameList);
                            echo ">>>>>>>>>>>>>>>>>>>>>>>>>-------------------------<<<<<<<<<<<<<<<<<<<<<<<<<\n";
                            print_R($toMerge);
                            sleep(1);
                            echo "<<<<<<<<<<<<<<<<<<<<<<<<<------------------------->>>>>>>>>>>>>>>>>>>>>>>>>\n";
                            //die();
                        }
                        if($matchedPlayer>=3)//整合入队伍
                        {
                            DB::beginTransaction();
                            //合并入查到的映射里面
                            $mergeToMap = $this->mergeToTeamMap($teamInfo,$currentMap['tid'],$teamMapModel,$teamNameMapModel);
                            if(!$mergeToMap)
                            {
                                DB::rollBack();
                            }
                            else
                            {
                                //把映射写回原来的队伍内容
                                $updateTeam = $teamModel->updateTeam($teamInfo['team_id'],["tid"=>$currentMap['tid']]);
                                if($updateTeam)
                                {
                                    echo "merged team ".$teamInfo['team_id']." to existed:".$currentMap['tid']."\n";
                                    foreach($playerList_toProcess as $player_toProcess)
                                    {
                                        if(isset($toMerge[$player_toProcess['player_id']]))
                                        {
                                            $toMerge[$player_toProcess['player_id']] = array_unique($toMerge[$player_toProcess['player_id']]);
                                            foreach($toMerge[$player_toProcess['player_id']] as $pid_to_merge)
                                            {
                                                //合并入创建的映射里面
                                                $mergeToMap = $playerServices->mergeToPlayerMap($player_toProcess, $pid_to_merge, $playerModel, $playerMapModel, $playerNameMapModel);
                                                if(!$mergeToMap)
                                                {
                                                    DB::rollBack();break;
                                                }
                                                else
                                                {
                                                    echo "merged player ".$player_toProcess['player_id']." to existed ".$pid_to_merge."\n";
                                                }
                                            }
                                        }
                                        else
                                        {
                                            //创建队员
                                            $insertPlayer = $totalPlayerModel->insertPlayer(['game'=>$player_toProcess['game'],'original_source'=>$player_toProcess['original_source']]);
                                            //创建成功
                                            if($insertPlayer)
                                            {
                                                //合并入创建的映射里面
                                                $mergeToMap = $playerServices->mergeToPlayerMap($player_toProcess, $insertPlayer, $playerModel, $playerMapModel, $playerNameMapModel);
                                                if(!$mergeToMap)
                                                {
                                                    DB::rollBack();
                                                }
                                                else
                                                {
                                                    echo "merged player ".$player_toProcess['player_id']." to created ".$insertPlayer."\n";
                                                }
                                            }
                                        }
                                    }
                                    //sleep(1);
                                    //die();
                                    DB::commit();
                                }
                                else
                                {
                                    DB::rollBack();
                                }
                            }
                            //合并
                            $toCreate = 0;
                        }
                    }
                }
                if($toCreate==1)//以自身创建一个整合队伍
                {
                    DB::beginTransaction();
                    //创建队伍
                    $insertTeam = $totalTeamModel->insertTeam(['game'=>$teamInfo['game'],'original_source'=>$teamInfo['original_source']]);
                    //创建成功
                    if($insertTeam)
                    {
                        //合并入查到的映射里面
                        $mergeToMap = $this->mergeToTeamMap($teamInfo,$insertTeam,$teamMapModel,$teamNameMapModel);
                        if(!$mergeToMap)
                        {
                            DB::rollBack();
                        }
                        else
                        {
                            //把映射写回原来的队伍内容
                            $updateTeam = $teamModel->updateTeam($teamInfo['team_id'],["tid"=>$insertTeam]);
                            if($updateTeam)
                            {
                                echo "merged team ".$teamInfo['team_id']." to created:".$insertTeam."\n";
                                //循环队员列表
                                foreach($playerList_toProcess as $player_toProcess)
                                {
                                    $insertPlayer = 0;
                                    $nameToCheck = generateNameHash($player_toProcess['player_name']);
                                    foreach($currentExistedPlayer as $p => $nameList)
                                    {
                                        if(in_array($nameToCheck,$nameList))
                                        {
                                            $insertPlayer = $p;
                                            break;
                                        }
                                    }
                                    if($insertPlayer==0)
                                    {
                                        //创建队员
                                        $insertPlayer = $totalPlayerModel->insertPlayer(['game'=>$player_toProcess['game'],'original_source'=>$player_toProcess['original_source']]);
                                    }
                                    //创建成功
                                    if($insertPlayer)
                                    {
                                        //合并入查到的映射里面
                                        $mergeToMap = $playerServices->mergeToPlayerMap($player_toProcess,$insertPlayer,$playerModel,$playerMapModel,$playerNameMapModel);
                                        if(!$mergeToMap)
                                        {
                                            DB::rollBack();break;
                                        }
                                        else
                                        {
                                            $currentExistedPlayer[$insertPlayer] = getNames($player_toProcess,["player_name","en_name","cn_name"],["aka"]);
                                            echo "merged player ".$player_toProcess['player_id']." to created ".$insertPlayer."\n";
                                        }
                                    }
                                    else
                                    {
                                        DB::rollBack();break;
                                    }
                                }
                                echo "success!";
                                DB::commit();
                            }
                            else
                            {
                                DB::rollBack();
                            }
                        }
                    }
                    else
                    {
                        DB::rollBack();
                    }
                }
            }
            else//找到映射
            {
                //不做操作
                //修复缺失的映射
            }
        }
        //return $return;
    }
    //把对于合并到已经查到的队伍映射
    function mergeToTeamMap($teamInfo = [],$tid,$teamMapModel,$teamNameMapModel)
    {
        $insertMap = $teamMapModel->insertMap(["tid"=>$tid,"team_id"=>$teamInfo['team_id']]);
        if($insertMap)
        {
            $aka = json_decode($teamInfo['aka'], true);
            $nameList = getNames($teamInfo,["team_name","en_name","cn_name"],["aka"]);
            foreach ($nameList as $name)
            {
                if($name != "")
                {
                    //保存名称映射
                    $saveMap = $teamNameMapModel->saveMap(["name_hash" => $name, "game" => $teamInfo['game'], "tid" => $tid]);
                    if (!$saveMap) {
                        //echo "insertTeamMapError";
                        return false;
                        //break;
                    }
                }
            }
            return true;
        }
    }
}
