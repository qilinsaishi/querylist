<?php

namespace App\Collect\match\lol;

class scoregg
{
    protected $data_map =
        [
        ];
    public function collect($arr)
    {
        $cdata = [];
        $res = $url = $arr['detail'] ?? [];
        $type=$arr['detail']['type'] ?? '';
        if (!empty($res)) {
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
            //处理战队采集数据

        }

        return $cdata;

    }
    public function process($arr)
    {
        /* {
             "matchID":"11444",//比赛id
             "start_time":"1609493400",//开始时间
             "match_date":"2021-01-01",//比赛日期
             "match_time":"17:30",//比赛时间
             "status":"2",//status:0表示即将开始，1表示正在进行，2已结结束
             "teamID_a":"732",//战队a
             "teamID_b":"631",//战队b
             "team_a_win":"1",//战队a比分
             "team_b_win":"0",//战队b比分
             "team_short_name_a":"369",//战队a名称
             "team_short_name_b":"beishang",//战队b名称
             "team_image_thumb_a":"https://img1.famulei.com/z/2373870/p/2012/0711050319207_100X100.png",//战队a logo
             "team_image_thumb_b":"https://img1.famulei.com/z/2373870/p/1911/2516453877801_100X100.png",//战队b logo
             "tournament_name":"2020LPL全明星周末",//赛事名称
             "homesite_a":"0",
             "homesite_b":"0",
             "homesite":"",
             "is_publist":0,
             "live_video_url1":"https://img1.famulei.com/z/2373870/p/211/0116440477593.jpg?x-oss-process=image/resize,m_fill,h_416,w_760",
             "game_count":"1",//游戏阶段
             "is_have_video_link":"1",
             "title":"",
             "r_type":"0",//赛事下面的阶段类型
             "roundID":"426",//赛事下面的一级分类id
             "round_name":"荣耀日",//赛事下面的一级分类名称
             "tournamentID":"186",//赛事id
             "source":"scoregg",//来源
             "type":"match",//比赛type=tournament 表示赛程
             "game":"lol",//游戏
             "round_son_id":"",//赛事下面的二级分类id
             "round_son_pid":"",//赛事下面的一级分类id
             "round_son_name":"",//赛事下面的二级分类名称
         }*/

        var_dump($arr);
    }


}
