<?php

namespace App\Collect\lol_qq\lol;

class hero
{
    protected $data_map =
        [
        ];
    //lol 英雄数据接口

    public function collect($arr)
    {
        $cdata = [];
        $url = $arr['detail']['url'] ?? '';
        $res = curl_get($url);
        $res = $res['hero'] ?? [];
        if (!empty($res)) {
            $res['show_list_img'] = 'https://game.gtimg.cn/images/lol/act/img/champion/' . $res['alias'] . '.png';
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

            return $cdata;
        }

    }

    public function process($arr)
    {
        var_dump($arr);
    }
}
