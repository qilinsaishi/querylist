<?php

namespace App\Services;

use App\Libs\AjaxRequest;
use App\Models\CollectResultModel;
use App\Models\CollectUrlModel;
use App\Models\Match\scoregg\tournamentModel;
use App\Models\MissionModel;
use App\Models\TeamModel;
use App\Services\MissionService as oMission;
use App\Models\Team\TotalTeamModel as TotalTeamModel;
use App\Models\Team\TeamMapModel as TeamMapModel;
use App\Models\Team\TeamNameMapModel as TeamNameMapModel;
use Illuminate\Support\Facades\DB;
use QL\QueryList;

class TeamResultService
{
    public function insertTeamData($mission_type,$game)
    {
        $this->getScoreggTeamDetail($game);
        //采集玩加（www.wanplus.com）战队信息
         $this->insertWanplus($game, $mission_type);
        //采集cpseo（2cpseo.com）战队信息
         $this->insertCpseoTeam($game, $mission_type);


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
                        echo "lol-information-cpseo-insert:" . $insert . ' lenth:' . strlen($data['detail']) . "\n";
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
    public function intergration($id = 0)
    {
        $totalTeamModel = new TotalTeamModel();
        $teamMapModel = new TeamMapModel();
        $teamNameMapModel = new TeamNameMapModel();
        $teamModel = new TeamModel();

        $return = false;
        if($id==0)
        {
            $teamList = $teamModel->getTeamList([/*"source"=>config("app.default_source.team"),*/"fields"=>"team_id,team_name,en_name,aka,original_source,game","tid"=>0,"page_size"=>1000]);
        }
        else
        {
            $teamInfo = $teamModel->getTeamById($id);
            $teamList = [$teamInfo];
        }
        foreach($teamList as $teamInfo)
        {
            if(isset($teamInfo['team_id']))
            {
                echo "start to process team:".$teamInfo['team_id']."\n";
                //如果当前来源不相同于默认来源
                if($teamInfo['original_source']==config("app.default_source.team"))
                {
                    //尝试获取总表到映射表的对应关系
                    $currentMap = $teamMapModel->getTeamByTeamId($teamInfo['team_id']);
                    //如果没取到
                    if(!isset($currentMap['tid']))
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
                                    echo "merged ".$teamInfo['team_id']." to ".$insertTeam."\n";
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
                            //echo "insertTeamError";
                            DB::rollBack();
                        }
                    }
                    else//找到映射
                    {
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
                                echo "merged ".$teamInfo['team_id']." to ".$currentMap['tid']."\n";
                                DB::commit();
                            }
                            else
                            {
                                DB::rollBack();
                            }
                        }
                    }
                }
                else
                {
                    //根据名称找到映射
                    $name = $this->generageNameHash($teamInfo['team_name']);
                    $currentMap = $teamNameMapModel->getTeamByNameHash($name,$teamInfo['game']);
                    if(isset($currentMap['tid']))
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
                            $updateTeam = $teamModel->updateTeam($teamInfo['team_id'],["tid"=>$currentMap['tid'],"original_source"=>$teamInfo['original_source']]);
                            if($updateTeam)
                            {
                                echo "merged ".$teamInfo['team_id']." to ".$currentMap['tid']."\n";
                                DB::commit();
                            }
                            else
                            {
                                DB::rollBack();
                            }
                        }
                    }
                    else//没有匹配上 创建
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
                                    echo "merged ".$teamInfo['team_id']." to ".$insertTeam."\n";
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
                            //echo "insertTeamError";
                            DB::rollBack();
                        }
                    }
                }
            }

        }

        //return $return;
    }
    function generageNameHash($name = "")
    {
        $name = strtolower($name);
        $name = trim($name);
        $replaceList = [" ","."];
        foreach($replaceList as $key)
        {
            $name = str_replace($key,"",$name);
        }
        echo "hash:".$name."\n";
        return md5($name);
    }
    //把对于合并到已经查到的队伍映射
    function mergeToTeamMap($teamInfo = [],$tid,$teamMapModel,$teamNameMapModel)
    {
        $insertMap = $teamMapModel->insertMap(["tid"=>$tid,"team_id"=>$teamInfo['team_id']]);
        if($insertMap)
        {
            $aka = json_decode($teamInfo['aka'], true);
            $nameList = (array_merge([$teamInfo['team_name'], $teamInfo['en_name']], $aka));
            foreach ($nameList as $key => $name)
            {
                if ($name == "")
                {
                    unset($nameList[$key]);
                }
                else
                {
                    $nameList[$key] = $this->generageNameHash($name);
                }
            }
            $nameList = array_unique($nameList);
            foreach ($nameList as $name)
            {
                //保存名称映射
                $saveMap = $teamNameMapModel->saveMap(["name_hash" => $name, "game" => $teamInfo['game'], "tid" => $tid]);
                if (!$saveMap) {
                    //echo "insertTeamMapError";
                    return false;
                    //break;
                }
            }
            return true;
        }
    }
}
