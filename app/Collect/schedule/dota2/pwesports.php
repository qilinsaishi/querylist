<?php

namespace App\Collect\schedule\dota2;

class pwesports
{
    protected $data_map =
        [
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

        var_dump($arr);
    }
}
