<?php

namespace App\Collect\match\dota2;

class gamedota2
{
    protected $data_map =
        [
            'tournament'=>[
                'game'=>['path'=>"",'default'=>"dota2"],//游戏
                'tournament_id'=>['path'=>"tournament_id",'default'=>''],//赛事ID
                'tournament_name'=>['path'=>"title",'default'=>''],//赛事名称
                'start_time'=>['path'=>"",'default'=>0],//开始时间
                'end_time'=>['path'=>"",'default'=>0],//开始时间
                'logo'=>['path'=>"logo",'default'=>''],//logo
                'pic'=>['path'=>"logo",'default'=>''],//关联图片
                'game_logo'=>['path'=>"",'default'=>''],//关联游戏图片
            ],
            'team'=>[
                'game'=>['path'=>"",'default'=>"dota2"],//游戏
                'site_id'=>['path'=>"id",'default'=>0],//队伍ID
                'team_name'=>['path'=>"name",'default'=>0],//队伍名称
                'logo'=>['path'=>"logo",'default'=>''],//logo
                'original_source'=>['path'=>"",'default'=>'gamedota2'],//初始来源
                'aka'=>['path'=>"",'default'=>''],//别名
            ],
            'list'=>[
                'gamedota2'=>[
                    'match_id'=>['path'=>"id",'default'=>0],//比赛唯一ID
                    'game'=>['path'=>"",'default'=>"dota2"],//游戏
                    'home_score'=>['path'=>"team1.score",'default'=>0],//主队得分
                    'away_score'=>['path'=>"team2.score",'default'=>0],//客队得分
                    'home_id'=>['path'=>"team1.id",'default'=>0],//主队id
                    'away_id'=>['path'=>"team2.id",'default'=>0],//客队id
                    'logo'=>['path'=>"game_icon",'default'=>""],//logo
                    "tournament_id"=>['path'=>"tournament_id",'default'=>""],//赛事唯一ID
                    "extra"=>['path'=>"extra",'default'=>[]],//额外信息
                    "start_time"=>['path'=>"timestamp",'default'=>[]]
                ],
                'bilibili'=>[
                    'match_id'=>['path'=>"id",'default'=>0],//比赛唯一ID
                    'game'=>['path'=>"",'default'=>"dota2"],//游戏
                    'home_score'=>['path'=>"home_score",'default'=>0],//主队得分
                    'away_score'=>['path'=>"away_score",'default'=>0],//客队得分
                    'home_id'=>['path'=>"home_team.id",'default'=>0],//主队id
                    'away_id'=>['path'=>"away_team.id",'default'=>0],//客队id
                    'logo'=>['path'=>"game_icon",'default'=>""],//logo
                    "tournament_id"=>['path'=>"tournament_id",'default'=>""],//赛事唯一ID
                    "extra"=>['path'=>"extra",'default'=>[]],//额外信息
                    "start_time"=>['path'=>"timestamp",'default'=>[]]
                    ],
                'international'=>[
                    'match_id'=>['path'=>"id",'default'=>0],//比赛唯一ID
                    'game'=>['path'=>"",'default'=>"dota2"],//游戏
                    'home_score'=>['path'=>"team1.score",'default'=>0],//主队得分
                    'away_score'=>['path'=>"team2.score",'default'=>0],//客队得分
                    'home_id'=>['path'=>"team1.id",'default'=>0],//主队id
                    'away_id'=>['path'=>"team2.id",'default'=>0],//客队id
                    'logo'=>['path'=>"game_icon",'default'=>""],//logo
                    "tournament_id"=>['path'=>"tournament_id",'default'=>""],//赛事唯一ID
                    "extra"=>['path'=>"extra",'default'=>[]],//额外信息
                    "start_time"=>['path'=>"timestamp",'default'=>""]
                    ],
                //开始时间
            ],
        ];

    public function collect($arr)
    {
        $cdata = [];
        $res = $url = $arr['detail'] ?? [];
        if (!empty($res)) {
            //处理战队采集数据
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
                'source_link' => '',
                'title' => $arr['title'] ?? '',
                'mission_type' => $arr['mission_type'],
                'source' => $arr['source'],
                'status' => 1,
            ];
            //处理战队采集数据

        }

        return $cdata;
    }
    public function process($arr)
    {
        $data = ['match_list'=>[],'team'=>[],'tournament'=>[]];
        if($arr['content']['type']=="tournament")
        {
            $arr['content']['tournament_id'] = md5($arr['content']['link']);
            $arr['content']['logo'] = getImage($arr['content']['logo']);
            $data['tournament'][] = getDataFromMapping($this->data_map['tournament'],$arr['content']);

        }
        elseif($arr['content']['type']=="match")
        {
            if(!isset($arr['content']['subtype']) || $arr['content']['subtype']=="gamedota2")
            {
                $arr['content']['team1']['logo'] = getImage($arr['content']['team1']['logo']);
                $arr['content']['team2']['logo'] = getImage($arr['content']['team2']['logo']);
                $data['team'][] = getDataFromMapping($this->data_map['team'],$arr['content']['team1']);
                $data['team'][] = getDataFromMapping($this->data_map['team'],$arr['content']['team2']);
                $arr['content']['tournament_id'] = md5($arr['content']['link']);
                $arr['content']['extra'] = [
                    "home"=> ['prize'=>$arr['content']['team1']['win_prize_num']??0,'title'=>$arr['content']['team1']['match_phase_title']??""],
                    "away"=> ['prize'=>$arr['content']['team2']['win_prize_num']??0,'title'=>$arr['content']['team2']['match_phase_title']??""],
                ];
                $arr['content']['timestamp'] = $arr['content']['date']." ".$arr['content']['time'];
                $data['match_list'][] = getDataFromMapping($this->data_map['list']['gamedota2'],$arr['content']);
            }
            elseif($arr['content']['subtype']=="bilibili")
            {
                //print_R($arr['content']);
                //die();
                $arr['content']['home_team']['logo'] = getImage($arr['content']['home_team']['logo']);
                $arr['content']['away_team']['logo'] = getImage($arr['content']['away_team']['logo']);
                $arr['content']['home_team']['name'] = $arr['content']['home_team']['title'];
                $arr['content']['away_team']['name'] = $arr['content']['away_team']['title'];
                $data['team'][] = getDataFromMapping($this->data_map['team'],$arr['content']['home_team']);
                $data['team'][] = getDataFromMapping($this->data_map['team'],$arr['content']['away_team']);
                $arr['content']['timestamp'] = date("Y-m-d H:i:s",$arr['content']['stime']);
                $arr['content']['tournament_id'] = md5($arr['content']['link']);
                $data['match_list'][] = getDataFromMapping($this->data_map['list']['bilibili'],$arr['content']);
            }
            elseif($arr['content']['subtype']=="international")
            {
                $arr['content']['team1']['logo'] = getImage($arr['content']['team1']['logo']);
                $arr['content']['team2']['logo'] = getImage($arr['content']['team2']['logo']);
                $data['team'][] = getDataFromMapping($this->data_map['team'],$arr['content']['team1']);
                $data['team'][] = getDataFromMapping($this->data_map['team'],$arr['content']['team2']);
                $arr['content']['tournament_id'] = md5($arr['content']['link']);
                $data['match_list'][] = getDataFromMapping($this->data_map['list']['international'],$arr['content']);
            }


        }
        return $data;
    }
}
