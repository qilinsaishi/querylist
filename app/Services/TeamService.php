<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
use App\Models\Match\scoregg\tournamentModel;
use App\Models\MissionModel;
use App\Models\TeamModel;
use App\Models\PlayerModel;
use App\Services\MissionService as oMission;
use App\Models\Player\TotalPlayerModel as TotalPlayerModel;
use App\Models\Player\PlayerMapModel as PlayerMapModel;
use App\Models\Player\PlayerNameMapModel as PlayerNameMapModel;

use App\Models\Team\TotalTeamModel as TotalTeamModel;
use App\Models\Team\TeamNameMapModel as TeamNameMapModel;
use App\Services\PlayerService;
use Illuminate\Support\Facades\DB;
use QL\QueryList;

class TeamService
{
    public function insertTeamData($mission_type,$game)
    {
        //$this->insertCpseoTeam($game);
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
                            //if (count($teamInfo) == 0) {
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
                            /*}else{
                                echo "exits-teaminfo-scoregg-".$game.'-'.$team_url. "\n";//表示teaminfo表记录已存在，跳出继续
                                continue;
                            }*/
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
                                    "mission_type" => 'team',
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
    public function insertCpseoTeam($game)
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
                    'mission_type' => 'team',
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
                            "mission_type" => 'team',
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
        $playerNameMapModel = new PlayerNameMapModel();

        $totalTeamModel = new TotalTeamModel();
        $teamNameMapModel = new TeamNameMapModel();

        $teamModel = new TeamModel();
        $playerModel = new PlayerModel();

        $playerServices = new PlayerService();
        $team_intergration = config("app.intergration.team")??[];
        $return = [];
        $teamList = $teamModel->getTeamList(["fields"=>"team_id,tid,team_name,en_name,cn_name,aka,original_source,game","tid"=>0,"game"=>$game,"sources"=>array_column($team_intergration,"source"),"page_size"=>99999999]);
        echo count($teamList);
        //生成所有队伍的可用名称列表
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
            $currentMap = $teamInfo;
            //如果没取到
            if($currentMap['tid']==0)
            {
                //获取队员列表
                $playerList_toProcess = $playerModel->getPlayerList(["team_id"=>$teamInfo['team_id'],"fields"=>"player_id,pid,player_name,cn_name,en_name,aka,game,original_source","page_size"=>1000]);
                //如果队伍里没人
                if(count($playerList_toProcess)==0)
                {
                    //跳过
                    echo "empty team:".$teamInfo['team_id']." pass\n";
                    continue;
                }
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
                        $currentMapTeamList = $teamModel->getTeamList(["tid"=>$currentMap['tid'],"fields"=>"team_id,tid"]);
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
                            $mergeToMap = $this->mergeToTeamMap($teamInfo,$currentMap['tid'],$teamModel,$teamNameMapModel);
                            if(!$mergeToMap)
                            {
                                DB::rollBack();
                            }
                            else
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
                                            $mergeToMap = $playerServices->mergeToPlayerMap($player_toProcess, $pid_to_merge, $playerModel,  $playerNameMapModel);
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
                                            $mergeToMap = $playerServices->mergeToPlayerMap($player_toProcess, $insertPlayer, $playerModel,  $playerNameMapModel);
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
                        $mergeToMap = $this->mergeToTeamMap($teamInfo,$insertTeam,$teamModel,$teamNameMapModel);
                        if(!$mergeToMap)
                        {
                            DB::rollBack();
                        }
                        else
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
                                    $mergeToMap = $playerServices->mergeToPlayerMap($player_toProcess,$insertPlayer,$playerModel,$playerNameMapModel);
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
    function mergeToTeamMap($teamInfo = [],$tid,$teamModel,$teamNameMapModel)
    {
        $aka = json_decode($teamInfo['aka'], true);
        $nameList = getNames($teamInfo, ["team_name", "en_name", "cn_name"], ["aka"]);
        foreach ($nameList as $name)
        {
            if ($name != "")
            {
                //保存名称映射
                $saveMap = $teamNameMapModel->saveMap(["name_hash" => $name, "game" => $teamInfo['game'], "tid" => $tid]);
                if (!$saveMap)
                {
                    return false;
                }
            }
        }
        //把映射写回原来的队伍
        $updateTeam = $teamModel->updateTeam($teamInfo['team_id'],["tid"=>$tid]);
        if(!$updateTeam)
        {
            return false;
        }
        return true;
    }
    //解绑某个队伍
    function disintegration($team_id,$teamModel,$totalTeamModel,$teamNameMapModel,$transaction = 1)
    {
        $return = ["result"=>false,"log"=>[]];
        //获取队伍信息
        $teamInfo = $teamModel->getTeamById($team_id,"team_id,tid,team_full_name,team_name,cn_name,en_name,aka");
        //找到队伍
        if(isset($teamInfo['team_id']))
        {
            if($teamInfo['tid']>0)
            {
                if($transaction)
                {
                    //自动打开事务
                    $return["log"][] = "事务开启";
                    DB::beginTransaction();
                }
                //-----------------------------------删除名称映射
                //获取当前占用的名称列表
                $currentHashList = $teamNameMapModel->getHashByTid($teamInfo['tid']);
                //获取团队中其他队伍需要占用的名称列表
                $toKeepHashList = [];
                $otherTeamList = $teamModel->getTeamList(['tid'=>$teamInfo['tid'],"except_team"=>$team_id,"fields"=>"team_id,game,tid,team_full_name,team_name,cn_name,en_name,aka"]);
                foreach($otherTeamList as $otherTeam)
                {
                    $aka = json_decode($otherTeam['aka'], true);
                    //合并 去重
                    $toKeepHashList = array_unique(array_merge($toKeepHashList, getNames($otherTeam,["team_full_name","team_name","en_name","cn_name"],["aka"])));
                }
                foreach($currentHashList as $hash)
                {
                    //不在其他队伍需要占用的列表中
                    if(!in_array($hash['name_hash'],$toKeepHashList))
                    {
                        //删除
                        $deleteHash = $teamNameMapModel->deleteMap($hash['id']);
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
                $updateTeam = $teamModel->updateTeam($teamInfo['team_id'],["tid"=>0]);
                if(!$updateTeam)
                {
                    $return["log"][] = "队伍表：".$teamInfo['team_id']."改写失败";
                    if($transaction)
                    {
                        DB::rollBack();
                    }
                    $return['result'] = false;
                    return $return;
                }
                else
                {
                    $return["log"][] = "队伍表：".$teamInfo['team_id']."改写成功";
                }
                //如果还有其他队伍
                if(count($otherTeamList))
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
                    $updateTotalTeam = $this->addRedirect($totalTeamModel,$teamInfo['tid'],$teamInfo['team_id'],0 );
                    if($updateTotalTeam)
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
                $return["log"][] = "队伍未进行整合，跳过";
                $return['result'] = true;
                return $return;
            }
        }
        else
        {
            $return["log"][] = "队伍不存在";
            $return['result'] = false;
            return $return;
        }
    }
    //合并2个整合过的队伍
    public function merge2mergedTeam($tid=0,$tid2Merge=0)
    {
        $return = ["result"=>false,"log"=>[]];
        //如果双方ID有问题
        if($tid<=0 || $tid2Merge<=0)
        {
            $return["result"] = false;
            $return["log"][] = "ID有误";
            return $return;
        }
        //如果是同一个
        elseif($tid == $tid2Merge)
        {
            $return["result"] = true;
            $return["log"][] = "同一个队伍，无需再次合并";
            return $return;
        }
        else
        {
            $totalTeamModel = new totalTeamModel();
            $teamModel = new TeamModel();
            $teamNameMapModel = new TeamNameMapModel();
            $totalTeam1 = $totalTeamModel->getTeamById($tid,"tid,redirect");
            if(isset($totalTeam1['tid']))
            {
                $totalTeam1['redirect'] = json_decode($totalTeam1['redirect'],true);
                if(isset($totalTeam1['redirect']['team_id']) || isset($totalTeam1['redirect']['tid']) )
                {
                    //主队伍已经重定向
                    $return["result"] = false;
                    $return["log"][] = "主队伍已经重定向了";
                    return $return;
                }
                $teamList2Merge = $teamModel->getTeamList(["tid"=>$tid2Merge,"fields"=>"team_id,tid,game,team_name,en_name,cn_name,aka,team_full_name"]);
                //没有队伍
                if(count($teamList2Merge)==0)
                {
                    //主队伍已经重定向
                    $return["result"] = false;
                    $return["log"][] = "转入队伍找不到了";
                    return $return;
                }
                else
                {
                    //开启事务
                    DB::beginTransaction();
                    foreach($teamList2Merge as $team2Merge)
                    {
                        //解绑队伍
                        $disintergration = $this->disintegration($team2Merge['team_id'],$teamModel,$totalTeamModel,$teamNameMapModel,0);
                        $return['log'] = array_merge($return['log'],$disintergration['log']);
                        if($disintergration)
                        {
                            $return["log"][] = "队伍:".$team2Merge['team_id']."解绑成功";
                            //合并
                            $merge = $this->mergeToTeamMap($team2Merge,$tid,$teamModel,$teamNameMapModel);
                            if($merge)
                            {
                                $return["log"][] = "队伍:".$team2Merge['team_id']."并入成功";
                            }
                            else
                            {
                                DB::rollBack();
                                $return["result"] = false;
                                $return["log"][] = "队伍:".$team2Merge['team_id']."并入失败";
                                return $return;
                            }
                        }
                        else
                        {
                            DB::rollBack();
                            $return["result"] = false;
                            $return["log"][] = "队伍:".$team2Merge['team_id']."解绑失败";
                            return $return;
                        }
                    }
                    //更新映射
                    $addRedict = $this->addRedirect($totalTeamModel,$tid2Merge,0,$tid);
                    if(!$addRedict)
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
                $return["log"][] = "主队伍不存在";
                return $return;
            }
        }
    }
    //把未整合的队伍合并到已经整合过的队伍中
    public function mergeTeam2mergedTeam($tid,$teamId2Merge=0)
    {
        $return = ["result"=>false,"log"=>[]];
        //如果双方ID有问题
        if($tid<=0 || $teamId2Merge<=0)
        {
            $return["result"] = false;
            $return["log"][] = "ID有误";
            return $return;
        }
        else
        {
            $teamModel = new TeamModel();
            $teamNameMapModel = new TeamNameMapModel();
            $teamInfo = $teamModel->getTeamById($teamId2Merge);
            //没找到
            if(!isset($teamInfo['team_id']))
            {
                $return["result"] = false;
                $return["log"][] = "转入队伍不存在了";
                return $return;
            }
            //同一个队伍
            elseif($teamInfo['tid'] == $tid)
            {
                $return["result"] = true;
                $return["log"][] = "同一个队伍，无需再次合并";
                return $return;
            }
            //不是同一个且已经整合过
            elseif($teamInfo['tid'] > 0)
            {
                $return["result"] = false;
                $return["log"][] = "转入队伍已经被其他队伍整合了";
                return $return;
            }
            else
            {
                //开启事务
                DB::beginTransaction();
                //合并
                $merge = $this->mergeToTeamMap($teamInfo,$tid,$teamModel,$teamNameMapModel);
                if($merge)
                {
                    DB::commit();
                    $return["result"] = true;
                    $return["log"][] = "合并成功";
                    return $return;
                }
                else
                {
                    DB::rollBack();
                    $return["result"] = false;
                    $return["log"][] = "合并失败";
                    return $return;
                }
            }
        }
    }
    //合并2个未整合过的队伍
    public function merge2unmergedTeam($teamid=0,$teamId2Merge=0)
    {
        $return = ["result"=>false,"log"=>[]];
        $teamModel = new TeamModel();
        $teamNameMapModel = new TeamNameMapModel();
        $totalTeamModel = new TotalTeamModel();
        if($teamid<=0 || $teamId2Merge<=0)
        {
            $return["result"] = false;
            $return["log"][] = "ID有误";
            return $return;
        }
        elseif($teamid == $teamId2Merge)
        {
            $return["result"] = false;
            $return["log"][] = "同一个队伍不需要合并";
            return $return;
        }
        else {
            $teamInfo = $teamModel->getTeamById($teamid);
            $team2MergeInfo = $teamModel->getTeamById($teamId2Merge);
            if (!$teamInfo['team_id'])
            {
                $return["result"] = false;
                $return["log"][] = "转入队伍不存在";
                return $return;
            }
            elseif ($teamInfo['tid'] > 0)
            {
                $return["result"] = false;
                $return["log"][] = "转入队伍是一个已经整合了的队伍";
                return $return;
            }
            if (!$team2MergeInfo['team_id'])
            {
                $return["result"] = false;
                $return["log"][] = "被转入队伍不存在";
                return $return;
            }
            elseif ($team2MergeInfo['tid'] > 0)
            {
                if ($team2MergeInfo['tid'] == $teamInfo['tid'])
                {
                    $return["result"] = true;
                    $return["log"][] = "属于同一个整合队伍";
                    return $return;
                }
                else
                {
                    $return["result"] = false;
                    $return["log"][] = "被转入队伍是一个已经整合了的队伍";
                    return $return;
                }
            }
        }
        //开启事务
        DB::beginTransaction();
        $insertTeam = $totalTeamModel->insertTeam(['game'=>$teamInfo['game'],'original_source'=>$teamInfo['original_source']]);
        //创建成功
        if($insertTeam)
        {
            //合并入新增的映射里面
            $mergeToMap1 = $this->mergeToTeamMap($teamInfo, $insertTeam, $teamModel, $teamNameMapModel);
            if (!$mergeToMap1)
            {
                DB::rollBack();
                $return["result"] = false;
                $return["log"][] = "整合队伍1失败";
                return $return;
            }
            //合并入新增的映射里面
            $mergeToMap2 = $this->mergeToTeamMap($team2MergeInfo, $insertTeam, $teamModel, $teamNameMapModel);
            if (!$mergeToMap2)
            {
                DB::rollBack();
                $return["result"] = false;
                $return["log"][] = "整合队伍2失败";
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
            $return["log"][] = "创建整合队伍失败";
            return $return;
        }
    }
    //合并1个未整合过的队伍
    public function merge1unmergedTeam($teamid=0)
    {
        $return = ["result"=>false,"log"=>[]];
        $teamModel = new TeamModel();
        $teamNameMapModel = new TeamNameMapModel();
        $totalTeamModel = new TotalTeamModel();
        if($teamid<=0)
        {
            $return["result"] = false;
            $return["log"][] = "ID有误";
            return $return;
        }
        else
        {
            $teamInfo = $teamModel->getTeamById($teamid);
            if(!$teamInfo['team_id'])
            {
                $return["result"] = false;
                $return["log"][] = "队伍不存在";
                return $return;
            }
            else
            {
                if($teamInfo['tid']>0)
                {
                    $return["result"] = true;
                    $return["log"][] = "转入队伍是一个已经整合了的队伍";
                    return $return;
                }
            }
        }
        //开启事务
        DB::beginTransaction();
        $insertTeam = $totalTeamModel->insertTeam(['game'=>$teamInfo['game'],'original_source'=>$teamInfo['original_source']]);
        //创建成功
        if($insertTeam)
        {
            $return["log"][] = "创建整合队伍成功";
            //合并入查到的映射里面
            $mergeToMap = $this->mergeToTeamMap($teamInfo, $insertTeam, $teamModel, $teamNameMapModel);
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
            $return["log"][] = "创建整合队伍失败";
            return $return;
        }
    }
    //在总表中更新到新数据的映射
    public function addRedirect($totalTeamModel,$tid,$new_team_id=0,$new_tid=0)
    {
        $totalTeam = $totalTeamModel->getTeamById($tid,"tid,redirect");
        if(isset($totalTeam['tid']))
        {
            $totalTeam['redirect'] = json_decode($totalTeam['redirect'],true)??[];
            if($new_team_id>0)
            {
                $totalTeam['redirect']['team_id'] = $new_team_id;
            }
            else
            {
                unset($totalTeam['redirect']['team_id']);
            }
            if($new_tid>0)
            {
                $totalTeam['redirect']['tid'] = $new_tid;
            }
            else
            {
                unset($totalTeam['redirect']['tid']);
            }
            return $totalTeamModel->updateTeam($tid,$totalTeam);
        }
        else
        {
            return false;
        }
    }

    //通过scoregg站点的team_id获取战队的基础数据
    public function getScoreggTeamInfo($team_id=0){
        $scoregg_url='https://www.scoregg.com/big-data/team/'.$team_id;
        $qt=QueryList::get($scoregg_url);
        $victory_rate=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(0) .circle-inner-text .num')->text();//胜率
        $victory_rate_rank=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(0) .circle-text-des .light')->text();//联赛排名
        $kda=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(1) .kda-chart-container .circle-inner-text .num')->text();//kda
        $kda_detail=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(1) .industry-text')->text();//kda明细
        $kda_rank=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(1) .circle-text-des')->text();//kda
        //获取数据明细
        $data_list_item=$qt->rules(array(
            'score-num' => array('.score-num','text'),
            'score-des' => array('.score-des','text'),
            'score-rank' => array('.score-rank','text'),
        ))->range('.right-content .basic-show .basic-show-container .data-list-item .item')->queryData();
        $data_list_item=$data_list_item ?? [];
        $total_count=$win=$lose=0;
        $base_ability_detail=[];
        if(count($data_list_item)>0){
            foreach ($data_list_item as $key=>$val){
                if(strpos($val['score-des'],'比赛场次')!==false ){
                    $total_count=intval($val['score-num'])??0;
                    $win=intval($val['score-rank']);
                    $lose=($total_count-$win);
                    //用kda替换第一个元素比赛场次
                    $base_ability_detail['kda']['score-num']=$kda;
                    $base_ability_detail['kda']['score-des']='KDA';
                    $base_ability_detail['kda']['score-rank']=trim($kda_rank,' 联赛第 ');
                }
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

            }
        }
        //分解总击杀数，死亡数，助攻数
        $total_kills=$total_deaths=$total_assists=0;
        list($total_kills,$total_deaths,$total_assists)=explode(' / ',$kda_detail);
        //蓝方
        $blue_count=$blue_win_count=$blue_lose_count=0;
        $blue_victory_rate=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(2) .circle-inner-text .num')->text();//蓝方胜率
        $blue_race_stat=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(2) .circle-text')->text();//蓝方战绩
        $blue_count=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(2) .circle-text-des')->text();//蓝方场数
        $blue_count=str_replace(array('共 ',' 场'),'',$blue_count);
        $blue_win_count=intval($blue_race_stat);
        $blue_lose_count=intval($blue_count)-$blue_win_count;
        //红方
        $red_count=$red_win_count=$red_lose_count=0;
        $red_victory_rate=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(3) .circle-inner-text .num')->text();//蓝方胜率
        $red_race_stat=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(3) .circle-text')->text();//蓝方战绩
        $red_count=$qt->find('.right-content .basic-show .basic-show-container .circle:eq(3) .circle-text-des')->text();//蓝方场数
        $red_count=str_replace(array('共 ',' 场'),'',$red_count);
        $red_win_count=intval($red_race_stat);
        $red_lose_count=intval($red_count)-$red_win_count;

        $team_ability_and_base=[
            'victory_rate'=>trim($victory_rate,'%'),//胜率
            'victory_rate_rank'=>$victory_rate_rank,//排名
            'total_count'=>$total_count,//比赛场数
            'win'=>$win,//胜利场数
            'lose'=>$lose,//失败场数
            'total_kills'=>$total_kills,//总击杀数
            'total_deaths'=>$total_deaths,//总死亡数
            'total_assists'=>$total_assists,//总助攻数
            'base_ability_detail'=>$base_ability_detail,//基础数据明细
            'blue_victory_rate'=>trim($blue_victory_rate,'%'),//蓝方胜率
            'blue_count'=>$blue_count,//蓝方总场数
            'blue_win'=>$blue_win_count,//蓝方胜利场数
            'blue_lose'=>$blue_lose_count,//蓝方失败场数
            'red_victory_rate'=>trim($red_victory_rate,'%'),//红方胜率
            'red_count'=>$red_count,//红方总场数
            'red_win'=>$red_win_count,//红方胜利场数
            'red_lose'=>$red_lose_count,//红方失败场数
        ];
        return $team_ability_and_base;
    }

}
