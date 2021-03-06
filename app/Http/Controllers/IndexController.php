<?php

namespace App\Http\Controllers;

use App\Libs\AjaxRequest;
use App\Libs\ClientServices;
use App\Models\CollectResultModel;
use App\Models\Admin\DefaultConfig;
use App\Models\DataMapModel;
use App\Models\PlayerModel;
use App\Models\TeamModel;
use App\Models\Team\TotalTeamModel;
use App\Services\Data\DataService;
use App\Services\Data\ExtraProcessService;
use App\Services\Data\IntergrationService;
use App\Services\Data\PrivilegeService;
use App\Services\Data\RedisService;
use App\Services\MatchService;
use App\Services\TeamService;
use App\Services\PlayerService;
use Illuminate\Http\Request;


use QL\QueryList;
use GuzzleHttp\Client;


class IndexController extends Controller
{

    public function index()
    {

    }

    public function get()
    {
        $data=$this->payload;
        $return = (new DataService())->getData($data);
        return $return;
    }
    public function getIntergration()
    {
        $data=$this->payload;
        switch($data['type'])
        {
            case "team":
                if(isset($data['team_id']))
                {
                    $return = (new IntergrationService())->getTeamInfo($data['team_id'],0,1,1);
                }
                elseif(isset($data['tid']))
                {
                    $return = (new IntergrationService())->getTeamInfo(0,$data['tid'],1,1);
                }
                else
                {
                    $return = [];
                }
                break;
            case "player":
                if(isset($data['player_id']))
                {
                    $return = (new IntergrationService())->getPlayerInfo($data['player_id'],0,1,1);
                }
                elseif(isset($data['pid']))
                {
                    $return = (new IntergrationService())->getPlayerInfo(0,$data['pid'],1,1);
                }
                else
                {
                    $return = [];
                }
                break;
            case "teamList":
                $intergrationService = new IntergrationService();
                $data["game"] = $data["game"]??"lol";
                $teamList = (new TotalTeamModel())->getTeamList(["game"=>$data["game"],"page"=>$data["page"]??1,"page_size"=>$data['pageSize']??100]);
                $data["fields"] = $data["fields"]??"tid,team_name,en_name,cn_name,team_full_name,intergrated_id_list";
                $return = [];
                foreach($teamList as $team)
                {
                    $return[] = getFieldsFromArray($intergrationService->getTeamInfo(0, $team['tid'], 1)['data'], $data["fields"]);
                }
                break;
            case "playerList_team_id":
                $intergrationService = new IntergrationService();
                $data["team_id"] = $data["team_id"]??0;
                $teamInfo = (new TeamModel)->getTeamById($data["team_id"],"team_id,tid");
                $teamList = (new TeamModel())->getTeamList(["tid"=>$teamInfo['tid'],"fields"=>"team_id,tid","page"=>$data["page"]??1,"page_size"=>$data['pageSize']??100]);
                $playerList = (new PlayerModel())->getPlayerList(["fields"=>"player_id,pid","team_ids"=>array_unique(array_column($teamList,"team_id")),"page"=>$data["page"]??1,"page_size"=>$data['pageSize']??100]);
                $data["fields"] = $data["fields"]??"pid,player_name,en_name,cn_name,intergrated_id_list";
                $return = [];
                foreach($playerList as $player)
                {
                    if($player['pid']>0 && !isset($return[$player['pid']]))
                    {
                        $playerInfo = $intergrationService->getPlayerInfo(0, $player['pid'], 1);
                        $return[$player['pid']] = getFieldsFromArray($intergrationService->getPlayerInfo(0, $player['pid'], 1)['data'], $data["fields"]);
                    }
                }
                $return = array_values($return);
                break;
            case "playerList_tid_id":
                $intergrationService = new IntergrationService();
                $data["tid"] = $data["tid"]??0;
                $teamList = (new TeamModel())->getTeamList(["tid"=>$data["tid"],"fields"=>"team_id,tid","page"=>$data["page"]??1,"page_size"=>$data['pageSize']??100]);
                $playerList = (new PlayerModel())->getPlayerList(["fields"=>"player_id,pid","team_ids"=>array_unique(array_column($teamList,"team_id")),"page"=>$data["page"]??1,"page_size"=>$data['pageSize']??100]);
                $data["fields"] = $data["fields"]??"pid,player_name,en_name,cn_name,intergrated_id_list";
                $return = [];
                foreach($playerList as $player)
                {
                    if($player['pid']>0)
                    {
                        $playerInfo = $intergrationService->getPlayerInfo(0, $player['pid'], 1);
                        $return[] = getFieldsFromArray($intergrationService->getPlayerInfo(0, $player['pid'], 1)['data'], $data["fields"]);
                    }
                }
                break;
        }
        return $return;
    }
    public function intergration()
    {
        $data=$this->payload;
        switch($data['type'])
        {
            case "mergeTeam2mergedTeam":
                $return = (new TeamService())->mergeTeam2mergedTeam($data['tid'],$data['team_id']);
                break;
            case "merge2mergedTeam":
                $return = (new TeamService())->merge2mergedTeam($data['tid_1'],$data['tid_2']);
                break;
            case "merge2unmergedTeam":
                $return = (new TeamService())->merge2unmergedTeam($data['team_id_1'],$data['team_id_2']);
                break;
            case "merge1unmergedTeam":
                $return = (new TeamService())->merge1unmergedTeam($data['team_id']);
                break;
            case "mergePlayer2mergedPlayer":
                $return = (new PlayerService())->mergePlayer2mergedPlayer($data['pid'],$data['player_id']);
                break;
            case "merge2mergedPlayer":
                $return = (new PlayerService())->merge2mergedPlayer($data['pid_1'],$data['pid_2']);
                break;
            case "merge2unmergedPlayer":
                $return = (new PlayerService())->merge2unmergedPlayer($data['player_id_1'],$data['player_id_2']);
                break;
            case "merge1unmergedPlayer":
                $return = (new PlayerService())->merge1unmergedPlayer($data['player_id']);
                break;
        }
        return $return;
    }

