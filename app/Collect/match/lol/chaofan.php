<?php

namespace App\Collect\match\lol;

use App\Libs\ClientServices;

class chaofan
{
    protected $data_map =
        [
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


        var_dump($arr);
    }
}
