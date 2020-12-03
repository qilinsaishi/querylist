<?php

namespace App\Collect\summoner\kpl;

use App\Libs\ClientServices;

class pvp_qq
{
    //召唤师技能
    protected $data_map =
        [
        ];

    public function collect($arr)
    {
        $url = $arr['detail']['url'] ?? '';
        $client = new ClientServices();
        $res = $client->curlGet($url);//curl获取json数据
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

            return $cdata;
        }
    }

    public function process($arr)
    {
        /**
         * https://game.gtimg.cn/images/yxzj/img201606/summoner/80104.jpg  召唤师技能小图 （80104是召唤师id）
         * https://game.gtimg.cn/images/yxzj/img201606/summoner/80104-big.jpg  召唤师技能大图
         * summoner_id=>召唤师id,summoner_name=>召唤师名称,summoner_rank=>召唤师技能解锁，summoner_description=>描述
         */

        var_dump($arr);
    }
}
