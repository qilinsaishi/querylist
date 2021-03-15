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
        $type = $arr['detail']['type'] ?? '';
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
            tournamentID 赛事id
            tournament_name  赛事名称
            tournament_image_thumb 赛事缩略图
            start_time_string: "03-24 16:00"  开始时间
            Best of 3  =>game_count: "3"
            status 状态：//status:0表示即将开始，1表示正在进行，2已结结束
            round_name 赛事下面的一级组
            round_son_name: 组下面的二级组
            matchID 比赛id
            teamID_a
            team_a_name
            team_a_image
            team_a_image_thumb
            team_a_win 比分a
            teamID_b
            team_b_name
            team_b_image
            team_b_image_thumb
            team_b_win 比分b
            result_list =array();有值则包含了队员英雄
            "source":"scoregg",//来源
            "type":"match",//比赛type=tournament 表示赛程
            "game":"lol",//游戏
            "status":"2",//status:0表示即将开始，1表示正在进行，2已结结束
         }*/

        var_dump($arr);
    }


}
