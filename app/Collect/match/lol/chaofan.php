<?php

namespace App\Collect\match\lol;

use App\Libs\ClientServices;

class chaofan
{
    protected $data_map =
        [
            'list'=>[
                'home_score'=>['path'=>"home.score",'default'=>0],//主队得分
                'away_score'=>['path'=>"away.score",'default'=>0],//客队得分
                'home_id'=>['path'=>"home.team_id",'default'=>0],//主队id
                'away_id'=>['path'=>"away.team_id",'default'=>0],//客队id
                'match_name'=>['path'=>"title",'default'=>""],//比赛名称
                'logo'=>['path'=>"game_icon",'default'=>""],//logo
                "match_id"=>['path'=>"id",'default'=>""],//比赛唯一ID
                "tournament_id"=>['path'=>"tournament_id",'default'=>""],//赛事唯一ID
                "extra"=>['path'=>"extra_data",'default'=>[]],//额外信息
                "start_time"=>['path'=>"start_time",'default'=>[]],//开始时间

            ],
            'team'=>[
                'team_id'=>['path'=>"team_id",'default'=>0],//队伍ID
                'team_name'=>['path'=>"name",'default'=>0],//队伍名称
                'logo'=>['path'=>"logo",'default'=>''],//logo
            ],
            'tournament_info'=>[],
            'latest_news'=>[
                'event_id'=>['path'=>"id",'default'=>""],//事件ID
                'evnet_name'=>['path'=>"title",'default'=>""],//标题
                'type'=>['path'=>"type",'default'=>0],//类型
                'sub_type'=>['path'=>"sub_type",'default'=>0],//子分类
                'pics'=>['path'=>"thumbs",'default'=>[]],//关联图片列表

            ],
            'latest_video'=>[],
            'hot_schedules'=>[]
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
         * tournament_info:最近赛事列表
         * latest_video:最新视频
         * latest_news：最新新闻
         * hot_schedules:热点赛事
         * game_id：游戏id(1:lol;)
         */

        $data = ['match_list'=>[],'team'=>[],'event_list'=>[]];
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
        foreach($arr['content']['latest_news'] as $key => $value)
        {
            $value['thumbs'] = $value['thumbs']??[];
            $data['event_list'][$key] = getDataFromMapping($this->data_map['latest_news'],$value);
        }
        foreach($arr['content']['hot_schedules'] as $key => $value)
        {
            $start_time = date("Y-m-d H:i:s",$value['start_time']);
            $end_time = date("Y-m-d H:i:s",$value['end_time']);
            echo "name:".$value['title']."\n"."start_time:".$start_time."\n"."end_time:".$end_time."\n";
        }
        foreach($data['event_list'] as $key => $value)
        {
            if(count($value['pics'])>0)
            {
                foreach($value['pics'] as $k => $pic)
                {
                    $data['event_list'][$key]['pics'][$k] = getImage($pic);
                }
            }
        }
        print_R($data['match_list']);
        print_R($data['team']);
        die();
        print_R($arr['content']['tournament_info']);
        die();
        //print_R($arr['content']['hot_schedules']);
        die();
    }
}