    public function refresh()
    {
        $redisService = new RedisService();
        $dataType = $this->request->get("dataType","defaultConfig");
        $keyName= $this->request->get("key_name","");
        $params= $this->request->get("params",'[]');
        $data = $redisService->refreshCache($dataType,json_decode($params,true),$keyName);
        $return=[];
        if($data) {
            $return = (new DataService())->getData($data);
        }
        return $return;
    }
    public function refreshGame()
    {
        $redisService = new RedisService();
        $game = $this->request->get("game","lol");
        $match_id = $this->request->get("match_id",13436);
        $source = in_array($game,["lol","kpl"])?"scoregg":"shangniu";
        $params = ["matchDetail"=>["dataType"=>"matchDetail","source"=>$source,"match_id"=>$match_id,"game"=>$game],"reset"=>1];

        $functionList = (new PrivilegeService())->getFunction($params);
        if(count($functionList))
        {
            $class = current($functionList);
            $function = $class['function'];
            $matchDetail = $class['class']->$function($match_id);
            $rt=0;
            if(isset($matchDetail['match_id']))
            {
                if($source=="scoregg")
                {
                    $res=(new MatchService())->updateOneScoreggMatchList($match_id,$game,$matchDetail['next_try'],$matchDetail['try']);
                    $rt= $res['result']??0;
                    return  $rt;
                }
                elseif($source=="shangniu")
                {
                    $res=(new MatchService())->updateOneShangMatchList($match_id,$game,$matchDetail['next_try'],$matchDetail['try'],$matchDetail['tournament_id'],$matchDetail['home_display'],$matchDetail['away_display']);
                    $rt= $res['result']??0;
                    return $rt;
                }
            }
        }

    }
    public function createMission(){
        $game = $this->request->get("game","lol");
        $site_id = $this->request->get("site_id",0);
        $type=$this->request->get("type","team");
        $source=$game=="dota2"?"shangniu":"scoregg";
        $mission_id=0;
        if($type=='team'){
            $mission_id=(new TeamService())->createTeamMission($game,$site_id,$source);
        }
        if($type=='player'){
            $mission_id=(new PlayerService())->createPlayerMission($game,$site_id,$source);
        }
        return $mission_id;
    }
    public function truncate()
    {
        $redisService = new RedisService();
        $prefix = $this->request->get("prefix","");
        $return = $redisService->truncate($prefix);
        return $return;
    }
    public function submit()
    {
        $oModel = new DataMapModel();
        $data = $this->payload;
        if(isset($data['site_id']))
        {
            return ['result'=>$oModel->saveMap($data)];
        }
        else
        {
            return ['result'=>false];
        }
    }
    public function sitemap()
    {
        $data=$this->payload;
        $return = (new DataService())->siteMap($data);
        return $return;
    }

}
