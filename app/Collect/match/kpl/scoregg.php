<?php

namespace App\Collect\match\kpl;

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
    {/* {

             "source":"scoregg",//来源
             "type":"match",//比赛type=tournament 表示赛程
             "game":"lol",//游戏
             "status":"2",//status:0表示即将开始，1表示正在进行，2已结结束
         }*/

        var_dump($arr);
    }
}
