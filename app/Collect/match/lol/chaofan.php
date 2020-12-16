<?php

namespace App\Collect\match\lol;

use App\Libs\ClientServices;

class chaofan
{
    protected $data_map =
        [
            'list'=>[
                'game'=>['path'=>"",'default'=>"lol"],//游戏
                'home_score'=>['path'=>"home.score",'default'=>0],//主队得分
                'away_score'=>['path'=>"away.score",'default'=>0],//客队得分
                'home_id'=>['path'=>"home.team_id",'default'=>0],//主队id
                'away_id'=>['path'=>"away.team_id",'default'=>0],//客队id
                'logo'=>['path'=>"game_icon",'default'=>""],//logo
                "match_id"=>['path'=>"id",'default'=>""],//比赛唯一ID
                "tournament_id"=>['path'=>"tournament_id",'default'=>""],//赛事唯一ID
                "extra"=>['path'=>"extra_data",'default'=>[]],//额外信息
                "start_time"=>['path'=>"start_time",'default'=>[]],//开始时间

            ],
            'tournament'=>[
                'game'=>['path'=>"",'default'=>"lol"],//游戏
                'tournament_id'=>['path'=>"id",'default'=>''],//赛事ID
                'tournament_name'=>['path'=>"title",'default'=>''],//赛事名称
                'start_time'=>['path'=>"",'default'=>0],//开始时间
                'end_time'=>['path'=>"",'default'=>0],//开始时间
                'logo'=>['path'=>"",'default'=>''],//logo
                'pic'=>['path'=>"",'default'=>''],//关联图片
                'game_logo'=>['path'=>"",'default'=>''],//关联游戏图片
            ],
            'team'=>[
                'game'=>['path'=>"",'default'=>"lol"],//游戏
                'team_id'=>['path'=>"team_id",'default'=>0],//队伍ID
                'team_name'=>['path'=>"name",'default'=>0],//队伍名称
                'logo'=>['path'=>"logo",'default'=>''],//logo
            ],
            'latest_news'=>[
                'game'=>['path'=>"",'default'=>"lol"],//游戏
                'event_id'=>['path'=>"id",'default'=>""],//事件ID
                'event_title'=>['path'=>"title",'default'=>""],//标题
                'type'=>['path'=>"type",'default'=>0],//类型
                'sub_type'=>['path'=>"sub_type",'default'=>0],//子分类
                'pics'=>['path'=>"thumbs",'default'=>[]],//关联图片列表
                'published_time'=>['path'=>"published_at",'default'=>''],//发布时间

            ],
            'latest_video'=>[],
            'hot_schedules'=>[
                'game'=>['path'=>"",'default'=>"lol"],//游戏
                'tournament_id'=>['path'=>"id",'default'=>''],//赛事ID
                'tournament_name'=>['path'=>"title",'default'=>''],//赛事名称
                'start_time'=>['path'=>"start_time",'default'=>0],//开始时间
                'end_time'=>['path'=>"end_time",'default'=>0],//开始时间
                'logo'=>['path'=>"logo",'default'=>''],//logo
                'pic'=>['path'=>"thumb",'default'=>''],//关联图片
                'game_logo'=>['path'=>"game_icon",'default'=>''],//关联游戏图片
            ]
        ];

    protected $status_list = [
        0=>'全部',1=>'即将开始',2=>'正在进行',3=>'已经结束'
    ];

    public function collect($arr)
    {
        $url = $arr['detail']['url'] ?? '';
        $client = new ClientServices();
        $res = [];
        $cdata = [];
        $result = $client->curlGet($url);
        if ($result['code'] == 0) {
            $res = $result['data'] ?? [];
        }
        if (!empty($res)) {
            $cdata = [
                'mission_id' => $arr['mission_id'],
                'content' => json_encode($res),
                'game' => $arr['game'],
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
    {
        /**
         * //tournament_info:最近赛事列表
         * latest_video:最新视频
         * //latest_news：最新新闻
         * hot_schedules:热点赛事
         * game_id：游戏id(1:lol;)
         */
        
        $data = ['match_list'=>[],'team'=>[],'event'=>[],'tournament'=>[]];
        foreach($arr['content']['list'] as $key => $value)
        {
            $value['extra_data'] = json_decode($value['extra_data']??"[]",true);
            $value['start_time'] = date("Y-m-d H:i:s",$value['start_time']);
            $value['home']['logo'] = getImage($value['home']['logo']);
            $value['away']['logo'] = getImage($value['away']['logo']);
            $data['team'][$value['home']['team_id']] = getDataFromMapping($this->data_map['team'],$value['home']);
            $data['team'][$value['away']['team_id']] = getDataFromMapping($this->data_map['team'],$value['away']);
            $value['game_icon'] = getImage($value['game_icon']);
            $data['match_list'][$key] = getDataFromMapping($this->data_map['list'],$value);
        }
        foreach($arr['content']['tournament_info'] as $key => $value)
        {
           $data['tournament'][$key] = getDataFromMapping($this->data_map['tournament'],$value);
        }
        foreach($arr['content']['hot_schedules'] as $key => $value)
        {
            $value['logo'] = getImage($value['logo']);
            $value['thumb'] = getImage($value['thumb']);
            $value['game_icon'] = getImage($value['game_icon']);
            $data['tournament'][] = getDataFromMapping($this->data_map['hot_schedules'],$value);
        }
        foreach($arr['content']['latest_news'] as $key => $value)
        {
            $value['thumbs'] = $value['thumbs']??[];
            if(count($value['thumbs'])>0)
            {
                foreach($value['thumbs'] as $k => $pic)
                {
                    $value['thumbs'][$k] = getImage($pic);
                }
            }
            $value['published_at'] = date("Y-m-d H:i:s",$value['published_at']);
            $data['event'][$key] = getDataFromMapping($this->data_map['latest_news'],$value);
        }
        return $data;
    }
}
