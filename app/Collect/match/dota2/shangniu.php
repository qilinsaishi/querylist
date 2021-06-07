<?php

namespace App\Collect\match\dota2;

use App\Libs\ClientServices;

class shangniu
{
    protected $data_map =
        [
            'tournament'=>[
                'game'=>['path'=>"",'default'=>"dota2"],//游戏
                'tournament_id'=>['path'=>"tournamentId",'default'=>''],//赛事ID
                'tournament_name'=>['path'=>"tournamentName",'default'=>''],//赛事名称
                'start_time'=>['path'=>"startTime",'default'=>0],//开始时间
                'end_time'=>['path'=>"endTime",'default'=>0],//开始时间
                'logo'=>['path'=>"tournamentLogo",'default'=>''],//logo
                'status'=>['path'=>"status",'default'=>'0'],//关联图片
                'game_logo'=>['path'=>"",'default'=>''],//关联游戏图片
            ],

            'list'=>[
                'match_id'=>['path'=>"id",'default'=>0],//比赛唯一ID
                'match_status'=>['path'=>"status",'default'=>0],//比赛状态
                'game'=>['path'=>"",'default'=>"dota2"],//游戏
                'home_score'=>['path'=>"homeScore",'default'=>0],//主队得分
                'away_score'=>['path'=>"awayScore",'default'=>0],//客队得分
                'home_id'=>['path'=>"homeId",'default'=>0],//主队id
                'away_id'=>['path'=>"awayId",'default'=>0],//客队id
                'logo'=>['path'=>"",'default'=>""],//logo
                'home_logo'=>['path'=>"homeLogo",'default'=>""],//主队logo
                'away_logo'=>['path'=>"awayLogo",'default'=>""],//客队logo
                'home_name'=>['path'=>"homeName",'default'=>''],//主队名称
                'away_name'=>['path'=>"awayName",'default'=>''],//主队名称
                "tournament_id"=>['path'=>"tournamentId",'default'=>""],//赛事唯一ID
                "start_time"=>['path'=>"matchTime",'default'=>[]],
                "match_pre"=>['path'=>"match_pre",'default'=>[]],//赛前数据
                "match_live"=>['path'=>"livedata",'default'=>[]],//赛事进程
                "match_data"=>['path'=>"match_data",'default'=>[]],//赛事数据
                // "round"=>['path'=>"round_list",'default'=>[]]//轮次数据
            ]
        ];
    public function collect($arr)
    {
        $client = new ClientServices();
        $cdata = [];
        $res =$arr['detail'] ?? [];
        $type = $arr['detail']['type'] ?? '';

        if($type=='match'){//赛程
            $res['matchTime']=date("Y-m-d H:i:s",substr($res['matchTime'],0,-3));
            $referer_url='https://www.shangniu.cn/esports/dota-live-'.$res['id'].'.html?tab=1';
            //战队信息分析
            $team_base_url='https://www.shangniu.cn/api/game/user/match/getMatchProspect?matchId='.$res['id'].'&gameType=dota';
            $headers = ['referer' => $referer_url];
            $teamBaseData= $client->curlGet($team_base_url, [],$headers);

            print_r($teamBaseData);exit;
        }else{//赛事
            if($res['status']==0){
                $res['status']=4;
            }
            if($res['endTime']==null){
                $res['endTime']=0;
            }
        }
        if (count($res)>0) {
            //处理战队采集数据
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link' => $arr['source_link'],
                'title' => $arr['title'] ?? '',
                'mission_type' => $arr['mission_type'],
                'source' => $arr['source'],
                'status' => 1,
            ];

        }
        return $cdata;
    }
    public function process($arr)
    {

        $redis = app("redis.connection");
        $data = ['tournament'=>[],'match_list'=>[]];
        if($arr['content']['type']=="tournament")
        {
            $arr['content']['startTime']=strtotime(date('Y-m-d H:i:s', $arr['content']['startTime'])) ?? 0;
            $arr['content']['endTime']=strtotime(date('Y-m-d H:i:s', $arr['content']['endTime'])) ?? 0;
            $arr['content']['tournamentLogo'] = (isset($arr['content']['tournamentLogo']) && $arr['content']['tournamentLogo']!='') ?getImage($arr['content']['tournamentLogo'],$redis):'';
            $data['tournament'][] = getDataFromMapping($this->data_map['tournament'],$arr['content']);

        }
        else
        {
            $currentKeyList = array_column($this->data_map['list'],'path');
            $keyList = array_keys($arr['content']);
            $arr['content']['match_data'] = [];
            $arr['content']['start_time'] = date("Y-m-d H:i:s",$arr['content']['start_time']);
            foreach($keyList as $key)
            {
                if(!in_array($key,$currentKeyList))
                {
                    $arr['content']['match_data'][$key] = $arr['content'][$key];
                    unset($arr['content'][$key]);
                }
            }

          //  $arr['content']['match_data'] = $this->processImg($arr['content']['match_data'],$redis);
            $data['match_list'][] = getDataFromMapping($this->data_map['list'],$arr['content']);
        }
        return $data;
    }
}
