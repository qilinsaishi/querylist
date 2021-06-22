<?php

namespace App\Collect\team\dota2;

use App\Libs\ClientServices;
use App\Models\MissionModel;
use QL\QueryList;

class shangniu
{
    protected $data_map =
        [
            "team_name"=>['path'=>"teamName",'default'=>''],
            "en_name"=>['path'=>"en_name",'default'=>''],
            "team_full_name"=>['path'=>"team_full_name",'default'=>''],
            "location"=>['path'=>"location","default"=>"未知"],
            "established_date"=>['path'=>"",'default'=>"未知"],
            "coach"=>['path'=>"",'default'=>"暂无"],
            "logo"=>['path'=>"teamLogo",'default'=>''],
            "description"=>['path'=>"",'default'=>"暂无"],
            "race_stat"=>['path'=>"",'default'=>[]],
            "original_source"=>['path'=>"",'default'=>"shangniu"],
            "game"=>['path'=>"",'default'=>"dota2"],
            "site_id"=>['path'=>"teamId",'default'=>0],
            "team_history"=>['path'=>"",'default'=>[]],
            "team_stat"=>['path'=>"team_stat",'default'=>[]],
            "aka"=>['path'=>"",'default'=>[]],
        ];
    public function collect($arr)
    {
        $cdata=[];
        $url=$arr['detail']['url'] ?? $arr['source_link'];
        $res= $arr['detail'] ?? [];

        $teamInfo=$this->shangniuTeam($url,$res['teamId']);
        $res=array_merge($res,$teamInfo);

        if (is_array($res) && count($res)>0) {
            $cdata = [
                'mission_id' => $arr['mission_id'],//任务id
                'content' => json_encode($res),
                'game' => $arr['game'],//游戏类型
                'source_link' => $url,
                'title' => $arr['detail']['title'] ?? '',
                'mission_type' => $arr['mission_type'],
                'source' => $arr['source'],
                'status' => 1,
                'update_time' => date("Y-m-d H:i:s")
            ];
        }
        return $cdata;
    }
    public function process($arr)
    {print_r($arr);exit;
        $redis = app("redis.connection");
        $arr['content']['teamLogo'] = getImage($arr['content']['teamLogo']??'',$redis);
        $data = getDataFromMapping($this->data_map,$arr['content']);
        return $data;
    }
    /**
     * 来自https://www.shangniu.cn
     * @param $url
     * @return array
     */
    public function shangniuTeam($url,$teamId=0)
    {
        $res = [];
        $client = new ClientServices();
        $content=file_get_contents($url);
        //================================映射数据============================================
        $bodyStart = strpos($content, '(function(');
        $bodyEnd = strpos($content, '){return {layout');
        $functionIndexs = substr($content, $bodyStart, $bodyEnd - $bodyStart);
        $functionIndexs = str_replace(array('(function(', ')'), '', $functionIndexs);
        $functionIndexs=explode(',',$functionIndexs);
        //映射反转
        $functionIndexs = array_flip($functionIndexs);

        $serverStart = 'serverRendered:';
        $teamInfoStart=strpos($content, $serverStart);
        $teamInfoEnd=strripos($content, '));</script><script');
        $teamInfoString=substr($content,$teamInfoStart+strlen($serverStart),$teamInfoEnd-$teamInfoStart);
        $teamInfoString=substr($teamInfoString,strpos($teamInfoString,'}}(')+3);
        $teamInfoData=explode(',',$teamInfoString);
        foreach ($functionIndexs as $functionIndexKey=>$functionIndexValue){
            $functionIndexs[$functionIndexKey]=trim($teamInfoData[$functionIndexValue],'"');
        }
        //================================映射数据============================================

        $teamStatStart=strpos($content,'teamStatInfo:');
        $teamStatEnd=strpos($content,',commonHeroList');
        $teamStatContent=substr($content,$teamStatStart,$teamStatEnd-$teamStatStart);
        $teamStatContent=str_replace(array('teamStatInfo:{'),'',$teamStatContent);
        $teamStatContentList=explode(',',$teamStatContent);
        $tournamentId=0;
        if(is_array($teamStatContentList) && count($teamStatContentList)>0){
            foreach ($teamStatContentList as $teamStatContentInfo){
                if(strpos($teamStatContentInfo,'tournamentId:')!==false){
                    $tournamentId=str_replace('tournamentId:','',$teamStatContentInfo);
                    if(!is_numeric($tournamentId)){
                        $tournamentId=$functionIndexs[$tournamentId];
                    }
                }
            }
        }
        //===========================赛事战队统计数据=========================================
        $shangniu_url='https://www.shangniu.cn/api/game/user/team/getPcTournamentTeamStatInfo?gameType=dota&tournamentId='.$tournamentId.'&teamId='.$teamId;
        $shangniu_headers = ['referer' => $url];
        $getTournamentTeamStatInfo= $client->curlGet($shangniu_url, [],$shangniu_headers);
        $teamStat=$getTournamentTeamStatInfo['body'] ?? [];
        if(isset($teamStat['id'])){
            unset($teamStat['id']);
        }
        if(isset($teamStat['version'])){
            unset($teamStat['version']);
        }
        if(isset($teamStat['createdTime'])){
            unset($teamStat['createdTime']);
        }
        if(isset($teamStat['updatedTime'])){
            unset($teamStat['updatedTime']);
        }
        if(isset($teamStat['commonHeroList'])){
            unset($teamStat['commonHeroList']);
        }
        //===========================赛事战队统计数据=========================================


        $start=strpos($content,'<div class="wrap-box">');
        $end=strpos($content,'<footer class="footer sn-footer">');
        $html=substr($content,$start,$end-$start);
        //================战队基本信息=================================
        $teamInfo = QueryList::html($html)->rules([
            "team_full_name" => [".team-info-new .full-name","text"],
            "descList" => [".team-info-new .desc-list .item","texts"],
        ])->query(function ($item){
            $en_name=$location='';
            if(isset($item['descList']) && count($item['descList'])>0){
                foreach($item['descList'] as $descInfo){
                    if(strpos($descInfo,'英文名称') !==false){
                        $en_name=str_replace('英文名称：','',$descInfo);
                    }
                    if(strpos($descInfo,'所在地区')!==false){
                        $location=str_replace('所在地区：','',$descInfo);
                    }
                }
                unset($item['descList']);
            }
            $item['location']=$location;
            $item['en_name']=$en_name;
            return $item;
        })->getData();
        $teamInfo=$teamInfo->all();
        //================战队基本信息=================================
        //=====================战队所属队员列表=========================
        $playerList = QueryList::html($html)->rules([
            "player_logo" => [".logo-box img","src"],
            "player_name" => ["a","text"],
            "player_url" => ["a","href"],
            "position" => [".position","text"],
        ])->range('.left-box .sider:eq(0) .player-list .item')->query(function ($playerInfo){
            $playerInfo['player_url']='https://www.shangniu.cn'. $playerInfo['player_url'];
            return $playerInfo;
        })->getData();
        $playerList=$playerList->all();
        //=====================战队所属队员列表=========================

        $res=[
            'team_full_name'=>$teamInfo['team_full_name'] ?? '',
            'location'=>$teamInfo['location'] ?? '',
            'en_name'=>$teamInfo['en_name'] ?? '',
            'playerList'=>$playerList,
            'team_stat'=>$teamStat ?? [],
        ];

        return $res;
    }

    public function processMemberList($team_id,$arr)
    {

        $missionList = [];
        $missionModel=new MissionModel();
        if(isset($arr['content']['playerList']) && count($arr['content']['playerList'])>0)
        {
            foreach($arr['content']['playerList'] as $member)
            {
                $site_id = str_replace(array('https://www.shangniu.cn/esports/dota-player-','.html'),'',$member['player_url']);
                $params = [
                    'game' => 'dota2',
                    'mission_type' => 'player',
                    'source_link' => $member['player_url'] ?? '',
                ];
                $missionCount = $missionModel->getMissionCount($params);//过滤已经采集过的赛事任务
                if($missionCount==0){
                    $member['game']='dota2';
                    $member['source']='shangniu';
                    $member['team_id']=$team_id;
                    $member['current']=1;
                    $member['site_id']=$site_id;
                    $mission = [
                        'mission_type'=>"player",
                        "asign_to" => 1,
                        'mission_status'=>1,
                        'source_link' =>$member['player_url'] ?? '',
                        'title'=>'shangniu-'.$member['player_name'] ?? '',
                        'detail'=>json_encode($member),
                    ];
                    $missionList[] = $mission;
                }

            }
        }
        return $missionList;
    }
}
