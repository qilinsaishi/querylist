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
        $data = ['match_list'=>[],'tournament'=>[]];
        if($arr['content']['type']=="tournament")
        {
            $arr['content']['tournament_id'] = md5($arr['content']['link']);
            $arr['content']['logo'] = getImage($arr['content']['logo']);
            $data['tournament'][] = getDataFromMapping($this->data_map['tournament'],$arr['content']);
        }
        else
        {
            return [];
        }
        return $data;
    }
}
