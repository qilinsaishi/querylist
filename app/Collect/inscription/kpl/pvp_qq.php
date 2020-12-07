<?php

namespace App\Collect\inscription\kpl;

use App\Libs\ClientServices;

class pvp_qq
{
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
         * 铭文数据
         * "ming_id" => "1504"
         * "ming_type" => "red"
         * "ming_grade" => "5"
         * "ming_name" => "异变"
         * "ming_des" => "<p>物理攻击力+2</p><p>物理穿透+3.6</p>"
         * 'ming_img'=>'https://game.gtimg.cn/images/yxzj/img201606/mingwen/1504.png'
         */
        var_dump($arr);
    }
}
